<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class TenantController extends Controller
{
    public function index()
    {
        return view('admin.tenant.index');
    }

    /**
     * Mengambil data untuk Server-side DataTables dengan Cache terfragmentasi per request parameter.
     */
    public function getDatatable(Request $request)
    {
        $draw   = $request->input('draw');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);

        $columns = [
            0  => null,
            1  => 'name',
            2  => 'subdomain',
            3  => 'plan',
            4  => 'disabled',
            5  => 'created_at',
            6  => null,
        ];

        // 2. ✅ MEMBUAT CACHE KEY UNIK BERDASARKAN PARAMETER REQUEST
        // Ini agar pencarian/pagination user A tidak bertabrakan dengan user B
        $cacheKey = 'tenants_dt_' . md5(json_encode($request->all()));

        // Jika cache dengan key tersebut ada, ambil. Jika tidak, jalankan query dan simpan selama 10 menit.
        $response = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($start, $length, $columns, $request) {

            $query = DB::table('tenants');
            $totalRecords = DB::table('tenants')->count();

            // Filter per kolom
            $columnSearches = $request->input('columns', []);
            foreach ($columnSearches as $index => $columnData) {
                $searchValue = $columnData['search']['value'] ?? '';
                if (empty($searchValue) && $searchValue !== '0') {
                    continue;
                }

                if (!isset($columns[$index]) || $columns[$index] === null) {
                    continue;
                }

                // Khusus created_at → date range
                if ((int) $index === 5) {
                    $parts = explode('|', $searchValue);
                    $from  = $parts[0] ?? '';
                    $to    = $parts[1] ?? '';

                    if (!empty($from)) {
                        // Parse ISO string dari frontend ke format datetime standar MySQL
                        $fromFormatted = Carbon::parse($from)->setTimezone('UTC')->format('Y-m-d H:i:s');
                        $query->where('tenants.created_at', '>=', $fromFormatted); // Sesuaikan 'tenants.created_at' jika ini tabel lain
                    }

                    if (!empty($to)) {
                        // Parse ISO string dari frontend ke format datetime standar MySQL
                        $toFormatted = Carbon::parse($to)->setTimezone('UTC')->format('Y-m-d H:i:s');
                        $query->where('tenants.created_at', '<=', $toFormatted); // Sesuaikan 'tenants.created_at' jika ini tabel lain
                    }
                    continue;
                }

                // Khusus disabled (index 4)
                if ((int) $index === 4) {
                    $query->where('disabled', $searchValue);
                    continue;
                }

                $query->where($columns[$index], 'like', "%{$searchValue}%");
            }

            $filteredRecords = $query->count();

            // Ordering
            $orderColumnIndex = (int) $request->input('order.0.column', 5);
            $orderDir         = in_array(strtolower($request->input('order.0.dir', 'desc')), ['asc', 'desc'])
                ? $request->input('order.0.dir', 'desc')
                : 'desc';
            $orderColumn      = $columns[$orderColumnIndex] ?? 'created_at';

            $query->orderBy($orderColumn, $orderDir);

            // Fetch data & formatting
            $data = $query->skip($start)->take($length)->get()->map(function ($item) {
                $item->created_at = Carbon::parse($item->created_at)->toIso8601String();
                $item->updated_at = Carbon::parse($item->updated_at)->toIso8601String();
                return $item;
            });

            return [
                'recordsTotal'    => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data'            => $data,
            ];
        });

        // Kembalikan response JSON dengan menggabungkan nilai 'draw' yang dinamis
        return response()->json([
            'draw'            => intval($draw),
            'recordsTotal'    => $response['recordsTotal'],
            'recordsFiltered' => $response['recordsFiltered'],
            'data'            => $response['data'],
        ]);
    }

    public function form($id = null)
    {
        $tenant = null;

        if ($id) {
            // Caching detail form per ID selama 30 menit (di-clear saat update/toggle)
            $tenant = Cache::remember("tenant_detail_{$id}", now()->addMinutes(30), function () use ($id) {
                return DB::table('tenants')->where('id', $id)->first();
            });

            if (!$tenant) {
                abort(404);
            }
        }

        return view('admin.tenant.create-edit', [
            'tenant' => $tenant
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Nama wajib diisi',
        ]);

        DB::table('tenants')->insert([
            'name' => $data['name'],
            'subdomain' => $request->input('subdomain'),
            'plan' => $request->input('plan'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. ✅ CACHE BUSTER: Hancurkan seluruh cache pencarian & datatable tenant karena ada data baru
        $this->clearTenantCache();

        return response()->json([
            'message' => 'Tenant berhasil ditambahkan'
        ]);
    }

    public function update(Request $request, $id)
    {
        $tenant = DB::table('tenants')->where('id', $id)->first();

        if (!$tenant) {
            return response()->json([
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $data = $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Nama wajib diisi',
        ]);

        DB::table('tenants')->where('id', $id)->update([
            'name' => $data['name'],
            'subdomain' => $request->input('subdomain'),
            'plan' => $request->input('plan'),
            'updated_at' => now(),
        ]);

        // 3. ✅ CACHE BUSTER: Bersihkan cache spesifik ID dan cache global datatable
        $this->clearTenantCache($id);

        return response()->json([
            'message' => 'Tenant berhasil diupdate'
        ]);
    }

    public function destroy($id)
    {
        $tenant = DB::table('tenants')->where('id', $id)->first();

        if (!$tenant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tenant tidak ditemukan'
            ], 404);
        }

        $isUsedByStore = DB::table('stores')->where('tenant_id', $id)->exists();

        if ($isUsedByStore) {
            return response()->json([
                'status'  => 'warning',
                'message' => 'Tenant tidak bisa dihapus karena masih digunakan oleh data Toko!'
            ], 422);
        }

        DB::table('tenants')->where('id', $id)->delete();

        // 3. ✅ CACHE BUSTER: Bersihkan cache karena data dihapus
        $this->clearTenantCache($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Tenant berhasil dihapus'
        ]);
    }

    public function toggleStatus($id)
    {
        // 1. Cari data tenant
        $tenant = DB::table('tenants')->where('id', $id)->first();

        if (!$tenant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data tenant tidak ditemukan'
            ], 404);
        }

        // 2. Tentukan status baru (0 jadi 1, 1 jadi 0)
        $newStatus = $tenant->disabled ? 0 : 1;
        $statusText = $newStatus ? 'dinonaktifkan' : 'diaktifkan';

        // 3. Gunakan Database Transaction agar perubahan di kedua tabel aman (All or Nothing)
        DB::transaction(function () use ($id, $newStatus) {

            // Update status Tenant utama
            DB::table('tenants')->where('id', $id)->update([
                'disabled'   => $newStatus,
                'updated_at' => now(),
            ]);

            // ✅ SEKALIGUS UPDATE SEMUA STORES YANG MEMILIKI TENANT_ID TERKAIT
            DB::table('stores')->where('tenant_id', $id)->update([
                'disabled'   => $newStatus,
                'updated_at' => now(),
            ]);
        });

        // 4. ✅ CACHE BUSTER: Bersihkan cache terpusat
        $this->clearTenantCache($id);

        return response()->json([
            'status'  => 'success',
            'message' => "Tenant beserta seluruh Toko di bawahnya berhasil {$statusText}"
        ]);
    }

    /**
     * API Endpoint untuk Select2 Tenant dengan optimasi Cache Search.
     */
    public function searchTenant(Request $request)
    {
        $search = $request->input('q', '');

        // Membuat cache key unik berdasarkan teks keyword yang diketik di Select2
        $cacheKey = 'tenants_select2_' . md5($search);

        $tenants = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($search) {
            $query = DB::table('tenants')->where('disabled', 0);

            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%");
            }

            return $query->select('id', 'name')
                ->take(15)
                ->get();
        });

        return response()->json($tenants);
    }

    /**
     * 4. ✅ FUNGSI PEMBANTU (HELPER) UNTUK MEMBERSIHKAN CACHE SECARA MASSAL
     */
    private function clearTenantCache($id = null)
    {
        // Jika driver cache kamu menggunakan 'redis' atau 'memcached', kita bisa pakai tags.
        // Namun karena menggunakan default driver (file), kita buster via flush/forget pola kunci berikut:

        if ($id) {
            Cache::forget("tenant_detail_{$id}");
        }

        // Gunakan Cache::flush() jika ingin membersihkan seluruh cache aplikasi sekaligus,
        // Namun jika hanya ingin menghapus cache hasil query ber-pola dinamis (md5), pastikan driver cache kamu mendukung.
        // Jika menggunakan driver 'file' (default), disarankan langsung panggil Cache::flush() agar aman dan data mutakhir instan merata.
        Cache::flush();
    }
}
