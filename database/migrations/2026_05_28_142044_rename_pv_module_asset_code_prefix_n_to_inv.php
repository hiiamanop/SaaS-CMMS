<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('assets')
            ->where('category', 'PV Module')
            ->get(['id', 'asset_code', 'name'])
            ->each(function ($asset) {
                DB::table('assets')->where('id', $asset->id)->update([
                    'asset_code' => preg_replace('/-N(\d+)-/', '-INV$1-', $asset->asset_code),
                    'name'       => preg_replace('/-N(\d+)-/', '-INV$1-', $asset->name),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('assets')
            ->where('category', 'PV Module')
            ->get(['id', 'asset_code', 'name'])
            ->each(function ($asset) {
                DB::table('assets')->where('id', $asset->id)->update([
                    'asset_code' => preg_replace('/-INV(\d+)-/', '-N$1-', $asset->asset_code),
                    'name'       => preg_replace('/-INV(\d+)-/', '-N$1-', $asset->name),
                ]);
            });
    }
};
