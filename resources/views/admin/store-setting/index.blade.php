@extends('layouts.admin')

@section('content')
    <div class="w-full" x-data="storeSettingsPage()">
        <div class="bg-white rounded-xl p-5 space-y-3">
            <div
                class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between border-b border-slate-100 pb-4 mb-6">
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="text-xl font-bold text-gray-800 tracking-tight">
                        Pengaturan {{ $store->name }}
                    </h2>
                    <span
                        class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200 uppercase tracking-wider shadow-2xs">
                        {{ $store->branch_code }}
                    </span>
                </div>

                <div class="flex items-center">
                    <a href="{{ route('store') }}"
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 hover:text-slate-800 transition-colors bg-slate-50 hover:bg-slate-100 md:bg-transparent md:hover:bg-transparent px-3 py-1.5 md:p-0 rounded-lg border border-slate-200 md:border-none">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="size-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                        </svg>
                        <span>Kembali</span>
                    </a>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Tambah Parameter Baru</h3>
                <form @submit.prevent="submitCreate" class="flex flex-wrap md:flex-nowrap gap-3 items-end">
                    <div class="w-full md:w-1/3">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Key</label>
                        <input type="text" x-model="createForm.key" placeholder="Contoh: pph_tax_rate"
                            class="block w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                    </div>
                    <div class="w-full md:w-1/2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Value</label>
                        <input type="text" x-model="createForm.value" placeholder="Contoh: 0.11"
                            class="block w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                    </div>
                    <button type="submit"
                        class="w-full md:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-lg transition-colors">
                        Simpan
                    </button>
                </form>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <table id="settingsTable" class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th>No</th>
                            <th>Key</th>
                            <th>Value</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                        <tr class="bg-white filter-row border-b border-slate-100">
                            <td></td> {{-- Kolom No kosong --}}
                            <td data-orderable="false" class="p-2">
                                <input type="text" placeholder="Cari Key..."
                                    class="column-search w-full px-2 py-1 text-xs border border-neutral-300 rounded focus:outline-sky-500">
                            </td>
                            <td data-orderable="false" class="p-2">
                                <input type="text" placeholder="Cari Value..."
                                    class="column-search w-full px-2 py-1 text-xs border border-neutral-300 rounded focus:outline-sky-500">
                            </td>
                            <td data-orderable="false">
                                <div class="relative flex items-center">
                                    <input type="text" id="createdAtSearch" placeholder="Pilih Rentang Tanggal..."
                                        class=" w-full px-2 py-1 text-xs border border-neutral-300 rounded focus:outline-sky-500"
                                        readonly>
                                    <button type="button" id="clearDate"
                                        class="absolute right-2 text-xs text-neutral-400 hover:text-neutral-600 hidden">&times;</button>
                                </div>
                            </td>
                            <td></td> {{-- Kolom Aksi kosong --}}
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <template x-if="editModalOpen">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center z-50">
                    <div class="bg-white rounded-xl border border-slate-200 p-6 w-full max-w-md shadow-xl mx-4">
                        <h3 class="text-base font-bold text-slate-800 mb-4">Edit Pengaturan</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Key</label>
                                <input type="text" x-model="editForm.key"
                                    class="block w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 mb-1">Value</label>
                                <textarea x-model="editForm.value" rows="3"
                                    class="block w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end gap-2 mt-6">
                            <button @click="editModalOpen = false"
                                class="px-4 py-2 text-sm font-medium text-slate-500 bg-slate-100 hover:bg-slate-200 rounded-lg">Batal</button>
                            <button @click="submitUpdate"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg">Simpan
                                Perubahan</button>
                        </div>
                    </div>
                </div>
            </template>
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

            function storeSettingsPage() {
                return {
                    storeId: "{{ $store->id }}",
                    createForm: {
                        key: '',
                        value: ''
                    },
                    editForm: {
                        id: '',
                        key: '',
                        value: ''
                    },
                    editModalOpen: false,

                    init() {
                        // 1. Inisialisasi DataTables
                        const table = $('#settingsTable').DataTable({
                            processing: true,
                            serverSide: true,
                            dom: 'rtip',
                            orderCellsTop: true,
                            ajax: `/store/${this.storeId}/settings/data`,
                            columns: [{
                                    data: null,
                                    searchable: false,
                                    orderable: false,
                                    render: function(data, type, row, meta) {
                                        return meta.row + meta.settings._iDisplayStart + 1;
                                    }
                                },
                                {
                                    data: 'key'
                                },
                                {
                                    data: 'value'
                                },
                                {
                                    data: 'created_at',
                                    render: function(data) {
                                        return new Date(data).toLocaleDateString('id-ID', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        });
                                    }
                                },
                                {
                                    data: null,
                                    searchable: false,
                                    orderable: false,
                                    render: function(data, type, row) {
                                        return `
                                    <div class="flex gap-2">
                                        <button onclick="window.triggerEditSetting(${row.id}, '${row.key}', '${row.value}')" class="px-2 py-1 text-xs font-semibold text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors">Edit</button>
                                        <button onclick="window.triggerDeleteSetting(${row.id})" class="px-2 py-1 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors">Hapus</button>
                                    </div>
                                `;
                                    }
                                }
                            ]
                        });

                        // 2. Logika Pencarian Kolom Teks (Key & Value saja) -> SEKARANG PAKAI DEBOUNCE 500ms
                        table.columns().every(function(index) {
                            const that = this;

                            // Bungkus fungsi eksekusi draw() ke dalam helper debounce bawaan kamu
                            $('.filter-row td').eq(index).find('input.column-search').on('keyup change clear', debounce(
                                function() {
                                    if (that.search() !== this.value) {
                                        that.search(this.value).draw();
                                    }
                                }, 500)); // Menahan request selama 500ms setelah user selesai mengetik
                        });

                        // 3. ⚡ INTEGRASI FLATPICKR DATE RANGE KHUSUS KOLOM INDEX 3 (created_at)
                        const dateInput = flatpickr("#createdAtSearch", {
                            mode: "range",
                            dateFormat: "Y-m-d",
                            onChange: function(selectedDates, dateStr, instance) {
                                const clearBtn = $('#clearDate');

                                if (selectedDates.length > 0) {
                                    clearBtn.removeClass('hidden');
                                }

                                // Jalankan pencarian jika sudah ada tanggal yang dipilih (bisa 1 atau 2 tanggal)
                                if (selectedDates.length >= 1) {
                                    // 1. Ambil objek Date murni
                                    const startObj = selectedDates[0];
                                    const endObj = selectedDates[1] ? selectedDates[1] :
                                    startObj; // Jika 1 tanggal, samakan

                                    // 2. Set waktu mulai (00:00:00) & akhir (23:59:59) sesuai waktu lokal user
                                    const localStart = new Date(startObj.getFullYear(), startObj.getMonth(),
                                        startObj.getDate(), 0, 0, 0);
                                    const localEnd = new Date(endObj.getFullYear(), endObj.getMonth(), endObj
                                        .getDate(), 23, 59, 59);

                                    // 3. Konversi ke ISO 8601 (Otomatis menyesuaikan ke UTC)
                                    const startIso = localStart.toISOString();
                                    const endIso = localEnd.toISOString();

                                    const customSearchValue = `${startIso}|${endIso}`;

                                    // Kirim ke backend menggunakan index kolom yang sesuai (cek kembali apakah benar index 3 atau 5)
                                    table.column(3).search(customSearchValue).draw();
                                }
                            }
                        });

                        // Fungsi tombol X untuk mereset filter tanggal
                        $('#clearDate').on('click', function() {
                            dateInput.clear();
                            $(this).addClass('hidden');
                            // Pastikan index kolom sama dengan yang di atas
                            table.column(3).search('').draw();
                        });

                        // Mapping fungsi global agar terbaca oleh tombol di dalam DataTables
                        window.triggerEditSetting = (id, key, value) => {
                            this.editForm = {
                                id,
                                key,
                                value
                            };
                            this.editModalOpen = true;
                        };

                        window.triggerDeleteSetting = (id) => {
                            this.deleteSetting(id);
                        };
                    },

                    submitCreate() {
                        axios.post(`/store/${this.storeId}/settings/store`, this.createForm)
                            .then(res => {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: res.data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                this.createForm = {
                                    key: '',
                                    value: ''
                                }; // Reset form
                                $('#settingsTable').DataTable().ajax.reload(null, false);
                            })
                            .catch(err => {
                                Swal.fire('Gagal!', err.response.data.message || 'Terjadi kesalahan.', 'error');
                            });
                    },

                    submitUpdate() {
                        axios.put(`/store/${this.storeId}/settings/${this.editForm.id}/update`, this.editForm)
                            .then(res => {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: res.data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                this.editModalOpen = false;
                                $('#settingsTable').DataTable().ajax.reload(null, false);
                            })
                            .catch(err => {
                                Swal.fire('Gagal!', err.response.data.message || 'Terjadi kesalahan.', 'error');
                            });
                    },

                    deleteSetting(id) {
                        Swal.fire({
                            title: 'Hapus Parameter?',
                            text: "Tindakan ini akan menghapus pengaturan secara permanen.",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc2626',
                            cancelButtonColor: '#9ca3af',
                            confirmButtonText: 'Ya, Hapus!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                axios.delete(`/store/${this.storeId}/settings/${id}/destroy`)
                                    .then(res => {
                                        Swal.fire({
                                            title: 'Terhapus!',
                                            text: res.data.message,
                                            icon: 'success',
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                        $('#settingsTable').DataTable().ajax.reload(null, false);
                                    })
                                    .catch(() => {
                                        Swal.fire('Gagal!', 'Tidak dapat menghapus data.', 'error');
                                    });
                            }
                        });
                    }
                }
            }
        </script>
    </div>
@endsection
