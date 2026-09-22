@props(['title' => null])
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1D4B2E">
    <title>{{ $title ?? 'Admin' }} — KARSA</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-cream-50 text-forest-950 antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex min-h-full">
        {{-- Sidebar desktop --}}
        <aside class="hidden w-64 shrink-0 flex-col border-r border-leaf-100 bg-white lg:flex">
            @include('layouts.partials.admin-nav')
        </aside>

        {{-- Sidebar mobile (overlay) --}}
        <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-40 lg:hidden">
            <div class="absolute inset-0 bg-black/40" @click="sidebarOpen = false"></div>
            <aside class="relative flex h-full w-64 flex-col bg-white">
                @include('layouts.partials.admin-nav')
            </aside>
        </div>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center gap-3 border-b border-leaf-100 bg-white px-4 py-3 lg:px-8">
                <button type="button" class="rounded-lg p-2 text-ink-500 hover:bg-leaf-100 lg:hidden" @click="sidebarOpen = true" aria-label="Buka menu">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="h-6 w-6"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                </button>
                <h1 class="font-display text-lg font-bold text-forest-950">{{ $title ?? 'Dasbor' }}</h1>
                <div class="ml-auto flex items-center gap-3 text-sm text-ink-500">
                    <span>{{ auth()->user()?->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-1.5 font-medium text-forest-800 hover:bg-leaf-100">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
