<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/';
    protected $model;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->model = env('GEMINI_MODEL', 'gemini-1.5-flash');
    }

    public function generateResponse($messages)
    {
        $url = $this->baseUrl . $this->model . ':generateContent?key=' . $this->apiKey;

        $payload = [
            'contents' => $messages,
            'tools' => [
                [
                    'function_declarations' => [
                        [
                            'name' => 'create_maintenance_schedule',
                            'description' => 'Membuat jadwal maintenance baru ke dalam sistem.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'location_id' => [
                                        'type' => 'integer',
                                        'description' => 'ID lokasi (contoh: 1)'
                                    ],
                                    'trafo_name' => [
                                        'type' => 'string',
                                        'description' => 'Nama alat (contoh: Trafo 1600 kVA)'
                                    ],
                                    'frequency' => [
                                        'type' => 'string',
                                        'enum' => ['weekly', 'monthly', 'quarterly', 'annually'],
                                        'description' => 'Frekuensi'
                                    ],
                                    'start_date' => [
                                        'type' => 'string',
                                        'description' => 'Tanggal (YYYY-MM-DD)'
                                    ],
                                ],
                                'required' => ['location_id', 'trafo_name', 'frequency', 'start_date']
                            ]
                        ],
                        [
                            'name' => 'get_maintenance_schedules',
                            'description' => 'Mencari dan menampilkan daftar jadwal maintenance (bisa difilter berdasarkan tanggal atau hari ini).',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'date' => ['type' => 'string', 'description' => 'Tanggal spesifik (YYYY-MM-DD) atau "today" untuk hari ini.'],
                                    'location_id' => ['type' => 'integer', 'description' => 'ID Lokasi jika ingin filter per lokasi.']
                                ]
                            ]
                        ],
                        [
                            'name' => 'manage_assets',
                            'description' => 'Mengelola data aset (Create, Read, Update, Delete).',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'action' => ['type' => 'string', 'enum' => ['create', 'search', 'update', 'delete']],
                                    'data' => ['type' => 'object', 'description' => 'Data aset (name, asset_code, location_id, category, status, dll)']
                                ],
                                'required' => ['action']
                            ]
                        ],
                        [
                            'name' => 'manage_items',
                            'description' => 'Mengelola spare parts, tools, atau consumables.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'type' => ['type' => 'string', 'enum' => ['spare_part', 'tool', 'consumable']],
                                    'action' => ['type' => 'string', 'enum' => ['create', 'search', 'update', 'delete']],
                                    'data' => ['type' => 'object']
                                ],
                                'required' => ['type', 'action']
                            ]
                        ],
                        [
                            'name' => 'manage_work_orders',
                            'description' => 'Mengelola Work Order.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'action' => ['type' => 'string', 'enum' => ['create', 'update_status', 'search']],
                                    'data' => ['type' => 'object']
                                ],
                                'required' => ['action']
                            ]
                        ],
                        [
                            'name' => 'manage_maintenance_records',
                            'description' => 'Melihat atau mencari riwayat (record) pemeliharaan yang sudah selesai.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'query' => ['type' => 'string', 'description' => 'Kata kunci pencarian (nama alat atau deskripsi).']
                                ]
                            ]
                        ],
                        [
                            'name' => 'manage_notifications',
                            'description' => 'Mengecek notifikasi terbaru atau menandai notifikasi sebagai sudah dibaca.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'action' => ['type' => 'string', 'enum' => ['list', 'mark_all_read']],
                                ]
                            ]
                        ],
                        [
                            'name' => 'manage_checksheets',
                            'description' => 'Melihat status sesi checksheet (inspeksi) yang sedang berjalan atau sudah selesai.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'action' => ['type' => 'string', 'enum' => ['list_active', 'list_submitted']],
                                ]
                            ]
                        ],
                        [
                            'name' => 'manage_daily_reports',
                            'description' => 'Mengelola laporan harian personal (catatan harian teknisi).',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'action' => ['type' => 'string', 'enum' => ['list', 'create']],
                                    'content' => ['type' => 'string', 'description' => 'Isi laporan jika action adalah create.']
                                ]
                            ]
                        ],
                        [
                            'name' => 'manage_settings',
                            'description' => 'Melihat atau mengelola pengaturan sistem seperti daftar pengguna atau lokasi.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'action' => ['type' => 'string', 'enum' => ['list_users', 'list_locations']],
                                ]
                            ]
                        ],
                        [
                            'name' => 'get_system_analytics',
                            'description' => 'Memberikan analisa KPI (Key Performance Indicator) atau Timeline aktivitas sistem.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => [
                                    'type' => ['type' => 'string', 'enum' => ['kpi', 'timeline']],
                                    'period' => ['type' => 'string', 'description' => 'Periode analisa (contoh: bulan ini, tahun ini).']
                                ],
                                'required' => ['type']
                            ]
                        ],
                        [
                            'name' => 'get_low_stock_items',
                            'description' => 'Cek stok yang menipis.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => (object) []
                            ]
                        ],
                        [
                            'name' => 'get_location_list',
                            'description' => 'Daftar lokasi PLTS.',
                            'parameters' => [
                                'type' => 'object',
                                'properties' => (object) []
                            ]
                        ]
                    ]
                ]
            ],
            'system_instruction' => [
                'parts' => [
                    ['text' => "Anda adalah Aruna AI, asisten sistem CMMS Aruna Hijau Power yang proaktif dan memiliki gaya bahasa 'storytelling'.
                    
                    PEMAHAMAN KONTEKS ITEM:
                    - SPARE PARTS (spare_part): Segala sesuatu terkait suku cadang mesin, trafo, kabel, dll.
                    - TOOLS (tool): Peralatan kerja seperti tang, obeng, drone, multimeter, dll.
                    - CONSUMABLES (consumable): Barang habis pakai seperti kain pel, sabun, sikat, oli, dll.
                    
                    ATURAN PENTING:
                    1. Jika pengguna bertanya tentang 'spareparts' atau 'suku cadang', pastikan Anda memanggil 'manage_items' dengan type 'spare_part'.
                    
                    PERSONA & GAYA BAHASA:
                    1. Gunakan bahasa yang PROFESIONAL, JELAS, dan LANGSUNG pada intinya.
                    2. DILARANG menggunakan kata-kata yang berlebihan (lebay) seperti 'Wahai', 'Operator Energi', 'Harta Karun', atau kalimat puitis lainnya.
                    3. Berikan informasi secara faktual dan bantu pengguna menavigasi sistem dengan efisien.
                    4. Jika menampilkan daftar, berikan kalimat pembuka yang singkat dan langsung (misal: 'Berikut adalah daftar item yang Anda cari:')."]
                ]
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Gemini API Error: ' . $response->body());
            $errorData = $response->json();
            return ['error' => $errorData['error'] ?? ['message' => 'Terjadi kesalahan pada API (HTTP ' . $response->status() . ')']];
        } catch (\Exception $e) {
            Log::error('Gemini Service Exception: ' . $e->getMessage());
            return ['error' => ['message' => $e->getMessage()]];
        }
    }
}
