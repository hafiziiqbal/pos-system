<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StoreSettingController extends Controller
{
    public function index($store_id)
    {
        $store = Store::select('id', 'name', 'branch_code')->where('id', $store_id)->first();
        if (!$store) {
            abort(404);
        }
        return view('admin.store-setting.index', compact('store'));
    }

    public function getDatatable(Request $request, $store_id)
    {
        $draw   = $request->input('draw');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);

        $columns = [
            0 => null, // No
            1 => 'key',
            2 => 'value',
            3 => 'created_at',
            4 => null, // Aksi
        ];

        // Cache Key unik per parameter pencarian DataTables khusus untuk store_id ini
        $cacheKey = "store_settings_dt_{$store_id}_" . md5(json_encode($request->all()));

        $response = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($start, $length, $columns, $request, $store_id) {

            $query = DB::table('store_settings')->where('store_id', $store_id);
            $totalRecords = DB::table('store_settings')->where('store_id', $store_id)->count();

            // Filter Pencarian Kolom
            $columnSearches = $request->input('columns', []);
            foreach ($columnSearches as $index => $columnData) {
                $searchValue = $columnData['search']['value'] ?? '';

                if (empty($searchValue)) {
                    continue;
                }
                if (!isset($columns[$index]) || $columns[$index] === null) {
                    continue;
                }

                // Khusus Filter created_at menggunakan date range format "from|to"
                if ((int) $index === 3) {
                    $parts = explode('|', $searchValue);
                    $from  = $parts[0] ?? '';
                    $to    = $parts[1] ?? '';

                    if (!empty($from)) {
                        // Parse ISO string dari frontend ke format datetime standar MySQL
                        $fromFormatted = Carbon::parse($from)->setTimezone('UTC')->format('Y-m-d H:i:s');
                        $query->where('created_at', '>=', $fromFormatted); // Sesuaikan 'tenants.created_at' jika ini tabel lain
                    }

                    if (!empty($to)) {
                        // Parse ISO string dari frontend ke format datetime standar MySQL
                        $toFormatted = Carbon::parse($to)->setTimezone('UTC')->format('Y-m-d H:i:s');
                        $query->where('created_at', '<=', $toFormatted); // Sesuaikan 'tenants.created_at' jika ini tabel lain
                    }
                    continue;
                }

                $query->where($columns[$index], 'like', "%{$searchValue}%");
            }

            $filteredRecords = $query->count();

            // Pengurutan (Ordering)
            $orderColumnIndex = (int) $request->input('order.0.column', 3); // Default urut berdasarkan created_at
            $orderDir         = in_array(strtolower($request->input('order.0.dir', 'desc')), ['asc', 'desc'])
                ? $request->input('order.0.dir', 'desc')
                : 'desc';
            $orderColumn      = $columns[$orderColumnIndex] ?? 'created_at';

            $query->orderBy($orderColumn, $orderDir);

            // Fetch & format tanggal ke ISO String agar aman dibaca JS
            $data = $query->skip($start)->take($length)->get()->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at)->toIso8601String();
                return $item;
            });

            return [
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => $data,
            ];
        });

        return response()->json([
            'draw'            => intval($draw),
            'recordsTotal'    => $response['recordsTotal'],
            'recordsFiltered' => $response['recordsFiltered'],
            'data'            => $response['data'],
        ]);
    }

    public function store(Request $request, $store_id)
    {
        $validated = $request->validate([
            'key'   => 'required|string|max:255',
            'value' => 'required|string',
        ], [
            'key.required'   => 'Nama kunci (key) wajib diisi.',
            'key.max'        => 'Nama kunci maksimal 255 karakter.',
            'value.required' => 'Nilai pengesetan (value) wajib diisi.',
        ]);

        // Cek duplikasi key untuk store_id yang sama
        $exists = DB::table('store_settings')->where('store_id', $store_id)->where('key', $validated['key'])->exists();
        if ($exists) {
            return response()->json(['message' => 'Kunci (key) tersebut sudah ada di toko ini.'], 422);
        }

        DB::table('store_settings')->insert([
            'store_id'   => $store_id,
            'key'        => $validated['key'],
            'value'      => $validated['value'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Cache::flush(); // Hapus cache global DataTables

        return response()->json(['message' => 'Pengaturan baru berhasil ditambahkan']);
    }

    public function update(Request $request, $store_id, $id)
    {
        $validated = $request->validate([
            'key'   => 'required|string|max:255',
            'value' => 'required|string',
        ], [
            'key.required'   => 'Nama kunci (key) wajib diisi.',
            'value.required' => 'Nilai pengesetan (value) wajib diisi.',
        ]);

        // Pastikan key tidak bentrok dengan baris lain di store yang sama
        $exists = DB::table('store_settings')
            ->where('store_id', $store_id)
            ->where('key', $validated['key'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Kunci (key) sudah digunakan pada pengaturan lain di toko ini.'], 422);
        }

        DB::table('store_settings')->where('id', $id)->update([
            'key'        => $validated['key'],
            'value'      => $validated['value'],
            'updated_at' => now(),
        ]);

        Cache::flush();

        return response()->json(['message' => 'Pengaturan berhasil diperbarui']);
    }

    public function destroy($store_id, $id)
    {
        DB::table('store_settings')->where('id', $id)->delete();
        Cache::flush();

        return response()->json(['message' => 'Pengaturan berhasil dihapus']);
    }
}
