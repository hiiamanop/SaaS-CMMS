<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\PvMap;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PvMapController extends Controller
{
    // Get map for location (if exists)
    public function show(Location $location)
    {
        $maps = PvMap::where('location_id', $location->id)->get();

        $mapsWithData = $maps->map(function($map) {
            $modules = Asset::where('location_id', $map->location_id)
                ->where('transformer_block', $map->transformer_block)
                ->where('category', 'PV Module')
                ->select('id', 'name', 'asset_code', 'transformer_block', 'string_number', 'module_slot', 'visual_row', 'visual_col', 'status')
                ->get();

            return [
                'id' => $map->id,
                'transformer_block' => $map->transformer_block,
                'map_status' => $map->map_status,
                'modules' => $modules,
            ];
        });

        return response()->json($mapsWithData);
    }

    // Upload & parse CSV for preview
    public function uploadCsv(Request $request, Location $location)
    {
        if (!auth()->user()->isAdminOrSupervisor()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $filename = $file->getClientOriginalName();

        // Extract transformer block from filename (e.g., T01-249_PV_Layout.csv → T01)
        if (!preg_match('/^(T\d+)/', $filename, $matches)) {
            return response()->json(['error' => 'Invalid filename format. Expected: T01-xxx.csv'], 422);
        }

        $transformerBlock = $matches[1];

        // Parse CSV with auto delimiter detection (, or ;)
        $modules = [];
        $handle = fopen($file->getRealPath(), 'r');
        $firstLine = fgets($handle);
        $delimiter = str_contains($firstLine, ';') ? ';' : ',';
        rewind($handle);
        fgetcsv($handle, 0, $delimiter); // skip header

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (count($row) < 5) continue;

            [$block, $string, $slot, $visualRow, $visualCol] = array_map('trim', $row);

            if ($block !== $transformerBlock) {
                fclose($handle);
                return response()->json(['error' => "CSV contains mixed blocks. Expected only {$transformerBlock}"], 422);
            }

            $string = (int) $string;
            $slot = (int) $slot;
            $strPad = str_pad($string, 2, '0', STR_PAD_LEFT);
            $slotPad = str_pad($slot, 2, '0', STR_PAD_LEFT);
            $code = "{$block}-INV{$strPad}-S{$slotPad}";

            $modules[] = [
                'asset_code' => $code,
                'transformer_block' => $block,
                'string_number' => $string,
                'module_slot' => $slot,
                'visual_row' => (int) $visualRow,
                'visual_col' => (int) $visualCol,
            ];
        }
        fclose($handle);

        if (empty($modules)) {
            return response()->json(['error' => 'No valid data in CSV'], 422);
        }

        // Check if map already exists
        $existingMap = PvMap::where('location_id', $location->id)
            ->where('transformer_block', $transformerBlock)
            ->first();

        return response()->json([
            'transformer_block' => $transformerBlock,
            'location_id' => $location->id,
            'modules' => $modules,
            'existing_map' => $existingMap ? true : false,
            'action' => $existingMap ? 'update' : 'create',
        ]);
    }

    // Save map & update assets
    public function save(Request $request, Location $location)
    {
        if (!auth()->user()->isAdminOrSupervisor()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'transformer_block' => 'required|string',
            'modules' => 'required|array',
            'modules.*.asset_code' => 'required|string',
            'modules.*.visual_row' => 'required|integer',
            'modules.*.visual_col' => 'required|integer',
        ]);

        $transformerBlock = $request->transformer_block;
        $modules = $request->modules;

        DB::transaction(function () use ($location, $transformerBlock, $modules) {
            // Create or update PvMap
            $map = PvMap::updateOrCreate(
                [
                    'location_id' => $location->id,
                    'transformer_block' => $transformerBlock,
                ],
                [
                    'map_status' => 'published',
                    'map_data' => $modules,
                ]
            );

            // Upsert assets — create if not exists, update position if exists
            foreach ($modules as $module) {
                Asset::updateOrCreate(
                    ['asset_code' => $module['asset_code'], 'location_id' => $location->id],
                    [
                        'name'              => $module['asset_code'],
                        'category'          => 'PV Module',
                        'status'            => $module['status'] ?? 'active',
                        'location'          => $location->name,
                        'transformer_block' => $module['transformer_block'],
                        'string_number'     => $module['string_number'],
                        'module_slot'       => $module['module_slot'],
                        'visual_row'        => $module['visual_row'],
                        'visual_col'        => $module['visual_col'],
                    ]
                );
            }
        });

        return response()->json(['success' => true, 'message' => "Map {$transformerBlock} saved and published"]);
    }

    // Get specific map for editing
    public function getMap(Location $location, $transformerBlock)
    {
        $map = PvMap::where('location_id', $location->id)
            ->where('transformer_block', $transformerBlock)
            ->firstOrFail();

        $modules = Asset::where('location_id', $location->id)
            ->where('transformer_block', $transformerBlock)
            ->where('category', 'PV Module')
            ->select('id', 'name', 'asset_code', 'transformer_block', 'string_number', 'module_slot', 'visual_row', 'visual_col', 'status')
            ->get();

        return response()->json([
            'map' => $map,
            'modules' => $modules,
        ]);
    }
}
