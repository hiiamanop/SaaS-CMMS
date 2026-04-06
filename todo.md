Build a complete CMMS (Computerized Maintenance Management System) web application
for PLTS (Solar Power Plant) maintenance management called SaaS-CMMS.

=============================================================================
TECH STACK
=============================================================================

- Backend: Laravel 11
- Frontend: Blade + Tailwind CSS + Alpine.js
- UI Components: shadcn/ui design language (replicated in Blade)
- Database: MySQL
- Auth: Laravel Breeze (Blade stack)
- Charts: Chart.js (via CDN)
- Calendar: FullCalendar.js (via CDN)
- PDF Export: barryvdh/laravel-dompdf
- Excel Export: maatwebsite/laravel-excel
- Icons: Lucide (via CDN)
- Font: Inter (Google Fonts)

=============================================================================
USER ROLES
=============================================================================

- Admin: full access to everything including settings
- Supervisor (SPV ONM): create/assign work orders, approve checksheets, view reports
- Technician (Teknisi ONM): fill checksheets on tablet, update assigned job status
- PM (Project Manager): view and approve reports, sign off checksheets

=============================================================================
LAYOUT & NAVIGATION
=============================================================================

- Sidebar layout with collapsible navigation
- Top navbar with notification bell and unread badge count
- Breadcrumb on every inner page
- Role-based menu visibility per role above
- Responsive and tablet-friendly throughout
- Flash messages (success, error, warning) displayed as toast notifications
- Confirm dialog (Alpine.js modal) before any delete action
- ALL forms must have a Cancel button that navigates back to previous page
  using url()->previous() — styled as outline/ghost button next to Submit

Sidebar menu items:
- Dashboard
- Assets
- Spare Parts
- Maintenance Schedule
- Work Orders
- My Jobs (visible to Technician only)
- Maintenance Records
- Checksheet (tablet-optimized)
- Schedule Report
- Timeline
- KPI Dashboard
- Settings (visible to Admin only)

=============================================================================
DATABASE TABLES
=============================================================================

users
- id, name, email, password, role (admin|supervisor|technician|pm),
  avatar, phone, is_active, timestamps

assets
- id, code, name, category, location, description,
  photos (JSON — multiple photos), brand, model,
  serial_number, purchase_date, purchase_price,
  status (active|inactive|under_maintenance), timestamps

spare_parts
- id, asset_id, code (auto-generated, read-only), name, brand,
  qty_actual, qty_minimum, unit, location, unit_price,
  description, timestamps
- code format: SP-[ASSET_CODE]-[3-digit increment per asset]
  e.g. SP-TRF01-001, SP-TRF01-002, SP-INV01-001

maintenance_schedules
- id, asset_id, category, equipment_name, item_pekerjaan,
  type (mingguan|bulanan|semesteran|tahunan|corrective),
  planned_weeks (JSON — array of {month, week} objects),
  shutdown_required (boolean), shutdown_duration_hours,
  checklist_template (JSON), status (active|inactive), timestamps

work_orders
- id, code, asset_id, schedule_id (nullable),
  title, description,
  type (preventive_mingguan|preventive_bulanan|preventive_semesteran
        |preventive_tahunan|corrective|emergency),
  priority (low|medium|high|critical),
  status (open|in_progress|pending_review|closed),
  created_by, due_date, start_date, started_at, completed_at,
  assigned_to_external (varchar nullable),
  timestamps

work_order_assignees
- id, work_order_id, user_id
  (replaces single assigned_to — supports multiple assignees)

work_order_logs
- id, work_order_id, user_id, status_from, status_to, notes, timestamps

checklist_items
- id, work_order_id, label, is_checked, checked_by, checked_at, order

maintenance_records
- id, work_order_id (nullable), asset_id, technician_id,
  maintenance_date, type, findings, actions_taken,
  duration_minutes, downtime_minutes,
  parts_used (JSON), photos (JSON), notes, timestamps

checksheet_types
- id, name (Mingguan|Bulanan|Semesteran|Tahunan), frequency

checksheet_templates
- id, checksheet_type_id, lokasi_inspeksi, item_inspeksi,
  metode_inspeksi, standar_ketentuan, order

