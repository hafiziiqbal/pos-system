<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POS SYSTEM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="{{ asset('vendor/tailwind/browser@4.map.js') }}" defer></script>
    <style type="text/tailwindcss">
        @variant dark (&:where(.dark, .dark *));
    </style>
    <script src="{{ asset('vendor/axios/axios.min.js') }}"></script>
    <script src="{{ asset('vendor/alpine/cdn.min.js') }}" defer></script>
    <script src="{{ asset('vendor/sweetalert/sweetalert2@11.map.js') }}" defer></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8" x-data="loginForm()">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                    POS <span class="text-blue-600">SYSTEM</span>
                </h1>
                <p class="text-gray-500 mt-2">Silakan masuk ke akun Anda</p>
            </div>

            <!-- Form -->
            <form @submit.prevent="handleLogin">
                @csrf

                <!-- Email / Username -->
                <div class="mb-5">
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email </label>
                    <input type="text" x-model="form.email" name="email" id="email"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition duration-200"
                        placeholder="Masukkan email Anda" required>
                    <p x-show="errors.email" x-text="errors.email" class="text-red-500 text-sm mt-1"></p>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Kata Sandi</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" id="password"
                            x-model="form.password"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition duration-200"
                            placeholder="••••••••" required>
                        <p x-show="errors.password" x-text="errors.password" class="text-red-500 text-sm mt-1"></p>

                        <!-- Toggle Password Visibility -->
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 focus:outline-none">
                            <svg x-show="!showPassword" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
                    </div>
                </div>


                <!-- Submit Button -->
                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-300 flex items-center justify-center space-x-2"
                    :class="isLoading ? 'opacity-70 cursor-not-allowed' : ''" :disabled="isLoading">
                    <template x-if="!isLoading">
                        <span>Masuk</span>
                    </template>
                    <template x-if="isLoading">
                        <div class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <template x-if="isLoading && !isSuccess">
                                <span>Memproses...</span>
                            </template>

                            <template x-if="isSuccess">
                                <span>Mengarahkan...</span>
                            </template>
                        </div>
                    </template>
                </button>
            </form>

            <!-- Footer -->
            <p class="mt-8 text-center text-xs text-gray-400 uppercase tracking-widest">
                &copy; 2026 POS SYSTEM v1.0
            </p>
        </div>
    </div>

    <script>
        function loginForm() {
            return {
                showPassword: false,
                isLoading: false,
                isSuccess: false,
                form: {
                    email: '',
                    password: ''
                },
                errors: {},

                init() {
                    axios.defaults.headers.common['X-CSRF-TOKEN'] =
                        document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                },

                validate() {
                    this.errors = {};

                    if (!this.form.email) {
                        this.errors.email = 'Email / Username wajib diisi';
                    }

                    if (!this.form.password) {
                        this.errors.password = 'Password wajib diisi';
                    }

                    return Object.keys(this.errors).length === 0;
                },

                async handleLogin() {
                    if (!this.validate()) return;

                    this.isLoading = true;

                    try {
                        const response = await axios.post("{{ route('login') }}", this.form);
                        this.isSuccess = true;
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.data.message,
                            timer: 1500,
                            showConfirmButton: false
                        });

                        setTimeout(() => {
                            window.location.href = response.data.redirect;
                        }, 1500);

                    } catch (error) {
                        this.isSuccess = false;
                        if (error.response) {
                            if (error.response.status === 422) {
                                // VALIDATION ERROR LARAVEL
                                this.errors = error.response.data.errors;

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Validasi Gagal',
                                    text: 'Periksa kembali input Anda'
                                });

                            } else if (error.response.status === 401) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Login Gagal',
                                    text: error.response.data.message
                                });

                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Terjadi kesalahan server'
                                });
                            }
                        }
                        this.isLoading = false;
                    }


                }
            }
        }
    </script>
</body>

</html>
