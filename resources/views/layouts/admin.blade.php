<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>POS SYSTEM</title>

    <script src="{{ asset('vendor/tailwind/browser@4.map.js') }}" defer></script>
    <style type="text/tailwindcss">
        @variant dark (&:where(.dark, .dark *));
    </style>
    <style>
        /* Sembunyikan elemen x-cloak bawaan Alpine */
        [x-cloak] {
            display: none !important;
        }

        /* Style Loading Screen HTML Murni */
        #pure-preloader {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            /* Default Light Mode */
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        /* Support Dark Mode Manual sebelum Tailwind Aktif */
        html.dark #pure-preloader {
            background-color: #0a0a0a;
        }

        /* Animasi Spinner Murni */
        .pure-spinner {
            width: 48px;
            height: 48px;
            border: 4px solid #e5e5e5;
            border-top-color: #2563eb;
            /* Warna Biru */
            border-radius: 50%;
            animation: pure-spin 1s linear infinite;
        }

        @keyframes pure-spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Class untuk menghilangkan preloader secara halus */
        .preloader-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
    </style>
    <script src="{{ asset('vendor/axios/axios.min.js') }}"></script>
    <script src="{{ asset('vendor/alpine/cdn.min.js') }}" defer></script>
    <script src="{{ asset('vendor/sweetalert/sweetalert2@11.map.js') }}" defer></script>
    <script src="{{ asset('vendor/jquery/3.7.1/jquery.min.js') }}"></script>
</head>

