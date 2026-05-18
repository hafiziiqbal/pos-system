<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Bersihkan cache Spatie
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Buat Tenant Palsu / System Tenant untuk Super Admin
        $systemTenantId = DB::table('tenants')->insertGetId([
            'name'       => 'System Administration',
            'subdomain'  => 'system',
            'plan'       => 'pro',
            'disabled'   => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // =====================================================================
        // 3. DAFTARKAN SEMUA PERMISSIONS (Hak Akses Spesifik)
        // Permissions tidak terikat tenant (global), jadi langsung dibuat saja.
        // =====================================================================
        $permissions = [
            // Modul Setup & Pengguna
            'manage tenants',
            'manage stores',
            'manage store settings',
            'manage users',
            // Modul Master Data
            'create categories',
            'read categories',
            'update categories',
            'delete categories',
            'create products',
            'read products',
            'update products',
            'delete products',
            'manage suppliers',
            'manage customers',
            // Modul Transaksi POS
            'open close shifts',
            'manage all shifts',
            'create transactions',
            'process payments',
            'void transactions',
            // Modul Pengadaan & Gudang
            'create purchase orders',
            'receive purchase orders',
            'manage stocks',
            // Modul Laporan
            'view sales reports',
            'view profit reports',
            'view inventory reports'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }


        // =====================================================================
        // 4. SETUP SUPER ADMIN (Terikat ke System Tenant)
        // =====================================================================
        $superAdminRole = Role::create([
            'name'       => 'Super Admin',
            'tenant_id'  => $systemTenantId,
            'guard_name' => 'web'
        ]);

        $superAdminUser = User::create([
            'name'      => 'Pemilik Sistem',
            'email'     => 'super@admin.com',
            'password'  => bcrypt('password123'),
            'tenant_id' => $systemTenantId,
            'store_id'  => null,
            'disabled'  => 0
        ]);

        // Beri tahu Spatie bahwa kita menugaskan role di dalam System Tenant
        setPermissionsTeamId($systemTenantId);
        $superAdminUser->assignRole($superAdminRole);


        // =====================================================================
        // 5. BUAT ROLE TEMPLATE UNTUK KLIEN / TENANT BARU
        // (tenant_id diset null agar berfungsi sebagai Blueprint/Master Role)
        // =====================================================================

        // A. Tenant Admin (Punya semua akses KECUALI manage tenants)
        $tenantAdminRole = Role::firstOrCreate(['name' => 'Tenant Admin', 'tenant_id' => null, 'guard_name' => 'web']);
        $tenantAdminRole->syncPermissions(array_diff($permissions, ['manage tenants']));

        // B. Store Manager
        $storeManagerRole = Role::firstOrCreate(['name' => 'Store Manager', 'tenant_id' => null, 'guard_name' => 'web']);
        $storeManagerRole->syncPermissions([
            'manage users',
            'read categories',
            'read products',
            'manage customers',
            'manage all shifts',
            'void transactions',
            'manage stocks',
            'view sales reports',
            'view inventory reports'
        ]);

        // C. Cashier
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier', 'tenant_id' => null, 'guard_name' => 'web']);
        $cashierRole->syncPermissions([
            'open close shifts',
            'create transactions',
            'process payments',
            'read categories',
            'read products',
            'manage customers'
        ]);

        // D. Warehouse / Purchasing
        $warehouseRole = Role::firstOrCreate(['name' => 'Warehouse', 'tenant_id' => null, 'guard_name' => 'web']);
        $warehouseRole->syncPermissions([
            'manage suppliers',
            'create purchase orders',
            'receive purchase orders',
            'manage stocks',
            'read products',
            'view inventory reports'
        ]);
    }
}
