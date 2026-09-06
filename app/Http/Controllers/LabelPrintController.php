<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Consumable;
use App\Models\SparePart;
use App\Models\Tool;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LabelPrintController extends Controller
{
    /**
     * Standard Label Dimensions (Width x Height in mm)
     * 1 mm = 2.83465 points in PDF standard
     */
    private const SIZES = [
        'roll_70x40'  => ['width' => 70,  'height' => 40,  'name' => 'Stiker Roll 70 x 40 mm (Standar)'],
        'roll_50x30'  => ['width' => 50,  'height' => 30,  'name' => 'Stiker Roll 50 x 30 mm (Mini/String)'],
        'roll_100x50' => ['width' => 100, 'height' => 50,  'name' => 'Stiker Roll 100 x 50 mm (Besar)'],
        'sheet_a4'    => ['width' => 210, 'height' => 297, 'name' => 'Lembar A4 Grid (24 Stiker/Lembar)'],
    ];

    public function print(Request $request)
    {
        $type   = $request->get('type', 'asset');
        $format = $request->get('format', 'roll_70x40');
        $items  = $this->resolveItems($request, $type);

        if ($items->isEmpty()) {
            return back()->with('error', 'Tidak ada item yang dipilih untuk dicetak.');
        }

        // Prepare label data with generated QR Code SVG base64
        $labels = $items->map(function ($item) use ($type) {
            return $this->formatItemLabel($item, $type);
        });

        $size = self::SIZES[$format] ?? self::SIZES['roll_70x40'];
        $widthPt  = $size['width'] * 2.83465;
        $heightPt = $size['height'] * 2.83465;

        $viewName = $format === 'sheet_a4' ? 'labels.pdf.sheet_a4' : 'labels.pdf.thermal_roll';

        $pdf = Pdf::loadView($viewName, [
            'labels' => $labels,
            'format' => $format,
            'size'   => $size,
        ]);

        if ($format === 'sheet_a4') {
            $pdf->setPaper('a4', 'portrait');
        } else {
            $pdf->setPaper([0, 0, $widthPt, $heightPt], 'portrait');
        }

        $filename = "LABEL_STIKER_{$type}_" . now()->format('Ymd_His') . ".pdf";

        return $pdf->stream($filename);
    }

    private function resolveItems(Request $request, string $type)
    {
        $ids = $request->get('ids');
        if (!empty($ids)) {
            $idArray = is_array($ids) ? $ids : explode(',', $ids);
            $idArray = array_filter(array_map('trim', $idArray));
        } else {
            $idArray = [];
        }

        switch ($type) {
            case 'asset':
                $query = Asset::query();
                if (!empty($idArray)) {
                    $query->whereIn('id', $idArray);
                } else {
                    if ($request->filled('transformer_block') && $request->transformer_block !== 'all') {
                        $query->where('transformer_block', $request->transformer_block);
                    }
                    if ($request->filled('category')) {
                        $query->where('category', $request->category);
                    }
                    if ($request->filled('search')) {
                        $query->where(function ($q) use ($request) {
                            $q->where('name', 'like', "%{$request->search}%")
                              ->orWhere('asset_code', 'like', "%{$request->search}%");
                        });
                    }
                }
                return $query->orderBy('transformer_block')->orderBy('asset_code')->limit(500)->get();

            case 'spare-part':
                $query = SparePart::query();
                if (!empty($idArray)) {
                    $query->whereIn('id', $idArray);
                } elseif ($request->filled('category')) {
                    $query->where('category', $request->category);
                }
                return $query->orderBy('part_code')->limit(500)->get();

            case 'tool':
                $query = Tool::query();
                if (!empty($idArray)) {
                    $query->whereIn('id', $idArray);
                } elseif ($request->filled('condition')) {
                    $query->where('condition', $request->condition);
                }
                return $query->orderBy('tool_code')->limit(500)->get();

            case 'consumable':
                $query = Consumable::query();
                if (!empty($idArray)) {
                    $query->whereIn('id', $idArray);
                } elseif ($request->filled('category')) {
                    $query->where('category', $request->category);
                }
                return $query->orderBy('item_code')->limit(500)->get();

            default:
                return collect();
        }
    }

    private function formatItemLabel($item, string $type): array
    {
        $qrValue = '';
        $code = '';
        $name = '';
        $category = '';
        $location = '';
        $meta = '';

        switch ($type) {
            case 'asset':
                $code     = $item->asset_code;
                $name     = $item->name;
                $category = $item->category;
                $location = $item->location ?: ($item->transformer_block ? "Blok {$item->transformer_block}" : 'PLTS Lestari');
                $qrValue  = route('assets.show', $item);
                $meta     = $item->brand ? "Brand: {$item->brand}" : '';
                break;

            case 'spare-part':
                $code     = $item->part_code;
                $name     = $item->name;
                $category = $item->category ?? 'Spare Part';
                $location = $item->location ?: 'Gudang Rak';
                $qrValue  = route('spare-parts.show', $item);
                $meta     = "Stok: {$item->qty_actual} {$item->unit}";
                break;

            case 'tool':
                $code     = $item->tool_code;
                $name     = $item->name;
                $category = $item->category ?? 'Tool';
                $location = $item->location ?: 'Rak Tools';
                $qrValue  = $item->tool_code;
                $meta     = "Kondisi: " . ucfirst($item->condition);
                break;

            case 'consumable':
                $code     = $item->item_code;
                $name     = $item->name;
                $category = $item->category ?? 'Consumable';
                $location = $item->location ?: 'Gudang';
                $qrValue  = $item->item_code;
                $meta     = "Stok: {$item->qty_actual} {$item->unit}";
                break;
        }

        // Generate QR code SVG and encode to base64
        $svg = QrCode::size(120)->margin(0)->errorCorrection('M')->generate($qrValue);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($svg);

        return [
            'code'      => $code,
            'name'      => $name,
            'category'  => $category,
            'location'  => $location,
            'meta'      => $meta,
            'qr_base64' => $qrBase64,
        ];
    }
}
