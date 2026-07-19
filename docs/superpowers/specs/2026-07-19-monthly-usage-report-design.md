# Monthly Activity & Item-Usage Report — Design

**Date:** 2026-07-19
**Status:** Approved (design), pending implementation plan

## Problem

Client wants: every time a **spare part, consumable, or tool** is used, it is recorded, and a **monthly report** shows (a) what work was done and (b) what items were consumed. The report should also be produced automatically each month.

## Current state (why this isn't free today)

- **Spare parts** — already fully tracked. `MaintenanceRecord` captures `parts` used → `maintenance_record_parts` line items + `StockService::deduct` writes the `stock_movements` ledger (locked, negative-guarded).
- **Consumables** — `qty_actual` edited manually in the form. **No usage record exists.**
- **Tools** — `qty_available`/`condition` only. **No usage record exists.**
- Existing report module `ScheduleReportController` renders a live page + DomPDF export (`schedule-report/pdf/*`). DomPDF (`barryvdh/laravel-dompdf`) is installed.
- Laravel scheduler is already active in `routes/console.php` with daily `cmms:*` commands.
- Default filesystem disk is `local` — **ephemeral on Laravel Cloud.**

**Core insight:** the report is the easy half. The real work is *capturing* consumable/tool usage, which does not exist yet.

## Decisions (from client)

- Capture usage **inside the Maintenance Record** (same place spare parts are logged) — one consistent, work-linked capture point. No separate "Pemakaian Barang" menu.
- **Tools** = usage log only ("dipakai di pekerjaan"), no checkout/return, no stock change.
- Report generation = **both** a live on-demand page *and* an automatic monthly snapshot archive.
- Activities in the report: **Work Orders, Maintenance Records, Checksheet Sessions, Findings.**

## Design

### Phase 1 — Capture consumable & tool usage (the missing half)

Mirror the existing `maintenance_record_parts` pattern.

**New tables**
- `maintenance_record_consumables`: `id`, `maintenance_record_id` (FK, cascade), `consumable_id` (FK), `qty_used` (int), `unit_price` (decimal 15,2 nullable, snapshot at time of use), `timestamps`.
- `maintenance_record_tools`: `id`, `maintenance_record_id` (FK, cascade), `tool_id` (FK), `timestamps`. Usage log only — no qty.

**Models**
- `MaintenanceRecordConsumable`, `MaintenanceRecordTool` (belongsTo record + item).
- `MaintenanceRecord`: add `consumables()` and `tools()` hasMany relations.

**Stock deduction**
- Consumables are truly consumed → decrement `Consumable.qty_actual` with the same locked/negative-guarded pattern as `StockService`. Add `StockService::deductConsumable(Consumable, qty, reason, userId)` (or a small `ConsumableStockService`) that locks the row, guards against negative, decrements. No ledger table for consumables in Phase 1 — the `maintenance_record_consumables` line items (joined to the record date) are the audit trail the report reads.
- Tools: insert a usage row only. No stock change.

**Controller** (`MaintenanceRecordController::store`/`update`)
- Extend validation: `consumables.*.consumable_id`+`qty_used`, `tools.*.tool_id`.
- Filter empty rows (same as parts).
- Inside the existing transaction: create line items, deduct consumables, catch the out-of-stock exception the same way spare parts do.
- On `update`: reverse prior consumable deductions before re-applying (match however parts handle edits; if parts don't currently reverse on edit, keep consumables consistent with parts' behavior to avoid surprises — note this in the plan).

**Views**
- `maintenance-records/create` + `edit`: add a consumable picker (item + qty, like the spare-part rows) and a tool picker (item only). Provide `$consumables` and `$tools` from the controller.
- `maintenance-records/show` + the record PDF: list consumables and tools used.

### Phase 2 — Reports menu (live page + PDF)

**Route/controller:** new `ReportController::index` + `ReportController::exportPdf`. Route group `reports` (auth). New top-level **Reports** nav item in `layouts/app.blade.php`.

**Filters:** month, year, location (default = current month/year, all locations). Follow `ScheduleReportController`'s filter style.

**Sections**
- *Aktivitas:*
  - Work Orders in month (by `due_date`/relevant date) + status counts.
  - Maintenance Records in month (by `maintenance_date`) + result/status.
  - Checksheet Sessions filled in month.
  - Findings recorded in month + status.
- *Barang Terpakai* (all sourced from maintenance-record line items joined on `maintenance_date` within the month):
  - Spare parts — from `maintenance_record_parts`, grouped by part: total qty, total value (qty × unit_price).
  - Consumables — from `maintenance_record_consumables`, grouped: total qty, total value.
  - Tools — from `maintenance_record_tools`, grouped: usage count (no value).

**PDF:** `reports/pdf/monthly.blade.php` via DomPDF, same download pattern as schedule-report.

Scope note: "barang terpakai" = items used in maintenance work. Manual `stock_adjustment` deductions are intentionally excluded (not "terpakai in a job").

### Phase 3 — Automatic monthly snapshot + archive

**New table** `monthly_reports`: `id`, `year`, `month`, `location_id` (nullable FK), `pdf_path` (string), `generated_at`, `generated_by_user_id` (nullable). Unique on (`year`,`month`,`location_id`).

**Command** `cmms:generate-monthly-report`
- Runs for the **previous** month. Renders the Phase-2 PDF, stores it to disk, upserts a `monthly_reports` row.
- Register in `routes/console.php`: `Schedule::command('cmms:generate-monthly-report')->monthlyOn(1, '00:30')`.

**Archive UI:** an "Arsip" tab on the Reports page listing stored monthly PDFs with download links.

**⚠️ Deployment (Laravel Cloud):** local disk is ephemeral. Archived PDFs must be written to a persistent disk (object storage / S3). Confirm `FILESYSTEM_DISK` / bucket config before Phase 3 ships; store `pdf_path` against that disk.

## Build order & shippability

1. **Phase 1** — capture (migrations, models, controller, views). *Prerequisite for any consumable/tool data in the report.*
2. **Phase 2** — live Reports page + PDF + nav.
3. **Phase 3** — monthly command + archive tab.

Each phase is independently shippable.

## Out of scope (YAGNI)

- Tool checkout/return (pinjam-kembali) system.
- Standalone "Pemakaian Barang" menu decoupled from maintenance.
- Consumable-specific movement ledger table (line items suffice for the report).
- Emailing reports / scheduled delivery beyond on-disk archive.
