# Testing B3 (Stock Integrity) via Docker

This project includes a Docker setup for running PHPUnit tests without local PHP/MySQL installation.

## Prerequisites

- Docker & Docker Compose installed
- Git

## Quick Start

### 1. Run Tests (Auto-Setup)

```bash
cd /home/naufa/workspace/SaaS-CMMS
docker-compose -f docker-compose.test.yml up --build
```

This will:
- Pull PHP 8.3 & MySQL 8.0 images
- Install Composer dependencies
- Run Laravel migrations on test database
- Execute `php artisan test` automatically
- Display results in console

### 2. Manual Testing (Interactive)

If you want to run tests manually or debug:

```bash
# Start services without auto-test
docker-compose -f docker-compose.test.yml up -d

# SSH into app container
docker exec -it cmms-test sh

# Inside container, run tests
php artisan test

# Or run specific test file
php artisan test tests/Unit/Services/StockServiceTest.php
php artisan test tests/Feature/StockAdjustmentTest.php

# Exit container
exit

# Cleanup
docker-compose -f docker-compose.test.yml down
```

### 3. View MySQL Logs

```bash
docker logs cmms-mysql-test -f
```

### 4. Cleanup

```bash
docker-compose -f docker-compose.test.yml down -v
```

The `-v` flag removes volumes (cleans up test database).

## Test Files

### Unit Tests
- `tests/Unit/Services/StockServiceTest.php` — 5 tests for StockService
  - Deduct success
  - Deduct failure (out of stock)
  - Add always succeeds
  - Stock movement recorded
  - Concurrent atomicity

### Feature Tests
- `tests/Feature/StockAdjustmentTest.php` — 2 integration tests
  - Adjust stock add
  - Adjust stock reduce (fails if insufficient)

## Expected Output

On successful run:

```
Tests:  7 passed (XX assertions)
Duration: X.XXXs
```

If any test fails, Docker output will show:

```
FAILED  Tests\Unit\Services\StockServiceTest > testDeductThrowsOutOfStockException
...
```

## Troubleshooting

### Port conflict on 3307
If MySQL port 3307 is in use:
```bash
# Edit docker-compose.test.yml, change:
# ports:
#   - "3308:3306"  # Use 3308 instead
```

### Container won't start
```bash
# Check logs
docker-compose -f docker-compose.test.yml logs app
docker-compose -f docker-compose.test.yml logs mysql
```

### Permission denied on docker socket
```bash
# Add user to docker group
sudo usermod -aG docker $USER
# Log out and back in
```

## CI/CD Integration

For GitHub Actions, use:

```yaml
- name: Run Tests
  run: docker-compose -f docker-compose.test.yml up --build --abort-on-container-exit
```

---

**Next Steps:**
- Fix any failing tests before merge to `main`
- Add more feature tests as new functionality is added
- Run full suite: `php artisan test` (includes all tests, not just B3)
