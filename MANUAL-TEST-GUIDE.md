# B3 Stock Integrity — Manual Testing Guide

Panduan testing B3 (Stock Integrity) secara manual via Docker untuk verifikasi production-readiness.

---

## Quick Start (Auto)

```bash
cd /home/naufa/workspace/SaaS-CMMS
chmod +x run-tests.sh
./run-tests.sh
```

Expected output:
```
Tests:  7 passed
Duration: Xs
```

---

## Manual Testing (Interactive)

Jika Anda ingin debug atau run tests satu per satu:

### 1. Start Docker Containers

```bash
cd /home/naufa/workspace/SaaS-CMMS
docker-compose -f docker-compose.test.yml up -d
```

Wait for MySQL to be ready (check logs):
```bash
docker logs cmms-mysql-test -f
# Wait for: "[System] [MY-000000] ready for connections"
```

### 2. SSH into App Container

```bash
docker exec -it cmms-test sh
```

### 3. Run Tests

**All tests:**
```bash
php artisan test
```

**Unit tests only:**
```bash
php artisan test tests/Unit/Services/StockServiceTest.php -v
```

**Feature tests only:**
```bash
php artisan test tests/Feature/StockAdjustmentTest.php -v
```

**Single test:**
```bash
php artisan test tests/Unit/Services/StockServiceTest.php::testDeductSucceedsWhenSufficientStock
```

### 4. Manual DB Inspection

Check stock mutations directly:

```bash
mysql -h 127.0.0.1 -P 3307 -u cmms_user -psecret cmms_test
```

Inside MySQL:
```sql
-- List all spare parts
SELECT id, name, qty_actual FROM spare_parts LIMIT 5;

-- List stock movements
SELECT * FROM stock_movements ORDER BY created_at DESC;

-- Check if a specific part has movements
SELECT * FROM stock_movements WHERE spare_part_id = 1;
```

### 5. Test Concurrent Mutations (Manual Concurrency Test)

Inside container (`docker exec -it cmms-test sh`):

```bash
# Create a test script that spawns concurrent requests
cat > test-concurrent.php << 'EOF'
<?php
require 'bootstrap/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SparePart;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();

// Create spare part with qty=5
$part = SparePart::factory()->create(['qty_actual' => 5]);

echo "Initial qty: {$part->qty_actual}\n";

// First deduct (should succeed)
try {
    StockService::deduct($part, 3, 'test', 1);
    $part->refresh();
    echo "After first deduct (qty=3): {$part->qty_actual}\n";
} catch (Exception $e) {
    echo "First deduct failed: {$e->getMessage()}\n";
}

// Second deduct (should fail - only 2 left, need 3)
try {
    StockService::deduct($part, 3, 'test', 1);
    echo "Second deduct succeeded (UNEXPECTED)\n";
} catch (\App\Exceptions\OutOfStockException $e) {
    echo "Second deduct failed as expected: {$e->getMessage()}\n";
}

// Verify qty unchanged after failed deduct
$part->refresh();
echo "Final qty (should still be 2): {$part->qty_actual}\n";

// Check audit trail
$movements = \App\Models\StockMovement::where('spare_part_id', $part->id)->get();
echo "Stock movements recorded: {$movements->count()}\n";

DB::rollBack();
EOF

php artisan tinker < test-concurrent.php
```

Expected output:
```
Initial qty: 5
After first deduct (qty=3): 2
Second deduct failed as expected: Spare part "..." tidak cukup. Tersedia: 2, Diminta: 3
Final qty (should still be 2): 2
Stock movements recorded: 1
```

### 6. Exit Container

```bash
exit
```

### 7. Stop & Cleanup

```bash
# Stop containers but keep volumes
docker-compose -f docker-compose.test.yml stop

# Full cleanup (delete volumes)
docker-compose -f docker-compose.test.yml down -v
```

---

## Expected Test Results

### Unit Tests (5 tests)
```
✓ testDeductSucceedsWhenSufficientStock
✓ testDeductThrowsOutOfStockException
✓ testAddAlwaysSucceeds
✓ testStockMovementRecorded
✓ testConcurrentDeductIsAtomic
```

### Feature Tests (2 tests)
```
✓ testAdjustStockAdd
✓ testAdjustStockDeductFails
```

**Total: 7 tests, all passing** ✅

---

## Troubleshooting

### Container won't start
```bash
docker-compose -f docker-compose.test.yml logs app
docker-compose -f docker-compose.test.yml logs mysql
```

### MySQL connection error
```
"SQLSTATE[HY000] [2002] Can't connect to local MySQL server"
```
→ MySQL container not ready. Wait 10-15 seconds, then retry.

### Port 3307 already in use
Edit `docker-compose.test.yml`:
```yaml
ports:
  - "3308:3306"  # Change to 3308
```

### Permission denied
```bash
sudo usermod -aG docker $USER
# Log out and back in
```

### Out of memory
```bash
# Clean unused Docker resources
docker system prune -a
```

---

## Performance Checks

After tests pass, verify:

1. **Migration speed:** Should complete in < 2 seconds
2. **Test execution:** Should complete in < 5 seconds total
3. **Database locks:** No deadlock errors in logs

---

## Next: Production Readiness

Before deploying to Laravel Cloud:

- [ ] All 7 tests passing ✓
- [ ] Manual concurrency test passed
- [ ] No timeout errors in logs
- [ ] Database health check passed
- [ ] Production env template reviewed (`.env.production.example`)
- [ ] Load testing planned (concurrent user simulation)

---

## Questions?

Check logs:
```bash
docker-compose -f docker-compose.test.yml logs -f
```

Or review test code:
- `tests/Unit/Services/StockServiceTest.php`
- `tests/Feature/StockAdjustmentTest.php`
