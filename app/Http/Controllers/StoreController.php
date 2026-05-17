<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    public function index()
    {
        return view('admin.store.index');
    }

    /**
     * Mengambil data Server-side DataTables dengan Cache terfragmentasi.
     */
    public function getDatatable(Request $request)
    {
        $draw   = $request->input('draw');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);

        $columns = [
            0  => null,                   // No
            1  => 'tenants.name',         // Nama Tenant (Hasil Join)
            2  => 'stores.name',          // Nama Toko
            3  => 'stores.branch_code',   // Kode Cabang
            4  => 'stores.address',       // Alamat
            5  => 'stores.disabled',      // Status Disabled (0 / 1)
            6  => 'stores.created_at',    // Tanggal Dibuat
            7  => null,                   // Aksi
        ];

        // ✅ MEMBUAT CACHE KEY UNIK BERDASARKAN PARAMETER REQUEST DATATABLES
        $cacheKey = 'stores_dt_' . md5(json_encode($request->all()));

        // Ingat response query selama 10 menit
        $response = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($start, $length, $columns, $request) {

            $query = DB::table('stores')
                ->join('tenants', 'stores.tenant_id', '=', 'tenants.id')
                ->select('stores.*', 'tenants.name as tenant_name');

            $totalRecords = DB::table('stores')->count();

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

                // Khusus created_at (index 6) → date range
                if ((int) $index === 6) { // Sesuaikan dengan index kolom tanggalmu
                    $parts = explode('|', $searchValue);
                    $from  = $parts[0] ?? '';
                    $to    = $parts[1] ?? '';

                    if (!empty($from)) {
                        // Konversi "2026-05-16T17:00:00.000Z" menjadi "2026-05-16 17:00:00"
                        $fromFormatted = Carbon::parse($from)->setTimezone('UTC')->format('Y-m-d H:i:s');
                        $query->where('stores.created_at', '>=', $fromFormatted);
                    }

                    if (!empty($to)) {
                        // Konversi "2026-05-17T16:59:59.000Z" menjadi "2026-05-17 16:59:59"
                        $toFormatted = Carbon::parse($to)->setTimezone('UTC')->format('Y-m-d H:i:s');
                        $query->where('stores.created_at', '<=', $toFormatted);
                    }
                    continue;
                }

                // Khusus disabled (index 5) → Exact Match
                if ((int) $index === 5) {
                    $query->where('stores.disabled', $searchValue);
                    continue;
                }

                $query->where($columns[$index], 'like', "%{$searchValue}%");
            }

            $filteredRecords = $query->count();

            // Ordering / Pengurutan
            $orderColumnIndex = (int) $request->input('order.0.column', 6);
            $orderDir         = in_array(strtolower($request->input('order.0.dir', 'desc')), ['asc', 'desc'])
                ? $request->input('order.0.dir', 'desc')
                : 'desc';
            $orderColumn      = $columns[$orderColumnIndex] ?? 'stores.created_at';

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

        return response()->json([
            'draw'            => intval($draw),
            'recordsTotal'    => $response['recordsTotal'],
            'recordsFiltered' => $response['recordsFiltered'],
            'data'            => $response['data'],
        ]);
    }

    /**
     * Menampilkan Form Create / Edit dengan caching detail objek store.
     */
    public function form($id = null)
    {
        $store = null;

        if ($id) {
            // ✅ Caching detail form selama 30 menit
            // Kita join ke tenants agar mendapatkan 'tenant_name' untuk inisialisasi awal Select2 di Blade
            $store = Cache::remember("store_detail_{$id}", now()->addMinutes(30), function () use ($id) {
                return DB::table('stores')
                    ->join('tenants', 'stores.tenant_id', '=', 'tenants.id')
                    ->where('stores.id', $id)
                    ->select('stores.*', 'tenants.name as tenant_name')
                    ->first();
            });

            if (!$store) {
                abort(404);
            }
        }

        return view('admin.store.create-edit', [
            'store' => $store
        ]);
    }

    // ─── CONTOH IMPLEMENTASI CACHE BUSTER PADA MUTASI DATA ──────────────────

    public function store(Request $request)
    {
        // 1. Proses Validasi Aturan & Pesan Kustom Bahasa Indonesia
        $validatedData = $request->validate([
            'tenant_id'   => 'required|exists:tenants,id',
            'name'        => 'required|string|max:255',
            'branch_code' => 'required|string|max:50|unique:stores,branch_code',
            'address'     => 'required|string',
            'timezone'    => ['required', Rule::in(['+07:00', '+08:00', '+09:00'])],
            'currency'    => ['required', Rule::in(['IDR', 'USD', 'SGD'])],
        ], [
            'tenant_id.required'   => 'Tenant pemilik wajib dipilih.',
            'tenant_id.exists'     => 'Tenant yang dipilih tidak valid atau tidak terdaftar.',

            'name.required'        => 'Nama toko wajib diisi.',
            'name.max'             => 'Nama toko maksimal 255 karakter.',

            'branch_code.required' => 'Kode cabang wajib diisi.',
            'branch_code.max'      => 'Kode cabang maksimal 50 karakter.',
            'branch_code.unique'   => 'Kode cabang sudah digunakan oleh toko lain.',

            'address.required'     => 'Alamat lengkap toko wajib diisi.',

            'timezone.required'    => 'Zona waktu wajib dipilih.',
            'timezone.in'          => 'Pilihan zona waktu tidak sah.',

            'currency.required'    => 'Mata uang wajib dipilih.',
            'currency.in'          => 'Pilihan mata uang tidak sah.',
        ]);

        // 2. Eksekusi Insert Data menggunakan data yang telah tervalidasi
        DB::table('stores')->insert([
            'tenant_id'   => $validatedData['tenant_id'],
            'name'        => $validatedData['name'],
            'branch_code' => strtoupper($validatedData['branch_code']), // Otomatis diset huruf kapital agar rapi
            'address'     => $validatedData['address'],
            'timezone'    => $validatedData['timezone'],
            'currency'    => $validatedData['currency'],
            'disabled'    => 0, // Set default aktif saat pertama kali buat
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // 3. Pemicu hapus cache usang DataTables
        $this->clearStoreCache();

        return response()->json([
            'message' => 'Toko berhasil ditambahkan'
        ]);
    }

    public function update(Request $request, $id)
    {
        // 1. Cek Ketersediaan Data Dulu
        $store = DB::table('stores')->where('id', $id)->first();

        if (!$store) {
            return response()->json([
                'message' => 'Data Toko tidak ditemukan'
            ], 404);
        }

        // 2. Proses Validasi (Perhatikan Rule Unique yang mengabaikan $id saat ini)
        $validatedData = $request->validate([
            'tenant_id'   => 'required|exists:tenants,id',
            'name'        => 'required|string|max:255',
            // Menggunakan Rule::unique()->ignore() agar tidak error saat nyimpen kode cabang yang sama untuk ID ini
            'branch_code' => ['required', 'string', 'max:50', Rule::unique('stores')->ignore($id)],
            'address'     => 'required|string',
            'timezone'    => ['required', Rule::in(['+07:00', '+08:00', '+09:00'])],
            'currency'    => ['required', Rule::in(['IDR', 'USD', 'SGD'])],
        ], [
            'tenant_id.required'   => 'Tenant pemilik wajib dipilih.',
            'tenant_id.exists'     => 'Tenant yang dipilih tidak valid atau tidak terdaftar.',
            'name.required'        => 'Nama toko wajib diisi.',
            'name.max'             => 'Nama toko maksimal 255 karakter.',
            'branch_code.required' => 'Kode cabang wajib diisi.',
            'branch_code.max'      => 'Kode cabang maksimal 50 karakter.',
            'branch_code.unique'   => 'Kode cabang sudah digunakan oleh toko lain.',
            'address.required'     => 'Alamat lengkap toko wajib diisi.',
            'timezone.required'    => 'Zona waktu wajib dipilih.',
            'timezone.in'          => 'Pilihan zona waktu tidak sah.',
            'currency.required'    => 'Mata uang wajib dipilih.',
            'currency.in'          => 'Pilihan mata uang tidak sah.',
        ]);

        // 3. Eksekusi Update
        DB::table('stores')->where('id', $id)->update([
            'tenant_id'   => $validatedData['tenant_id'],
            'name'        => $validatedData['name'],
            'branch_code' => strtoupper($validatedData['branch_code']),
            'address'     => $validatedData['address'],
            'timezone'    => $validatedData['timezone'],
            'currency'    => $validatedData['currency'],
            'updated_at'  => now(),
        ]);

        // ✅ Pemicu hapus cache spesifik ID dan global list
        $this->clearStoreCache($id);

        return response()->json(['message' => 'Toko berhasil diupdate']);
    }

    public function destroy($id)
    {
        $store = DB::table('stores')->where('id', $id)->first();

        if (!$store) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data Toko tidak ditemukan'
            ], 404);
        }

        // Lakukan Pengecekan Relasi Lain Jika Ada (misal: apakah toko ini punya data transaksi/user kasir?)
        // Contoh (hanya jika ada tabel users/kasir terkait toko ini):
        // $isUsedByUser = DB::table('users')->where('store_id', $id)->exists();
        // if ($isUsedByUser) { return response()->json([...], 422); }

        DB::table('stores')->where('id', $id)->delete();

        // ✅ Bersihkan cache karena data dihapus
        $this->clearStoreCache($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'Toko berhasil dihapus permanen'
        ]);
    }

    public function toggleStatus($id)
    {
        // 1. Ambil data store sekaligus status disabled dari tenant pemiliknya
        $store = DB::table('stores')
            ->join('tenants', 'stores.tenant_id', '=', 'tenants.id')
            ->where('stores.id', $id)
            ->select('stores.*', 'tenants.disabled as tenant_disabled', 'tenants.name as tenant_name')
            ->first();

        if (!$store) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data Toko tidak ditemukan'
            ], 404);
        }

        // 2. ✅ CEK VALIDASI LOGIKA: Jika Tenant berstatus disabled (1), kunci tombol aksi toko
        if ((int) $store->tenant_disabled === 1) {
            return response()->json([
                'status'  => 'warning',
                'message' => "Toko tidak bisa diaktifkan karena Tenant induknya (\"{$store->tenant_name}\") sedang dinonaktifkan!"
            ], 422); // Gunakan 422 agar SweetAlert bisa menangkap state warning dengan pas
        }

        // 3. Jika lolos validasi, tentukan status baru (0 jadi 1, 1 jadi 0)
        $newStatus = $store->disabled ? 0 : 1;
        $statusText = $newStatus ? 'dinonaktifkan' : 'diaktifkan kembali';

        DB::table('stores')->where('id', $id)->update([
            'disabled'   => $newStatus,
            'updated_at' => now(),
        ]);

        // 4. Bersihkan cache agar data baru langsung merender di antarmuka admin
        $this->clearStoreCache($id);

        return response()->json([
            'status'  => 'success',
            'message' => "Toko berhasil {$statusText}"
        ]);
    }

    /**
     * ✅ HELPER UNTUK MEMBERSIHKAN CACHE STORES SECARA TOTAL
     */
    private function clearStoreCache($id = null)
    {
        if ($id) {
            Cache::forget("store_detail_{$id}");
        }

        // Bersihkan seluruh cache temporary MD5 DataTables agar data mutakhir langsung tampil
        Cache::flush();
    }
}
