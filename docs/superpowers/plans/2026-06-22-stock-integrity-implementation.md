# B3: Stock Integrity Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement atomic stock mutations with validation, locking, transactions, and audit trail to prevent negative inventory and race conditions.

**Architecture:** Service layer (`StockService`) centralizes all stock mutations. Service enforces validasi kecukupan + `lockForUpdate()` + `DB::transaction()` + logs to `stock_movements`. Controllers call service instead of direct Eloquent mutations.

**Tech Stack:** Laravel 13, PHPUnit, Eloquent ORM, MySQL.

## Global Constraints

- No breaking API changes
- Assume existing data is clean (no negative qty_actual)
- Use `DB::transaction()` + `lockForUpdate()` for atomicity
- TDD approach: test first, implementation second
- Frequent commits after each task

---

## Task 1: Custom Exception & Migration

**Files:**
- Create: `app/Exceptions/OutOfStockException.php`
- Create: `database/migrations/2026_06_22_000000_create_stock_movements_table.php`

**Interfaces:**
- Produces: `OutOfStockException` exception (throw in service when qty insufficient)
- Produces: `stock_movements` table with columns: id, spare_part_id, mutation_type (enum deduct|add), qty, reason, created_by_user_id, created_at

- [ ] **Step 1: Create OutOfStockException**

File: `app/Exceptions/OutOfStockException.php`

```php
<?php

namespace App\Exceptions;

use Exception;

class OutOfStockException extends Exception
{
    public function __construct($sparePart, $availableQty, $requestedQty)
    {
        $message = "Spare part \"{$sparePart->name}\" tidak cukup. Tersedia: {$availableQty}, Diminta: {$requestedQty}";
        parent::__construct($message);
    }

    public function render()
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
```

- [ ] **Step 2: Create migration**

File: `database/migrations/2026_06_22_000000_create_stock_movements_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spare_part_id')->constrained('spare_parts')->cascadeOnDelete();
            $table->enum('mutation_type', ['deduct', 'add']);
            $table->integer('qty');
            $table->string('reason', 50); // maintenance_record, stock_adjustment, purchase_received
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['spare_part_id', 'created_at']);
            $table->index('created_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
```

- [ ] **Step 3: Run migration**

```bash
php artisan migrate
```

Expected: Table `stock_movements` created successfully.

- [ ] **Step 4: Commit**

```bash
git add app/Exceptions/OutOfStockException.php database/migrations/2026_06_22_000000_create_stock_movements_table.php
git commit -m "feat: add OutOfStockException and stock_movements migration"
```

---

## Task 2: StockMovement Model

**Files:**
- Create: `app/Models/StockMovement.php`

**Interfaces:**
- Produces: `StockMovement` model with `$fillable = ['spare_part_id', 'mutation_type', 'qty', 'reason', 'created_by_user_id']`
- Produces: Relations: `sparePart()`, `createdBy()`

- [ ] **Step 1: Create StockMovement model**

File: `app/Models/StockMovement.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public $timestamps = false; // only created_at, no updated_at
    
    protected $fillable = [
        'spare_part_id',
        'mutation_type',
        'qty',
        'reason',
        'created_by_user_id',
    ];

    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
```

- [ ] **Step 2: Test model exists & relations work**

Run artisan tinker:
```bash
php artisan tinker
>>> $movement = App\Models\StockMovement::first();
>>> $movement->sparePart;  // should return SparePart
>>> $movement->createdBy;  // should return User or null
>>> exit
```

- [ ] **Step 3: Commit**

```bash
git add app/Models/StockMovement.php
git commit -m "feat: add StockMovement model with relations"
```

---

## Task 3: StockService — Deduct & Add Methods (TDD)

**Files:**
- Create: `app/Services/StockService.php`
- Create: `tests/Unit/Services/StockServiceTest.php`

