# Monthly Activity & Item-Usage Report — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.
>
> **Note (user instruction):** do NOT `git commit` or `git push` — the user handles that. The `git add`/`git commit` steps below are left as logical checkpoints; when executing, stage/verify but leave committing to the user.

**Goal:** Capture consumable & tool usage inside Maintenance Records, then surface a monthly Reports page (activities + items used) with PDF export and an auto-generated monthly archive.

**Architecture:** Mirror the existing spare-part flow (`maintenance_record_parts` + `StockService`). Add two parallel line-item tables for consumables (stock-deducting) and tools (log-only). A `ReportService::forMonth()` aggregates activities + item usage for a given month; the Reports controller renders it live and to PDF, and a scheduled command archives the PDF each month.

**Tech Stack:** Laravel 13, Blade + Alpine.js, `barryvdh/laravel-dompdf`, sqlite in-memory tests (PHPUnit).

## Global Constraints

- Follow existing codebase patterns exactly — spare parts are the reference implementation for capture; `ScheduleReportController` for report/PDF.
- Tools are **log-only**: usage is recorded but stock/qty is never changed (client chose "log pemakaian", not checkout/return).
- Consumable/tool usage is captured **only in `MaintenanceRecordController::store()`** — `update()` does not touch line items today (parts aren't edited there either); stay consistent, do not add line-item editing to `update()`.
- Report scope = items used **in maintenance work** only. Manual `stock_adjustment` deductions are excluded.
- Report filters are **month + year only** (no location filter — the entities have no clean shared location key; out of scope).
- Tests: PHPUnit, sqlite `:memory:`, build models with `Model::create([...])` (only `UserFactory` exists — do not rely on model factories for `Consumable`/`Tool`/`SparePart`).
- Money display: `IDR ` + `number_format(value)` (matches `maintenance-records/show.blade.php`).
- Do not commit or push — user handles version control.

---

## Phase 1 — Capture consumable & tool usage

### Task 1: Migrations for the two line-item tables

**Files:**
- Create: `database/migrations/2026_07_19_000001_create_maintenance_record_consumables_table.php`
- Create: `database/migrations/2026_07_19_000002_create_maintenance_record_tools_table.php`

**Interfaces:**
- Produces: tables `maintenance_record_consumables` (cols: `id`, `maintenance_record_id`, `consumable_id`, `qty_used`, `unit_price` nullable, timestamps) and `maintenance_record_tools` (cols: `id`, `maintenance_record_id`, `tool_id`, timestamps).

- [ ] **Step 1: Write the consumables migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_record_consumables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consumable_id')->constrained()->cascadeOnDelete();
            $table->integer('qty_used');
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_record_consumables');
    }
};
```

- [ ] **Step 2: Write the tools migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_record_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_record_tools');
    }
};
```

- [ ] **Step 3: Run the migrations**

Run: `php artisan migrate`
Expected: both tables created, no errors.

- [ ] **Step 4: Stage (do not commit — user handles it)**

```bash
git add database/migrations/2026_07_19_000001_create_maintenance_record_consumables_table.php database/migrations/2026_07_19_000002_create_maintenance_record_tools_table.php
```

---

### Task 2: Line-item models + MaintenanceRecord relations

**Files:**
- Create: `app/Models/MaintenanceRecordConsumable.php`
- Create: `app/Models/MaintenanceRecordTool.php`
- Modify: `app/Models/MaintenanceRecord.php` (add two relations after `parts()` on line 27)

**Interfaces:**
- Produces:
  - `MaintenanceRecordConsumable` with `$fillable = ['maintenance_record_id','consumable_id','qty_used','unit_price']`, `unit_price` cast `decimal:2`, relations `consumable()` (belongsTo `Consumable`), `maintenanceRecord()`.
  - `MaintenanceRecordTool` with `$fillable = ['maintenance_record_id','tool_id']`, relations `tool()` (belongsTo `Tool`), `maintenanceRecord()`.
  - `MaintenanceRecord::consumables()` → hasMany `MaintenanceRecordConsumable`; `MaintenanceRecord::tools()` → hasMany `MaintenanceRecordTool`.

- [ ] **Step 1: Create `MaintenanceRecordConsumable`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRecordConsumable extends Model
{
    protected $fillable = ['maintenance_record_id', 'consumable_id', 'qty_used', 'unit_price'];

    protected function casts(): array
    {
        return ['unit_price' => 'decimal:2'];
    }

    public function maintenanceRecord() { return $this->belongsTo(MaintenanceRecord::class); }
    public function consumable() { return $this->belongsTo(Consumable::class); }
}
```

- [ ] **Step 2: Create `MaintenanceRecordTool`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRecordTool extends Model
{
    protected $fillable = ['maintenance_record_id', 'tool_id'];

    public function maintenanceRecord() { return $this->belongsTo(MaintenanceRecord::class); }
    public function tool() { return $this->belongsTo(Tool::class); }
}
```

- [ ] **Step 3: Add relations to `MaintenanceRecord`**

In `app/Models/MaintenanceRecord.php`, immediately after the `parts()` method (line 27), add:

```php
    public function consumables() { return $this->hasMany(MaintenanceRecordConsumable::class); }
    public function tools() { return $this->hasMany(MaintenanceRecordTool::class); }
```

- [ ] **Step 4: Sanity-check the classes load**

Run: `php artisan tinker --execute="echo App\Models\MaintenanceRecordConsumable::class, PHP_EOL, App\Models\MaintenanceRecordTool::class;"`
Expected: prints both class names, no error.

- [ ] **Step 5: Stage (do not commit)**

```bash
git add app/Models/MaintenanceRecordConsumable.php app/Models/MaintenanceRecordTool.php app/Models/MaintenanceRecord.php
```

---

