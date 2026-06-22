# B3: Stock Integrity via Service Layer — Design Spec

**Date:** 2026-06-22 · **Author:** CTO · **Status:** Ready for Review · **Priority:** Blocker (rilis jangan lalu ini)

---

## Problem Statement

Aplikasi memiliki **3 lokasi yang mutasi stok** (SparePart.qty_actual):
1. `MaintenanceRecordController:186` — decrement saat submit maintenance record
2. `SparePartController:104` — increment saat add stock (penerimaan)
3. `SparePartController:109` — decrement saat adjust stock (koreksi/opname)

**Current state (risiko tinggi):**
- Tidak ada validasi kecukupan stok sebelum decrement (stok bisa **negatif**)
- Tidak ada `lockForUpdate()` → race condition (2 request concurrent bisa decrement overlap)
- Hanya 1 file pakai `DB::transaction` → operasi multi-tabel tidak atomic (setengah jadi saat error)
- Tidak ada audit trail (siapa, kapan, berapa banyak, alasan)

**Impact:** Client bisa salah hitung inventory, keputusan maintenance berdasarkan stok salah.

---

## Scope & Constraints

✅ **In scope:**
- Perbaiki ketiga lokasi mutasi stok
- Validasi: tidak boleh jadi negatif (baik decrement maupun adjustment)
- Transaksi + lock pada semua operasi
- Audit trail (siapa, kapan, berapa, alasan)
- TDD untuk logika kritis (5 unit + 3 feature test)

❌ **Out of scope:**
- Multi-lokasi stok (transfer antar lokasi) — future feature
- Forecast/planning stok — future feature
- Backfill data negative existing — asumsikan data clean

---

## Architecture

### **New Components**

#### 1. Service: `App\Services\StockService`

**Public methods:**

```php
/**
 * Deduct stock (pemakaian / adjustment).
 * @throws OutOfStockException jika qty_actual < $qty
 * @throws QueryException jika lock timeout / DB error
 * @return void
 */
public static function deduct(
    SparePart $sparePart,
    int $qty,
    string $reason,  // 'maintenance_record' | 'stock_adjustment'
    int $userId      // auth()->id()
): void

/**
 * Add stock (penerimaan baru).
 * Tidak ada validasi kecukupan (always succeed).
 * @return void
 */
public static function add(
    SparePart $sparePart,
    int $qty,
    string $reason,  // 'stock_adjustment' | 'purchase_received'
    int $userId
): void
```

**Internal flow (both methods):**
```
1. DB::transaction begin
2. SparePart::lockForUpdate()->find($sparePart->id)  // baca row terbaru, lock
3. IF deduct: cek qty_actual >= $qty (else throw OutOfStockException)
4. $sparePart->decrement() or increment()
5. StockMovement::create(['spare_part_id' => ..., 'mutation_type' => $deduct|$add, ...])
6. Commit transaksi
```

#### 2. Migration: `stock_movements` Table

```
CREATE TABLE stock_movements (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    spare_part_id BIGINT UNSIGNED NOT NULL,
    mutation_type ENUM('deduct', 'add') NOT NULL,
    qty INT NOT NULL,
    reason VARCHAR(50) NOT NULL,  -- 'maintenance_record', 'stock_adjustment', etc
    created_by_user_id BIGINT UNSIGNED NULLABLE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (spare_part_id) REFERENCES spare_parts(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (spare_part_id, created_at),
    INDEX (created_by_user_id)
);
```

#### 3. Model: `App\Models\StockMovement`

```php
class StockMovement extends Model {
    public $timestamps = false;  // only created_at
    protected $fillable = ['spare_part_id', 'mutation_type', 'qty', 'reason', 'created_by_user_id'];
    
    public function sparePart() { return $this->belongsTo(SparePart::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by_user_id'); }
}
```

---

## Data Flow & Changes

### **Flow 1: MaintenanceRecord Submit**
**File:** `MaintenanceRecordController::store()` (around L92–198)

**Current:**
```php
DB::transaction(function() {
    $record = MaintenanceRecord::create(...);
    foreach ($parts as $part) {
        $sparePart->decrement('qty_actual', $part['qty_used']);  // ❌ no validation, no lock
    }
});
```

