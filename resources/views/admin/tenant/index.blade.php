@extends('layouts.admin') {{-- sesuaikan dengan nama file layout kamu --}}

@section('content')
    <div class="w-full">
        <div class="bg-white rounded-xl p-5 space-y-3">

            <a href="{{ route('tenant.form') }}"
                class="whitespace-nowrap inline-flex items-center gap-2 rounded-sm bg-sky-500 border border-sky-500 px-4 py-2 text-sm font-medium tracking-wide text-white transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500 active:opacity-100 active:outline-offset-0 disabled:opacity-75 disabled:cursor-not-allowed dark:bg-sky-500 dark:border-sky-500 dark:text-white dark:focus-visible:outline-sky-500">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Tambah
            </a>

            <table id="tenantTable" class="display"></table>
        </div>

        {{-- ─── Styles ───────────────────────────────────────────────────────────── --}}
        <link rel="stylesheet" href="{{ asset('/vendor/datatables/dataTables.css') }}" />
        <link rel="stylesheet" href="{{ asset('/vendor/datatables/fixedColumns.dataTables.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('/vendor/flatpickr/flatpickr.min.min.css') }}" />

        {{-- ─── Scripts ──────────────────────────────────────────────────────────── --}}
        <script src="{{ asset('vendor/jquery/3.7.1/jquery.min.js') }}"></script>
        <script src="{{ asset('/vendor/datatables/dataTables.js') }}"></script>
        <script src="{{ asset('/vendor/datatables/dataTables.fixedColumns.min.js') }}"></script>
        <script src="{{ asset('js/date-format.js') }}"></script>

        <script>
            // ═══════════════════════════════════════════════════════════════════════
            // DATATABLE
            // ═══════════════════════════════════════════════════════════════════════
            let fixedConfig = window.innerWidth < 768 ? false : {
                left: 3
            };

            $('#tenantTable').DataTable({
                dom: 'lrtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/tenant/datatable',
                    type: 'GET'
                },
                order: [
                    [5, 'desc']
                ],
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: fixedConfig,
                columnDefs: [{
                    targets: '_all',
                    className: 'dt-nowrap'
                }],

                columns: [
                    // 0 – No
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        title: 'No',
                        render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1,
                    },
                    // 1 – Nama
                    {
                        data: 'name',
                        title: 'Nama'
                    },
                    // 2 – Subdomain
                    {
                        data: 'subdomain',
                        title: 'Subdomain'
                    },
                    // 3 – Plan
                    {
                        data: 'plan',
                        title: 'Plan'
                    },
                    // 4 – Status
                    {
                        data: 'status',
                        title: 'Status'
                    },
                    // 5 – Created At
                    {
                        data: 'created_at',
                        title: 'Ditambahkan Pada',
                        render: (data) => formatIndoDateTime(data),
                    },
                    // 6 – Action
                    {
                        data: 'hasil',
                        title: 'Aksi',
                        render: (data, type, row) => {
                            return `
                                <div class="flex items-center gap-2">
                                    <button
                                        class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 mr-1">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        Detail
                                    </button>
                                </div>
                             `
                        }
                    }
                ]
            })
        </script>
    </div>
@endsection
