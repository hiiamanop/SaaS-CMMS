# Task 1: Custom Exception & Migration

## Deliverable
Create `app/Exceptions/OutOfStockException.php` and migration file `database/migrations/2026_06_22_000000_create_stock_movements_table.php`.

## Context
This is the foundational task for B3 (Stock Integrity blocker). No other tasks depend on seeing it, but Tasks 2–7 will fail if these don't exist.

## Steps
1. Create OutOfStockException class that formats a user-friendly 422 message
2. Create migration for `stock_movements` table with: id, spare_part_id, mutation_type (enum deduct|add), qty, reason (varchar 50), created_by_user_id, created_at, indexes on (spare_part_id, created_at) and created_by_user_id
3. Run migration to verify success
4. Commit both files

## Code to Write (exact)

### app/Exceptions/OutOfStockException.php
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

### database/migrations/2026_06_22_000000_create_stock_movements_table.php
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

## Expected Result
- Files created and readable
- Migration runs without error (`php artisan migrate`)
- No exceptions thrown when querying empty stock_movements table
