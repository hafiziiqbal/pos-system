@extends('layouts.admin')

@section('content')
    <style>
        /* 1. Mengatur Container Utama Select2 Single */
        .select2-container--default .select2-selection--single {
            display: block !important;
            width: 100% !important;
            height: auto !important;
            padding: 0.625rem 1rem !important;
            font-size: 0.875rem !important;
            line-height: 1.25rem !important;
            color: #334155 !important;
            background-color: #ffffff !important;
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.75rem !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            outline: none !important;
        }

        /* 2. Mengatur Tampilan Text di Dalam Select2 Single */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155 !important;
            padding-left: 0 !important;
            padding-right: 2rem !important;
            line-height: 1.25rem !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #94a3b8 !important;
        }

        /* 3. Menghilangkan & Merapikan Ikon Panah Bawaan Select2 Single */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            top: 0 !important;
            right: 0.75rem !important;
            width: 1.25rem !important;
        }

        /* 4. Efek Focus State Ring */
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 4px #dbeafe !important;
        }

        /* 5. Handle Tampilan Error Jika Validasi Gagal */
        .select2-error-state .select2-selection--single,
        .select2-error-state .select2-selection--multiple {
            border-color: #f87171 !important;
        }

        /* 6. Merapikan Kotak Dropdown Pencarian Yang Muncul Di Bawahnya */
        .select2-dropdown {
            border-color: #e2e8f0 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem !important;
            outline: none !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border-color: #3b82f6 !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #3b82f6 !important;
        }
    </style>

    <div class="w-full" x-data="userForm()">
        <div class="bg-white rounded-xl p-5 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800" x-text="form.id ? 'Edit User' : 'Tambah User Baru'"></h2>
                <a href="{{ route('user') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-5 p-1">

                    {{-- Grid 2 Kolom untuk Relasi (Tenant & Store) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Input Pilih Tenant (Select2 AJAX) --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Tenant (Opsional)</label>
                            <div class="relative" :class="errors.tenant_id ? 'select2-error-state' : ''">
                                <select id="tenantSelect2" class="w-full">
                                    <option value="">-- Cari & Pilih Tenant --</option>
                                </select>
                            </div>
                            <template x-if="errors.tenant_id">
                                <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.tenant_id"></p>
                            </template>
                        </div>

                        {{-- Input Pilih Store (Select2 AJAX) --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Toko (Opsional)</label>
                            <div class="relative" :class="errors.store_id ? 'select2-error-state' : ''">
                                <select id="storeSelect2" class="w-full">
                                    <option value="">-- Cari & Pilih Toko --</option>
                                </select>
                            </div>
                            <template x-if="errors.store_id">
                                <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.store_id"></p>
                            </template>
                        </div>
                    </div>

                    {{-- Input Nama --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
                        <div class="relative">
                            <input type="text" x-model="form.name"
                                class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                :class="errors.name ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'"
                                placeholder="Masukkan nama lengkap">
                        </div>
                        <template x-if="errors.name">
                            <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.name"></p>
                        </template>
                    </div>

                    {{-- Input Email & Role --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Alamat Email</label>
                            <div class="relative">
                                <input type="email" x-model="form.email"
                                    class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                    :class="errors.email ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'"
                                    placeholder="email@example.com">
                            </div>
                            <template x-if="errors.email">
                                <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.email"></p>
                            </template>
                        </div>

                        {{-- Role dan Permissions (Spatie Multiple Select) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Role (Single Select - Diubah agar pilih 1 saja) --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Role Pengguna</label>
                                <div class="relative" :class="errors.roles ? 'select2-error-state' : ''">
                                    <select id="rolesSelect2" class="w-full">
                                        <option value=""></option>
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <template x-if="errors.roles">
                                    <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.roles"></p>
                                </template>
                            </div>

                            {{-- Permission Tambahan (Tetap bisa pilih banyak) --}}
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Permission Ekstra
                                    (Opsional)</label>
                                <div class="relative" :class="errors.permissions ? 'select2-error-state' : ''">
                                    <select id="permissionsSelect2" multiple="multiple" class="w-full">
                                        @foreach ($permissions as $permission)
                                            <option value="{{ $permission->name }}">{{ $permission->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <template x-if="errors.permissions">
                                    <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.permissions"></p>
                                </template>
                                <p class="text-xs text-slate-400 mt-1">*Hanya jika user butuh hak akses di luar role
                                    bawaannya.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Input Password --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Password
                            <span x-show="form.id" class="text-xs text-gray-400 font-normal ml-1">(Kosongkan jika tidak
                                ingin mengubah password)</span>
                        </label>
                        <div class="relative">
                            <input type="password" x-model="form.password"
                                class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                :class="errors.password ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'"
                                placeholder="Masukkan password (minimal 8 karakter)">
                        </div>
                        <template x-if="errors.password">
                            <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.password"></p>
                        </template>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-4">
                        <button type="submit" :disabled="isLoading"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md shadow-blue-200 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all duration-200 transform active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">

                            <span x-show="!isLoading" x-text="form.id ? 'Simpan Perubahan' : 'Daftarkan User'"></span>

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

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            window.userData = @json($user ?? null);
            window.userRoles = @json($userRoles ?? []);
            window.userPermissions = @json($userPermissions ?? []);

            function userForm() {
                return {
                    isLoading: false,
                    form: {
                        id: '',
                        tenant_id: '',
                        store_id: '',
                        name: '',
                        email: '',
                        password: '',
                        roles: [], // Tetap dikirim sebagai array agar backend store/update tidak patah
                        permissions: []
                    },
                    errors: {},

                    init() {
                        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content');
                        const alpineInstance = this;

                        // 1. SELECT2 ROLE (Single Selection)
                        $('#rolesSelect2').select2({
                            placeholder: '-- Pilih Role --',
                            allowClear: true,
                            width: '100%'
                        }).on('change', function() {
                            const selectedValue = $(this).val();
                            // Membungkus nilai ke dalam array [val] agar lolos dari validasi 'roles' => 'nullable|array' di backend
                            alpineInstance.form.roles = selectedValue ? [selectedValue] : [];
                        });

                        // 2. SELECT2 PERMISSIONS (Multiple Selection)
                        $('#permissionsSelect2').select2({
                            placeholder: '-- Pilih Permission Tambahan --',
                            allowClear: true,
                            width: '100%'
                        }).on('change', function() {
                            alpineInstance.form.permissions = $(this).val() || [];
                        });

                        // 3. SELECT2 TENANT (AJAX Async)
                        $('#tenantSelect2').select2({
                            placeholder: '-- Cari & Pilih Tenant --',
                            allowClear: true,
                            width: '100%',
                            ajax: {
                                url: '/tenants/search',
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        q: params.term
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
                            alpineInstance.form.tenant_id = $(this).val() || '';
                            // Setiap tenant berubah, paksa bersihkan input Store pendukungnya
                            $('#storeSelect2').val(null).trigger('change');
                        });

                        // 4. SELECT2 STORE (AJAX Async)
                        $('#storeSelect2').select2({
                            placeholder: '-- Cari & Pilih Toko --',
                            allowClear: true,
                            width: '100%',
                            ajax: {
                                url: '/store/search',
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        q: params.term,
                                        tenant_id: alpineInstance.form.tenant_id
                                    };
                                },
                                processResults: function(data) {
                                    return {
                                        results: data.map(store => ({
                                            id: store.id,
                                            text: `${store.name} (${store.branch_code})`
                                        }))
                                    };
                                },
                                cache: true
                            }
                        }).on('change', function() {
                            alpineInstance.form.store_id = $(this).val() || '';
                        });

                        // 5. DATA BINDING JIKA MODE EDIT
                        if (window.userData) {
                            this.form = {
                                id: window.userData.id,
                                tenant_id: window.userData.tenant_id || '',
                                store_id: window.userData.store_id || '',
                                name: window.userData.name,
                                email: window.userData.email,
                                password: '',
                                roles: window.userRoles || [],
                                permissions: window.userPermissions || []
                            };

                            // Set nilai awal di Select2 Roles (ambil data index ke-0 karena bentuknya single selection)
                            if (this.form.roles.length > 0) {
                                $('#rolesSelect2').val(this.form.roles[0]).trigger('change');
                            }

                            // Set nilai awal di Select2 Multiple Permissions
                            $('#permissionsSelect2').val(this.form.permissions).trigger('change');

                            // Set default Tenant Option jika ada
                            if (window.userData.tenant_id && window.userData.tenant_name) {
                                const tenantOption = new Option(window.userData.tenant_name, window.userData.tenant_id, true,
                                    true);
                                $('#tenantSelect2').append(tenantOption).trigger('change.select2');
                                this.form.tenant_id = window.userData.tenant_id;
                            }

                            // Set default Store Option jika ada
                            if (window.userData.store_id && window.userData.store_name) {
                                const storeOption = new Option(window.userData.store_name, window.userData.store_id, true,
                                    true);
                                $('#storeSelect2').append(storeOption).trigger('change.select2');
                                this.form.store_id = window.userData.store_id;
                            }
                        }
                    },

                    validate() {
                        this.errors = {};

                        if (!this.form.tenant_id) this.errors.tenant_id = 'Tenant wajib dipilih';
                        if (!this.form.name) this.errors.name = 'Nama lengkap wajib diisi';

                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!this.form.email) {
                            this.errors.email = 'Email wajib diisi';
                        } else if (!emailRegex.test(this.form.email)) {
                            this.errors.email = 'Format email tidak valid';
                        }

                        // Mengubah validasi pengecekan mengikuti array roles hasil single-select
                        if (!this.form.roles || this.form.roles.length === 0) {
                            this.errors.roles = 'Role pengguna wajib dipilih';
                        }

                        // Validasi Password aturan Create vs Edit
                        if (!this.form.id && !this.form.password) {
                            this.errors.password = 'Password wajib diisi untuk user baru';
                        } else if (this.form.password && this.form.password.length < 8) {
                            this.errors.password = 'Password minimal 8 karakter';
                        }

                        return Object.keys(this.errors).length === 0;
                    },

                    async handleSubmit() {
                        if (!this.validate()) return;
                        this.isLoading = true;

                        try {
                            let response;
                            if (this.form.id) {
                                response = await axios.put(`/user/update/${this.form.id}`, this.form);
                            } else {
                                response = await axios.post(`/user/store`, this.form);
                            }

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            setTimeout(() => {
                                window.location.href = "{{ route('user') }}";
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
