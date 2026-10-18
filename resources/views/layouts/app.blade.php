<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        {{-- Fon & ikon dibungkus tempatan melalui resources/css/app.css (bukan CDN) --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            ::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }
            ::-webkit-scrollbar-track {
                background: #f1f5f9;
            }
            ::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #94a3b8;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div x-data="{
            sidebarOpen: false,
            init() {
                if (window.innerWidth >= 1024) {
                    this.sidebarOpen = true;
                }
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 1024) {
                        this.sidebarOpen = true;
                    }
                });
            }
        }" x-init="init()" class="flex h-dvh overflow-hidden">
            <!-- Mobile Sidebar Overlay -->
            <div x-cloak x-show="sidebarOpen && window.innerWidth < 1024" @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/50 transition-opacity" x-transition></div>

            <!-- Sidebar -->
            <div x-cloak x-show="sidebarOpen" class="fixed left-0 top-0 z-40 w-64 h-dvh lg:static lg:z-auto lg:relative print:hidden">
                @include('layouts.navigation')
            </div>

            <div class="flex min-h-0 min-w-0 flex-1 flex-col">
                <header class="sticky z-20 border-b border-gray-200 bg-white print:hidden">
                    <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8" style="height:63px;">
                        <!-- Left: Hamburger -->
                        <div class="flex items-center">
                            <button @click="sidebarOpen = !sidebarOpen" class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition lg:hidden">
                                <i class="fas fa-bars text-base"></i>
                            </button>
                        </div>

                        <!-- Right: Notifications -->
                        <div class="flex items-center gap-2 sm:gap-3" x-data="{ open: false }">
                            <div class="relative">
                                <button @click="open = !open" @click.outside="open = false" class="relative inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-600 hover:bg-gray-100 transition leading-none">
                                    <i class="fas fa-bell text-base"></i>
                                    @if (($notifikasiBelumDibaca ?? 0) > 0)
                                        <span class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold">
                                            {{ $notifikasiBelumDibaca > 9 ? '9+' : $notifikasiBelumDibaca }}
                                        </span>
                                    @endif
                                </button>

                                <div x-cloak x-show="open" x-transition class="absolute right-0 mt-2 w-80 max-h-96 overflow-y-auto bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                        <span class="font-semibold text-sm text-gray-700">Notifikasi</span>
                                        @if (($notifikasiBelumDibaca ?? 0) > 0)
                                            <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
                                                @csrf
                                                <button type="submit" class="text-xs text-green-600 hover:underline">Tanda semua dibaca</button>
                                            </form>
                                        @endif
                                    </div>

                                    @forelse (($senaraiNotifikasi ?? []) as $n)
                                        <form method="POST" action="{{ route('notifikasi.baca', $n) }}">
                                            @csrf
                                            <button type="submit" class="w-full text-left px-4 py-3 border-b border-gray-50 hover:bg-gray-50 {{ $n->dibaca_pada ? '' : 'bg-green-50' }}">
                                                <p class="text-sm font-medium text-gray-800">{{ $n->judul }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $n->mesej }}</p>
                                                <p class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                                            </button>
                                        </form>
                                    @empty
                                        <p class="px-4 py-6 text-center text-sm text-gray-400">Tiada notifikasi.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="print:hidden">
                    <x-breadcrumb />
                </div>

                @isset($header)
                    <div class="border-b border-gray-200 bg-white px-4 py-6 sm:px-6 lg:px-4 print:hidden">
                        <div class="mx-auto sm:px-6 lg:px-4">
                            {{ $header }}
                        </div>
                    </div>
                @endisset

                <main class="flex-1 overflow-auto {{ $latarPutih ? 'bg-white' : 'bg-gray-50' }}">
                    <div class="px-4 py-8 sm:px-6 lg:px-4">
                        <div class="mx-auto sm:px-6 lg:px-4">
                            {{ $slot }}
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>