checksheet_sessions
- id, checksheet_type_id, plts_location, equipment_location,
  period_label, year, week_number, month, semester,
  status (draft|submitted),
  submitted_at, submitted_by,
  signed_by_teknisi, signed_date_teknisi,
  signed_by_spv, signed_date_spv,
  signed_by_pm, signed_date_pm,
  timestamps

checksheet_results
- id, session_id, template_id,
  result (P|X|null), notes,
  photos (JSON — required for X on Bulanan/Semesteran/Tahunan)

checksheet_abnormals
- id, session_id, tanggal, abnormal_description,
  penanganan, tgl_selesai, pic

notifications
- id, user_id, type, title, message, data (JSON),
  is_read, read_at, timestamps

=============================================================================
FEATURE 1 — DASHBOARD
=============================================================================

Summary cards:
- Total Assets (with active/inactive count)
- Open Work Orders
- Overdue Tasks (red highlight)
- Low Stock Alerts (orange highlight)
- Today's Pending Checksheets

Content sections:
- Recent work orders table (last 8)
- Upcoming maintenance schedules (next 7 days)
- Work order trend chart — last 6 months (Chart.js bar chart)
- Low stock spare parts list (top 5)

=============================================================================
FEATURE 2 — ASSET MANAGEMENT
=============================================================================

List page:
- Search and filter by category, status, location
- Export to Excel button (exports current filtered list)
- Each row shows: code, name, category, location, status badge, actions

Create / Edit form:
- Fields: code, name, category, location, brand, model,
  serial number, purchase date, purchase price, status, description
- Multiple photo upload (store as JSON array of paths)
- Cancel button → back to previous page

Asset detail page with Alpine.js tabs:
- Overview tab: all asset info + photo gallery
  - Photos displayed as thumbnail grid
  - Each photo is clickable → opens fullscreen lightbox overlay
  - Lightbox: full-size image, close (X) button top right,
    prev/next arrows if multiple photos, dark overlay background
  - Lightbox implemented with Alpine.js
- Spare Parts tab: list of spare parts for this asset
- Work Orders tab: all work orders linked to this asset
- Maintenance History tab: all maintenance records for this asset

Delete: confirm dialog before deleting

=============================================================================
FEATURE 3 — SPARE PARTS
=============================================================================

List page:
- Search and filter by asset, brand, low stock status
- Export to Excel button (exports current filtered list)
- Columns: code (auto), name, brand, asset, qty actual,
  qty minimum, unit, stock status, actions
- Low stock badge (red) when qty_actual <= qty_minimum
- Stock level progress bar per row

Create form:
- Fields: asset (select), name, brand, qty_actual,
  qty_minimum, unit, location, unit_price, description
- Part code is NOT shown on create form — auto-generated on save
- Format: SP-[ASSET_CODE]-[3-digit increment per asset]
- Cancel button → back to previous page

Edit form:
- Part code shown as read-only field (cannot be changed)
- All other fields editable
- Cancel button → back to previous page

Adjust Stock modal:
- Select: Add Stock / Reduce Stock
- Input: quantity amount
- Notes field (reason for adjustment)

Auto notification when qty_actual <= qty_minimum after any update

=============================================================================
FEATURE 4 — MAINTENANCE SCHEDULE
=============================================================================

List view:
- Filter by category, type, status, PLTS location
- Columns: no, category, equipment name, item pekerjaan,
  type badge, shutdown (Y/N), status, actions

Calendar view (FullCalendar.js):
- Toggle between List and Calendar using Alpine.js tab
- Color-coded events by type

Create / Edit form (follows Excel structure hierarchy):
- Category (PV Module / Inverter / Panel LV / Transformer)
- Equipment Name (e.g., Transformer 01 — can select existing or create new)
- Item Pekerjaan (work item description)
- Type: Mingguan | Bulanan | Semesteran | Tahunan | Corrective
- Planned Weeks: dynamic week picker — select which W1/W2/W3/W4
  of which months are planned (visual month-week grid checkboxes)
- Shutdown Required: toggle Yes/No
- Shutdown Duration (hours) — shown only if shutdown = Yes
- Shutdown Date — shown only if shutdown = Yes
- Checklist template: dynamic add/remove checklist items
- Status: Active / Inactive
- Cancel button → back to previous page

