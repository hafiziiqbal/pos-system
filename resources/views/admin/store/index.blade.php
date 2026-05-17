@extends('layouts.admin') {{-- sesuaikan dengan nama file layout kamu --}}

@section('content')
    <style>
        .search-row th::before,
        .search-row th::after,
        .search-row td::before,
        .search-row td::after {
            display: none !important;
        }

        .search-row th,
        .search-row td {
            pointer-events: auto !important;
            background-image: none !important;
        }
    </style>

    <div class="w-full" x-data="storePage()">
        <div class="bg-white rounded-xl p-5 space-y-3">
            <h2 class="text-xl font-bold text-gray-800">Toko</h2>
            <a href="{{ route('store.form') }}"
                class="whitespace-nowrap inline-flex items-center gap-2 rounded-sm bg-sky-500 border border-sky-500 px-4 py-2 text-sm font-medium tracking-wide text-white transition hover:opacity-75 text-center focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-500 active:opacity-100 active:outline-offset-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Tambah
            </a>

            <table id="storeTable" class="display w-full border border-neutral-200">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tenant</th>
                        <th>Nama Toko</th>
                        <th>Kode Cabang</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Ditambahkan Pada</th>
                        <th>Aksi</th>
                    </tr>
                    <tr class="search-row bg-neutral-50/50">
                        <td></td>
                        <td><input type="text" placeholder="Cari Tenant..."
                                class="dt-col-search w-full px-2 py-1 text-xs border border-neutral-300 rounded focus:outline-sky-500">
                        </td>
                        <td><input type="text" placeholder="Cari Toko..."
                                class="dt-col-search w-full px-2 py-1 text-xs border border-neutral-300 rounded focus:outline-sky-500">
                        </td>
                        <td><input type="text" placeholder="Cari Nama Cabang..."
                                class="dt-col-search w-full px-2 py-1 text-xs border border-neutral-300 rounded focus:outline-sky-500">
                        </td>
                        <td><input type="text" placeholder="Cari Alamat..."
                                class="dt-col-search w-full px-2 py-1 text-xs border border-neutral-300 rounded focus:outline-sky-500">
                        </td>

                        <td data-orderable="false" class="p-1">
                            <select
                                class="dt-col-search w-full px-2 py-1 text-xs border border-neutral-300 rounded focus:outline-sky-500 bg-white block text-neutral-800"
                                style="min-width: 100px;">
                                <option value="">Semua</option>
                                <option value="0">Active</option>
                                <option value="1">Disabled</option>
                            </select>
                        </td>
                        <td>
                            <div class="relative flex items-center">
                                <input type="text" id="createdAtSearch" placeholder="Pilih Rentang Tanggal..."
                                    class="dt-col-search w-full px-2 py-1 text-xs border border-neutral-300 rounded focus:outline-sky-500 bg-white"
                                    readonly>
                                <button type="button" id="clearDate"
                                    class="absolute right-2 text-xs text-neutral-400 hover:text-neutral-600 hidden">&times;</button>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </thead>
            </table>
        </div>

        {{-- ─── Styles ───────────────────────────────────────────────────────────── --}}
        <link rel="stylesheet" href="{{ asset('/vendor/datatables/dataTables.css') }}" />
        <link rel="stylesheet" href="{{ asset('/vendor/datatables/fixedColumns.dataTables.min.css') }}" />
        <link rel="stylesheet" href="{{ asset('/vendor/flatpickr/flatpickr.min.css') }}" />

        {{-- ─── Scripts ──────────────────────────────────────────────────────────── --}}
        <script src="{{ asset('/vendor/datatables/dataTables.js') }}"></script>
        <script src="{{ asset('/vendor/datatables/dataTables.fixedColumns.min.js') }}"></script>
        <script src="{{ asset('/vendor/flatpickr/flatpickr.js') }}"></script>
        <script src="{{ asset('js/date-format.js') }}"></script>

        <script>
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            let fixedConfig = window.innerWidth < 768 ? false : {
                left: 3
            };

            // Inisialisasi DataTable
            const table = $('#storeTable').DataTable({
                dom: 'lrtip',
                processing: true,
                serverSide: true,
                orderCellsTop: true, // 1. ✅ WAJIB: Kunci sorting hanya di baris header atas teks judul
                ajax: {
                    url: '/store/datatable',
                    type: 'GET'
                },
                order: [
                    [6, 'desc']
                ],
                scrollX: true,
                scrollCollapse: true,
                fixedColumns: fixedConfig,
                columnDefs: [{
                    targets: '_all',
                    className: 'dt-nowrap'
                }],
                columns: [{
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1,
                    },
                    {
                        data: 'tenant_name'
                    }, // 1. Nama Tenant (diambil dari alias select join)
                    {
                        data: 'name'
                    }, // 2. Nama Toko
                    {
                        data: 'branch_code'
                    }, // 3. Kode Cabang
                    {
                        data: 'address'
                    }, // 4. Alamat
                    {
                        data: 'disabled', // 5. Status
                        render: (data) => data == 1 ?
                            '<span class="px-2 py-1 bg-red-100 text-red-700 rounded-md text-xs">Disabled</span>' :
                            '<span class="px-2 py-1 bg-green-100 text-green-700 rounded-md text-xs">Active</span>'
                    },
                    {
                        data: 'created_at',
                        render: (data) => formatIndoDateTime(data)
                    }, // 6. Tanggal Dibuat
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: (data, type, row) => {
                            const baseUrl = "{{ route('store.form', ':id') }}";
                            const finalUrl = data ? baseUrl.replace(':id', data) :
                                "{{ route('store.form') }}";

                            const settingUrl = "{{ route('store.settings', ':store_id') }}";
                            const finalSettingUrl = settingUrl.replace(':store_id', data);

                            const isDisabled = row.disabled == 1;

                            const toggleClass = isDisabled ?
                                'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500' :
                                'bg-neutral-500 hover:bg-neutral-600 focus:ring-neutral-400';

                            const toggleIcon = isDisabled ?
                                `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>` :
                                `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" /></svg>`;

                            const toggleTitle = isDisabled ? 'Aktifkan Tenant' : 'Nonaktifkan Tenant';

                            return `
                                <div class="flex items-center gap-2">

                                    <a href="${finalSettingUrl}"
                                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>

                                    </a>

                                    <button @click="toggleStoreStatus(${data}, '${row.name}', ${row.disabled})"
                                        title="${toggleTitle}"
                                        class="cursor-pointer inline-flex items-center px-3 py-1.5 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 ${toggleClass}">
                                        ${toggleIcon}
                                    </button>

                                    <a href="${finalUrl}"
                                        class="inline-flex items-center px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                        </svg>
                                    </a>

                                    <button @click="deleteStore(${data}, '${row.name}')"
                                        class="cursor-pointer inline-flex items-center px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:outline-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            `;
                        }
                    }
                ]
            });

            // 2. ✅ LOGIK PENCARIAN & DEBOUNCE DIASAH ULANG
            // Memisahkan input text (pakai keyup) dengan select box (pakai change)
            $('input.dt-col-search').on('keyup', debounce(function() {
                if ($(this).attr('id') === 'createdAtSearch') return;

                const colIndex = $(this).closest('td').index();
                const value = this.value;

                if (table.column(colIndex).search() !== value) {
                    table.column(colIndex).search(value).draw();
                }
            }, 500));

            $('select.dt-col-search').on('change', function() {
                const colIndex = $(this).closest('td').index();
                const value = this.value;

                if (table.column(colIndex).search() !== value) {
                    table.column(colIndex).search(value).draw();
                }
            });

            // ═══════════════════════════════════════════════════════════════════════
            // INTEGRASI FLATPICKR DATE RANGE
            // ═══════════════════════════════════════════════════════════════════════
            const dateInput = flatpickr("#createdAtSearch", {
                mode: "range",
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr, instance) {
                    const clearBtn = $('#clearDate');

                    if (selectedDates.length > 0) {
                        clearBtn.removeClass('hidden');
                    }

                    if (selectedDates.length >= 1) {
                        // 1. Ambil objek Date murni dari Flatpickr
                        const startObj = selectedDates[0];
                        // Jika tanggal kedua belum dipilih, samakan objeknya dengan yang pertama
                        const endObj = selectedDates[1] ? selectedDates[1] : startObj;

                        // 2. Set waktu mulai ke jam 00:00:00 sesuai waktu lokal komputer user (WIB/WITA/WIT)
                        const localStart = new Date(startObj.getFullYear(), startObj.getMonth(), startObj.getDate(),
                            0, 0, 0);

                        // 3. Set waktu akhir ke jam 23:59:59 sesuai waktu lokal komputer user
                        const localEnd = new Date(endObj.getFullYear(), endObj.getMonth(), endObj.getDate(), 23, 59,
                            59);

                        // 4. Konversi ke format ISO String (.toISOString())
                        // Fungsi ini otomatis mengubah waktu lokal user ke standar UTC bersimbol 'Z' di belakangnya
                        // Contoh: Jam 00:00:00 WITA (Makassar) otomatis dikonversi menjadi 16:00:00 UTC hari sebelumnya
                        const startIso = localStart.toISOString();
                        const endIso = localEnd.toISOString();

                        const customSearchValue = `${startIso}|${endIso}`;

                        // Kirim string ISO rentang waktu UTC penuh ke backend
                        table.column(6).search(customSearchValue).draw();
                    }
                }
            });

            $('#clearDate').on('click', function() {
                dateInput.clear();
                $(this).addClass('hidden');

                table.column(6).search('').draw();
            });

            function storePage() {
                return {
                    toggleStoreStatus(id, name, currentStatus) {
                        const actionText = currentStatus == 1 ? 'mengaktifkan kembali' : 'menonaktifkan';

                        Swal.fire({
                            title: 'Ubah Status Toko?',
                            text: `Anda akan ${actionText} toko "${name}".`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: currentStatus == 1 ? '#059669' : '#6b7280',
                            cancelButtonColor: '#9ca3af',
                            confirmButtonText: 'Ya, Lanjutkan!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const toggleUrl = `/store/${id}/toggle`;

                                axios.patch(toggleUrl, {}, {
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                                .getAttribute('content')
                                        }
                                    })
                                    .then(response => {
                                        Swal.fire({
                                            title: 'Berhasil!',
                                            text: response.data.message,
                                            icon: 'success',
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                        // Pastikan ID selector datatable store kamu sesuai (misal: #storeTable atau #tenantTable)
                                        $('#storeTable').DataTable().ajax.reload(null, false);
                                    })
                                    .catch(error => {
                                        // ✅ CEK RESPONSE ERROR DARI BACKEND (Gagal validasi status tenant)
                                        if (error.response && error.response.status === 422) {
                                            const data = error.response.data;

                                            Swal.fire({
                                                title: data.status === 'warning' ? 'Perhatian!' : 'Gagal!',
                                                text: data.message || 'Permintaan tidak dapat diproses.',
                                                icon: data.status === 'warning' ? 'warning' : 'error'
                                            });
                                        } else {
                                            // Error umum server crash / network error
                                            Swal.fire('Gagal!',
                                                'Terjadi kesalahan sistem saat mengubah status toko.', 'error');
                                        }
                                    });
                            }
                        });
                    },
                    deleteStore(id, name) {
                        Swal.fire({
                            title: 'Apakah kamu yakin?',
                            text: `Toko "${name}" akan dihapus permanen dari sistem.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#e11d48',
                            cancelButtonColor: '#64748b',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                const deleteUrl = `/store/${id}`;
                                axios.delete(deleteUrl, {
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                                .getAttribute('content')
                                        }
                                    })
                                    .then(response => {
                                        Swal.fire({
                                            title: 'Berhasil!',
                                            text: response.data.message,
                                            icon: 'success',
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                        $('#storeTable').DataTable().ajax.reload(null, false);
                                    })
                                    .catch(error => {
                                        if (error.response && error.response.data) {
                                            const data = error.response.data;
                                            Swal.fire({
                                                title: data.status === 'warning' ? 'Perhatian' : 'Gagal!',
                                                text: data.message || 'Terjadi kesalahan sistem.',
                                                icon: data.status === 'warning' ? 'warning' : 'error'
                                            });
                                        } else {
                                            Swal.fire('Error', 'Gagal memproses permintaan hapus data.', 'error');
                                        }
                                    });
                            }
                        });
                    }
                }
            }
        </script>
    </div>
@endsection
