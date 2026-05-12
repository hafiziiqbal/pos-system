<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    public function index()
    {
        return view('admin.tenant.index');
    }

    public function getDatatable(Request $request)
    {
        $draw   = $request->input('draw');
        $start  = $request->input('start', 0);
        $length = $request->input('length', 10);

        $columns = [
            0  => null, // No (row number)
            1  => 'name',
            2  => 'subdomain',
            3  => 'plan',
            4  => 'status',
            5  => 'created_at',
            6  => null, // Action
        ];

        $query = DB::table('tenants');

        $totalRecords = DB::table('tenants')->count();

        // ✅ Filter per kolom
        $columnSearches = $request->input('columns', []);
        foreach ($columnSearches as $index => $columnData) {
            $searchValue = $columnData['search']['value'] ?? '';
            if (empty($searchValue) || !isset($columns[$index]) || $columns[$index] === null) {
                continue;
            }

            // ✅ Khusus created_at → date range dengan format "from|to"
            if ((int) $index === 5) {
                $parts = explode('|', $searchValue);
                $from  = $parts[0] ?? '';
                $to    = $parts[1] ?? '';

                if (!empty($from)) {
                    $query->whereDate('created_at', '>=', $from);
                }
                if (!empty($to)) {
                    $query->whereDate('created_at', '<=', $to);
                }
                continue;
            }

            // Kolom lain → LIKE biasa
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

        $data = $query->skip($start)->take($length)->get()->map(function ($item) {
            $item->created_at = Carbon::parse($item->created_at)
                ->toIso8601String();
            $item->updated_at = Carbon::parse($item->updated_at)
                ->toIso8601String();
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
        $tenant = null;

        if ($id) {
            $tenant = DB::table('tenants')->where('id', $id)->first();

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
            'subdomain' => 'required|unique:tenants,subdomain,' . $id,
            'plan' => 'required'
        ], [
            'name.required' => 'Nama wajib diisi',
            'subdomain.required' => 'Subdomain wajib diisi',
            'subdomain.unique' => 'Subdomain sudah digunakan',
            'plan.required' => 'Plan wajib diisi'
        ]);

        DB::table('tenants')->where('id', $id)->update([
            'name' => $data['name'],
            'subdomain' => $data['subdomain'],
            'plan' => $data['plan'],
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Tenant berhasil diupdate'
        ]);
    }
}