Auto-generate work order when planned week is due (Laravel Scheduler)

=============================================================================
FEATURE 5 — WORK ORDERS (Trace & Assign Job)
=============================================================================

List page:
- Filter by: status, type, priority, asset, assignee, date range
- Export to Excel button (exports current filtered list)
- Columns: code, asset, title, type badge, priority badge,
  status badge, assignees (stacked avatars, max 2 + "+N more"),
  due date, start date, actions
- Priority color badges:
  Low = gray | Medium = blue | High = orange | Critical = red
- Type badges: color-coded per type

Work Order types:
- Preventive — Mingguan
- Preventive — Bulanan
- Preventive — Semesteran
- Preventive — Tahunan
- Corrective
- Emergency

Create form:
- Asset (select)
- Title, Description
- Type (select from list above)
- Priority (Low / Medium / High / Critical)
- Due Date
- Start Date
- Assign Technicians: multi-select searchable dropdown
  - Can select multiple internal users
  - Option at bottom: "Other (External)" → shows text input
    "Nama Pihak Eksternal" (required if selected)
  - External name saved in assigned_to_external column
  - External assignee shown with gray "Eksternal" badge in list/detail
- Cancel button → back to previous page

Work Order detail page with Alpine.js tabs:
- Header card: code, title, type badge, priority badge,
  status badge, assignees with avatars, due date, start date
- Details tab: full description, asset info, schedule link
- Checklist tab: checklist items with checkbox toggle
- Activity Log tab: vertical timeline of status change history

Update Status modal:
- Select new status: Open → In Progress → Pending Review → Closed
- Notes field (required)
- If status = In Progress → set started_at = now
- If status = Closed → redirect to create Maintenance Record

Reassign (admin/supervisor only):
- Multi-select to add/remove assignees
- Can also update external assignee name
- Notifications sent to all new internal assignees

Notifications sent to all assigned internal technicians on:
- New assignment
- Status changes

=============================================================================
FEATURE 6 — MY JOBS
=============================================================================

- Technician's personal view of all work orders assigned to them
- Filter by: status, priority, type
- Large touch-friendly rows for tablet use (min 48px height)
- Same detail page as Work Orders
- Shows external assignee note if applicable

=============================================================================
FEATURE 7 — MAINTENANCE RECORD
=============================================================================

List page:
- Filter by: asset, technician, date range, type
- Export to Excel button
- Columns: asset, technician, date, type, duration, downtime, actions

Create form (auto-filled from work order when closing WO):
- Asset (auto-filled)
- Technician (auto-filled from current user)
- Maintenance Date
- Type: Preventive — Mingguan / Bulanan / Semesteran / Tahunan /
        Corrective / Emergency
- Findings (textarea)
- Actions Taken (textarea)
- Parts Used: dynamic table rows
  - Select spare part (dropdown filtered by asset)
  - Quantity used
  - Unit (auto-filled from spare part)
  - On save: auto-deduct qty_actual from spare_parts
  - After deduct: check if below minimum → trigger notification
- Duration (minutes)
- Downtime (minutes)
- Photo upload (multiple) — preview thumbnails
- Notes
- Cancel button → back to previous page

Detail view: all fields, parts used table, photo gallery with lightbox

=============================================================================
FEATURE 8 — CHECKSHEET MODULE (Tablet-Optimized)
=============================================================================

All checksheet pages must be designed for tablet field use:
- Minimum tap target: 48px
- Font size: minimum 16px body, 20px labels
- No hover-only interactions
- Works in portrait and landscape orientation
- Auto-save draft to database every 30 seconds

--- Checksheet Home Page ---

Shows today's pending checksheets as cards, grouped by type:
- Mingguan (current week)
- Bulanan (current month)
- Semesteran (current semester)
- Tahunan (current year)

Each card shows: type label, PLTS location, period, status badge
Status: Not Started (gray) / In Progress (blue) / Submitted (green)

Large tap-friendly cards. Tap to open and fill.

--- Checksheet Fill Page (all types) ---

Sticky top bar shows:
- Checksheet type and period
- Progress: "X of Y items completed"
- Auto-save indicator

