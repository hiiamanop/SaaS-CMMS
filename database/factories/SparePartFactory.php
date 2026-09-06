<?php

namespace Database\Factories;

use App\Models\SparePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SparePart>
 */
class SparePartFactory extends Factory
{
    protected $model = SparePart::class;

    public function definition(): array
    {
        return [
            'part_code' => 'SP-' . fake()->unique()->numerify('#####'),
            'name' => fake()->word() . ' Part',
            'category' => 'General',
            'unit' => 'pcs',
            'qty_actual' => 10,
            'qty_minimum' => 2,
            'unit_price' => fake()->randomFloat(2, 10000, 500000),
            'supplier' => fake()->company(),
            'location' => 'Warehouse A',
            'description' => fake()->sentence(),
        ];
    }
}
