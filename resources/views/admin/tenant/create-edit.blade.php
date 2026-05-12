@extends('layouts.admin') {{-- sesuaikan dengan nama file layout kamu --}}
@section('content')
    <div class="w-full" x-data="tenantForm()">
        <div class="bg-white rounded-xl p-5 space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-800" x-text="form.id ? 'Edit Tenant' : 'Tambah Tenant Baru'"></h2>
                <a href="{{ route('tenant') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Kembali</a>
            </div>

            <form @submit.prevent="handleSubmint" class="space-y-4">
                <div class="space-y-5 p-1">
                    {{-- Input Nama --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Tenant</label>
                        <div class="relative">
                            <input type="text" x-model="form.name"
                                class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                :class="errors.name ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'"
                                placeholder="Contoh: PT Maju Bersama">
                        </div>
                        <template x-if="errors.name">
                            <p class="mt-1.5 text-xs font-medium text-red-500 flex items-center" x-text="errors.name"></p>
                        </template>
                    </div>

                    {{-- Input Subdomain --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Subdomain</label>
                        <div class="relative">
                            <input type="text" x-model="form.subdomain"
                                class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                                :class="errors.subdomain ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'"
                                placeholder="Contoh: pt-maju-bersama">
                        </div>
                        <template x-if="errors.subdomain">
                            <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.subdomain"></p>
                        </template>
                    </div>

                    {{-- Input Plan --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Pilih Plan</label>
                        <select x-model="form.plan"
                            class="block w-full px-4 py-2.5 text-slate-700 bg-white border rounded-xl transition-all duration-200 outline-none focus:ring-4 focus:ring-blue-100 sm:text-sm"
                            :class="errors.plan ? 'border-red-400' : 'border-slate-200 focus:border-blue-500'">
                            <option value="">-- Pilih Paket --</option>
                            <option value="free">Free</option>
                            <option value="basic">Basic</option>
                            <option value="pro">Professional</option>
                        </select>
                        <template x-if="errors.plan">
                            <p class="mt-1.5 text-xs font-medium text-red-500" x-text="errors.plan"></p>
                        </template>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-4">
                        <button type="submit" :disabled="isLoading"
                            class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-md shadow-blue-200 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 transition-all duration-200 transform active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed">

                            <span x-show="!isLoading" x-text="form.id ? 'Simpan Perubahan' : 'Daftarkan Tenant'"></span>

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

        <script>
            window.tenantData = @json($tenant);

            function tenantForm() {
                return {
                    isLoading: false,
                    isSuccess: false,
                    form: {
                        id: '',
                        name: '',
                        subdomain: '',
                        plan: ''
                    },
                    errors: {},

                    init() {
                        axios.defaults.headers.common['X-CSRF-TOKEN'] =
                            document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        // 🔥 isi data kalau edit
                        if (window.tenantData) {
                            this.form = {
                                id: window.tenantData.id,
                                name: window.tenantData.name,
                                subdomain: window.tenantData.subdomain,
                                plan: window.tenantData.plan
                            };
                        }
                    },

                    validate() {
                        this.errors = {};

                        if (!this.form.name) {
                            this.errors.name = 'Nama wajib diisi';
                        }

                        // if (!this.form.subdomain) {
                        //     this.errors.subdomain = 'Subdomain wajib diisi';
                        // }

                        // if (!this.form.plan) {
                        //     this.errors.plan = 'Plan wajib diisi';
                        // }

                        return Object.keys(this.errors).length === 0;
                    },

                    async handleSubmint() {
                        if (!this.validate()) return;

                        this.isLoading = true;

                        try {
                            let response;

                            // 🔥 cek create / update
                            if (this.form.id) {
                                response = await axios.put(`/tenant/update/${this.form.id}`, this.form);
                            } else {
                                response = await axios.post(`/tenant/store`, this.form);
                            }

                            this.isSuccess = true;

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.data.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            setTimeout(() => {
                                window.location.href = "{{ route('tenant') }}";
                            }, 1500);

                        } catch (error) {
                            this.isSuccess = false;
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
                                    text: 'Terjadi kesalahan'
                                });
                            }
                        }
                    }
                }
            }
        </script>
    </div>
@endsection