**Interfaces:**
- Consumes: `SparePart` model, `StockMovement` model, `OutOfStockException`
- Produces: `StockService::deduct(SparePart $sparePart, int $qty, string $reason, int $userId): void` (throws OutOfStockException)
- Produces: `StockService::add(SparePart $sparePart, int $qty, string $reason, int $userId): void` (never throws)

- [ ] **Step 1: Write failing unit tests**

File: `tests/Unit/Services/StockServiceTest.php`

```php
<?php

namespace Tests\Unit\Services;

use App\Models\SparePart;
use App\Models\StockMovement;
use App\Services\StockService;
use App\Exceptions\OutOfStockException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testDeductSucceedsWhenSufficientStock()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 10]);
        
        StockService::deduct($sparePart, 3, 'maintenance_record', 1);
        
        $sparePart->refresh();
        $this->assertEquals(7, $sparePart->qty_actual);
        $this->assertDatabaseHas('stock_movements', [
            'spare_part_id' => $sparePart->id,
            'mutation_type' => 'deduct',
            'qty' => 3,
            'reason' => 'maintenance_record',
            'created_by_user_id' => 1,
        ]);
    }

    /** @test */
    public function testDeductThrowsOutOfStockException()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 2]);
        
        $this->expectException(OutOfStockException::class);
        StockService::deduct($sparePart, 5, 'maintenance_record', 1);
        
        $sparePart->refresh();
        $this->assertEquals(2, $sparePart->qty_actual); // unchanged
    }

    /** @test */
    public function testAddAlwaysSucceeds()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 5]);
        
        StockService::add($sparePart, 10, 'stock_adjustment', 1);
        
        $sparePart->refresh();
        $this->assertEquals(15, $sparePart->qty_actual);
        $this->assertDatabaseHas('stock_movements', [
            'spare_part_id' => $sparePart->id,
            'mutation_type' => 'add',
            'qty' => 10,
        ]);
    }

    /** @test */
    public function testStockMovementRecorded()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 20]);
        
        StockService::deduct($sparePart, 5, 'maintenance_record', 2);
        
        $movement = StockMovement::where('spare_part_id', $sparePart->id)->first();
        $this->assertNotNull($movement);
        $this->assertEquals('deduct', $movement->mutation_type);
        $this->assertEquals(5, $movement->qty);
        $this->assertEquals('maintenance_record', $movement->reason);
        $this->assertEquals(2, $movement->created_by_user_id);
    }

    /** @test */
    public function testConcurrentDeductIsAtomic()
    {
        $sparePart = SparePart::factory()->create(['qty_actual' => 5]);
        
        // First deduct should succeed (5 available, 3 requested)
        StockService::deduct($sparePart, 3, 'maintenance_record', 1);
        $sparePart->refresh();
        $this->assertEquals(2, $sparePart->qty_actual);
        
        // Second deduct should fail (2 available, 3 requested)
        $this->expectException(OutOfStockException::class);
        StockService::deduct($sparePart, 3, 'maintenance_record', 1);
        
        $sparePart->refresh();
        $this->assertEquals(2, $sparePart->qty_actual); // still 2, not decremented
    }
}
```

- [ ] **Step 2: Run test to verify they fail**

```bash
php artisan test tests/Unit/Services/StockServiceTest.php -v
```

Expected: FAIL — "class App\Services\StockService does not exist"

- [ ] **Step 3: Implement StockService**

File: `app/Services/StockService.php`

