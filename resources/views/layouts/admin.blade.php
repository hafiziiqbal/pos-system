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
    <script src="{{ asset('vendor/axios/axios.min.js') }}"></script>
    <script src="{{ asset('vendor/alpine/cdn.min.js') }}" defer></script>
    <script src="{{ asset('vendor/sweetalert/sweetalert2@11.map.js') }}" defer></script>
</head>

<body>

    <div x-data="{ showSidebar: window.innerWidth >= 768 }" class="relative flex w-full flex-col md:flex-row">
        <!-- This allows screen readers to skip the sidebar and go directly to the main content. -->
        <a class="sr-only" href="#main-content">skip to the main content</a>

        <!-- dark overlay for when the sidebar is open on smaller screens  -->
        <div x-cloak x-show="showSidebar" class="fixed inset-0 z-10 bg-neutral-950/10 backdrop-blur-xs md:hidden"
            aria-hidden="true" x-on:click="showSidebar = false" x-transition.opacity=""></div>

        <nav x-cloak
            class="fixed left-0 z-20 flex h-svh w-60 shrink-0 flex-col border-r border-neutral-300 bg-neutral-50 p-4 transition-transform duration-300 md:w-64 dark:border-neutral-700 dark:bg-neutral-900"
            x-bind:class="showSidebar ? 'translate-x-0' : '-translate-x-full'" aria-label="sidebar navigation">
            <!-- logo  -->
            <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
                POS <span class="text-blue-600">SYSTEM</span>
            </h1>

            <!-- search  -->
            <div class="relative my-4 flex w-full max-w-xs flex-col gap-1 text-neutral-600 dark:text-neutral-300">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="currentColor" fill="none"
                    stroke-width="2"
                    class="absolute left-2 top-1/2 size-5 -translate-y-1/2 text-neutral-600/50 dark:text-neutral-300/50"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="search"
                    class="w-full border border-neutral-300 rounded-sm bg-white px-2 py-1.5 pl-9 text-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black disabled:cursor-not-allowed disabled:opacity-75 dark:border-neutral-700 dark:bg-neutral-950/50 dark:focus-visible:outline-white"
                    name="search" aria-label="Search" placeholder="Search" />
            </div>

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
                                    'store'),
                                'text-slate-500 hover:text-slate-900 hover:bg-slate-50' => !request()->routeIs(
                                    'store'),
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


        <!-- toggle button for small screen  -->
        {{-- <button
            class="fixed right-4 top-4 z-20 rounded-full bg-black p-4 md:hidden text-neutral-100 dark:bg-white dark:text-black"
            x-on:click="showSidebar = ! showSidebar">
            <svg x-show="showSidebar" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                class="size-5" aria-hidden="true">
                <path
                    d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" />
            </svg>
            <svg x-show="! showSidebar" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                class="size-5" aria-hidden="true">
                <path
                    d="M0 3a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5-1v12h9a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1zM4 2H2a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h2z" />
            </svg>
            <span class="sr-only">sidebar toggle</span>
        </button> --}}
    </div>

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