Header section (fill before items):
- PLTS Location (dropdown)
- Equipment/Trafo Location (text)
- Period (auto-filled: week number / month / semester / year)

Inspection items — vertical list grouped by Lokasi Inspeksi:

Each item card shows:
- Lokasi Inspeksi label (section header)
- Item Inspeksi (bold)
- Metode Inspeksi (gray subtext)
- Standar Ketentuan (light gray helper text)
- Result toggle: P (OK, green) / X (Anomali, red) / default gray
  - Large buttons, minimum 48px height
- When X is selected:
  - Notes textarea appears (required for all types)
  - Photo upload field appears:
    - MINGGUAN: optional (no asterisk, no validation block)
    - BULANAN: required (*) — cannot submit without photo
    - SEMESTERAN: required (*) — cannot submit without photo
    - TAHUNAN: required (*) — cannot submit without photo
  - Accepted formats: JPG, PNG, HEIC
  - Max 5MB per photo
  - Show thumbnail previews after upload
  - Multiple photos allowed per item
  - Photos stored in: storage/public/checksheet-photos/[session_id]/[item_id]/

Cannot submit if:
- Any item is still gray (not filled)
- Any X item on Bulanan/Semesteran/Tahunan has no photo uploaded
- Submit button shows tooltip: "Lengkapi foto untuk semua item anomali"
  if blocked by missing required photos

Abnormal Notes section (shown at bottom for Semesteran and Tahunan):
- Add row button
- Each row: Tanggal | Deskripsi Abnormal | Penanganan | Tgl Selesai | PIC

Signature section at bottom:
- Dibuat oleh (Teknisi ONM): name input + date
- Diperiksa oleh (SPV ONM): name input + date
- Disetujui oleh (PM): name input + date

On submit:
- Save all results and photos to database
- Mark session as Submitted
- Auto-generate PDF of the completed checksheet
- Show success screen with link to view/download PDF

--- Checksheet Item Templates (pre-seeded) ---

WEEKLY (Checksheet Mingguan):
PV Module:
- Pengecekan kondisi PV Module | Visual Check | Bersih, Tidak Kotor
- Pengecekan mounting PV Module | Visual Check | Tidak Bergeser, Tidak Turun
- Pengecekan kondisi skun grounding antar PV | Visual Check | Tidak Terlepas
- Pengecekan bracing, end clamp, dan midclamp | Visual Check | Tidak Terlepas
- Pengecekan kabel DC String | Visual Check | Tidak Terkelupas/Robek, Rapih
Inverter:
- Kebersihan Inverter | Visual Check & Pembersihan | Bersih, Tidak Kotor
- MC4 Connector | Visual Check & Thermal Test | Tidak Rusak/Terbakar, Tidak Kendur
- Thermal check MC4 | Thermal Test/Measurement | ≤60°C Normal, 60-70°C Warning, >70°C Critical
- Pengecekan Torque Kabel LV AC | Visual Check | Sesuai Standar
- Historikal Alarm | Pengecekan Software | Tidak Ada Alarm
Panel LV:
- Pengecekan kondisi SPD | Visual Check | Koneksi Grounding, Indikator Status, Tidak Rusak
- Pengecekan kondisi visual MCCB Inverter | Visual Check | Kondisi Fisik, Indikator Trip
- Pengecekan CB Control | Visual Check | Tidak Terlepas, Terbakar
- Visual Check UPS | Visual Check | Tidak Ada Alarm

MONTHLY (Checksheet Bulanan):
PV Module:
- Measurement Tegangan DC Cable | Action & Monitoring | Unbalance < 5%
- Measurement Arus DC Cable | Action & Monitoring | Unbalance < 5%
- Thermal Monitoring DC Cable | Action & Monitoring | Max 70°C
Inverter:
- Checking and Measuring Grounding | Action & Monitoring | Max 5 Ohm
- Thermal Monitoring AC Cable | Visual Check & Thermal Test | Max 70°C
- Thermal Monitoring DC Cable | Visual Check & Thermal Test | Max 70°C
- Checking Condition AC Cable | Action & Monitoring | Tidak Rusak/Terbakar
- Checking Termination AC Cable | Action & Monitoring | Tidak Bergeser, Tidak Ada Anomali
Panel LV:
- Thermal Monitoring AC Cable at Peak Hour | Visual Check & Thermal Test | Max 70°C
- Thermal Monitoring Busbar at Peak Hour | Visual Check & Thermal Test | Max 70°C
- Thermal Monitoring Scun Cable at Peak Hour | Visual Check & Thermal Test | Max 70°C
- Thermal Monitoring Material Protection | Visual Check & Thermal Test | Max 70°C