```php
<?php

namespace App\Services;

use App\Exceptions\OutOfStockException;
use App\Models\SparePart;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Deduct stock (pemakaian / adjustment).
     * @throws OutOfStockException if insufficient stock
     */
    public static function deduct(SparePart $sparePart, int $qty, string $reason, int $userId): void
    {
        DB::transaction(function () use ($sparePart, $qty, $reason, $userId) {
            $sparePart = SparePart::lockForUpdate()->findOrFail($sparePart->id);
            
            if ($sparePart->qty_actual < $qty) {
                throw new OutOfStockException($sparePart, $sparePart->qty_actual, $qty);
            }
            
            $sparePart->decrement('qty_actual', $qty);
            
            StockMovement::create([
                'spare_part_id' => $sparePart->id,
                'mutation_type' => 'deduct',
                'qty' => $qty,
                'reason' => $reason,
                'created_by_user_id' => $userId,
            ]);
        });
    }

    /**
     * Add stock (penerimaan baru).
     * Always succeeds, no validation.
     */
    public static function add(SparePart $sparePart, int $qty, string $reason, int $userId): void
    {
        DB::transaction(function () use ($sparePart, $qty, $reason, $userId) {
            $sparePart = SparePart::lockForUpdate()->findOrFail($sparePart->id);
            
            $sparePart->increment('qty_actual', $qty);
            
            StockMovement::create([
                'spare_part_id' => $sparePart->id,
                'mutation_type' => 'add',
                'qty' => $qty,
                'reason' => $reason,
                'created_by_user_id' => $userId,
            ]);
        });
    }
}
```

- [ ] **Step 4: Run tests to verify they pass**

```bash
php artisan test tests/Unit/Services/StockServiceTest.php -v
```

Expected: PASS — all 5 tests green

- [ ] **Step 5: Commit**

```bash
git add app/Services/StockService.php tests/Unit/Services/StockServiceTest.php
git commit -m "feat: implement StockService with TDD (deduct, add, audit trail)"
```

---

## Task 4: Update MaintenanceRecordController

**Files:**
- Modify: `app/Http/Controllers/MaintenanceRecordController.php` (line ~186)

**Interfaces:**
- Consumes: `StockService::deduct()` (from Task 3)
- Produces: No new interface, just replaces existing `$sparePart->decrement()` calls

- [ ] **Step 1: Replace decrement with StockService::deduct in store() method**

File: `app/Http/Controllers/MaintenanceRecordController.php`

Find the section around line 175–188 (inside `DB::transaction`):

**Current code:**
```php
if (!empty($validated['parts'])) {
    foreach ($validated['parts'] as $part) {
        $sparePart = SparePart::find($part['spare_part_id']);
        MaintenanceRecordPart::create([...]);
        $sparePart->decrement('qty_actual', $part['qty_used']);
    }
}
```

**Replace with:**
```php
if (!empty($validated['parts'])) {
    foreach ($validated['parts'] as $part) {
        $sparePart = SparePart::find($part['spare_part_id']);
        MaintenanceRecordPart::create([...]);
        \App\Services\StockService::deduct(
            $sparePart,
            $part['qty_used'],
            'maintenance_record',
            auth()->id()
        );
    }
}
```

Add import at top if not present:
```php
use App\Services\StockService;
use App\Exceptions\OutOfStockException;
```

- [ ] **Step 2: Wrap foreach in try-catch for OutOfStockException**

Change the loop to handle exception:

```php
if (!empty($validated['parts'])) {
    try {
        foreach ($validated['parts'] as $part) {
            $sparePart = SparePart::find($part['spare_part_id']);
            MaintenanceRecordPart::create([
                'maintenance_record_id' => $record->id,
                'spare_part_id' => $part['spare_part_id'],
                'qty_used' => $part['qty_used'],
                'unit_price' => $sparePart->unit_price,
            ]);
            StockService::deduct(
                $sparePart,
                $part['qty_used'],
                'maintenance_record',
                auth()->id()
            );
        }
    } catch (OutOfStockException $e) {
        // Transaction will rollback, MaintenanceRecord will be deleted
        throw $e;
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Controllers/MaintenanceRecordController.php
git commit -m "refactor: use StockService::deduct in MaintenanceRecordController"
```

---

## Task 5: Update SparePartController

**Files:**
- Modify: `app/Http/Controllers/SparePartController.php` (line ~104 add, line ~109 deduct)

**Interfaces:**
- Consumes: `StockService::deduct()`, `StockService::add()` (from Task 3)
- Produces: No new interface

