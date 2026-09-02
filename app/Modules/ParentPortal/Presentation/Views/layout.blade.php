<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Portail Parent') - Academia EduPortal</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Phosphor Icons & Google Material Symbols -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <!-- Google Fonts: Inter / Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
            line-height: 1;
        }
        [x-cloak] { display: none !important; }
        
        /* Custom scrollbars */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    @include('SchoolDashboard::components.searchable-select')
    @stack('styles')
</head>
<body class="bg-[#F8FAFC] text-slate-800 h-full flex overflow-hidden antialiased">

    <!-- LEFT SIDEBAR -->
    <aside class="w-64 bg-[#061536] text-white flex flex-col h-full shrink-0 shadow-xl z-20 select-none">
        
        <!-- Logo / Brand Header -->
        <div class="p-6 pb-5 flex items-center gap-3 border-b border-white/5">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 text-white flex items-center justify-center font-black text-xl shadow-lg shadow-blue-500/20">
                <i class="ph-bold ph-graduation-cap text-2xl"></i>
            </div>
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="font-extrabold text-base tracking-tight text-white leading-none">EduPortal</span>
                </div>
                <p class="text-[11px] font-medium text-blue-200/60 mt-1 truncate">Portail Parent</p>
            </div>
        </div>

        <!-- Navigation Links -->
        @php
            $currentParent = $parent ?? auth('parent')->user();
            $firstKid = isset($children) && $children instanceof \Illuminate\Support\Collection && $children->isNotEmpty() 
                ? $children->first() 
                : (isset($child) ? $child : ($currentParent ? app(\App\Modules\ParentPortal\Application\Services\ParentPortalService::class)->childrenOf($currentParent)->first() : null));
            $kidId = $firstKid?->id;
        @endphp
        <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-1.5" id="parent-sidebar-nav">
            
            <!-- 1. DASHBOARD -->
            <a href="{{ route('parent.dashboard') }}" 
               class="flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl font-semibold text-[13.5px] transition-all {{ request()->routeIs('parent.dashboard') ? 'bg-[#1E3A8A] text-white shadow-md shadow-blue-950/40' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                <span class="material-symbols-outlined text-[20px] {{ request()->routeIs('parent.dashboard') ? 'text-blue-200' : 'text-slate-400' }}">dashboard</span>
                <span>Tableau de Bord</span>
            </a>

            <!-- 2. SUIVI ACADÉMIQUE (SUBMENU) -->
            @php
                $isAcademicActive = request()->routeIs('parent.academic', 'parent.bulletin', 'parent.attendance', 'parent.homework', 'parent.diplomes');
            @endphp
            <div x-data="{ open: {{ $isAcademicActive ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold text-[13.5px] transition-all {{ $isAcademicActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-[20px] {{ $isAcademicActive ? 'text-blue-300' : 'text-slate-400' }}">school</span>
                        <span>Scolarité</span>
                    </div>
                    <i class="ph-bold ph-caret-down text-[13px] transition-transform duration-200 text-slate-400" :class="{ 'rotate-180 text-white': open }"></i>
                </button>
                <div x-show="open" x-collapse class="pl-9 pr-2 py-1.5 space-y-1 text-[12.5px]">
                    <a href="{{ route('parent.academic') }}" 
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.academic') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.academic') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>Vue d'ensemble</span>
                    </a>
                    @if($kidId)
                        <a href="{{ route('parent.bulletin', $kidId) }}" 
                           class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.bulletin') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.bulletin') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                            <span>Bulletins & Notes</span>
                        </a>
                        <a href="{{ route('parent.attendance', $kidId) }}" 
                           class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.attendance') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.attendance') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                            <span>Présences & Retards</span>
                        </a>
                        <a href="{{ route('parent.homework', $kidId) }}" 
                           class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.homework') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.homework') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                            <span>Devoirs & Examens</span>
                        </a>
                        <a href="{{ route('parent.diplomes', $kidId) }}" 
                           class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.diplomes') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.diplomes') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                            <span>Diplômes & Prix</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- 3. FINANCES & FRAIS (SUBMENU) -->
            @php
                $isFinanceActive = request()->routeIs('parent.finance', 'parent.fees');
            @endphp
            <div x-data="{ open: {{ $isFinanceActive ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold text-[13.5px] transition-all {{ $isFinanceActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-[20px] {{ $isFinanceActive ? 'text-blue-300' : 'text-slate-400' }}">account_balance_wallet</span>
                        <span>Finances</span>
                    </div>
                    <i class="ph-bold ph-caret-down text-[13px] transition-transform duration-200 text-slate-400" :class="{ 'rotate-180 text-white': open }"></i>
                </button>
                <div x-show="open" x-collapse class="pl-9 pr-2 py-1.5 space-y-1 text-[12.5px]">
                    <a href="{{ route('parent.finance') }}" 
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.finance') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.finance') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>Portefeuille & Bilan</span>
                    </a>
                    @if($kidId)
                        <a href="{{ route('parent.fees', $kidId) }}" 
                           class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.fees') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.fees') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                            <span>Échéancier Scolarité</span>
                        </a>
                    @endif
                </div>
            </div>

            <!-- 4. SERVICES DE VIE SCOLAIRE (SUBMENU) -->
            @php
                $isServicesActive = request()->routeIs('parent.services', 'parent.canteen', 'parent.transport', 'parent.infirmary', 'parent.school-access');
            @endphp
            <div x-data="{ open: {{ $isServicesActive ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold text-[13.5px] transition-all {{ $isServicesActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-[20px] {{ $isServicesActive ? 'text-blue-300' : 'text-slate-400' }}">directions_bus</span>
                        <span>Vie Scolaire</span>
                    </div>
                    <i class="ph-bold ph-caret-down text-[13px] transition-transform duration-200 text-slate-400" :class="{ 'rotate-180 text-white': open }"></i>
                </button>
                <div x-show="open" x-collapse class="pl-9 pr-2 py-1.5 space-y-1 text-[12.5px]">
                    <a href="{{ route('parent.services') }}" 
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.services') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.services') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>Vue d'ensemble</span>
                    </a>
                    <a href="{{ route('parent.school-access') }}" 
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.school-access') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.school-access') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>Accès Scolaire 🔐</span>
                    </a>
                    @if($kidId)
                        <a href="{{ route('parent.canteen', $kidId) }}" 
                           class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.canteen') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.canteen') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                            <span>Cantine & Menus</span>
                        </a>
                        <a href="{{ route('parent.transport', $kidId) }}" 
                           class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.transport') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.transport') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                            <span>Transport & Bus GPS</span>
                        </a>
                    @endif
                    <a href="{{ route('parent.infirmary') }}" 
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.infirmary') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.infirmary') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>Santé & Infirmerie</span>
                    </a>
                </div>
            </div>

            <!-- 5. SCHOOL TRACK (SUBMENU) -->
            @php
                $isSchoolTrackActive = request()->routeIs('parent.school-track.*');
                $stAccessActive = $schoolTrackStatus['active'] ?? false;
                $stAccessEnabled = $schoolTrackStatus['moduleEnabled'] ?? true;
            @endphp
            @if($stAccessEnabled)
            @if($stAccessActive)
            <div x-data="{ open: {{ $isSchoolTrackActive ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold text-[13.5px] transition-all {{ $isSchoolTrackActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-[20px] {{ $isSchoolTrackActive ? 'text-blue-300' : 'text-slate-400' }}">explore</span>
                        <span>School Track</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[9.5px] font-black uppercase tracking-wider px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">Actif</span>
                        <i class="ph-bold ph-caret-down text-[13px] transition-transform duration-200 text-slate-400" :class="{ 'rotate-180 text-white': open }"></i>
                    </div>
                </button>
                <div x-show="open" x-collapse class="pl-9 pr-2 py-1.5 space-y-1 text-[12.5px]">
                    <a href="{{ route('parent.school-track.index') }}"
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.school-track.index') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.school-track.index') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>Découvrir les écoles</span>
                    </a>
                    <a href="{{ route('parent.school-track.compare') }}"
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.school-track.compare') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.school-track.compare') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>Comparateur</span>
                    </a>
                    <a href="{{ route('parent.school-track.map') }}"
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.school-track.map') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.school-track.map') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>Carte interactive</span>
                    </a>
                </div>
            </div>
            @else
            <a href="{{ route('parent.dashboard', ['school_track' => 'locked']) }}#school-track-card"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold text-[13.5px] transition-all text-slate-300 hover:text-white hover:bg-white/5">
                <div class="flex items-center gap-3.5">
                    <span class="material-symbols-outlined text-[20px] text-slate-400">explore</span>
                    <span>School Track</span>
                </div>
                <i class="ph-bold ph-lock-simple text-[13px] text-amber-400"></i>
            </a>
            @endif
            @endif

            <!-- 6. MESSAGES & COMMUNICATIONS -->
            @php $isNotificationsActive = request()->routeIs('parent.notifications', 'parent.children.add-form'); @endphp
            <div x-data="{ open: {{ $isNotificationsActive ? 'true' : 'false' }} }">
                <button type="button" @click="open = !open" 
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl font-semibold text-[13.5px] transition-all {{ $isNotificationsActive ? 'bg-white/10 text-white' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    <div class="flex items-center gap-3.5">
                        <span class="material-symbols-outlined text-[20px] {{ $isNotificationsActive ? 'text-blue-300' : 'text-slate-400' }}">forum</span>
                        <span>Messagerie</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        @php
                            $unreadCount = ($parent ?? auth('parent')->user()) ? ($parent ?? auth('parent')->user())->notificationLogs()->whereNull('read_at')->count() : 0;
                        @endphp
                        @if($unreadCount > 0)
                            <span class="text-[10px] font-extrabold bg-blue-500 text-white px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                        @endif
                        <i class="ph-bold ph-caret-down text-[13px] transition-transform duration-200 text-slate-400" :class="{ 'rotate-180 text-white': open }"></i>
                    </div>
                </button>
                <div x-show="open" x-collapse class="pl-9 pr-2 py-1.5 space-y-1 text-[12.5px]">
                    <a href="{{ route('parent.notifications') }}" 
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.notifications') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.notifications') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>Notifications</span>
                    </a>
                    <a href="{{ route('parent.children.add-form') }}" 
                       class="flex items-center gap-2 py-1.5 px-2 rounded-lg transition {{ request()->routeIs('parent.children.add-form') ? 'text-blue-300 font-bold bg-white/5' : 'text-slate-400 hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('parent.children.add-form') ? 'bg-blue-400' : 'bg-slate-600' }}"></span>
                        <span>+ Ajouter un enfant</span>
                    </a>
                </div>
            </div>

        </nav>

        <!-- Sidebar Bottom Footer -->
        <div class="p-4 border-t border-white/5 space-y-2">
            <!-- Find Schools / School Track Quick Action -->
            <a href="{{ route('parent.school-track.index') }}" 
               class="flex items-center justify-center gap-2 w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white text-[12.5px] font-extrabold transition shadow-md shadow-blue-950/40">
                <span class="material-symbols-outlined text-[18px]">search</span>
                <span>Explorer les écoles</span>
            </a>

            <!-- Help Center button -->
            <a href="mailto:support@academia.school" class="flex items-center justify-center gap-2 w-full py-2 px-3 rounded-xl bg-white/5 hover:bg-white/10 text-slate-300 hover:text-white text-[12.5px] font-semibold transition border border-white/5">
                <span class="material-symbols-outlined text-[17px] text-blue-300">help</span>
                <span>Centre d'Aide</span>
            </a>

            <div class="pt-1 flex items-center justify-between px-1 text-[12px] text-slate-400">
                <a href="{{ route('parent.settings') }}" class="hover:text-white transition flex items-center gap-1.5 {{ request()->routeIs('parent.settings') ? 'text-blue-300 font-bold' : '' }}">
                    <span class="material-symbols-outlined text-[16px]">settings</span>
                    <span>Paramètres</span>
                </a>

                <form action="{{ route('parent.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="hover:text-red-400 transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">logout</span>
                        <span>Déconnexion</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- MAIN APP WRAPPER -->
    <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden bg-white">
        
        <!-- TOP APP HEADER -->
        <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-6 shrink-0 z-10">
            
            <!-- Left Header: Title + Subnav Tabs -->
            <div class="flex items-center gap-8">
                <div class="flex items-center gap-3">
                    <h1 class="text-[17px] font-extrabold text-slate-900 tracking-tight">Portail Parent</h1>
                </div>

                <div class="hidden md:flex items-center gap-6 text-[13.5px] font-semibold">
                    <a href="{{ route('parent.dashboard') }}" class="pb-1 transition {{ request()->routeIs('parent.dashboard') ? 'text-[#061536] border-b-2 border-[#061536]' : 'text-slate-400 hover:text-slate-600' }}">
                        Mes Enfants
                    </a>
                    <a href="{{ route('parent.notifications') }}" class="pb-1 transition {{ request()->routeIs('parent.notifications') ? 'text-[#061536] border-b-2 border-[#061536]' : 'text-slate-400 hover:text-slate-600' }}">
                        Actualités & Événements
                    </a>
                </div>
            </div>

            <!-- Right Header Actions -->
            <div class="flex items-center gap-3.5">
                
                <!-- Search Button / Quick Search -->
                <div class="relative hidden sm:block">
                    <span class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
                        <span class="material-symbols-outlined text-[18px]">search</span>
                    </span>
                    <input type="text" placeholder="Rechercher..." 
                           class="w-48 lg:w-60 bg-slate-50 border border-slate-200/80 rounded-xl pl-9 pr-3 py-1.5 text-[12.5px] font-medium text-slate-700 outline-none focus:border-blue-500 focus:bg-white transition placeholder-slate-400">
                </div>

                <!-- Notifications Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button type="button" @click="open = !open" @click.outside="open = false" 
                            class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition relative">
                        <span class="material-symbols-outlined text-[21px]">notifications</span>
                        @if(($unreadNotificationsCount ?? $unreadCount ?? 0) > 0)
                            <span class="absolute top-1.5 right-1.5 bg-rose-500 text-white text-[9px] font-black w-4 h-4 rounded-full flex items-center justify-center shadow-sm">
                                {{ min(99, $unreadNotificationsCount ?? $unreadCount) }}
                            </span>
                        @endif
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="open" x-cloak class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 z-50 overflow-hidden">
                        <div class="p-3.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">Notifications</span>
                            <a href="{{ route('parent.notifications') }}" class="text-[11px] font-bold text-blue-600 hover:underline">Tout voir</a>
                        </div>
                        <div class="max-h-80 overflow-y-auto divide-y divide-slate-100 text-[12px]">
                            @forelse(($parent ?? auth('parent')->user())->notificationLogs()->orderByDesc('created_at')->take(6)->get() as $n)
                                <div class="p-3.5 hover:bg-slate-50 transition {{ $n->read_at ? '' : 'bg-blue-50/40' }}">
                                    <p class="font-bold text-slate-800 leading-snug">{{ $n->title }}</p>
                                    <p class="text-slate-500 mt-1 text-[11px] leading-normal">{{ \Illuminate\Support\Str::limit($n->body, 80) }}</p>
                                    <p class="text-[10px] font-medium text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }}</p>
                                </div>
                            @empty
                                <div class="p-6 text-center text-slate-400 text-xs">
                                    <i class="ph-bold ph-bell-slash text-2xl text-slate-300 mb-1 block"></i>
                                    Aucune notification
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Add Child Link -->
                <a href="{{ route('parent.children.add-form') }}" 
                   class="hidden sm:inline-flex items-center gap-1.5 text-[12px] font-bold text-slate-600 hover:text-blue-700 bg-slate-100 hover:bg-blue-50 px-3 py-1.5 rounded-xl transition">
                    <i class="ph-bold ph-user-plus text-[14px]"></i>
                    <span>Ajouter enfant</span>
                </a>

                <!-- User Profile / Avatar -->
                <div class="flex items-center gap-2.5 pl-2 border-l border-slate-200/60">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-700 to-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-sm overflow-hidden">
                        @if(($parent ?? auth('parent')->user())->photo_path ?? false)
                            <img src="{{ asset('storage/' . ($parent ?? auth('parent')->user())->photo_path) }}" class="w-full h-full object-cover">
                        @else
                            {{ substr(($parent ?? auth('parent')->user())->name ?? 'P', 0, 1) }}
                        @endif
                    </div>
                    <div class="hidden lg:block text-left">
                        <p class="text-[12.5px] font-bold text-slate-800 leading-none truncate max-w-[120px]">{{ ($parent ?? auth('parent')->user())->name ?? 'Parent' }}</p>
                    </div>
                </div>
            </div>
        </header>

        <!-- SUBNAV FOR CURRENT SELECTED CHILD (When inside a child view) -->
        @isset($child)
        <div class="bg-white border-b border-slate-200/80 px-6 py-2 shrink-0">
            <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
                
                <!-- Breadcrumb + Child identity -->
                <div class="flex items-center gap-2 text-[13px] text-slate-500">
                    <a href="{{ route('parent.dashboard') }}" class="hover:text-slate-800 font-semibold flex items-center gap-1 transition">
                        <i class="ph-bold ph-arrow-left"></i> Mes Enfants
                    </a>
                    <span class="text-slate-300">/</span>
                    @php $siblings = $siblingChildren ?? collect([$child]); @endphp
                    @if($siblings->count() > 1)
                    <div class="relative">
                        <select onchange="window.location.href=this.value"
                                class="appearance-none bg-transparent font-bold text-slate-900 pr-5 py-0.5 outline-none cursor-pointer hover:text-blue-700 transition">
                            @foreach($siblings as $kid)
                            <option value="{{ route(request()->route()->getName(), $kid->id) }}" {{ $kid->id === $child->id ? 'selected' : '' }}>
                                {{ $kid->first_name }} {{ $kid->last_name }}
                            </option>
                            @endforeach
                        </select>
                        <i class="ph-bold ph-caret-down absolute right-0 top-1/2 -translate-y-1/2 text-[9px] pointer-events-none"></i>
                    </div>
                    @else
                    <span class="font-bold text-slate-900">{{ $child->first_name }} {{ $child->last_name }}</span>
                    @endif
                    <span class="text-[11px] font-medium bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">{{ $child->academicClass->name ?? 'Classe' }}</span>
                </div>

                <!-- Secondary Child Tabs -->
                <div class="flex items-center gap-1 overflow-x-auto text-[12.5px] font-bold pb-0.5">
                    @php
                        $childTabs = [
                            'parent.card' => ['Carte scolaire', 'ph-identification-badge'],
                            'parent.bulletin' => ['Bulletin', 'ph-scroll'],
                            'parent.attendance' => ['Présence', 'ph-calendar-check'],
                            'parent.homework' => ['Devoirs', 'ph-book-open'],
                            'parent.fees' => ['Frais', 'ph-wallet'],
                            'parent.canteen' => ['Cantine', 'ph-fork-knife'],
                            'parent.transport' => ['Transport', 'ph-bus'],
                            'parent.diplomes' => ['Diplômes', 'ph-medal'],
                        ];
                    @endphp
                    @foreach($childTabs as $rName => [$label, $icon])
                        <a href="{{ route($rName, $child->id) }}"
                           class="px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 {{ request()->routeIs($rName) ? 'bg-[#061536] text-white shadow-sm' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-100' }}">
                            <i class="ph-bold {{ $icon }}"></i>
                            <span>{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endisset

        <!-- MAIN SCROLLABLE CONTENT -->
        <main class="flex-1 overflow-y-auto bg-[#F8FAFC] p-6 lg:p-8">
            <div class="max-w-6xl mx-auto space-y-6">
                
                @if(session('success'))
                <div class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 border border-emerald-200/80 font-bold flex items-center gap-2.5 shadow-sm">
                    <i class="ph-fill ph-check-circle text-emerald-600 text-lg"></i>
                    <span>{{ session('success') }}</span>
                </div>
                @endif

                @if(session('error'))
                <div class="p-4 text-sm text-rose-800 rounded-2xl bg-rose-50 border border-rose-200/80 font-bold flex items-center gap-2.5 shadow-sm">
                    <i class="ph-fill ph-warning-circle text-rose-600 text-lg"></i>
                    <span>{{ session('error') }}</span>
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
