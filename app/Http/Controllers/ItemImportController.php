<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ToolsImport;
use App\Imports\SparePartsImport;
use App\Imports\ConsumablesImport;

class ItemImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
            'type' => 'required|in:tool,sparepart,consumable'
        ]);

        $file = $request->file('file');
        $type = $request->type;

        try {
            if ($type === 'tool') {
                Excel::import(new ToolsImport, $file);
                $message = 'Tools imported successfully.';
            } elseif ($type === 'sparepart') {
                Excel::import(new SparePartsImport, $file);
                $message = 'Spare parts imported successfully.';
            } else {
                Excel::import(new ConsumablesImport, $file);
                $message = 'Consumables imported successfully.';
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