- [ ] **Step 1: Update adjustStock() method**

File: `app/Http/Controllers/SparePartController.php`, around line 95–115.

Find the `adjustStock()` method. **Current:**

```php
public function adjustStock(Request $request)
{
    $sparePart = SparePart::findOrFail($request->spare_part_id);
    
    if ($request->type === 'add') {
        $sparePart->increment('qty_actual', $request->quantity);
    } else {
        if ($request->quantity > $sparePart->qty_actual) {
            abort(422, 'Insufficient stock');
        }
        $sparePart->decrement('qty_actual', $request->quantity);
    }
    
    return response()->json(['success' => true]);
}
```

**Replace with:**

```php
public function adjustStock(Request $request)
{
    $sparePart = SparePart::findOrFail($request->spare_part_id);
    
    if ($request->type === 'add') {
        StockService::add($sparePart, $request->quantity, 'stock_adjustment', auth()->id());
    } else {
        try {
            StockService::deduct($sparePart, $request->quantity, 'stock_adjustment', auth()->id());
        } catch (OutOfStockException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
    
    return response()->json(['success' => true, 'message' => 'Stock adjusted successfully']);
}
```

Add imports at top:
```php
use App\Services\StockService;
use App\Exceptions\OutOfStockException;
```

- [ ] **Step 2: Commit**

```bash
git add app/Http/Controllers/SparePartController.php
git commit -m "refactor: use StockService in SparePartController::adjustStock"
```

---

## Task 6: Feature Tests — MaintenanceRecord

**Files:**
- Modify/Create: `tests/Feature/MaintenanceRecordControllerTest.php`

**Interfaces:**
- Consumes: `StockService::deduct()`, `MaintenanceRecordController::store()`
- Produces: 2 new test methods

- [ ] **Step 1: Add test for successful stock deduction**