### Task 3: Generalize `OutOfStockException` + add `StockService::deductConsumable`

**Files:**
- Modify: `app/Exceptions/OutOfStockException.php` (constructor message wording)
- Modify: `app/Services/StockService.php` (add `deductConsumable`, add `use App\Models\Consumable;`)
- Test: `tests/Unit/Services/ConsumableStockServiceTest.php`

**Interfaces:**
- Consumes: `Consumable` model (`qty_actual` int column), `OutOfStockException`.
- Produces: `StockService::deductConsumable(Consumable $consumable, int $qty, int $userId): void` — locks the row, throws `OutOfStockException` if `qty_actual < qty`, else decrements `qty_actual`. No ledger row (consumables have no movement table).

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Services/ConsumableStockServiceTest.php`:

```php
<?php

namespace Tests\Unit\Services;

use App\Models\Consumable;
use App\Services\StockService;
use App\Exceptions\OutOfStockException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsumableStockServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testDeductConsumableReducesStock()
    {
        $c = Consumable::create([
            'item_code' => 'C-1', 'name' => 'Oli', 'unit' => 'liter',
            'qty_actual' => 10, 'qty_minimum' => 0,
        ]);

        StockService::deductConsumable($c, 3, 1);

        $this->assertEquals(7, $c->fresh()->qty_actual);
    }

    /** @test */
    public function testDeductConsumableThrowsWhenInsufficient()
    {
        $c = Consumable::create([
            'item_code' => 'C-2', 'name' => 'Majun', 'unit' => 'pcs',
            'qty_actual' => 2, 'qty_minimum' => 0,
        ]);

        $this->expectException(OutOfStockException::class);
        StockService::deductConsumable($c, 5, 1);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ConsumableStockServiceTest`
Expected: FAIL — `Call to undefined method App\Services\StockService::deductConsumable()`.

- [ ] **Step 3: Generalize the exception message**

Replace the constructor in `app/Exceptions/OutOfStockException.php` with (keeps the same 3-arg signature so existing `StockService::deduct` calls are unchanged):

```php
    public function __construct($item, $availableQty, $requestedQty)
    {
        $message = "Stok \"{$item->name}\" tidak cukup. Tersedia: {$availableQty}, Diminta: {$requestedQty}";
        parent::__construct($message);
    }
```

- [ ] **Step 4: Add `deductConsumable` to `StockService`**

In `app/Services/StockService.php`, add `use App\Models\Consumable;` near the other `use` lines, then add this method inside the class:

```php
    /**
     * Deduct consumable stock (pemakaian). No ledger table — line items are the audit trail.
     * @throws OutOfStockException if insufficient stock
     */
    public static function deductConsumable(Consumable $consumable, int $qty, int $userId): void
    {
        DB::transaction(function () use ($consumable, $qty) {
            $consumable = Consumable::lockForUpdate()->findOrFail($consumable->id);

            if ($consumable->qty_actual < $qty) {
                throw new OutOfStockException($consumable, $consumable->qty_actual, $qty);
            }

            $consumable->decrement('qty_actual', $qty);
        });
    }
```

*(`$userId` is accepted for call-site symmetry with `deduct()`; unused because there is no consumable ledger.)*

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=ConsumableStockServiceTest`
Expected: PASS (2 tests). Also run `php artisan test --filter=StockServiceTest` — still PASS (exception message change doesn't affect its assertions).

- [ ] **Step 6: Stage (do not commit)**

```bash
git add app/Exceptions/OutOfStockException.php app/Services/StockService.php tests/Unit/Services/ConsumableStockServiceTest.php
```

---

### Task 4: Capture consumables & tools in `MaintenanceRecordController::store()`

**Files:**
- Modify: `app/Http/Controllers/MaintenanceRecordController.php` (imports, `create()`, `store()`)
- Test: `tests/Feature/MaintenanceRecordUsageTest.php`

**Interfaces:**
- Consumes: `StockService::deductConsumable`, `MaintenanceRecordConsumable`, `MaintenanceRecordTool`, `Consumable`, `Tool`.
- Produces: POST `maintenance-records` accepts `consumables[]` (`consumable_id`,`qty_used`) and `tools[]` (`tool_id`); creates line items, deducts consumable stock, logs tools. `create()` passes `$consumables` and `$tools` to the view.

- [ ] **Step 1: Write the failing feature test**

Create `tests/Feature/MaintenanceRecordUsageTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Consumable;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceRecordUsageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testStoringRecordDeductsConsumableAndLogsTool()
    {
        $tech = User::factory()->create(['role' => 'technician']);
        $consumable = Consumable::create([
            'item_code' => 'C-10', 'name' => 'Oli', 'unit' => 'liter',
            'qty_actual' => 10, 'qty_minimum' => 0,
        ]);
        $tool = Tool::create([
            'tool_code' => 'T-10', 'name' => 'Tang', 'condition' => 'good',
            'qty_total' => 5, 'qty_available' => 5,
        ]);

        $response = $this->actingAs($tech)->post(route('maintenance-records.store'), [
            'technician_id'    => $tech->id,
            'maintenance_date' => '2026-07-10',
            'duration_minutes' => 60,
            'shutdown_minutes' => 0,
            'status_after'     => 'solved',
            'consumables'      => [['consumable_id' => $consumable->id, 'qty_used' => 4]],
            'tools'            => [['tool_id' => $tool->id]],
        ]);

        $response->assertRedirect();
        $this->assertEquals(6, $consumable->fresh()->qty_actual); // 10 - 4
        $this->assertEquals(5, $tool->fresh()->qty_available);    // unchanged (log-only)
        $this->assertDatabaseHas('maintenance_record_consumables', [
            'consumable_id' => $consumable->id, 'qty_used' => 4,
        ]);
        $this->assertDatabaseHas('maintenance_record_tools', [
            'tool_id' => $tool->id,
        ]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=MaintenanceRecordUsageTest`
Expected: FAIL — no `maintenance_record_consumables` row created (controller ignores the fields).

- [ ] **Step 3: Add imports**

In `app/Http/Controllers/MaintenanceRecordController.php`, add near the other `use` statements:

```php
use App\Models\MaintenanceRecordConsumable;
use App\Models\MaintenanceRecordTool;
use App\Models\Consumable;
use App\Models\Tool;
```

- [ ] **Step 4: Pass consumables & tools to the create view**

In `create()` (around line 55), add before the `return view(...)`:

```php
        $consumables = Consumable::orderBy('name')->get();
        $tools = Tool::orderBy('name')->get();
```

and extend the compact call:

```php
        return view('maintenance-records.create', compact('workOrder', 'workOrders', 'technicians', 'spareParts', 'consumables', 'tools'));
```

- [ ] **Step 5: Filter empty rows + validate in `store()`**

In `store()`, right after the existing `parts` filter block (line 72), add:

```php
        if ($request->has('consumables')) {
            $consumables = collect($request->consumables)->filter(fn($c) => !empty($c['consumable_id']))->values()->all();
            $request->merge(['consumables' => $consumables]);
        }
        if ($request->has('tools')) {
            $tools = collect($request->tools)->filter(fn($t) => !empty($t['tool_id']))->values()->all();
            $request->merge(['tools' => $tools]);
        }
```

In the `$request->validate([...])` array (after the `parts.*.qty_used` rule, line 89), add:

```php
            'consumables' => 'nullable|array',
            'consumables.*.consumable_id' => 'required|exists:consumables,id',
            'consumables.*.qty_used' => 'required|integer|min:1',
            'tools' => 'nullable|array',
            'tools.*.tool_id' => 'required|exists:tools,id',
```

- [ ] **Step 6: Persist line items inside the existing transaction**

In `store()`, immediately after the parts `if (!empty($validated['parts'])) { ... }` block (ends line 199), add:

```php
            if (!empty($validated['consumables'])) {
                foreach ($validated['consumables'] as $row) {
                    $consumable = Consumable::find($row['consumable_id']);
                    MaintenanceRecordConsumable::create([
                        'maintenance_record_id' => $record->id,
                        'consumable_id' => $row['consumable_id'],
                        'qty_used' => $row['qty_used'],
                        'unit_price' => $consumable->unit_price,
                    ]);
                    StockService::deductConsumable($consumable, $row['qty_used'], auth()->id());
                }
            }

            if (!empty($validated['tools'])) {
                foreach ($validated['tools'] as $row) {
                    MaintenanceRecordTool::create([
                        'maintenance_record_id' => $record->id,
                        'tool_id' => $row['tool_id'],
                    ]);
                }
            }
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=MaintenanceRecordUsageTest`
Expected: PASS.

- [ ] **Step 8: Stage (do not commit)**

```bash
git add app/Http/Controllers/MaintenanceRecordController.php tests/Feature/MaintenanceRecordUsageTest.php
```

---

### Task 5: Blade — create-form pickers + show-view sections

**Files:**
- Modify: `resources/views/maintenance-records/create.blade.php` (x-data init + two new blocks after the Parts block, line 143)
- Modify: `resources/views/maintenance-records/show.blade.php` (two new sections after the Parts block, line 69)
- Modify: `app/Http/Controllers/MaintenanceRecordController.php` `show()` eager-load (line 217)

**Interfaces:**
- Consumes: `$consumables`, `$tools` (from Task 4 `create()`); `$maintenanceRecord->consumables`, `->tools` relations (Task 2).

- [ ] **Step 1: Extend the create-form Alpine state**

In `resources/views/maintenance-records/create.blade.php`, the `x-data` object (starts line 17) currently initialises `parts`. Add two sibling arrays. Directly after the `parts: {{ ... }},` line, add:

```blade
                consumables: {{ old('consumables') ? json_encode(old('consumables')) : '[]' }},
                tools: {{ old('tools') ? json_encode(old('tools')) : '[]' }},
```

- [ ] **Step 2: Add the consumable + tool pickers**

In the same file, immediately after the Parts block's closing `</div>` (line 143, before `{{-- Photos --}}`), add:

```blade
            {{-- Consumables used --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Consumables Used</label>
                <div class="space-y-2">
                    <template x-for="(c, index) in consumables" :key="index">
                        <div class="flex gap-2 items-center">
                            <select :name="'consumables['+index+'][consumable_id]'" x-model="c.consumable_id" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                <option value="">Select consumable...</option>
                                @foreach($consumables as $cItem)<option value="{{ $cItem->id }}">{{ $cItem->name }} ({{ $cItem->qty_actual }} {{ $cItem->unit }} available)</option>@endforeach
                            </select>
                            <input :name="'consumables['+index+'][qty_used]'" x-model="c.qty_used" type="number" min="1" placeholder="Qty" class="w-24 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                            <button type="button" @click="consumables.splice(index,1)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg></button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="consumables.push({consumable_id:'',qty_used:1})" class="mt-2 inline-flex items-center gap-1.5 text-sm text-brand hover:text-blue-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>Add consumable
                </button>
            </div>

            {{-- Tools used (log only) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tools Used</label>
                <div class="space-y-2">
                    <template x-for="(t, index) in tools" :key="index">
                        <div class="flex gap-2 items-center">
                            <select :name="'tools['+index+'][tool_id]'" x-model="t.tool_id" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand">
                                <option value="">Select tool...</option>
                                @foreach($tools as $tItem)<option value="{{ $tItem->id }}">{{ $tItem->name }}</option>@endforeach
                            </select>
                            <button type="button" @click="tools.splice(index,1)" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/></svg></button>
                        </div>
                    </template>
                </div>
                <button type="button" @click="tools.push({tool_id:''})" class="mt-2 inline-flex items-center gap-1.5 text-sm text-brand hover:text-blue-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>Add tool
                </button>
            </div>
```

*(Loop vars renamed `$cItem`/`$tItem` to avoid confusion with the Alpine `c`/`t`. Blade renders the `<option>`s server-side before Alpine runs.)*

- [ ] **Step 3: Eager-load in `show()`**

In `MaintenanceRecordController::show()` (line 217), extend the `load` call:

```php
        $maintenanceRecord->load(['asset', 'technician', 'workOrder', 'parts.sparePart', 'consumables.consumable', 'tools.tool', 'photos']);
```

- [ ] **Step 4: Add show-view sections**

In `resources/views/maintenance-records/show.blade.php`, immediately after the Parts `@endif` (line 69), add:

```blade
    @if($mr->consumables->count())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-900">Consumables Used</h2></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase"><th class="px-5 py-3 text-left">Consumable</th><th class="px-5 py-3 text-left">Qty Used</th><th class="px-5 py-3 text-left">Unit Price</th><th class="px-5 py-3 text-left">Total</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($mr->consumables as $c)
            <tr>
                <td class="px-5 py-3 font-medium text-gray-900">{{ $c->consumable->name }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $c->qty_used }} {{ $c->consumable->unit }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $c->unit_price ? 'IDR '.number_format($c->unit_price) : '—' }}</td>
                <td class="px-5 py-3 text-gray-700 font-medium">{{ $c->unit_price ? 'IDR '.number_format($c->unit_price * $c->qty_used) : '—' }}</td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($mr->tools->count())
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-900">Tools Used</h2></div>
        <table class="w-full text-sm">
            <thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase"><th class="px-5 py-3 text-left">Tool</th></tr></thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($mr->tools as $t)
            <tr><td class="px-5 py-3 font-medium text-gray-900">{{ $t->tool->name }}</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @endif
```

- [ ] **Step 5: Manual verification**

Run the dev server, open the Create Maintenance Record form. Confirm the Consumables + Tools pickers render, Add/remove rows work, and submitting a record with one consumable + one tool shows both on the record's show page, with the consumable stock reduced on its Items page.

- [ ] **Step 6: Stage (do not commit)**

```bash
git add resources/views/maintenance-records/create.blade.php resources/views/maintenance-records/show.blade.php app/Http/Controllers/MaintenanceRecordController.php
```

---

## Phase 2 — Reports menu (live page + PDF)

### Task 6: `ReportService::forMonth()` aggregation

**Files:**
- Create: `app/Services/ReportService.php`
- Test: `tests/Feature/ReportServiceTest.php`

**Interfaces:**
- Consumes: `WorkOrder`, `MaintenanceRecord`, `ChecksheetSession`, `Finding`, `MaintenanceRecordPart`, `MaintenanceRecordConsumable`, `MaintenanceRecordTool`.
- Produces: `ReportService::forMonth(int $year, int $month): array` returning keys: `workOrders`, `records`, `checksheets`, `findings` (Eloquent collections), and `spareParts`, `consumables` (collections of `['name','unit','qty','value']`), `tools` (collection of `['name','count']`).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ReportServiceTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Consumable;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRecordConsumable;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testForMonthAggregatesConsumableUsage()
    {
        $consumable = Consumable::create([
            'item_code' => 'C-1', 'name' => 'Oli', 'unit' => 'liter',
            'qty_actual' => 10, 'qty_minimum' => 0, 'unit_price' => 50000,
        ]);
        $record = MaintenanceRecord::create([
            'record_number' => 'MR-TEST-0001',
            'maintenance_date' => '2026-07-10',
            'technician_id' => 1,
            'duration_minutes' => 30, 'shutdown_minutes' => 0,
            'status_after' => 'solved',
        ]);
        MaintenanceRecordConsumable::create([
            'maintenance_record_id' => $record->id,
            'consumable_id' => $consumable->id,
            'qty_used' => 4, 'unit_price' => 50000,
        ]);

        $data = ReportService::forMonth(2026, 7);

        $this->assertCount(1, $data['records']);
        $this->assertCount(1, $data['consumables']);
        $this->assertEquals(4, $data['consumables'][0]['qty']);
        $this->assertEquals(200000, $data['consumables'][0]['value']);
    }

    /** @test */
    public function testForMonthExcludesOtherMonths()
    {
        MaintenanceRecord::create([
            'record_number' => 'MR-TEST-0002',
            'maintenance_date' => '2026-06-10',
            'technician_id' => 1,
            'duration_minutes' => 30, 'shutdown_minutes' => 0,
            'status_after' => 'solved',
        ]);

        $data = ReportService::forMonth(2026, 7);

        $this->assertCount(0, $data['records']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReportServiceTest`
Expected: FAIL — class `App\Services\ReportService` not found.

- [ ] **Step 3: Implement `ReportService`**

Create `app/Services/ReportService.php`:

```php
<?php

namespace App\Services;

use App\Models\ChecksheetSession;
use App\Models\Finding;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRecordConsumable;
use App\Models\MaintenanceRecordPart;
use App\Models\MaintenanceRecordTool;
use App\Models\WorkOrder;

class ReportService
{
    public static function forMonth(int $year, int $month): array
    {
        $workOrders = WorkOrder::with('asset')
            ->whereYear('order_date', $year)->whereMonth('order_date', $month)
            ->get();

        $records = MaintenanceRecord::with(['asset', 'technician'])
            ->whereYear('maintenance_date', $year)->whereMonth('maintenance_date', $month)
            ->get();

        $checksheets = ChecksheetSession::with('schedule')
            ->whereNotNull('submitted_at')
            ->whereYear('submitted_at', $year)->whereMonth('submitted_at', $month)
            ->get();

        $findings = Finding::whereYear('found_date', $year)->whereMonth('found_date', $month)->get();

        $recordIds = $records->pluck('id');

        $spareParts = MaintenanceRecordPart::with('sparePart')
            ->whereIn('maintenance_record_id', $recordIds)->get()
            ->groupBy('spare_part_id')
            ->map(fn ($rows) => [
                'name'  => $rows->first()->sparePart->name ?? '—',
                'unit'  => $rows->first()->sparePart->unit ?? '',
                'qty'   => $rows->sum('qty_used'),
                'value' => $rows->sum(fn ($r) => $r->qty_used * ($r->unit_price ?? 0)),
            ])->values();

        $consumables = MaintenanceRecordConsumable::with('consumable')
            ->whereIn('maintenance_record_id', $recordIds)->get()
            ->groupBy('consumable_id')
            ->map(fn ($rows) => [
                'name'  => $rows->first()->consumable->name ?? '—',
                'unit'  => $rows->first()->consumable->unit ?? '',
                'qty'   => $rows->sum('qty_used'),
                'value' => $rows->sum(fn ($r) => $r->qty_used * ($r->unit_price ?? 0)),
            ])->values();

        $tools = MaintenanceRecordTool::with('tool')
            ->whereIn('maintenance_record_id', $recordIds)->get()
            ->groupBy('tool_id')
            ->map(fn ($rows) => [
                'name'  => $rows->first()->tool->name ?? '—',
                'count' => $rows->count(),
            ])->values();

        return compact('workOrders', 'records', 'checksheets', 'findings', 'spareParts', 'consumables', 'tools');
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter=ReportServiceTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Stage (do not commit)**

```bash
git add app/Services/ReportService.php tests/Feature/ReportServiceTest.php
```

---

### Task 7: `ReportController` + route + nav entry + live page

**Files:**
- Create: `app/Http/Controllers/ReportController.php`
- Create: `resources/views/reports/index.blade.php`
- Modify: `routes/web.php` (add import + routes)
- Modify: `resources/views/layouts/app.blade.php` (add nav entry to `$nav`, line 81)
- Test: `tests/Feature/ReportPageTest.php`

**Interfaces:**
- Consumes: `ReportService::forMonth`.
- Produces: route `reports.index` (GET `reports`) rendering `reports.index` with `$year,$month,$years` + all `ReportService` keys.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/ReportPageTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Consumable;
use App\Models\MaintenanceRecord;
use App\Models\MaintenanceRecordConsumable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testReportPageShowsConsumableUsedThatMonth()
    {
        $user = User::factory()->create();
        $consumable = Consumable::create([
            'item_code' => 'C-1', 'name' => 'OliMesin', 'unit' => 'liter',
            'qty_actual' => 10, 'qty_minimum' => 0, 'unit_price' => 50000,
        ]);
        $record = MaintenanceRecord::create([
            'record_number' => 'MR-TEST-0001', 'maintenance_date' => '2026-07-10',
            'technician_id' => $user->id, 'duration_minutes' => 30,
            'shutdown_minutes' => 0, 'status_after' => 'solved',
        ]);
        MaintenanceRecordConsumable::create([
            'maintenance_record_id' => $record->id, 'consumable_id' => $consumable->id,
            'qty_used' => 4, 'unit_price' => 50000,
        ]);

        $response = $this->actingAs($user)->get(route('reports.index', ['year' => 2026, 'month' => 7]));

        $response->assertOk();
        $response->assertSee('OliMesin');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=ReportPageTest`
Expected: FAIL — route `reports.index` not defined.

- [ ] **Step 3: Create the controller**

Create `app/Http/Controllers/ReportController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);
        $years = range(now()->year - 2, now()->year + 1);

        $data = ReportService::forMonth($year, $month);

        return view('reports.index', array_merge($data, compact('year', 'month', 'years')));
    }
}
```

- [ ] **Step 4: Add the route**

In `routes/web.php`, add the import near the other controller imports:

```php
use App\Http\Controllers\ReportController;
```

and inside the `auth`/`verified` group (e.g. after the Schedule Report routes), add:

```php
    // Reports (monthly activity & item usage)
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
```

- [ ] **Step 5: Create the view**

Create `resources/views/reports/index.blade.php`:

```blade
<x-app-layout>
    <div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-6">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <h1 class="text-xl font-bold text-gray-900">Monthly Report</h1>
            <form method="GET" action="{{ route('reports.index') }}" class="flex gap-2">
                <select name="month" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                    @endforeach
                </select>
                <select name="year" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 bg-brand text-white rounded-lg text-sm font-medium">Apply</button>
                <a href="{{ route('reports.pdf', ['year' => $year, 'month' => $month]) }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium">Export PDF</a>
            </form>
        </div>

        {{-- Activities --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="font-semibold text-gray-900 mb-2">Work Orders ({{ $workOrders->count() }})</h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    @forelse($workOrders as $wo)
                        <li>{{ $wo->wo_number }} — {{ $wo->title }} <span class="text-xs uppercase text-gray-400">({{ $wo->status }})</span></li>
                    @empty<li class="text-gray-400">None</li>@endforelse
                </ul>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="font-semibold text-gray-900 mb-2">Maintenance Records ({{ $records->count() }})</h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    @forelse($records as $r)
                        <li>{{ $r->record_number }} — {{ $r->asset->name ?? '—' }} <span class="text-xs uppercase text-gray-400">({{ $r->status_after }})</span></li>
                    @empty<li class="text-gray-400">None</li>@endforelse
                </ul>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="font-semibold text-gray-900 mb-2">Checksheets ({{ $checksheets->count() }})</h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    @forelse($checksheets as $cs)
                        <li>{{ $cs->schedule->trafo_name ?? ('Session #'.$cs->id) }}</li>
                    @empty<li class="text-gray-400">None</li>@endforelse
                </ul>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <h2 class="font-semibold text-gray-900 mb-2">Findings ({{ $findings->count() }})</h2>
                <ul class="text-sm text-gray-600 space-y-1">
                    @forelse($findings as $f)
                        <li>{{ $f->title }} <span class="text-xs uppercase text-gray-400">({{ $f->status }})</span></li>
                    @empty<li class="text-gray-400">None</li>@endforelse
                </ul>
            </div>
        </div>

        {{-- Items used --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-900">Barang Terpakai</h2></div>
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-5 py-3 text-left">Item</th><th class="px-5 py-3 text-left">Type</th>
                    <th class="px-5 py-3 text-left">Qty</th><th class="px-5 py-3 text-left">Value</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($spareParts as $it)
                        <tr><td class="px-5 py-3">{{ $it['name'] }}</td><td class="px-5 py-3 text-gray-500">Spare Part</td><td class="px-5 py-3">{{ $it['qty'] }} {{ $it['unit'] }}</td><td class="px-5 py-3">IDR {{ number_format($it['value']) }}</td></tr>
                    @endforeach
                    @foreach($consumables as $it)
                        <tr><td class="px-5 py-3">{{ $it['name'] }}</td><td class="px-5 py-3 text-gray-500">Consumable</td><td class="px-5 py-3">{{ $it['qty'] }} {{ $it['unit'] }}</td><td class="px-5 py-3">IDR {{ number_format($it['value']) }}</td></tr>
                    @endforeach
                    @foreach($tools as $it)
                        <tr><td class="px-5 py-3">{{ $it['name'] }}</td><td class="px-5 py-3 text-gray-500">Tool</td><td class="px-5 py-3">{{ $it['count'] }}x used</td><td class="px-5 py-3">—</td></tr>
                    @endforeach
                    @if($spareParts->isEmpty() && $consumables->isEmpty() && $tools->isEmpty())
                        <tr><td colspan="4" class="px-5 py-3 text-gray-400 text-center">No items used this month</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
```

- [ ] **Step 6: Add the nav entry**

In `resources/views/layouts/app.blade.php`, inside the `$nav` array (after the `work-orders.index` item, line 81), add:

```php
                        ['route' => 'reports.index', 'label' => 'Reports', 'icon' => 'M9 17v-6 M12 17V7 M15 17v-3 M4 4h16v16H4z', 'match' => 'reports*', 'roles' => null],
```

- [ ] **Step 7: Run test to verify it passes**

Run: `php artisan test --filter=ReportPageTest`
Expected: PASS.

- [ ] **Step 8: Stage (do not commit)**

```bash
git add app/Http/Controllers/ReportController.php resources/views/reports/index.blade.php routes/web.php resources/views/layouts/app.blade.php tests/Feature/ReportPageTest.php
```

---

### Task 8: PDF export

**Files:**
- Modify: `app/Http/Controllers/ReportController.php` (add `exportPdf`)
- Create: `resources/views/reports/pdf/monthly.blade.php`
- Modify: `routes/web.php` (add `reports.pdf` route)

**Interfaces:**
- Consumes: `ReportService::forMonth`, `Barryvdh\DomPDF\Facade\Pdf`.
- Produces: route `reports.pdf` (GET `reports/pdf`) returning a PDF download.

- [ ] **Step 1: Add the route**

In `routes/web.php`, directly after the `reports.index` route:

```php
    Route::get('reports/pdf', [ReportController::class, 'exportPdf'])->name('reports.pdf');
```

- [ ] **Step 2: Add `exportPdf` to the controller**

```php
    public function exportPdf(Request $request)
    {
        $year  = (int) $request->get('year', now()->year);
        $month = (int) $request->get('month', now()->month);

        $data = ReportService::forMonth($year, $month);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.pdf.monthly',
            array_merge($data, compact('year', 'month'))
        )->setPaper('a4', 'portrait');

        $monthName = \Carbon\Carbon::create()->month($month)->format('F');
        return $pdf->download("LAPORAN_BULANAN_{$monthName}_{$year}.pdf");
    }
```

- [ ] **Step 3: Create the PDF view**

Create `resources/views/reports/pdf/monthly.blade.php`:

```blade
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
    h1 { font-size: 16px; margin: 0 0 2px; }
    h2 { font-size: 13px; margin: 16px 0 6px; border-bottom: 1px solid #999; padding-bottom: 2px; }
    table { width: 100%; border-collapse: collapse; margin-top: 4px; }
    th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
    th { background: #f0f0f0; }
    .muted { color: #888; }
</style>
</head>
<body>
    <h1>Laporan Bulanan</h1>
    <div class="muted">{{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</div>

    <h2>Work Orders ({{ $workOrders->count() }})</h2>
    <table>
        <thead><tr><th>No</th><th>Title</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($workOrders as $wo)
            <tr><td>{{ $wo->wo_number }}</td><td>{{ $wo->title }}</td><td>{{ $wo->status }}</td></tr>
        @empty<tr><td colspan="3" class="muted">None</td></tr>@endforelse
        </tbody>
    </table>

    <h2>Maintenance Records ({{ $records->count() }})</h2>
    <table>
        <thead><tr><th>No</th><th>Asset</th><th>Date</th><th>Result</th></tr></thead>
        <tbody>
        @forelse($records as $r)
            <tr><td>{{ $r->record_number }}</td><td>{{ $r->asset->name ?? '—' }}</td><td>{{ $r->maintenance_date->format('d M Y') }}</td><td>{{ $r->status_after }}</td></tr>
        @empty<tr><td colspan="4" class="muted">None</td></tr>@endforelse
        </tbody>
    </table>

    <h2>Checksheets ({{ $checksheets->count() }})</h2>
    <table>
        <thead><tr><th>Session</th><th>Submitted</th></tr></thead>
        <tbody>
        @forelse($checksheets as $cs)
            <tr><td>{{ $cs->schedule->trafo_name ?? ('#'.$cs->id) }}</td><td>{{ optional($cs->submitted_at)->format('d M Y') }}</td></tr>
        @empty<tr><td colspan="2" class="muted">None</td></tr>@endforelse
        </tbody>
    </table>

    <h2>Findings ({{ $findings->count() }})</h2>
    <table>
        <thead><tr><th>Title</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($findings as $f)
            <tr><td>{{ $f->title }}</td><td>{{ $f->status }}</td></tr>
        @empty<tr><td colspan="2" class="muted">None</td></tr>@endforelse
        </tbody>
    </table>

    <h2>Barang Terpakai</h2>
    <table>
        <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Value</th></tr></thead>
        <tbody>
        @foreach($spareParts as $it)
            <tr><td>{{ $it['name'] }}</td><td>Spare Part</td><td>{{ $it['qty'] }} {{ $it['unit'] }}</td><td>IDR {{ number_format($it['value']) }}</td></tr>
        @endforeach
        @foreach($consumables as $it)
            <tr><td>{{ $it['name'] }}</td><td>Consumable</td><td>{{ $it['qty'] }} {{ $it['unit'] }}</td><td>IDR {{ number_format($it['value']) }}</td></tr>
        @endforeach
        @foreach($tools as $it)
            <tr><td>{{ $it['name'] }}</td><td>Tool</td><td>{{ $it['count'] }}x</td><td>—</td></tr>
        @endforeach
        @if($spareParts->isEmpty() && $consumables->isEmpty() && $tools->isEmpty())
            <tr><td colspan="4" class="muted">No items used this month</td></tr>
        @endif
        </tbody>
    </table>
</body>
</html>
```

- [ ] **Step 4: Manual verification**

Run the app, open `reports?year=2026&month=7`, click **Export PDF**. Expected: a PDF downloads titled `LAPORAN_BULANAN_July_2026.pdf` with the activity + items sections.

- [ ] **Step 5: Stage (do not commit)**

```bash
git add app/Http/Controllers/ReportController.php resources/views/reports/pdf/monthly.blade.php routes/web.php
```

---

## Phase 3 — Automatic monthly snapshot + archive

### Task 9: `monthly_reports` table + model

**Files:**
- Create: `database/migrations/2026_07_19_000003_create_monthly_reports_table.php`
- Create: `app/Models/MonthlyReport.php`

**Interfaces:**
- Produces: table + `MonthlyReport` model, `$fillable = ['year','month','location_id','pdf_path','generated_at','generated_by_user_id']`, `generated_at` cast datetime.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('pdf_path');
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['year', 'month', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_reports');
    }
};
```

- [ ] **Step 2: Write the model**

Create `app/Models/MonthlyReport.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    protected $fillable = ['year', 'month', 'location_id', 'pdf_path', 'generated_at', 'generated_by_user_id'];

    protected function casts(): array
    {
        return ['generated_at' => 'datetime'];
    }
}
```

- [ ] **Step 3: Run the migration**

Run: `php artisan migrate`
Expected: `monthly_reports` table created.

- [ ] **Step 4: Stage (do not commit)**

```bash
git add database/migrations/2026_07_19_000003_create_monthly_reports_table.php app/Models/MonthlyReport.php
```

---

### Task 10: `cmms:generate-monthly-report` command + scheduler

**Files:**
- Create: `app/Console/Commands/GenerateMonthlyReport.php`
- Modify: `routes/console.php` (register schedule)
- Test: `tests/Feature/GenerateMonthlyReportCommandTest.php`

**Interfaces:**
- Consumes: `ReportService::forMonth`, `MonthlyReport`, `Storage`, DomPDF.
- Produces: command `cmms:generate-monthly-report {--year=} {--month=}` — defaults to the previous month; renders the PDF, stores it on the default disk under `monthly-reports/`, upserts a `MonthlyReport` row.

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/GenerateMonthlyReportCommandTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\MonthlyReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateMonthlyReportCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testCommandGeneratesAndArchivesReport()
    {
        Storage::fake();

        $this->artisan('cmms:generate-monthly-report', ['--year' => 2026, '--month' => 7])
            ->assertExitCode(0);

        $report = MonthlyReport::where('year', 2026)->where('month', 7)->first();
        $this->assertNotNull($report);
        Storage::assertExists($report->pdf_path);
    }

    /** @test */
    public function testCommandIsIdempotentPerMonth()
    {
        Storage::fake();

        $this->artisan('cmms:generate-monthly-report', ['--year' => 2026, '--month' => 7]);
        $this->artisan('cmms:generate-monthly-report', ['--year' => 2026, '--month' => 7]);

        $this->assertEquals(1, MonthlyReport::where('year', 2026)->where('month', 7)->count());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=GenerateMonthlyReportCommandTest`
Expected: FAIL — command `cmms:generate-monthly-report` not found.

- [ ] **Step 3: Write the command**

Create `app/Console/Commands/GenerateMonthlyReport.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\MonthlyReport;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateMonthlyReport extends Command
{
    protected $signature = 'cmms:generate-monthly-report {--year=} {--month=}';
    protected $description = 'Generate and archive the monthly activity & item-usage report PDF';

    public function handle(): int
    {
        $target = now()->subMonthNoOverflow()->startOfMonth();
        $year  = (int) ($this->option('year') ?: $target->year);
        $month = (int) ($this->option('month') ?: $target->month);

        $data = ReportService::forMonth($year, $month);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.pdf.monthly',
            array_merge($data, compact('year', 'month'))
        )->setPaper('a4', 'portrait');

        $path = 'monthly-reports/LAPORAN_BULANAN_' . $year . '_' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.pdf';
        Storage::put($path, $pdf->output());

        MonthlyReport::updateOrCreate(
            ['year' => $year, 'month' => $month, 'location_id' => null],
            ['pdf_path' => $path, 'generated_at' => now(), 'generated_by_user_id' => null],
        );

        $this->info("Generated monthly report for {$year}-{$month}: {$path}");

        return self::SUCCESS;
    }
}
```

- [ ] **Step 4: Register in the scheduler**

In `routes/console.php`, after the existing `Schedule::command(...)` lines, add:

```php
Schedule::command('cmms:generate-monthly-report')->monthlyOn(1, '00:30')->description('Archive previous month activity & item-usage report');
```

- [ ] **Step 5: Run test to verify it passes**

Run: `php artisan test --filter=GenerateMonthlyReportCommandTest`
Expected: PASS (2 tests).

- [ ] **Step 6: Stage (do not commit)**

```bash
git add app/Console/Commands/GenerateMonthlyReport.php routes/console.php tests/Feature/GenerateMonthlyReportCommandTest.php
```

---

### Task 11: Archive tab on the Reports page + download route

**Files:**
- Modify: `app/Http/Controllers/ReportController.php` (`index` passes `$archives`; add `download`)
- Modify: `resources/views/reports/index.blade.php` (archive list section)
- Modify: `routes/web.php` (add `reports.download` route)

**Interfaces:**
- Consumes: `MonthlyReport`, `Storage`.
- Produces: route `reports.download` (GET `reports/{monthlyReport}/download`) streaming the archived PDF; `$archives` in the index view.

- [ ] **Step 1: Pass archives from `index`**

In `ReportController::index`, add `use App\Models\MonthlyReport;` at the top, and before the `return`:

```php
        $archives = MonthlyReport::orderByDesc('year')->orderByDesc('month')->get();
```

then include it:

```php
        return view('reports.index', array_merge($data, compact('year', 'month', 'years', 'archives')));
```

- [ ] **Step 2: Add the `download` method**

```php
    public function download(\App\Models\MonthlyReport $monthlyReport)
    {
        abort_unless(\Illuminate\Support\Facades\Storage::exists($monthlyReport->pdf_path), 404);
        return \Illuminate\Support\Facades\Storage::download($monthlyReport->pdf_path);
    }
```

- [ ] **Step 3: Add the route**

In `routes/web.php`, after `reports.pdf`:

```php
    Route::get('reports/{monthlyReport}/download', [ReportController::class, 'download'])->name('reports.download');
```

- [ ] **Step 4: Add the archive section to the view**

In `resources/views/reports/index.blade.php`, before the final closing `</div>` of the page container (`</div>` that closes `.max-w-7xl`), add:

```blade
        {{-- Archived monthly reports --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100"><h2 class="font-semibold text-gray-900">Arsip Laporan Bulanan</h2></div>
            <table class="w-full text-sm">
                <thead><tr class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase">
                    <th class="px-5 py-3 text-left">Period</th><th class="px-5 py-3 text-left">Generated</th><th class="px-5 py-3 text-left"></th>
                </tr></thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($archives as $a)
                        <tr>
                            <td class="px-5 py-3">{{ \Carbon\Carbon::create()->month($a->month)->format('F') }} {{ $a->year }}</td>
                            <td class="px-5 py-3 text-gray-500">{{ optional($a->generated_at)->format('d M Y H:i') }}</td>
                            <td class="px-5 py-3"><a href="{{ route('reports.download', $a) }}" class="text-brand font-medium">Download</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-3 text-gray-400 text-center">No archived reports yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
```

- [ ] **Step 5: Manual verification**

Run: `php artisan cmms:generate-monthly-report --year=2026 --month=7`, then open the Reports page. Expected: the archive table lists "July 2026" with a working Download link.

- [ ] **Step 6: Stage (do not commit)**

```bash
git add app/Http/Controllers/ReportController.php resources/views/reports/index.blade.php routes/web.php
```

---

## Post-implementation: Laravel Cloud storage note

The command writes PDFs to the **default filesystem disk** (`FILESYSTEM_DISK`, currently `local`). On Laravel Cloud the local disk is **ephemeral** — archived PDFs will vanish on redeploy. Before relying on the archive in production, set `FILESYSTEM_DISK` (and the archive writes) to a persistent object-storage disk (S3-compatible). `Storage::put`/`Storage::download` need no code change — only the disk config. Confirm the bucket/credentials with the deployment owner.

---

## Self-Review

- **Spec coverage:** capture consumables (Tasks 1–5) ✓; capture tools log-only (Tasks 1–5) ✓; Reports menu live page (Tasks 6–7) ✓; item usage report incl. spare parts + consumables + tools (Task 6/7) ✓; activities WO/records/checksheets/findings (Task 6/7) ✓; PDF export (Task 8) ✓; auto monthly snapshot + scheduler (Tasks 9–10) ✓; archive tab (Task 11) ✓; Laravel Cloud storage caveat (post-impl note) ✓. Location filtering intentionally dropped (documented in Global Constraints).
- **Placeholder scan:** no TBD/TODO; all code blocks concrete.
- **Type consistency:** `ReportService::forMonth` keys (`workOrders,records,checksheets,findings,spareParts,consumables,tools`) produced in Task 6, consumed identically in Tasks 7, 8, 10. Item array shape `['name','unit','qty','value']` (spareParts/consumables) and `['name','count']` (tools) consistent across index view + PDF view. `deductConsumable(Consumable,int,int)` signature consistent between Task 3 (def) and Task 4 (call). Blade loop vars `$cItem`/`$tItem` avoid shadowing Alpine `c`/`t`.
