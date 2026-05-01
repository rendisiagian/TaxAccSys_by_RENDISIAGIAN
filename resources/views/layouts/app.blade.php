<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Tax Accounting System — Multi-company tax & accounting management">

        <title>{{ config('app.name', 'Tax Accounting System') }} — @yield('title', __('Dashboard'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Lucide Icons CDN -->
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; }
            .sidebar { transition: width 0.3s ease; }
            .sidebar-link { transition: all 0.2s ease; }
            .sidebar-link:hover { transform: translateX(4px); }
            .sidebar-link.active { background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(139,92,246,0.1)); border-left: 3px solid #6366f1; }
            .stat-card { transition: all 0.3s ease; }
            .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
            .glass { background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
            .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
            .dropdown-animate { animation: dropdownFade 0.15s ease-out; }
            @keyframes dropdownFade { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: translateY(0); } }
        </style>
    </head>
    <body class="antialiased bg-slate-50 text-slate-800">
        <div class="flex min-h-screen" x-data="{ sidebarOpen: true, companyDropdown: false, profileDropdown: false, langDropdown: false }">

            {{-- ── SIDEBAR ───────────────────────────────────────── --}}
            <aside class="sidebar fixed inset-y-0 left-0 z-30 flex flex-col bg-slate-900 text-slate-300 shadow-xl"
                   :class="sidebarOpen ? 'w-64' : 'w-20'"
                   @click.away="if(window.innerWidth < 1024) sidebarOpen = false">

                {{-- Logo --}}
                <div class="flex items-center h-16 px-4 border-b border-slate-700/50">
                    <div class="flex items-center space-x-3" x-show="sidebarOpen">
                        <div class="w-9 h-9 rounded-lg gradient-bg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">TAS</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white leading-tight">Tax Accounting</p>
                            <p class="text-xs text-slate-400">System</p>
                        </div>
                    </div>
                    <div class="w-9 h-9 rounded-lg gradient-bg flex items-center justify-center mx-auto" x-show="!sidebarOpen">
                        <span class="text-white font-bold text-sm">T</span>
                    </div>
                </div>

                {{-- Company Badge --}}
                @if(isset($currentCompany))
                <div class="px-4 py-3 border-b border-slate-700/50" x-show="sidebarOpen">
                    <div class="bg-slate-800/50 rounded-lg p-2.5">
                        <p class="text-xs text-slate-400 uppercase tracking-wider">{{ __('Company') }}</p>
                        <p class="text-sm font-medium text-white truncate mt-0.5">{{ $currentCompany->name }}</p>
                        @if($currentCompany->company_type === 'construction')
                        <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded text-xs font-medium bg-amber-500/20 text-amber-300">
                            {{ __('Construction') }}
                        </span>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Navigation --}}
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    {{-- Dashboard --}}
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Dashboard') }}</span>
                    </a>

                    {{-- Master Data --}}
                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">Master Data</p>
                    <a href="{{ route('companies.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('companies.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="building-2" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Companies') }}</span>
                    </a>
                    <a href="{{ route('branches.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('branches.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="map-pin" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Branches') }}</span>
                    </a>
                    @if(isset($currentCompany) && $currentCompany->company_type === 'construction')
                    <a href="{{ route('projects.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('projects.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="folder-kanban" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Projects') }}</span>
                    </a>
                    @endif
                    <a href="{{ route('clients.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('clients.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">Clients</span>
                    </a>
                    <a href="{{ route('vendors.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('vendors.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="truck" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">Vendors</span>
                    </a>
                    @if(auth()->user()->role->slug === 'manager')
                    <a href="{{ route('users.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('users.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Users') }}</span>
                    </a>
                    @endif

                    {{-- Accounting --}}
                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">{{ __('Accounting') }}</p>
                    <a href="{{ route('coa.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('coa.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="book-open" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Chart of Accounts') }}</span>
                    </a>
                    <a href="{{ route('journals.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('journals.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="file-text" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Journal Entry') }}</span>
                    </a>
                    <a href="{{ route('reports.general_ledger') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('reports.general_ledger') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="book" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('General Ledger') }}</span>
                    </a>
                    <a href="{{ route('reports.trial_balance') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('reports.trial_balance') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="scale" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">Trial Balance</span>
                    </a>
                    <a href="{{ route('reports.financial_statements') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('reports.financial_statements') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="pie-chart" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">Financial Reports</span>
                    </a>

                    {{-- Taxation --}}
                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">{{ __('Taxation') }}</p>
                    <a href="{{ route('employees.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('employees.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="contact" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">Employees (PTKP)</span>
                    </a>
                    <a href="{{ route('taxes.pph21.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('taxes.pph21.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('PPh 21') }}</span>
                    </a>
                    <a href="{{ route('taxes.unifikasi.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('taxes.unifikasi.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="file-stack" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('PPh Unification') }}</span>
                    </a>
                    <a href="{{ route('taxes.ppn.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('taxes.ppn.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="receipt" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('VAT') }}</span>
                    </a>
                    <a href="{{ route('tax-audits.index') }}" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm {{ request()->routeIs('tax-audits.*') ? 'active text-white font-medium' : 'hover:bg-slate-800 hover:text-white' }}">
                        <i data-lucide="shield-alert" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">Tax Control</span>
                    </a>

                    {{-- Reconciliation --}}
                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">{{ __('Reconciliation') }}</p>
                    <a href="#" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 hover:text-white">
                        <i data-lucide="git-compare" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Fiscal Reconciliation') }}</span>
                    </a>
                    <a href="#" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 hover:text-white">
                        <i data-lucide="calculator" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Deferred Tax') }}</span>
                    </a>

                    {{-- Tax Audit --}}
                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">{{ __('Tax Audit') }}</p>
                    <a href="#" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 hover:text-white">
                        <i data-lucide="search" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('SP2DK') }}</span>
                    </a>
                    <a href="#" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 hover:text-white">
                        <i data-lucide="clipboard-check" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">{{ __('Tax Assessment') }}</span>
                    </a>

                    {{-- Export --}}
                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-500" x-show="sidebarOpen">{{ __('Export XML') }}</p>
                    <a href="#" class="sidebar-link flex items-center px-3 py-2.5 rounded-lg text-sm hover:bg-slate-800 hover:text-white">
                        <i data-lucide="file-code" class="w-5 h-5 flex-shrink-0"></i>
                        <span class="ml-3" x-show="sidebarOpen">Coretax XML</span>
                    </a>
                </nav>

                {{-- Sidebar Toggle --}}
                <div class="border-t border-slate-700/50 p-3">
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="w-full flex items-center justify-center p-2 rounded-lg hover:bg-slate-800 transition-colors">
                        <i data-lucide="panel-left-close" class="w-5 h-5" x-show="sidebarOpen"></i>
                        <i data-lucide="panel-left-open" class="w-5 h-5" x-show="!sidebarOpen"></i>
                    </button>
                </div>
            </aside>

            {{-- ── MAIN CONTENT ──────────────────────────────────── --}}
            <div class="flex-1 flex flex-col" :class="sidebarOpen ? 'ml-64' : 'ml-20'" style="transition: margin-left 0.3s ease">

                {{-- Top Navbar --}}
                <header class="sticky top-0 z-20 h-16 bg-white/80 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-6">
                    <div class="flex items-center space-x-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg hover:bg-slate-100">
                            <i data-lucide="menu" class="w-5 h-5"></i>
                        </button>
                        <h1 class="text-lg font-semibold text-slate-800">@yield('title', __('Dashboard'))</h1>
                    </div>

                    <div class="flex items-center space-x-3">
                        {{-- Company Switcher --}}
                        @if(isset($userCompanies) && $userCompanies->count() > 1)
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-sm transition-colors">
                                <i data-lucide="building-2" class="w-4 h-4 text-indigo-600"></i>
                                <span class="hidden sm:inline max-w-32 truncate">{{ $currentCompany->name ?? '' }}</span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="dropdown-animate absolute right-0 mt-2 w-72 bg-white rounded-xl shadow-lg border border-slate-200 py-2 z-50">
                                <p class="px-4 py-1.5 text-xs font-semibold text-slate-400 uppercase">{{ __('Switch Company') }}</p>
                                @foreach($userCompanies as $comp)
                                <form method="POST" action="{{ route('company.switch', $comp->id) }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2.5 hover:bg-indigo-50 flex items-center justify-between transition-colors {{ $comp->id === ($currentCompany->id ?? 0) ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                        <div>
                                            <p class="text-sm font-medium">{{ $comp->name }}</p>
                                            <p class="text-xs text-slate-400">{{ $comp->npwp }}</p>
                                        </div>
                                        @if($comp->id === ($currentCompany->id ?? 0))
                                        <i data-lucide="check" class="w-4 h-4 text-indigo-600"></i>
                                        @endif
                                    </button>
                                </form>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Language Switcher --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-1.5 px-3 py-2 rounded-lg hover:bg-slate-100 text-sm transition-colors">
                                <i data-lucide="globe" class="w-4 h-4"></i>
                                <span>{{ strtoupper(app()->getLocale()) }}</span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="dropdown-animate absolute right-0 mt-2 w-36 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-50">
                                <a href="{{ route('locale.switch', 'id') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 {{ app()->getLocale() === 'id' ? 'text-indigo-600 font-medium' : '' }}">🇮🇩 Indonesia</a>
                                <a href="{{ route('locale.switch', 'en') }}" class="block px-4 py-2 text-sm hover:bg-slate-50 {{ app()->getLocale() === 'en' ? 'text-indigo-600 font-medium' : '' }}">🇺🇸 English</a>
                            </div>
                        </div>

                        {{-- Profile Dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-slate-100 transition-colors">
                                <div class="w-8 h-8 rounded-full gradient-bg flex items-center justify-center">
                                    <span class="text-white text-xs font-semibold">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-medium leading-tight">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-slate-400">{{ auth()->user()->role->name ?? 'User' }}</p>
                                </div>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="dropdown-animate absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-50">
                                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2.5 text-sm hover:bg-slate-50 transition-colors">
                                    <i data-lucide="user" class="w-4 h-4 mr-2.5 text-slate-400"></i>{{ __('Profile') }}
                                </a>
                                <hr class="my-1 border-slate-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i data-lucide="log-out" class="w-4 h-4 mr-2.5"></i>{{ __('Log Out') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </header>

                {{-- Flash Messages --}}
                @if(session('success'))
                <div class="mx-6 mt-4 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg text-sm flex items-center" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition>
                    <i data-lucide="check-circle" class="w-5 h-5 mr-2 flex-shrink-0"></i>
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mx-6 mt-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm flex items-center" x-data="{ show: true }" x-show="show">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-2 flex-shrink-0"></i>
                    {{ session('error') }}
                </div>
                @endif

                {{-- Page Content --}}
                <main class="flex-1 p-6">
                    @yield('content')
                    {{ $slot ?? '' }}
                </main>

                {{-- Footer --}}
                <footer class="border-t border-slate-200 px-6 py-3 text-xs text-slate-400 flex items-center justify-between">
                    <span>&copy; {{ date('Y') }} Tax Accounting System</span>
                    <span>v1.0 — Fase 1 Foundation</span>
                </footer>
            </div>
        </div>

        <script>
            // Initialize Lucide icons
            document.addEventListener('DOMContentLoaded', () => { lucide.createIcons(); });
            // Re-init after Alpine updates
            document.addEventListener('alpine:initialized', () => { lucide.createIcons(); });
        </script>
        @stack('scripts')
    </body>
</html>