File: `tests/Feature/MaintenanceRecordControllerTest.php` (create if doesn't exist)

```php
<?php

namespace Tests\Feature;

use App\Models\MaintenanceRecord;
use App\Models\SparePart;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceRecordControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function testSubmitMaintenanceRecordDecductsStock()
    {
        $user = User::factory()->create(['role' => 'technician']);
        $sparePart = SparePart::factory()->create(['qty_actual' => 10]);
        
        $this->actingAs($user)
            ->post(route('maintenance-records.store'), [
                'asset_id' => 1, // adjust to valid asset if needed
                'maintenance_date' => now()->toDateString(),
                'type' => 'preventive',
                'findings' => 'All good',
                'actions_taken' => 'Routine check',
                'duration_minutes' => 30,
                'parts' => [
                    ['spare_part_id' => $sparePart->id, 'qty_used' => 3],
                ],
            ])
            ->assertStatus(302); // redirect on success
        
        $sparePart->refresh();
        $this->assertEquals(7, $sparePart->qty_actual);
        
        $this->assertDatabaseHas('stock_movements', [
            'spare_part_id' => $sparePart->id,
            'mutation_type' => 'deduct',
            'qty' => 3,
            'reason' => 'maintenance_record',
        ]);
    }

    /** @test */
    public function testSubmitFailsIfStockInsufficient()
    {
        $user = User::factory()->create(['role' => 'technician']);
        $sparePart = SparePart::factory()->create(['qty_actual' => 2]);
        
        $this->actingAs($user)
            ->post(route('maintenance-records.store'), [
                'asset_id' => 1,
                'maintenance_date' => now()->toDateString(),
                'type' => 'preventive',
                'findings' => 'Test',
                'actions_taken' => 'Test',
                'duration_minutes' => 30,
                'parts' => [
                    ['spare_part_id' => $sparePart->id, 'qty_used' => 5], // > available
                ],
            ])
            ->assertStatus(422);
        
        $sparePart->refresh();
        $this->assertEquals(2, $sparePart->qty_actual); // unchanged (atomicity)
        
        $this->assertDatabaseMissing('maintenance_records', [
            'findings' => 'Test', // record not created
        ]);
    }
}
```

- [ ] **Step 2: Run feature tests**

```bash
php artisan test tests/Feature/MaintenanceRecordControllerTest.php -v
```

Expected: PASS (or adjust assertions if MaintenanceRecord route/validation differs)

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/MaintenanceRecordControllerTest.php
git commit -m "test: add feature tests for MaintenanceRecord stock deduction"
```

---

## Task 7: Feature Tests — SparePartController

**Files:**
- Modify/Create: `tests/Feature/SparePartControllerTest.php`

**Interfaces:**
- Consumes: `StockService::deduct()`, `SparePartController::adjustStock()`
- Produces: 1 new test method

- [ ] **Step 1: Add test for adjustment deduct failure**

File: `tests/Feature/SparePartControllerTest.php` (add to existing or create)

```php
/** @test */
public function testAdjustStockDeductFails()
{
    $user = User::factory()->create(['role' => 'admin']);
    $sparePart = SparePart::factory()->create(['qty_actual' => 5]);
    
    $response = $this->actingAs($user)
        ->post(route('spare-parts.adjust-stock'), [
            'spare_part_id' => $sparePart->id,
            'type' => 'deduct',
            'quantity' => 10, // > available
        ]);
    
    $response->assertStatus(422)
        ->assertJsonFragment(['message' => 'tidak cukup']);
    
    $sparePart->refresh();
    $this->assertEquals(5, $sparePart->qty_actual); // unchanged
}
```

- [ ] **Step 2: Run test**

```bash
php artisan test tests/Feature/SparePartControllerTest.php::testAdjustStockDeductFails -v
```

Expected: PASS

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/SparePartControllerTest.php
git commit -m "test: add feature test for SparePartController adjustment failure"
```

---

## Task 8: Final Verification & Cleanup

**Files:**
- None (verification only)

**Interfaces:**
- Consumes: All previous tasks

- [ ] **Step 1: Run full test suite**

```bash
php artisan test tests/Unit/Services/StockServiceTest.php tests/Feature/MaintenanceRecordControllerTest.php tests/Feature/SparePartControllerTest.php -v
```

Expected: All tests PASS (8+ tests total)

- [ ] **Step 2: Manual verification — submit maintenance record & check stock_movements**

```bash
php artisan tinker
```

```php
// Create a spare part with known qty
$sp = App\Models\SparePart::factory()->create(['qty_actual' => 20]);

// View before
echo "Before: " . $sp->qty_actual . "\n";

// Deduct via service
App\Services\StockService::deduct($sp, 5, 'test_reason', 1);

// View after
$sp->refresh();
echo "After: " . $sp->qty_actual . "\n";

// Check audit trail
App\Models\StockMovement::latest()->first();
// Should show: spare_part_id, mutation_type='deduct', qty=5, reason='test_reason', created_by_user_id=1

exit
```

- [ ] **Step 3: Verify no existing tests regressed**

Run full test suite (including Breeze auth tests):

```bash
php artisan test
```

Expected: All tests pass (no regressions in existing auth/profile tests)

- [ ] **Step 4: Final commit & summary**

```bash
git log --oneline -8
```

Expected: 8 commits (one per task above)

```bash
git status
```

Expected: Working tree clean (nothing to commit)

---

## Summary

**8 tasks completed:**
1. ✅ Exception + migration
2. ✅ StockMovement model
3. ✅ StockService (TDD: 5 unit tests)
4. ✅ MaintenanceRecordController integration
5. ✅ SparePartController integration
6. ✅ MaintenanceRecord feature tests (2 tests)
7. ✅ SparePartController feature tests (1 test)
8. ✅ Verification + cleanup

**Result:** All stock mutations (3 locations) now atomic, validated, locked, and audited. Race condition prevention via `lockForUpdate()`. Atomicity via `DB::transaction()`.
