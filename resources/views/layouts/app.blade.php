<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'CMMS') }} — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>

<body class="h-full bg-gray-50 font-sans antialiased" x-data="{ sidebarOpen: true, mobileOpen: false }">
    <div class="flex h-full">

        {{-- Desktop Sidebar --}}
        <aside :class="sidebarOpen ? 'w-64' : 'w-16'"
            class="hidden lg:flex flex-col bg-gray-900 text-white transition-all duration-300 ease-in-out flex-shrink-0 h-screen sticky top-0">
            <div class="flex items-center h-16 px-4 border-b border-gray-700 flex-shrink-0">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path
                                d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="font-bold text-lg tracking-tight whitespace-nowrap">CMMS</span>
                </div>
                <button x-show="sidebarOpen" @click="sidebarOpen=false"
                    class="ml-auto text-gray-400 hover:text-white p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </button>
                <button x-show="!sidebarOpen" @click="sidebarOpen=true"
                    class="ml-auto text-gray-400 hover:text-white p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 space-y-0.5 px-2">
                @php
                    $user = auth()->user();
                    $nav = [
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z M9 22V12h6v10', 'match' => 'dashboard', 'roles' => null],
                        ['route' => 'assets.index', 'label' => 'Assets', 'icon' => 'M20 7H4a2 2 0 0 0-2 2v6c0 1.1.9 2 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zm-9 5H7', 'match' => 'assets*', 'roles' => null],
                        ['route' => 'spare-parts.index', 'label' => 'Spare Parts', 'icon' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 6v6l4 2', 'match' => 'spare-parts*', 'roles' => null],
                        ['route' => 'maintenance-schedules.index', 'label' => 'Maint. Schedule', 'icon' => 'M8 2v4 M16 2v4 M3 10h18 M3 6h18v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6z', 'match' => 'maintenance-schedules*', 'roles' => null],
                        ['route' => 'schedule-report.index', 'label' => 'Schedule Report', 'icon' => 'M12 20h9 M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z', 'match' => 'schedule-report*', 'roles' => null],
                        ['route' => 'work-orders.index', 'label' => 'Work Orders', 'icon' => 'M9 11l3 3L22 4 M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11', 'match' => 'work-orders*', 'roles' => ['admin', 'supervisor', 'pm']],
                        ['route' => 'maintenance-records.index', 'label' => 'WO Records', 'icon' => 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M16 13H8 M16 17H8', 'match' => 'maintenance-records*', 'roles' => null],
                        ['route' => 'checksheet.index', 'label' => 'Checksheet', 'icon' => 'M9 12l2 2 4-4M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z', 'match' => 'checksheet.index', 'roles' => null],
                        ['route' => 'checksheet.templates.index', 'label' => 'Checksheet Template', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'match' => 'checksheet.templates*', 'roles' => ['admin', 'supervisor', 'pm']],
                        ['route' => 'timeline.index', 'label' => 'Timeline', 'icon' => 'M12 2v20 M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6', 'match' => 'timeline*', 'roles' => null],
                        ['route' => 'kpi.index', 'label' => 'KPI Dashboard', 'icon' => 'M18 20V10 M12 20V4 M6 20v-6', 'match' => 'kpi*', 'roles' => null],
                    ];
                @endphp
                @foreach($nav as $item)
                    @if(!$item['roles'] || in_array($user->role, $item['roles']))
                        <a href="{{ route($item['route']) }}"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors group
                                              {{ request()->routeIs($item['match']) ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="{{ $item['icon'] }}" />
                            </svg>
                            <span x-show="sidebarOpen" class="truncate">{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
                @if($user->role === 'technician')
                    <a href="{{ route('work-orders.my-jobs') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('work-orders.my-jobs') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <path d="M3 9h18M9 21V9" />
                        </svg>
                        <span x-show="sidebarOpen" class="truncate">My Jobs</span>
                    </a>
                @endif
                @if($user->role === 'admin')
                    <a href="{{ route('settings.index') }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
                                  {{ request()->routeIs('settings*') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path
                                d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span x-show="sidebarOpen" class="truncate">Settings</span>
                    </a>
                @endif
            </nav>

            <div class="border-t border-gray-700 p-3 flex-shrink-0">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div
                        class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-sm font-semibold uppercase flex-shrink-0">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div x-show="sidebarOpen" class="min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Mobile overlay + sidebar --}}
        <div x-show="mobileOpen" @click="mobileOpen=false" class="fixed inset-0 bg-black/50 z-40 lg:hidden"
            x-transition:enter="transition-opacity duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display:none"></div>
        <aside x-show="mobileOpen"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 text-white lg:hidden flex flex-col"
            x-transition:enter="transition duration-200" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition duration-200"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" style="display:none">
            <div class="flex items-center h-16 px-4 border-b border-gray-700">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center mr-3 flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path
                            d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                    </svg>
                </div>
                <span class="font-bold text-lg">CMMS</span>
                <button @click="mobileOpen=false" class="ml-auto text-gray-400 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" x2="6" y1="6" y2="18" />
                        <line x1="6" x2="18" y1="6" y2="18" />
                    </svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 space-y-0.5 px-2">
                @foreach($nav as $item)
                    @if(!$item['roles'] || in_array($user->role, $item['roles']))
                        <a href="{{ route($item['route']) }}" @click="mobileOpen=false"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-400 hover:bg-gray-800 hover:text-white transition-colors">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="{{ $item['icon'] }}" />
                            </svg>
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </aside>

        {{-- Main --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            {{-- Top Navbar --}}
            <header
                class="bg-white border-b border-gray-200 h-16 flex items-center px-4 lg:px-6 flex-shrink-0 sticky top-0 z-30">
                <button @click="mobileOpen=true" class="lg:hidden text-gray-500 hover:text-gray-700 mr-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="4" x2="20" y1="6" y2="6" />
                        <line x1="4" x2="20" y1="12" y2="12" />
                        <line x1="4" x2="20" y1="18" y2="18" />
                    </svg>
                </button>
                <div class="flex-1 flex items-center">
                    @yield('breadcrumb')
                </div>
                <div class="flex items-center gap-2">
                    {{-- Notifications --}}
                    <div class="relative" x-data="notifBell()" x-init="load()">
                        <button @click="open=!open; if(open)load()"
                            class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                                <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                            </svg>
                            <span x-show="count>0" x-text="count>9?'9+':count"
                                class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-xs rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1 font-medium leading-none"></span>
                        </button>
                        <div x-show="open" @click.outside="open=false" x-transition
                            class="absolute right-0 top-full mt-1 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden"
                            style="display:none">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <span class="font-semibold text-sm text-gray-900">Notifications</span>
                                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-600 hover:underline">Mark all
                                        read</button>
                                </form>
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                                <template x-if="items.length===0">
                                    <div class="py-8 text-center text-sm text-gray-400">No new notifications</div>
                                </template>
                                <template x-for="n in items" :key="n.id">
                                    <a :href="n.url||'#'"
                                        class="flex gap-3 px-4 py-3 hover:bg-gray-50 transition-colors">
                                        <span class="mt-1.5 flex-shrink-0 w-2 h-2 rounded-full"
                                            :class="n.is_read?'bg-gray-200':'bg-blue-500'"></span>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate" x-text="n.title"></p>
                                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="n.message"></p>
                                        </div>
                                    </a>
                                </template>
                            </div>
                            <div class="px-4 py-2.5 bg-gray-50 border-t border-gray-100">
                                <a href="{{ route('notifications.index') }}"
                                    class="text-xs font-medium text-blue-600 hover:underline">View all</a>
                            </div>
                        </div>
                    </div>
                    {{-- User menu --}}
                    <div class="relative" x-data="{open:false}">
                        <button @click="open=!open"
                            class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                            <div
                                class="w-7 h-7 rounded-full bg-blue-600 text-white text-xs font-semibold flex items-center justify-center uppercase">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <span
                                class="hidden sm:block text-sm font-medium text-gray-700 max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>
                        <div x-show="open" @click.outside="open=false" x-transition
                            class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50"
                            style="display:none">
                            <div class="px-4 py-2.5 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                                Profile
                            </a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <polyline points="16 17 21 12 16 7" />
                                        <line x1="21" x2="9" y1="12" y2="12" />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Flash --}}
            @if(session('success') || session('error') || session('warning'))
                <div class="px-4 lg:px-6 pt-4 space-y-2" x-data="{show:true}" x-show="show">
                    @if(session('success'))
                        <div
                            class="flex items-center gap-3 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                            <svg class="w-4 h-4 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            <span class="flex-1">{{ session('success') }}</span>
                            <button @click="show=false"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <line x1="18" x2="6" y1="6" y2="18" />
                                    <line x1="6" x2="18" y1="6" y2="18" />
                                </svg></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div
                            class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                            <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" x2="12" y1="8" y2="12" />
                                <line x1="12" x2="12.01" y1="16" y2="16" />
                            </svg>
                            <span class="flex-1">{{ session('error') }}</span>
                            <button @click="show=false"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <line x1="18" x2="6" y1="6" y2="18" />
                                    <line x1="6" x2="18" y1="6" y2="18" />
                                </svg></button>
                        </div>
                    @endif
                    @if(session('warning'))
                        <div
                            class="flex items-center gap-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg px-4 py-3 text-sm">
                            <svg class="w-4 h-4 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                                <path d="M12 9v4" />
                                <path d="M12 17h.01" />
                            </svg>
                            <span class="flex-1">{{ session('warning') }}</span>
                            <button @click="show=false"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <line x1="18" x2="6" y1="6" y2="18" />
                                    <line x1="6" x2="18" y1="6" y2="18" />
                                </svg></button>
                        </div>
                    @endif
                </div>
            @endif

            <main class="flex-1 overflow-y-auto p-4 lg:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Global delete confirm modal --}}
    <div x-data="delModal()" @open-delete.window="open($event.detail.action,$event.detail.message)" x-show="show"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" style="display:none" x-transition>
        <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6" @click.stop>
            <div class="flex gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <polyline points="3 6 5 6 21 6" />
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Confirm Delete</h3>
                    <p class="text-sm text-gray-500 mt-0.5" x-text="message"></p>
                </div>
            </div>
            <div class="flex gap-3">
                <button @click="show=false"
                    class="flex-1 px-4 py-2 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                <form :action="action" method="POST" class="flex-1">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-full px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        function notifBell() { return { open: false, count: 0, items: [], async load() { try { const r = await fetch('{{ route("notifications.unread") }}'); const d = await r.json(); this.items = d.notifications; this.count = d.count; } catch (e) { } } } }
        function delModal() { return { show: false, action: '', message: 'Are you sure you want to delete this item?', open(a, m) { this.action = a; this.message = m || this.message; this.show = true; } } }
    </script>
</body>

</html>