SEMESTER (Checksheet Semesteran):
Inverter:
- Checking and Cleaning Fan, Inlet, and Outlet Air of Inverter | Action & Monitoring | Tidak Kotor
Panel LV:
- Checking and Cleaning Fan, Inlet, and Outlet Fan | Action & Monitoring | Tidak Kotor
- Checking Torque All Bolt | Action & Monitoring | Sesuai Standar Torque

ANNUAL (Checksheet Tahunan):
Transformer:
- Purifying Transformator Oil | Action & Monitoring | Oli Bersih, Tidak Berkurang dari 1150 Liter
- BDV Test | Action & Monitoring | >30 kV/2.5 mm
- DGA Test | Action & Monitoring | Gas dalam Oli Harus Kondisi Low
- Marking and Tightening Connection Check | Action & Monitoring | Tidak Bergeser

=============================================================================
FEATURE 9 — SCHEDULE REPORT PAGE
=============================================================================

This page has 5 tabs using Alpine.js.
Each tab shows the report table DIRECTLY ON SCREEN replicating the exact
Excel format, AND has an Export to PDF button.

--- Tab 1: Schedule Maintenance ---

Filter bar: Year | PLTS Location | Equipment Category

Table structure (exact Excel format):
- Frozen columns (sticky on horizontal scroll):
  No | Nama Alat/Mesin | Item Pekerjaan | Renc./Real. | Shutdown (Y/N)
- Scrollable columns:
  Jan W1 W2 W3 W4 | Feb W1 W2 W3 W4 | ... | Des W1 W2 W3 W4
- Last columns: Total Durasi Shutdown | Tanggal Shutdown

Each equipment shows 2 rows: Renc. (planned) and Real. (actual)
Equipment grouped under category section headers (bold row):
  A. PV MODULE → sub items (Transformer 01, 02, ...)
  B. INVERTER → sub items
  C. PANEL LV → sub items
  etc.

Cell indicators:
- Planned, not yet due: blue dot •
- Completed on time: green ✓
- Completed late: orange ✓
- Overdue not done: red ✗
- Not planned: empty

--- Tab 2: Checksheet Mingguan ---

Filter bar: Year | Month | PLTS Location

Table (exact Excel format):
Headers: Lokasi Inspeksi | Item Inspeksi | Metode Inspeksi |
         Standar Ketentuan | W1 | W2 | W3 | W4
Rows grouped by Lokasi Inspeksi with bold section header rows.

Result cells:
- P = green badge "P"
- X = red badge "X" (with tooltip showing notes on hover/tap)
- Empty = gray dash "—"

Below table:
- Catatan Abnormal sub-table (shown only if any X results exist):
  | Tanggal | Abnormal | Penanganan Abnormal | Tgl Selesai | PIC |
- Anomali photos shown as small thumbnails in the row
- Signature section:
  | Dibuat oleh (Teknisi ONM) | Diperiksa oleh (SPV ONM) | Disetujui oleh (PM) |
  Name and date shown below each column

--- Tab 3: Checksheet Bulanan ---

Filter bar: Year | PLTS Location

Table (exact Excel format):
Headers: Item Inspeksi | Metode Inspeksi | Standar Ketentuan |
         Jan W1-W4 | Feb W1-W4 | ... | Des W1-W4
Grouped by equipment section headers.
Same P/X cell display, abnormal notes sub-table, signature section.

--- Tab 4: Checksheet Semesteran ---

Filter bar: Year | Semester (Sem 1: Jan-Jun / Sem 2: Jul-Des) | PLTS Location

Table (exact Excel format):
Headers: Item Inspeksi | Metode Inspeksi | Standar Ketentuan |
         [W1 W2 W3 W4 for each month in selected semester]
Same P/X display.

Abnormal Notes sub-table:
| Tanggal | Abnormal | Penanganan Abnormal | Tgl Selesai | PIC |