<body>

    <!-- LOADING SCREEN (Murni HTML & CSS, Instan Muncul) -->
    <div id="pure-preloader">
        <div class="pure-spinner"></div>
        <p style="margin-top: 16px; font-family: sans-serif; font-size: 14px; color: #737373; font-weight: 500;">
            Memuat Sistem...
        </p>
    </div>

    <div x-cloak x-data="{ showSidebar: window.innerWidth >= 768 }" class="relative flex w-full flex-col md:flex-row">
        <!-- This allows screen readers to skip the sidebar and go directly to the main content. -->
        <a class="sr-only" href="#main-content">skip to the main content</a>

        <!-- dark overlay for when the sidebar is open on smaller screens  -->
        <div x-cloak x-show="showSidebar" class="fixed inset-0 z-10 bg-neutral-950/10 backdrop-blur-xs md:hidden"
            aria-hidden="true" x-on:click="showSidebar = false" x-transition.opacity=""></div>

        <nav x-cloak
            class="fixed left-0 z-20 flex h-svh w-60 shrink-0 flex-col border-r border-neutral-300 bg-neutral-50 p-4 transition-transform duration-300 md:w-64 dark:border-neutral-700 dark:bg-neutral-900"
            x-bind:class="showSidebar ? 'translate-x-0' : '-translate-x-full'" aria-label="sidebar navigation">
            <!-- logo  -->
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight mb-4">
                POS <span class="text-blue-600">SYSTEM</span>
            </h1>


            <!-- sidebar links  -->
            <div class="flex flex-col gap-2 overflow-y-auto pb-6">

                {{-- DASHBAORD --}}
                <a href="{{ route('dashboard') }}" @class([
                    'flex items-center gap-2 px-3 py-2 text-sm rounded-md font-semibold transition-all duration-200',
                    // State ACTIVE: Lebih kontras dan bersih
                    'bg-slate-100 text-slate-900 shadow-sm' => request()->routeIs('dashboard'),
                    // State INACTIVE: Minimalis
                    'text-slate-500 hover:bg-slate-50 hover:text-slate-900' => !request()->routeIs(
                        'dashboard'),
                ])>
                    <!-- Icon (Opsional tapi menambah kesan elegan) -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                {{-- <a href="#"
                    class="flex items-center gap-2 px-2 py-1.5 text-sm rounded-sm font-medium text-neutral-600 underline-offset-2 hover:bg-black/5 hover:text-neutral-900 focus-visible:underline focus:outline-hidden dark:text-neutral-300 dark:hover:bg-white/5 dark:hover:text-white">
                    <span>Support Us</span>
                </a> --}}

                {{-- USER & AKSES --}}
                <div x-data="{ open: {{ request()->routeIs('user*', 'access*') ? 'true' : 'false' }} }" class="space-y-1">

                    <!-- Parent Menu -->
                    <button @click="open = !open"
                        :class="open ? 'text-slate-900 bg-slate-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                        class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-md font-semibold transition-all duration-200 group">

                        <div class="flex items-center gap-2">
                            <!-- Icon Utama -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor"
                                class="w-4 h-4 text-slate-400 group-hover:text-slate-600">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>

                            <span>Pengguna & Akses</span>
                        </div>

                        <!-- Arrow Icon dengan Animasi Rotasi -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform duration-300"
                            :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Child Menu (Submenu) -->
                    <ul x-show="open" x-cloak x-collapse class="pl-7 space-y-1 border-l-2 border-slate-100 ml-5 mt-1">

                        <!-- Menu Tenant -->
                        <li>
                            <a href="{{ route('user') }}" @class([
                                'block px-3 py-1.5 text-sm rounded-md transition-colors',
                                'font-bold text-slate-900 bg-slate-100 shadow-sm' => request()->routeIs(
                                    'user*'),
                                'text-slate-500 hover:text-slate-900 hover:bg-slate-50' => !request()->routeIs(
                                    'user*'),
                            ])>
                                Pengguna
                            </a>
                        </li>

                        <!-- Menu Toko -->
                        <li>
                            <a href="{{ route('store') }}" @class([
                                'block px-3 py-1.5 text-sm rounded-md transition-colors',
                                'font-bold text-slate-900 bg-slate-100 shadow-sm' => request()->routeIs(
                                    'store*'),
                                'text-slate-500 hover:text-slate-900 hover:bg-slate-50' => !request()->routeIs(
                                    'store*'),
                            ])>
                                Akses
                            </a>
                        </li>
                    </ul>
                </div>

                {{-- TENANT & TOKO --}}
                <div x-data="{ open: {{ request()->routeIs('tenant*', 'store*') ? 'true' : 'false' }} }" class="space-y-1">

                    <!-- Parent Menu -->
                    <button @click="open = !open"
                        :class="open ? 'text-slate-900 bg-slate-50' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                        class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm rounded-md font-semibold transition-all duration-200 group">

                        <div class="flex items-center gap-2">
                            <!-- Icon Utama -->
                            <svg xmlns="http://www.w3.org/2000/svg"
                                class="w-4 h-4 text-slate-400 group-hover:text-slate-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span>Tenant & Toko</span>
                        </div>

                        <!-- Arrow Icon dengan Animasi Rotasi -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform duration-300"
                            :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Child Menu (Submenu) -->
                    <ul x-show="open" x-cloak x-collapse class="pl-7 space-y-1 border-l-2 border-slate-100 ml-5 mt-1">

                        <!-- Menu Tenant -->
                        <li>
                            <a href="{{ route('tenant') }}" @class([
                                'block px-3 py-1.5 text-sm rounded-md transition-colors',
                                'font-bold text-slate-900 bg-slate-100 shadow-sm' => request()->routeIs(
                                    'tenant*'),
                                'text-slate-500 hover:text-slate-900 hover:bg-slate-50' => !request()->routeIs(
                                    'tenant*'),
                            ])>
                                Tenant
                            </a>
                        </li>

                        <!-- Menu Toko -->
                        <li>
                            <a href="{{ route('store') }}" @class([
                                'block px-3 py-1.5 text-sm rounded-md transition-colors',
                                'font-bold text-slate-900 bg-slate-100 shadow-sm' => request()->routeIs(
                                    'store*'),
                                'text-slate-500 hover:text-slate-900 hover:bg-slate-50' => !request()->routeIs(
                                    'store*'),
                            ])>
                                Toko
                            </a>
                        </li>
                    </ul>
                </div>

            </div>
        </nav>

        <!-- main content  -->
        <div id="main-content"
            class="relative h-svh w-full overflow-y-auto p-4 pt-[5rem] bg-white dark:bg-neutral-950 transition-all duration-300"
            :class="showSidebar ? 'md:ml-64' : 'md:ml-0'">
            @yield('content')
        </div>

        <nav class="fixed top-0 right-0 z-10 flex items-center justify-between bg-neutral-50 border-b border-neutral-300 px-6 py-4 dark:border-neutral-700 dark:bg-neutral-900 transition-all duration-300"
            :class="showSidebar ? 'left-60 md:left-64' : 'left-0'" aria-label="penguin ui menu">

            <!-- Tombol Toggle -->
            <button x-on:click="showSidebar = !showSidebar"
                class="text-2xl font-bold text-neutral-900 dark:text-white focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
            <!-- Desktop Menu -->
            <ul class="items-center gap-4 flex">
                {{-- <li><a href="#"
                        class="font-bold text-black underline-offset-2 hover:text-black focus:outline-hidden focus:underline dark:text-white dark:hover:text-white"
                        aria-current="page">Products</a></li> --}}

                <li x-data>
                    <button @click="logout"
                        class="w-full rounded-sm bg-black border border-black px-4 py-2 text-center text-sm font-medium tracking-wide text-neutral-100 hover:opacity-75">
                        Logout
                    </button>
                </li>
            </ul>

        </nav>

    </div>

    <script>
        // Menggunakan DOMContentLoaded agar preloader hilang begitu HTML & Alpine selesai inisialisasi awal
        window.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('pure-preloader');
            if (preloader) {
                // Tambahkan class hidden untuk memicu efek fade-out CSS
                preloader.classList.add('preloader-hidden');

                // Hapus elemen dari DOM setelah animasi selesai agar tidak memberatkan memori
                setTimeout(() => {
                    preloader.remove();
                }, 400);
            }
        });
    </script>

    <script>
        function logout() {
            Swal.fire({
                title: 'Yakin logout?',
                text: 'Sesi kamu akan berakhir',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post("{{ route('logout') }}")
                        .then(() => {
                            window.location.href = "{{ route('login') }}";
                        })
                        .catch(() => {
                            Swal.fire('Error', 'Gagal logout', 'error');
                        });
                }
            });
        }
    </script>
</body>

</html>
