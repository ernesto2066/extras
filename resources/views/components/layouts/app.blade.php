<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name', 'Positivo') }}</title>
        <link rel="icon" href="{{ asset('images/positivos-logo.png') }}?v=2" type="image/png">
        <link rel="shortcut icon" href="{{ asset('images/positivos-logo.png') }}?v=2" type="image/png">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Livewire Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        <!-- Livewire Scripts -->
        @livewireScripts
    </body>
</html>