Anomali photos shown as small thumbnails in the abnormal row.

Signature section:
| Teknisi ONM | SPV ONM | PM |
Name and date below each.

--- Tab 5: Checksheet Tahunan ---

Filter bar: Year | PLTS Location

Table (exact Excel format):
Headers: Item Inspeksi | Metode Inspeksi | Standar Ketentuan |
         Jan W1-W4 | Feb W1-W4 | ... | Des W1-W4
Equipment grouped under TRANSFORMER section header.
Same P/X, abnormal notes, photos, signature section.

--- Export to PDF (all tabs) ---

- "Export PDF" button top right of each tab
- PDF landscape orientation
- PDF header matches Excel FORMULIR format:
  Top: "FORMULIR" label (top right)
  Title: PREVENTIVE MAINTENANCE SCHEDULE / CHECKSHEET MINGGUAN / etc.
  Row: No. Dokumen | No. Revisi | Tanggal Berlaku | Halaman
  Row: PLTS name | Location | Period | Year
- Table body: exact replica of on-screen table
- Anomali photos included as thumbnails next to X items
- PDF footer: signature section (Dibuat oleh | Diperiksa oleh | Disetujui oleh)
- Generated with barryvdh/laravel-dompdf
- Filename: [Type]_[Location]_[Period]_[Year].pdf

=============================================================================
FEATURE 10 — TIMELINE
=============================================================================

- Visual vertical timeline feed
- Filter by: asset, technician, date range
- Shows: work orders, maintenance records, submitted checksheets (chronological)
- Color coded:
  Preventive = blue | Corrective = orange | Completed = green | Overdue = red
- Each timeline item: icon, date, title, status badge, short description

=============================================================================
FEATURE 11 — KPI DASHBOARD
=============================================================================

Filter bar: date range, asset, location

KPI metric cards:
- MTTR (Mean Time To Repair) in hours
- MTBF (Mean Time Between Failures) in hours
- PM Compliance Rate (%)
- Work Order Completion Rate (%)
- Checksheet Completion Rate (%)
- Overdue Work Orders count
- Total Downtime Hours

Charts (Chart.js):
- Bar chart: work orders by status per month
- Line chart: MTTR trend over time
- Doughnut chart: work orders by priority
- Area chart: downtime hours per month

Export buttons:
- Export to PDF (dompdf)
- Export to Excel (laravel-excel)

=============================================================================
FEATURE 12 — NOTIFICATIONS
=============================================================================

- Bell icon in navbar with unread count badge
- Dropdown: latest 10 notifications (Alpine.js Popover)
- Mark as read (single) / Mark all as read
- Full notifications list page

Triggers:
- Spare part qty <= minimum → notify Admin & Supervisor
- New work order assigned → notify all assigned Technicians
- Work order overdue → notify Admin & Supervisor
- Work order status changed → notify WO creator
- Checksheet not submitted by end of day → notify Supervisor & Admin

=============================================================================
SEED DATA (for demo)
=============================================================================

Users:
- 1 Admin (admin@cmms.com / password)
- 1 Supervisor SPV ONM (spv@cmms.com / password)
- 2 Teknisi ONM (teknisi1@cmms.com, teknisi2@cmms.com / password)
- 1 PM (pm@cmms.com / password)

PLTS Locations (3):
- PLTS Pertiwi Lestari
- PLTS Rengiat
- PLTS Demo Site

Assets (10):
- Transformer 01–07 (category: PV Module, location varies)
- Inverter 1–3 (category: Inverter)
- Panel LV 1–2 (category: Panel LV)

Spare Parts (20):
- 3-5 per asset, auto-generated codes
- Some with qty_actual <= qty_minimum (to show low stock alerts)
- Brand field filled with realistic brands

Maintenance Schedules (8):
- Mix of all types (Mingguan, Bulanan, Semesteran, Tahunan, Corrective)
- Some with past planned weeks (overdue)
- Shutdown Y/N mix

Work Orders (20):
- Mix of all statuses and priorities
- Mix of all types
- Some assigned to multiple technicians
- Some with external assignee
- Some overdue
- Start Date filled for In Progress and Closed ones