**New:**
```php
DB::transaction(function() {
    $record = MaintenanceRecord::create(...);
    foreach ($parts as $part) {
        StockService::deduct($sparePart, $part['qty_used'], 'maintenance_record', auth()->id());
        // StockService handles own transaction + lock internally
    }
});
```

⚠️ **Note:** Nested transaction. Laravel handle ini via savepoint, OK.

### **Flow 2: SparePartController::adjustStock()**
**File:** `SparePartController::adjustStock()` (around L104–109)

**Current:**
```php
if ($request->type === 'add') {
    $sparePart->increment('qty_actual', $request->quantity);  // ✓ no validation needed
} else {
    if ($request->quantity > $sparePart->qty_actual) {  // ❌ race condition
        abort(422, 'Insufficient stock');
    }
    $sparePart->decrement('qty_actual', $request->quantity);  // ❌ no lock
}
```

**New:**
```php
if ($request->type === 'add') {
    StockService::add($sparePart, $request->quantity, 'stock_adjustment', auth()->id());
} else {
    try {
        StockService::deduct($sparePart, $request->quantity, 'stock_adjustment', auth()->id());
    } catch (OutOfStockException $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }
}
```

---

## Error Handling

| Exception | HTTP | Message | Action |
|-----------|------|---------|--------|
| `OutOfStockException` | 422 | "Spare part '{name}' tidak cukup. Tersedia: {qty_actual}, Diminta: {qty}" | User retry or adjust qty |
| `QueryException` (lock timeout) | 500 | "Gagal update stok (lock timeout). Coba lagi dalam beberapa detik." | Retry request |
| `ModelNotFoundException` | 404 | Standard Laravel 404 | — |

---

## Testing

### **Unit Tests (5)**

**File:** `tests/Unit/Services/StockServiceTest.php`

1. `testDeductSucceedsWhenSufficientStock` — normal case, stok berkurang
2. `testDeductThrowsOutOfStockException` — qty > available, exception raised
3. `testAddAlwaysSucceeds` — add tidak ada validasi
4. `testStockMovementRecorded` — setiap mutasi tercatat
5. `testConcurrentDeductIsAtomic` — 2 concurrent request, 1 succeed 1 fail (lock + transaksi)

### **Feature Tests (3)**

**File:** `tests/Feature/MaintenanceRecordControllerTest.php` + `SparePartControllerTest.php`

1. `testSubmitMaintenanceRecordDecductsStock` — record submit → stok berkurang + log ada
2. `testSubmitFailsIfStockInsufficient` — stok <qty → error 422, record **tidak dibuat** (atomicity), stok tidak berubah
3. `testAdjustStockDeductFails` — adjustment deduct > available → 422, stok tetap

---

## Backward Compatibility

✅ **No breaking change:**
- API response tetap sama
- Existing data assumed clean (no negative quantities)
- `stock_movements` bersifat read-only untuk audit, tidak mengubah logika existing

⚠️ **Perlu testing:** Verifikasi existing audit/dashboard tidak rusak.

---

## Definition of Done

- [ ] Migration & model created, tested
- [ ] `StockService` implemented, all unit test pass
- [ ] 3 controller updated + feature test pass
- [ ] Concurrent test pass (race condition solved)
- [ ] Manual verification: submit maintenance record, check `stock_movements` table terisi
- [ ] `stock_movements` query fast (index created)
- [ ] No existing test regresi
- [ ] Code review passed
- [ ] Commit to feature branch (not `main` yet)

---

## Questions for User Review

1. Audit trail di `stock_movements` — cukup field-nya (qty, reason, created_by, created_at)?
2. Reason enum values — 'maintenance_record' | 'stock_adjustment' | 'purchase_received'? Ada lagi?
3. Transaksi nested (MaintenanceRecord + StockService keduanya transaksi) OK pakai savepoint?
4. Concurrent test: simulasi 2 request pakai phpunit or manual curl? (Rekomendasi: manual curl test lebih realistik)
