<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <!-- Scripts -->
        @vite(['resources/scss/main.scss', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body>
        <x-banner />

        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="max-w-4xl mx-auto">
                {{ $slot }}
            </main>

            <x-ui.footer />
        </div>
        
        <x-toaster-hub/>
        
        @stack('modals')
        @livewireScripts
        
        <script>
        document.addEventListener('alpine:init', () => {
        // tu store global
        Alpine.store('ui', { navOpen:false, filtersOpen:false });

        // componente reutilizable para breakpoints
        Alpine.data('filtersShell', () => ({
            isDesktop: false,
            init() {
                const mql = window.matchMedia('(min-width: 1024px)');
                const set = e => {
                    this.isDesktop = e.matches;
                    if (this.isDesktop) $store.ui.filtersOpen = false; // cierra sheet al pasar a desktop
                };
                set(mql);
                (mql.addEventListener ? mql.addEventListener('change', set) : mql.addListener(set));
                }
            }));
        });
        </script>
    </body>
</html>
