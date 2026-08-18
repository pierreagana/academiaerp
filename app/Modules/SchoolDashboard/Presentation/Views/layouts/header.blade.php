<header class="h-16 flex items-center justify-between px-6 md:px-8 border-b border-slate-200/60 shrink-0">
    <!-- Left Navigation -->
    <div class="flex items-center gap-6">
        <div class="font-bold text-slate-800 text-[15px] flex items-center gap-2">
            Academia AI
        </div>
        <nav class="hidden md:flex items-center gap-5 text-[13.5px] font-medium text-slate-500">
            <a href="#" class="text-slate-900 font-semibold border-b-2 border-slate-900 pb-5 translate-y-[10px]">Tableau de Bord</a>
            <a href="#" class="hover:text-slate-800 transition">Rapports</a>
            <a href="#" class="hover:text-slate-800 transition">Paramètres</a>
        </nav>
    </div>

    <!-- Right Controls -->
    <div class="flex items-center gap-4">
        <!-- Search -->
        <div class="relative hidden sm:block">
            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
            <input type="text" placeholder="Rechercher élèves, personnel..." class="w-64 pl-10 pr-4 py-2 bg-slate-100/70 border-none rounded-full text-[13px] text-slate-700 focus:ring-2 focus:ring-primary-dynamic transition placeholder:text-slate-400">
        </div>

        <!-- Icons -->
        <div class="flex items-center gap-2">
            <button class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition relative">
                <i class="ph ph-bell text-[20px]"></i>
                <span class="absolute top-2 right-2 w-1.5 h-1.5 bg-red-500 rounded-full"></span>
            </button>
            <button class="w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:bg-slate-100 transition">
                <i class="ph ph-squares-four text-[20px]"></i>
            </button>
        </div>

        <!-- Avatar with Dropdown -->
        <div class="relative ml-2" x-data="{ open: false }" @click.away="open = false">
            <button @click="open = !open" class="w-8 h-8 rounded-full overflow-hidden border border-slate-200 focus:outline-none focus:ring-2 focus:ring-primary-dynamic transition">
                <img src="https://i.pravatar.cc/150?img=32" alt="Profile" class="w-full h-full object-cover">
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-100 py-1 z-50"
                 style="display: none;">
                
                <a href="{{ route('school.profile') }}" class="flex items-center gap-2 px-4 py-2 text-[13px] font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    <i class="ph ph-user text-lg text-slate-400"></i> Profil
                </a>
                
                <a href="{{ route('school.profile') }}" class="flex items-center gap-2 px-4 py-2 text-[13px] font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition">
                    <i class="ph ph-lock-key text-lg text-slate-400"></i> Modifier mot de passe
                </a>
                
                <div class="h-px bg-slate-100 my-1"></div>
                
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-[13px] font-medium text-red-600 hover:bg-red-50 transition">
                        <i class="ph ph-sign-out text-lg text-red-500"></i> Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
