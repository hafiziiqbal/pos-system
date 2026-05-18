<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.user.index');
    }

    public function getDatatable(Request $request)
    {
        $draw   = $request->input('draw');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);

        $columns = [
            0  => null,
            1  => 'users.name',
            2  => 'users.email',
            3  => 'role',
            4  => 'tenants.name',
            5  => 'stores.name',
            6  => 'users.disabled',
            7  => 'users.created_at',
            8  => null,
        ];

        $totalRecords = DB::table('users')->count();

        // 1. Bangun Query Utama Pengguna
        $query = DB::table('users')
            ->leftJoin('tenants', 'users.tenant_id', '=', 'tenants.id')
            ->leftJoin('stores', 'users.store_id', '=', 'stores.id')
            ->select(
                'users.*',
                'tenants.name as tenant_name',
                'stores.name as store_name'
            );

        // 2. Filter Pencarian Kolom Datatable
        $columnSearches = $request->input('columns', []);
        foreach ($columnSearches as $index => $columnData) {
            $searchValue = $columnData['search']['value'] ?? '';
            if (empty($searchValue) && $searchValue !== '0') {
                continue;
            }

            if (!isset($columns[$index]) || $columns[$index] === null) {
                continue;
            }

            if ($columns[$index] === 'role') {
                $query->whereIn('users.id', function ($q) use ($searchValue) {
                    $q->select('model_id')
                        ->from('model_has_roles')
                        ->join('roles', 'role_id', '=', 'roles.id')
                        ->where('model_type', User::class)
                        ->where('roles.name', 'like', "%{$searchValue}%");
                });
                continue;
            }

            if ((int) $index === 7) {
                $parts = explode('|', $searchValue);
                $from  = $parts[0] ?? '';
                $to    = $parts[1] ?? '';

                if (!empty($from)) {
                    $fromFormatted = Carbon::parse($from)->setTimezone('UTC')->format('Y-m-d H:i:s');
                    $query->where('users.created_at', '>=', $fromFormatted);
                }
                if (!empty($to)) {
                    $toFormatted = Carbon::parse($to)->setTimezone('UTC')->format('Y-m-d H:i:s');
                    $query->where('users.created_at', '<=', $toFormatted);
                }
                continue;
            }

            if ((int) $index === 6) {
                $query->where('users.disabled', $searchValue);
                continue;
            }

            $query->where($columns[$index], 'like', "%{$searchValue}%");
        }

        $filteredRecords = $query->count();

        $orderColumnIndex = (int) $request->input('order.0.column', 7);
        $orderDir         = in_array(strtolower($request->input('order.0.dir', 'desc')), ['asc', 'desc']) ? $request->input('order.0.dir', 'desc') : 'desc';
        $orderColumn      = $columns[$orderColumnIndex] ?? 'users.created_at';

        if ($orderColumn === 'role') $orderColumn = 'users.id';

        $query->orderBy($orderColumn, $orderDir);

        // 3. Ambil Data chunk halaman ini
        $data = $query->skip($start)->take($length)->get();
        $userIds = $data->pluck('id');

        // 4. AMBIL DIRECT PERMISSIONS (model_has_permissions)
        $directPermissions = DB::table('model_has_permissions')
            ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
            ->whereIn('model_id', $userIds)
            ->where('model_type', User::class)
            ->select('model_id', 'permissions.name')
            ->get()
            ->groupBy('model_id');

        // 5. AMBIL DATA USER ROLES (model_has_roles) SECARA MURNI
        $userRolesData = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('model_has_roles.model_id', $userIds)
            ->where('model_has_roles.model_type', User::class)
            ->select('model_has_roles.model_id', 'roles.id as role_id', 'roles.name as role_name')
            ->get();

        // 6. AMBIL SEMUA ROLE PERMISSIONS (role_has_permissions) SECARA GLOBAL
        $allActiveRoleIds = $userRolesData->pluck('role_id')->unique()->filter();
        $globalRolePermissions = collect();

        if ($allActiveRoleIds->isNotEmpty()) {
            $globalRolePermissions = DB::table('role_has_permissions')
                ->join('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
                ->whereIn('role_has_permissions.role_id', $allActiveRoleIds)
                ->select('role_has_permissions.role_id', 'permissions.name as permission_name')
                ->get()
                ->groupBy(fn($item) => (int) $item->role_id);
        }

        // 7. Mapping Data ke DataTables Array Response
        // 🌟 PERBAIKAN: use() hanya membawa variabel-variabel kueri builder murni yang dibutuhkan
        $data = $data->map(function ($item) use ($directPermissions, $userRolesData, $globalRolePermissions) {
            $item->created_at = $item->created_at ? Carbon::parse($item->created_at)->toIso8601String() : null;
            $item->updated_at = $item->updated_at ? Carbon::parse($item->updated_at)->toIso8601String() : null;

            $item->direct_permissions = [];
            $item->role_permissions = [];

            // A. Set Direct Permissions (model_has_permissions)
            if ($directPermissions->has($item->id)) {
                $item->direct_permissions = $directPermissions->get($item->id)->pluck('name')->all();
            }

            // Ambil data filter koleksi role khusus untuk user baris ini
            $myRoles = $userRolesData->where('model_id', $item->id);

            // B. Set Teks Nama Role untuk Kolom Utama di Tabel
            $item->role = $myRoles->isNotEmpty() ? $myRoles->pluck('role_name')->join(', ') : '-';

            // C. Set Role Permissions (role_has_permissions)
            $myRolePermissionsCollector = collect();
            foreach ($myRoles->pluck('role_id') as $roleId) {
                $roleId = (int) $roleId; // ← tambahkan ini
                if ($globalRolePermissions->has($roleId)) {
                    $myRolePermissionsCollector = $myRolePermissionsCollector->merge(
                        $globalRolePermissions->get($roleId)->pluck('permission_name')
                    );
                }
            }
            $item->role_permissions = $myRolePermissionsCollector->unique()->values()->all();

            unset($item->password);
            return $item;
        });

        return response()->json([
            'draw'            => intval($draw),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $data,
        ]);
    }

    public function form($id = null)
    {
        // 1. Ambil semua master data untuk pilihan Dropdown Select2
        $roles       = Role::all();
        $permissions = Permission::all();

        $user            = null;
        $userRoles       = [];
        $userPermissions = [];

        // 2. Jika ada ID, berarti mode EDIT
        if ($id) {
            // Ambil data user mentah beserta nama tenant dan store-nya
            $user = DB::table('users')
                ->leftJoin('tenants', 'users.tenant_id', '=', 'tenants.id')
                ->leftJoin('stores', 'users.store_id', '=', 'stores.id')
                ->select('users.*', 'tenants.name as tenant_name', 'stores.name as store_name')
                ->where('users.id', $id)
                ->first();

            if ($user) {
                // Ambil semua NAME role yang dimiliki user dari tabel pivot secara murni (bebas dari scope tenant Spatie)
                $userRoles = DB::table('model_has_roles')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->where('model_id', $id)
                    ->where('model_type', User::class)
                    ->pluck('roles.name')
                    ->all();

                // Ambil semua NAME direct permission (permission tambahan) yang dimiliki user
                $userPermissions = DB::table('model_has_permissions')
                    ->join('permissions', 'model_has_permissions.permission_id', '=', 'permissions.id')
                    ->where('model_id', $id)
                    ->where('model_type', User::class)
                    ->pluck('permissions.name')
                    ->all();
            }
        }

        // 3. Lempar semua data ke halaman Blade
        return view('admin.user.create-edit', compact('roles', 'permissions', 'user', 'userRoles', 'userPermissions'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'tenant_id'   => 'required|exists:tenants,id',
            'store_id'    => 'nullable|exists:stores,id',
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users,email',
            'password'    => 'required|string|min:8',
            'roles'       => 'nullable|array',
            'roles.*'     => 'exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user = User::create([
            'tenant_id'  => $validatedData['tenant_id'] ?? null,
            'store_id'   => $validatedData['store_id'] ?? null,
            'name'       => $validatedData['name'],
            'email'      => $validatedData['email'],
            'password'   => Hash::make($validatedData['password']),
            'disabled'   => 0,
        ]);

        // Tetapkan konteks tenant agar sinkronisasi role ke pivot tabel Spatie valid
        setPermissionsTeamId($user->tenant_id);

        if (!empty($validatedData['roles'])) {
            $user->assignRole($validatedData['roles']);
        }
        if (!empty($validatedData['permissions'])) {
            $user->givePermissionTo($validatedData['permissions']);
        }

        $this->clearUserCache();

        return response()->json(['message' => 'User berhasil ditambahkan']);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Data User tidak ditemukan'], 404);
        }

        $validatedData = $request->validate([
            'tenant_id'   => 'required|exists:tenants,id',
            'store_id'    => 'nullable|exists:stores,id',
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'password'    => 'nullable|string|min:8',
            'roles'       => 'required|array|max:1',
            'roles.*'     => 'exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user->tenant_id = $validatedData['tenant_id'] ?? null;
        $user->store_id  = $validatedData['store_id'] ?? null;
        $user->name      = $validatedData['name'];
        $user->email     = $validatedData['email'];

        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        // 🌟 LANGKAH UTAMA: Bersihkan data lama terlebih dahulu di baris paling awal!

        // 1. Hapus total seluruh Direct Permissions lama milik user ini tanpa sekat tenant
        DB::table('model_has_permissions')
            ->where('model_id', $user->id)
            ->where('model_type', User::class)
            ->delete();

        // 2. Hapus total ikatan Role lama milik user ini dari tabel pivot model_has_roles
        DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('model_type', User::class)
            ->delete();

        // 🌟 LANGKAH KEDUA: Masukkan data baru yang dikirim dari form input

        // 3. Daftarkan Role Baru secara manual (Aman dari error constraint tenant_id cannot be null)
        if (!empty($validatedData['roles'])) {
            $roleTarget = DB::table('roles')
                ->where('name', $validatedData['roles'][0])
                ->first();

            if ($roleTarget) {
                DB::table('model_has_roles')->insert([
                    'role_id'    => $roleTarget->id,
                    'model_type' => User::class,
                    'model_id'   => $user->id,
                    'tenant_id'  => $user->tenant_id, // Menggunakan tenant_id baru dari user yang valid
                ]);
            }
        }

        // 4. Daftarkan Direct Permissions tambahan yang baru (jika ada yang dipilih)
        setPermissionsTeamId($user->tenant_id);
        if (!empty($validatedData['permissions'])) {
            $user->givePermissionTo($validatedData['permissions']);
        }

        // Kembalikan state global Spatie ke null
        setPermissionsTeamId(null);

        $this->clearUserCache($id);

        return response()->json(['message' => 'User dan hak akses berhasil diperbarui']);
    }

    public function destroy($id)
    {
        // 1. Cari user menggunakan Eloquent Model (Jangan pakai DB::table)
        $user = User::find($id);

        // 2. Cek jika user tidak ditemukan
        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data User tidak ditemukan'
            ], 404);
        }

        // 3. Eksekusi hapus langsung dari instance model Eloquent
        // Ini akan memicu Eloquent Event 'deleted' yang memicu Spatie untuk membersihkan tabel pivot
        $user->delete();

        // 4. Bersihkan cache karena data dihapus
        $this->clearUserCache($id);

        return response()->json([
            'status'  => 'success',
            'message' => 'User berhasil dihapus permanen beserta role & permission-nya'
        ]);
    }
    public function toggleStatus($id)
    {
        // 1. Ambil data user beserta status disabled dari tenant dan store terkait
        $user = DB::table('users')
            ->leftJoin('tenants', 'users.tenant_id', '=', 'tenants.id')
            ->leftJoin('stores', 'users.store_id', '=', 'stores.id')
            ->where('users.id', $id)
            ->select(
                'users.*',
                'tenants.disabled as tenant_disabled',
                'tenants.name as tenant_name',
                'stores.disabled as store_disabled',
                'stores.name as store_name'
            )
            ->first();

        if (!$user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data User tidak ditemukan'
            ], 404);
        }

        // 2. ✅ CEK VALIDASI LOGIKA (Hanya berlalu jika status saat ini sedang disabled dan ingin diaktifkan)
        if ((int) $user->disabled === 1) {
            if ($user->tenant_id && (int) $user->tenant_disabled === 1) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => "User tidak bisa diaktifkan karena Tenant induknya (\"{$user->tenant_name}\") sedang dinonaktifkan!"
                ], 422);
            }

            if ($user->store_id && (int) $user->store_disabled === 1) {
                return response()->json([
                    'status'  => 'warning',
                    'message' => "User tidak bisa diaktifkan karena Toko tempatnya bekerja (\"{$user->store_name}\") sedang dinonaktifkan!"
                ], 422);
            }
        }

        // 3. Jika lolos validasi, tentukan status baru (0 jadi 1, 1 jadi 0)
        $newStatus = $user->disabled ? 0 : 1;
        $statusText = $newStatus ? 'dinonaktifkan' : 'diaktifkan kembali';

        DB::table('users')->where('id', $id)->update([
            'disabled'   => $newStatus,
            'updated_at' => now(),
        ]);

        // 4. Bersihkan cache
        $this->clearUserCache($id);

        return response()->json([
            'status'  => 'success',
            'message' => "User berhasil {$statusText}"
        ]);
    }

    /**
     * ✅ HELPER UNTUK MEMBERSIHKAN CACHE USERS SECARA TOTAL
     */
    private function clearUserCache($id = null)
    {
        if ($id) {
            Cache::forget("user_detail_{$id}");
        }

        // Bersihkan seluruh cache temporary MD5 DataTables
        Cache::flush();
    }
}
