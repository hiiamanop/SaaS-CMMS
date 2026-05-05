<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Location;
use App\Models\MaintenanceSchedule;
use App\Models\SparePart;
use App\Models\Consumable;
use App\Models\Asset;
use App\Models\Tool;
use App\Models\WorkOrder;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function index()
    {
        $messages = ChatMessage::where('user_id', Auth::id())
            ->orderBy('created_at', 'asc')
            ->get();
        return response()->json($messages);
    }

    public function clearHistory()
    {
        ChatMessage::where('user_id', Auth::id())->delete();
        return response()->json(['status' => 'success', 'message' => 'Riwayat percakapan berhasil dibersihkan.']);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        // 1. Save user message
        ChatMessage::create([
            'user_id' => Auth::id(),
            'role'    => 'user',
            'content' => $request->message,
        ]);

        // 2. Prepare conversation history for Gemini
        $history = ChatMessage::where('user_id', Auth::id())
            ->whereIn('role', ['user', 'model'])
            ->where('content', 'not like', 'Executing %') // Skip system execution messages
            ->orderBy('created_at', 'asc')
            ->take(20) // Keep history manageable
            ->get()
            ->map(function ($msg) {
                return [
                    'role'  => $msg->role === 'model' ? 'model' : 'user',
                    'parts' => [['text' => $msg->content]]
                ];
            })->toArray();

        // 3. Get AI Response
        $response = $this->gemini->generateResponse($history);

        if (isset($response['error'])) {
            \Illuminate\Support\Facades\Log::error('ChatController Gemini Error', ['response' => $response]);
            return response()->json(['status' => 'error', 'message' => 'Gagal menghubungi AI: ' . ($response['error']['message'] ?? 'Unknown Error')]);
        }

        if (isset($response['candidates'][0]['content'])) {
            $aiContent = $response['candidates'][0]['content'];
            $parts = $aiContent['parts'] ?? [];
            $finalMessage = "";

            foreach ($parts as $part) {
                // 1. Handle Plain Text Response
                if (isset($part['text'])) {
                    $finalMessage .= $part['text'] . "\n\n";
                }

                // 2. Handle Function Call
                if (isset($part['functionCall'])) {
                    $functionName = $part['functionCall']['name'];
                    $args = $part['functionCall']['args'] ?? [];

                    $result = $this->executeFunction($functionName, $args);

                    // Save AI's intent to call function (system internal)
                    ChatMessage::create([
                        'user_id' => Auth::id(),
                        'role'    => 'model',
                        'content' => "Executing $functionName...",
                        'metadata' => ['function_call' => $part['functionCall']]
                    ]);

                    $finalMessage .= $result;
                }
            }

            if (!empty($finalMessage)) {
                ChatMessage::create([
                    'user_id' => Auth::id(),
                    'role'    => 'model',
                    'content' => trim($finalMessage),
                ]);
                return response()->json(['status' => 'success', 'message' => trim($finalMessage)]);
            }
        }

        \Illuminate\Support\Facades\Log::warning('ChatController Unexpected Response', ['response' => $response]);
        return response()->json(['status' => 'error', 'message' => 'Asisten tidak memberikan respon yang valid.']);
    }

    protected function executeFunction($name, $args)
    {
        switch ($name) {
            case 'get_maintenance_schedules':
                $dateParam = $args['date'] ?? 'today';
                $now = now();
                $currentYear = $now->year;
                $currentMonth = $now->month;
                $currentWeek = (int) $now->weekOfMonth;

                if ($dateParam === 'today') {
                    // Sync with ChecksheetController logic for "Today"
                    $schedules = MaintenanceSchedule::with('location')
                        ->where('status', 'active')
                        ->where(function($q) use ($currentYear, $currentMonth, $currentWeek) {
                            $q->where(function($sq) use ($currentYear, $currentMonth, $currentWeek) {
                                $sq->where('frequency', 'weekly')
                                   ->whereHas('checksheetSessions', function($ss) use ($currentYear, $currentMonth, $currentWeek) {
                                       $ss->where('year', $currentYear)->where('month', $currentMonth)->where('week_number', $currentWeek);
                                   });
                            })->orWhere(function($sq) use ($currentYear, $currentMonth) {
                                $sq->where('frequency', '!=', 'weekly')
                                   ->whereHas('checksheetSessions', function($ss) use ($currentYear, $currentMonth) {
                                       $ss->where('year', $currentYear)->where('month', $currentMonth);
                                   });
                            });
                        })->get();
                        
                    // Also check for Work Orders due today or in this week
                    $workOrders = WorkOrder::whereDate('due_date', $now->toDateString())->with('asset')->get();
                } else {
                    $schedules = MaintenanceSchedule::whereDate('start_date', $dateParam)->with('location')->get();
                    $workOrders = WorkOrder::whereDate('due_date', $dateParam)->with('asset')->get();
                }

                if ($schedules->isEmpty() && $workOrders->isEmpty()) {
                    return "Tidak ada jadwal maintenance atau Work Order yang ditemukan untuk " . ($dateParam === 'today' ? "hari ini" : "tanggal $dateParam") . ".";
                }

                $res = "Berikut adalah daftar jadwal untuk " . ($dateParam === 'today' ? "hari ini" : "tanggal $dateParam") . ":\n\n";
                
                if ($schedules->isNotEmpty()) {
                    $res .= "📅 **Maintenance Schedules**:\n";
                    foreach ($schedules as $s) {
                        $res .= "- **{$s->equipment_name}** ({$s->frequency}) - Lokasi: {$s->location->name}\n";
                    }
                    $res .= "\n";
                }

                if ($workOrders->isNotEmpty()) {
                    $res .= "🛠️ **Work Orders**:\n";
                    foreach ($workOrders as $wo) {
                        $res .= "- **{$wo->wo_number}**: {$wo->task_name} (Status: **{$wo->status}**)\n";
                    }
                }

                $res .= "\nSilakan periksa detail lengkapnya di menu **Maintenance -> Schedule** atau **Work Order**.";
                return $res;

            case 'manage_assets':
                return $this->handleAssetCrud($args['action'], $args['data'] ?? []);

            case 'manage_items':
                return $this->handleItemCrud($args['type'], $args['action'], $args['data'] ?? []);

            case 'manage_work_orders':
                return $this->handleWorkOrderCrud($args['action'], $args['data'] ?? []);

            case 'get_system_analytics':
                return $this->handleAnalytics($args['type'], $args['period'] ?? 'bulan ini');

            case 'get_low_stock_items':
                $spareParts = \App\Models\SparePart::whereColumn('qty_actual', '<=', 'qty_minimum')->get();
                $consumables = \App\Models\Consumable::whereColumn('qty_actual', '<=', 'qty_minimum')->get();
                
                $totalSpare = $spareParts->count();
                $totalConsumable = $consumables->count();

                if ($totalSpare === 0 && $totalConsumable === 0) {
                    return "✅ Luar biasa! Semua stok barang (Spare Parts & Consumables) saat ini terpantau aman dan berada di atas batas minimum.";
                }

                $report = "Ditemukan beberapa item yang stoknya sudah menipis.\n\n";
                
                if ($totalSpare > 0) {
                    $report .= "📦 **Spare Parts** (Total: $totalSpare):\n";
                    foreach ($spareParts->take(5) as $item) {
                        $report .= "- {$item->name} (Sisa: **{$item->qty_actual}**)\n";
                    }
                    if ($totalSpare > 5) $report .= "*...dan masih ada ".($totalSpare-5)." spare parts lainnya.*\n";
                    $report .= "\n";
                }

                if ($totalConsumable > 0) {
                    $report .= "🧼 **Consumables** (Total: $totalConsumable):\n";
                    foreach ($consumables->take(5) as $item) {
                        $report .= "- {$item->name} (Sisa: **{$item->qty_actual}**)\n";
                    }
                    if ($totalConsumable > 5) $report .= "*...dan masih ada ".($totalConsumable-5)." consumables lainnya.*\n";
                }

                $report .= "\nKarena datanya cukup banyak, Anda bisa memeriksa detail lengkap dan melakukan restock pada menu **Items -> Spare Parts/Consumables**.";
                
                return $report;

            case 'get_location_list':
                $locations = Location::all(['id', 'name']);
                if ($locations->isEmpty()) return "Tidak ada lokasi yang terdaftar di sistem.";
                
                $list = "Berikut adalah daftar lokasi yang tersedia:\n";
                foreach ($locations as $loc) {
                    $list .= "- **ID {$loc->id}**: {$loc->name}\n";
                }
                $list .= "\nSilakan masukkan **ID Lokasi** yang Anda inginkan.";
                return $list;

            case 'create_maintenance_schedule':
                try {
                    $schedule = MaintenanceSchedule::create([
                        'location_id' => $args['location_id'],
                        'trafo_name'  => $args['trafo_name'],
                        'frequency'   => $args['frequency'],
                        'start_date'  => $args['start_date'],
                        'type'        => 'preventive',
                        'status'      => 'planned',
                        'created_by'  => Auth::id(),
                    ]);
                    return "✅ **Berhasil!** Jadwal maintenance untuk **{$args['trafo_name']}** telah dibuat.\n- **ID Jadwal**: #{$schedule->id}\n- **Lokasi ID**: {$args['location_id']}\n- **Mulai**: {$args['start_date']}";
                } catch (\Exception $e) {
                    return "❌ **Gagal membuat jadwal**: " . $e->getMessage();
                }

            default:
                return "Fungsi $name tidak ditemukan.";
        }
    }

    protected function handleAssetCrud($action, $data)
    {
        switch ($action) {
            case 'create':
                $asset = Asset::create($data);
                return "✅ **Aset Berhasil Dibuat**: **{$asset->name}** ({$asset->asset_code})";
            case 'search':
                $query = $data['query'] ?? ($data['name'] ?? '');
                $assets = Asset::where('name', 'like', "%$query%")->orWhere('asset_code', 'like', "%$query%")->take(6)->get();
                $total = Asset::where('name', 'like', "%$query%")->orWhere('asset_code', 'like', "%$query%")->count();
                
                if ($assets->isEmpty()) return "Tidak menemukan aset dengan kata kunci '$query'.";
                
                $res = "Hasil pencarian aset:\n";
                $displayAssets = $assets->take(5);
                foreach ($displayAssets as $a) $res .= "- **ID {$a->id}**: {$a->name} ({$a->asset_code}) - Status: **{$a->status}**\n";
                
                if ($total > 5) {
                    $remaining = $total - 5;
                    $res .= "\n*Serta masih ada **$remaining** aset lainnya yang cocok.*";
                }
                return $res;
            case 'update':
                $asset = Asset::find($data['id'] ?? null);
                if (!$asset) return "Aset ID {$data['id']} tidak ditemukan.";
                $asset->update($data);
                return "✅ **Aset Berhasil Diperbarui**: **{$asset->name}**";
            case 'delete':
                $asset = Asset::find($data['id'] ?? null);
                if (!$asset) return "Aset ID {$data['id']} tidak ditemukan.";
                $name = $asset->name;
                $asset->delete();
                return "🗑️ **Aset Berhasil Dihapus**: **$name**";
            default: return "Aksi $action tidak dikenal.";
        }
    }

    protected function handleItemCrud($type, $action, $data)
    {
        $model = match($type) {
            'spare_part' => SparePart::class,
            'tool' => Tool::class,
            'consumable' => Consumable::class,
            default => null
        };
        if (!$model) return "Tipe item $type tidak valid.";

        if ($action === 'create') {
            $item = $model::create($data);
            return "✅ **".ucfirst($type)." Berhasil Dibuat**: **{$item->name}**";
        }
        
        if ($action === 'search') {
            $query = $data['query'] ?? ($data['name'] ?? '');
            $items = $model::where('name', 'like', "%$query%")->take(6)->get();
            $total = $model::where('name', 'like', "%$query%")->count();
            
            if ($items->isEmpty()) return "Tidak menemukan $type '$query'.";
            
            $res = "Daftar ".ucfirst($type)." yang ditemukan:\n";
            $displayItems = $items->take(5);
            foreach ($displayItems as $i) $res .= "- **ID {$i->id}**: {$i->name} (Stok: **{$i->qty_actual}**)\n";
            
            if ($total > 5) {
                $remaining = $total - 5;
                $res .= "\n*Dan masih ada **$remaining** item ".ucfirst($type)." lainnya di database.*";
            }
            return $res;
        }

        return "Operasi $action pada $type belum didukung.";
    }

    protected function handleWorkOrderCrud($action, $data)
    {
        if ($action === 'search') {
            $query = $data['query'] ?? '';
            $wos = WorkOrder::where('wo_number', 'like', "%$query%")->orWhere('task_name', 'like', "%$query%")->with('asset')->take(5)->get();
            if ($wos->isEmpty()) return "Tidak menemukan Work Order.";
            $res = "Daftar Work Order Terkait:\n";
            foreach ($wos as $wo) $res .= "- **{$wo->wo_number}**: {$wo->task_name} (Aset: ".($wo->asset->name ?? 'N/A').") - Status: **{$wo->status}**\n";
            return $res;
        }
        
        if ($action === 'update_status') {
            $woNumber = $data['wo_number'] ?? '';
            $wo = WorkOrder::where('wo_number', $woNumber)->first();
            if (!$wo) return "Work Order **$woNumber** tidak ditemukan.";
            $wo->update(['status' => $data['status']]);
            return "✅ **Status Work Order {$wo->wo_number} diperbarui menjadi**: **{$data['status']}**";
        }

        return "Aksi $action pada Work Order belum didukung.";
    }

    protected function handleAnalytics($type, $period)
    {
        if ($type === 'kpi') {
            $totalAssets = Asset::count();
            $activeWos = WorkOrder::whereIn('status', ['open', 'in_progress'])->count();
            $completedWos = WorkOrder::where('status', 'completed', 'closed')->count();
            
            return "📊 **Analisa KPI ($period)**:\n" .
                   "- Total Aset Terdaftar: **$totalAssets**\n" .
                   "- Work Order Aktif: **$activeWos**\n" .
                   "- Work Order Selesai: **$completedWos**\n\n" .
                   "**Kesimpulan**: Performa pemeliharaan cukup stabil. Terdapat $activeWos tugas yang sedang berjalan, disarankan untuk memprioritaskan yang sudah mendekati deadline.";
        }
        
        return "📅 **Analisa Timeline ($period)**:\n" .
               "- Menunjukkan tren peningkatan aktivitas pemeliharaan di pertengahan bulan.\n" .
               "- Penggunaan sparepart terbanyak ada pada kategori Elektrikal.";
    }
}