Maintenance Records (10):
- Linked to closed work orders
- Realistic findings and actions in Indonesian
- Parts used with qty deductions

Checksheet Sessions (5):
- Mix of submitted and draft
- Some with X results and photos
- Various PLTS locations

Notifications (10):
- Mix of all types
- Some unread

Checksheet Templates:
- All items from the weekly/monthly/semester/annual lists above

=============================================================================
SCHEDULED JOBS (Laravel Scheduler — run daily)
=============================================================================

- cmms:check-schedules → auto-create work orders for due planned weeks
- cmms:check-overdue → flag overdue WOs, send notifications
- cmms:check-stock → check spare parts below minimum, send notifications
- cmms:check-checksheets → check unfilled checksheets at end of day,
  send reminder notifications to Supervisor & Admin

=============================================================================
UI STYLE GUIDE
=============================================================================

Design language: shadcn/ui replicated in Blade
- Cards: subtle border (border-gray-200), soft shadow (shadow-sm), rounded-lg
- Buttons:
  Primary: bg-gray-900 text-white hover:bg-gray-700
  Secondary: bg-white border border-gray-300 hover:bg-gray-50
  Destructive: bg-red-600 text-white hover:bg-red-700
  Ghost: transparent hover:bg-gray-100
  Cancel: always outline/ghost style
- Badges: rounded-full, color-coded per status/priority/type
- Tables: striped rows, hover:bg-gray-50, clean typography
- Forms: rounded-md border border-gray-300, focus:ring-2 focus:ring-gray-900
- Sidebar: active item = bg-gray-100 font-medium, left border accent
- Progress bars: rounded-full, color-coded (green/orange/red by level)
- Modals: Alpine.js, backdrop blur, rounded-xl, shadow-xl
- Toast: top-right fixed position, auto-dismiss after 4 seconds
- Tabs: underline style, active = border-b-2 border-gray-900
- Font: Inter (Google Fonts) — weights 400, 500, 600, 700
- Colors: neutral gray palette, accent colors per status only
- Spacing: consistent p-4/p-6 for cards, gap-4/gap-6 for grids

Tablet-specific (Checksheet & My Jobs pages):
- All tap targets minimum 48px height
- Font size minimum 16px body, 18-20px labels
- No hover-dependent interactions
- Large P/X toggle buttons (minimum 64px width × 48px height)
- Sticky headers and progress bars

=============================================================================
BUILD ORDER
=============================================================================

Build in this exact sequence, complete each step before moving to the next:

Step 1: Laravel 11 + Breeze (Blade stack) setup
Step 2: Install and configure:
        - Tailwind CSS + Alpine.js via Vite
        - Chart.js + FullCalendar.js + Lucide via CDN in layout
        - barryvdh/laravel-dompdf
        - maatwebsite/laravel-excel
Step 3: All database migrations
Step 4: All models with relationships
Step 5: All seeders (users, assets, spare parts, schedules, work orders,
        records, checksheet templates, checksheet sessions, notifications)
Step 6: App layout:
        - Sidebar (collapsible, role-based menu)
        - Navbar (notification bell with badge)
        - Breadcrumb
        - Toast notification system
        - Blade components: card, button, badge, modal, table
Step 7: Auth pages styled with shadcn design language
Step 8: Pages in this order:
        → Dashboard
        → Assets (with lightbox photo gallery)
        → Spare Parts (auto-code, brand field, adjust stock)
        → Maintenance Schedule (Excel-format hierarchy)
        → Work Orders (multi-assign, external assign, type labels)
        → My Jobs (tablet-friendly)
        → Maintenance Records (with parts deduction)
        → Checksheet (tablet-optimized, photo upload rules)
        → Schedule Report (5 tabs, on-screen tables, PDF export)
        → Timeline
        → KPI Dashboard (charts + export)
        → Notifications
        → Settings
Step 9: Artisan commands + Laravel Scheduler
Step 10: Final polish:
         - Empty states (friendly illustration + message) on all list pages
         - Loading states on form submissions
         - Responsive check on mobile, tablet, desktop
         - Flash toast messages on all CRUD actions
         - Cancel buttons on all forms
         - Export buttons on all list pages
         - Tablet testing for Checksheet and My Jobs