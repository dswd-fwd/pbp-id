<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Page Title' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen flex flex-col bg-gray-50">
        <div class="absolute inset-0 -z-10 h-full w-full bg-transparent bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:6rem_4rem]"></div>
        <livewire:navigation />
        <section class="flex-1 flex">
            {{ $slot }}
        </section>
        <footer class="h-16 bg-white w-full flex items-center justify-center border-t border-neutral-300">
            <p class="text-center font-bold text-neutral-800">
                Made by DSWD FWD ❤️
            </p>
        </footer>
        @livewireScripts
    </body>
</html>
