<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Personnel - Academia ERP</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Poppins', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-white min-h-screen">
    <div class="flex min-h-screen flex-col lg:flex-row">

        <!-- Left Panel: Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 md:px-16 lg:px-24 xl:px-32 py-12">

            <div class="max-w-md w-full mx-auto" x-data="{ activeTab: '{{ old('school_code') ? 'staff' : 'admin' }}' }">
                <h1 class="text-[32px] font-bold text-slate-900 tracking-tight mb-2">Heureux de vous revoir !</h1>
                <p class="text-[14px] text-slate-500 mb-6">Entrez vos identifiants pour accéder à votre portail académique.</p>

                <!-- Tabs -->
                <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 mb-6">
                    <button type="button" @click="activeTab = 'admin'"
                        :class="activeTab === 'admin' ? 'bg-white text-[#2F5F76] shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                        class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-lg text-[13px] font-bold transition">
                        <i class="ph-fill ph-shield-star text-base"></i> Admin
                    </button>
                    <button type="button" @click="activeTab = 'staff'"
                        :class="activeTab === 'staff' ? 'bg-white text-[#2F5F76] shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                        class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-lg text-[13px] font-bold transition">
                        <i class="ph-fill ph-users-three text-base"></i> Directeur / Personnel
                    </button>
                </div>

                <form action="{{ route('login') }}" method="POST" class="space-y-5">
                    @csrf
                    <input type="hidden" name="login_type" :value="activeTab">

                    @if ($errors->any())
                        <div class="bg-red-50 text-red-600 text-[13px] font-medium p-3 rounded-lg border border-red-200">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>- {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- School Code (Directeur/Personnel only) -->
                    <div x-show="activeTab === 'staff'" x-cloak>
                        <label for="school_code" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Code École</label>
                        <div class="relative">
                            <i class="ph-fill ph-buildings absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input type="text" id="school_code" name="school_code" :required="activeTab === 'staff'"
                                class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                placeholder="Ex: ACAD-ECO7278" value="{{ old('school_code') }}">
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-[12px] font-semibold text-slate-700 mb-1.5">
                            <span x-show="activeTab === 'admin'" x-cloak>Email</span>
                            <span x-show="activeTab === 'staff'" x-cloak>Email ou Identifiant</span>
                        </label>
                        <div class="relative">
                            <i class="ph-fill ph-envelope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input type="text" id="email" name="email" required autofocus
                                class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                placeholder="name@example.com ou identifiant" value="{{ old('email') }}">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Mot de passe</label>
                        <div class="relative">
                            <i class="ph-fill ph-lock-key absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input type="password" id="password" name="password" required
                                class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-10 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                placeholder="••••••••">
                            <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                                <i class="ph-fill ph-eye-slash text-lg"></i>
                            </button>
                        </div>
                    </div>



                    <div class="text-right pt-2 pb-2">
                        <a href="#" class="text-[12px] font-semibold text-slate-600 hover:text-slate-900">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="w-full bg-[#2B5A73] hover:bg-[#1E4357] text-white font-bold text-[14px] py-3 rounded-lg shadow-md transition flex items-center justify-center gap-2">
                        Se connecter <i class="ph-bold ph-arrow-right"></i>
                    </button>
                </form>

                <div class="text-center mt-6">
                    <p class="text-[12px] font-semibold text-slate-600">
                        Nouvel utilisateur ? <a href="{{ route('register') }}" class="text-slate-900 hover:underline">Inscrivez-vous pour gérer votre école sans effort !</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Panel: Visual/Brand -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-[#0D2F2A] overflow-hidden flex-col justify-between p-12">
            <!-- Background Image overlay -->
            <div class="absolute inset-0 z-0 opacity-40 mix-blend-overlay">
                <!-- Fallback to a gradient if image fails to load, but we use a random unsplash image representing students/school -->
                <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover" alt="Students">
            </div>
            
            <!-- Dark Gradient to ensure text readability -->
            <div class="absolute inset-0 z-0 bg-gradient-to-t from-[#0D2F2A] via-[#0D2F2A]/80 to-transparent"></div>
            <div class="absolute inset-0 z-0 bg-gradient-to-r from-[#0D2F2A] via-[#0D2F2A]/60 to-transparent"></div>

            <!-- Header -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-8 h-8 rounded bg-[#27A792] flex items-center justify-center text-white font-bold text-lg">
                    A
                </div>
                <span class="text-white font-bold text-xl tracking-tight">Academia ERP</span>
            </div>

            <!-- Content -->
            <div class="relative z-10 max-w-lg mb-12">
                <span class="inline-block border border-[#27A792] text-[#27A792] text-[10px] font-bold tracking-widest uppercase px-3 py-1.5 rounded-full mb-6">
                    NOUVEAU PORTAIL V2.0
                </span>
                
                <h2 class="text-5xl font-extrabold text-white leading-[1.1] tracking-tight mb-6">
                    Donner les moyens<br>à la prochaine génération<br>de leaders.
                </h2>
                
                <p class="text-[17px] text-slate-300 font-medium leading-relaxed max-w-md">
                    Gérez vos tâches administratives, suivez les progrès des élèves et connectez-vous avec vos équipes pédagogiques en un seul endroit sécurisé.
                </p>
            </div>
        </div>

    </div>
</body>
</html>
