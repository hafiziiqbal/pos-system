@extends('layouts.admin')

@section('content')
    <style>
        /* 1. Mengatur Container Utama Select2 */
        .select2-container--default .select2-selection--single {
            display: block !important;
            width: 100% !important;
            height: auto !important;
            /* Biar padding-y yang mengatur tinggi */
            padding: 0.625rem 1rem !important;
            /* x-4 py-2.5 menyesuaikan inputan */
            font-size: 0.875rem !important;
            /* sm:text-sm */
            line-height: 1.25rem !important;
            color: #334155 !important;
            /* text-slate-700 */
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            /* border-slate-200 */
            border-radius: 0.75rem !important;
            /* rounded-xl */
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            /* transition-all duration-200 */
            outline: none !important;
        }

        /* 2. Mengatur Tampilan Text di Dalam Select2 */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            padding-left: 0 !important;
            padding-right: 2rem !important;
            line-height: 1.25rem !important;
        }

        /* Mengatur warna placeholder bawaan select2 */
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
            /* text-slate-400 */
        }

        /* 3. Menghilangkan & Merapikan Ikon Panah Bawaan Select2 */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            top: 0 !important;
            right: 0.75rem !important;
            width: 1.25rem !important;
        }

        /* 4. Efek Focus State Ring (Menyamakan dengan focus:ring-4 focus:ring-blue-100) */
        .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #3b82f6 !important;
            /* focus:border-blue-500 */
            box-shadow: 0 0 0 4px #dbeafe !important;
            /* focus:ring-4 focus:ring-blue-100 */
        }

        /* 5. Handle Tampilan Error Jika Validasi Gagal */
        .select2-error-state .select2-selection--single {
            border-color: #f87171 !important;
            /* border-red-400 */
        }

        /* 6. Merapikan Kotak Dropdown Pencarian Yang Muncul Di Bawahnya */
        .select2-dropdown {
            border-color: #e2e8f0 !important;
            border-radius: 0.75rem !important;
            /* rounded-xl */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1) !important;
            /* shadow-lg */
            overflow: hidden !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            /* rounded-lg */
            padding: 0.5rem !important;
            outline: none !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #3b82f6 !important;
        }

        /* Warna saat item dropdown di-hover / disorot */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #3b82f6 !important;
            /* bg-blue-600 */
        }
    </style>
    <div class="w-full" x-data="storeForm()">
        <div class="bg-white rounded-xl p-5 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800" x-text="form.id ? 'Edit Toko (Store)' : 'Tambah Toko Baru'"></h2>
                <a href="{{ route('store') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-5 p-1">

                    {{-- Input Pilih Tenant (Select2 AJAX) --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tenant Pemilik</label>
                        <div class="relative" :class="errors.tenant_id ? 'select2-error-state' : ''">
                            <select id="tenantSelect2" class="w-full">
                                <option value="">-- Cari & Pilih Tenant --</option>
                            </select>
                        </div>
                        <template x-if="errors.tenant_id">
                            <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.tenant_id"></p>
                        </template>
                    </div>

                    {{-- Input Nama Toko --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Toko / Cabang</label>
                        <div class="relative">
                            <input type="text" x-model="form.name"
                                class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                :class="errors.name ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'"
                                placeholder="Contoh: Toko Pusat Jakarta">
                        </div>
                        <template x-if="errors.name">
                            <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.name"></p>
                        </template>
                    </div>

                    {{-- Input Kode Cabang --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Kode Cabang (Branch Code)</label>
                        <div class="relative">
                            <input type="text" x-model="form.branch_code"
                                class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                :class="errors.branch_code ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'"
                                placeholder="Contoh: JKTA-01">
                        </div>
                        <template x-if="errors.branch_code">
                            <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.branch_code"></p>
                        </template>
                    </div>

                    {{-- Input Alamat --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Lengkap</label>
                        <div class="relative">
                            <textarea x-model="form.address" rows="3"
                                class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                :class="errors.address ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'"
                                placeholder="Masukkan alamat lengkap toko..."></textarea>
                        </div>
                        <template x-if="errors.address">
                            <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.address"></p>
                        </template>
                    </div>

                    {{-- Grid 2 Kolom untuk Timezone & Currency --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Input Timezone --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Zona Waktu (Timezone)</label>
                            <select x-model="form.timezone"
                                class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                :class="errors.timezone ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'">
                                <option value="+07:00">Asia/Jakarta (WIB / +07:00)</option>
                                <option value="+08:00">Asia/Makassar (WITA / +08:00)</option>
                                <option value="+09:00">Asia/Jayapura (WIT / +09:00)</option>
                            </select>
                            <template x-if="errors.timezone">
                                <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.timezone"></p>
                            </template>
                        </div>

                        {{-- Input Currency --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Mata Uang (Currency)</label>
                            <select x-model="form.currency"
                                class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                :class="errors.currency ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'">
                                <option value="IDR">IDR (Rupiah)</option>
                                <option value="USD">USD (Dollar)</option>
                                <option value="SGD">SGD (Singapore Dollar)</option>
                            </select>
                            <template x-if="errors.currency">
                                <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.currency"></p>
                            </template>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-4">
                        <button type="submit" :disabled="isLoading"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md shadow-blue-200 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all duration-200 transform active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">

                            <span x-show="!isLoading" x-text="form.id ? 'Simpan Perubahan' : 'Daftarkan Toko'"></span>

                            <span x-show="isLoading" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Memproses...
                            </span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- Tambahan library pendukung Select2 (Gunakan versi lokal vendor kamu jika ada) --}}
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            // Data store dari backend (jika dalam mode edit)
            window.storeData = @json($store ?? null);

            function storeForm() {
                return {
                    isLoading: false,
                    form: {
                        id: '',
                        tenant_id: '',
                        name: '',
                        branch_code: '',
                        address: '',
                        timezone: '+07:00', // default value
                        currency: 'IDR' // default value
                    },
                    errors: {},

                    init() {
                        // Set global axios token csrf
                        axios.defaults.headers.common['X-CSRF-TOKEN'] =
                            document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        // Ambil scope instance Alpine agar bisa diakses di dalam jQuery Select2
                        const alpineInstance = this;

                        // ═══════════════════════════════════════════════════════════════════════
                        // INISIALISASI SELECT2 DENGAN API FETCHING
                        // ═══════════════════════════════════════════════════════════════════════
                        $('#tenantSelect2').select2({
                            placeholder: '-- Cari & Pilih Tenant --',
                            allowClear: true,
                            width: '100%',
                            ajax: {
                                url: '/tenants/search', // 👈 Sesuaikan dengan endpoint API pencarian Tenant kamu
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        q: params.term // keyword pencarian yang diketik user
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data.map(tenant => ({
                                            id: tenant.id,
                                            text: tenant.name
                                        }))
                                    };
                                },
                                cache: true
                            }
                        }).on('change', function() {
                            // Masukkan data ID yang dipilih dari Select2 ke variabel form Alpine
                            alpineInstance.form.tenant_id = $(this).val();
                        });

                        // ═══════════════════════════════════════════════════════════════════════
                        // BINDING DATA JIKA MODE EDIT
                        // ═══════════════════════════════════════════════════════════════════════
                        if (window.storeData) {
                            this.form = {
                                id: window.storeData.id,
                                tenant_id: window.storeData.tenant_id,
                                name: window.storeData.name,
                                branch_code: window.storeData.branch_code,
                                address: window.storeData.address,
                                timezone: window.storeData.timezone,
                                currency: window.storeData.currency
                            };

                            // Set opsi default di Select2 saat halaman edit dimuat pertama kali
                            if (window.storeData.tenant_id) {
                                const option = new Option(window.storeData.tenant_name, window.storeData.tenant_id, true, true);
                                $('#tenantSelect2').append(option).trigger('change');
                            }
                        }
                    },

                    validate() {
                        this.errors = {};

                        if (!this.form.tenant_id) this.errors.tenant_id = 'Pilihan tenant wajib diisi';
                        if (!this.form.name) this.errors.name = 'Nama toko wajib diisi';
                        if (!this.form.branch_code) this.errors.branch_code = 'Kode cabang wajib diisi';
                        if (!this.form.address) this.errors.address = 'Alamat wajib diisi';

                        return Object.keys(this.errors).length === 0;
                    },

                    async handleSubmit() {
                        if (!this.validate()) return;

                        this.isLoading = true;

                        try {
                            let response;

                            if (this.form.id) {
                                response = await axios.put(`/store/update/${this.form.id}`, this.form);
                            } else {
                                response = await axios.post(`/store/store`, this.form);
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            setTimeout(() => {
                                window.location.href = "{{ route('store') }}";
                            }, 1500);

                        } catch (error) {
                            this.isLoading = false;

                            if (error.response?.status === 422) {
                                this.errors = error.response.data.errors;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validasi Gagal',
                                    text: 'Periksa kembali input Anda'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Terjadi kesalahan saat memproses data'
                                });
                            }
                        }
                    }
                }
            }
        </script>
    </div>
@endsection
