<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Portail - Academia ERP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen">
    <div class="flex min-h-screen flex-col lg:flex-row">
        
        <!-- Left Panel: Login Form -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center px-8 md:px-12 lg:px-20 py-12 relative">
            
            <div class="max-w-md w-full mx-auto">
                <h1 class="text-[32px] font-bold text-slate-900 tracking-tight mb-2">Heureux de vous revoir !</h1>
                <p class="text-[14px] text-slate-500 mb-6">Entrez vos identifiants pour accéder à votre portail académique.</p>

                <!-- Tabs -->
                <div class="flex bg-slate-50 rounded-lg p-1 border border-slate-200 mb-6 relative">
                    <!-- Tab Background Highlight (Absolute, moves via JS, but we can just use class toggling for simplicity) -->
                    
                    <button onclick="switchTab('student')" id="tab-student" class="flex-1 flex flex-col items-center justify-center py-2.5 rounded-md text-[12px] font-bold transition bg-white shadow-sm text-[#2F5F76]">
                        <i class="ph-fill ph-student text-lg mb-0.5"></i>
                        Élève
                    </button>
                    
                    <button onclick="switchTab('teacher')" id="tab-teacher" class="flex-1 flex flex-col items-center justify-center py-2.5 rounded-md text-[12px] font-bold transition text-slate-500 hover:text-slate-700">
                        <i class="ph-fill ph-chalkboard-teacher text-lg mb-0.5"></i>
                        Professeur
                    </button>
                    
                    <button onclick="switchTab('parent')" id="tab-parent" class="flex-1 flex flex-col items-center justify-center py-2.5 rounded-md text-[12px] font-bold transition text-slate-500 hover:text-slate-700">
                        <i class="ph-fill ph-users-three text-lg mb-0.5"></i>
                        Parent
                    </button>
                </div>

                <form action="#" method="POST" class="space-y-5">
                    @csrf
                    
                    <input type="hidden" name="role" id="role-input" value="student">

                    <!-- Email / Identifier -->
                    <div>
                        <label for="identifier" id="identifier-label" class="block text-[12px] font-semibold text-slate-700 mb-1.5">Matricule ou Email</label>
                        <div class="relative">
                            <i class="ph-fill ph-user absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input type="text" id="identifier" name="identifier" required
                                class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                                placeholder="Ex: MAT12345">
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

                    <!-- School Code -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="school_code" class="block text-[12px] font-semibold text-slate-700">Code scolaire</label>
                            <a href="#" class="text-[11px] font-medium text-[#2F5F76] hover:underline">Qu'est-ce que c'est ?</a>
                        </div>
                        <div class="relative">
                            <i class="ph-fill ph-buildings absolute left-3 top-1/2 -translate-y-1/2 text-slate-300 text-lg"></i>
                            <input type="text" id="school_code" name="school_code" required
                                class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg pl-10 pr-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm placeholder:text-slate-300"
                                placeholder="SCH-XXXX">
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
                        Problème de connexion ? <a href="#" class="text-slate-900 hover:underline">Contactez votre école</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Panel: Visual/Brand -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-[#0D2F2A] overflow-hidden flex-col justify-between p-12">
            <!-- Background Image overlay -->
            <div class="absolute inset-0 z-0 opacity-40 mix-blend-overlay">
                <!-- Using an Unsplash image of students working together -->
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=2000&auto=format&fit=crop" class="w-full h-full object-cover" alt="Students">
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
                    PORTAIL DES FAMILLES
                </span>
                
                <h2 class="text-5xl font-extrabold text-white leading-[1.1] tracking-tight mb-6">
                    Apprendre, collaborer<br>et réussir<br>ensemble.
                </h2>
                
                <p class="text-[17px] text-slate-300 font-medium leading-relaxed max-w-md">
                    Accédez instantanément à vos cours, consultez vos résultats et communiquez avec l'équipe pédagogique en toute simplicité.
                </p>
            </div>
        </div>

    </div>

    <script>
        function switchTab(role) {
            // Update hidden input
            document.getElementById('role-input').value = role;

            const inactiveClasses = "flex-1 flex flex-col items-center justify-center py-2.5 rounded-md text-[12px] font-bold transition text-slate-500 hover:text-slate-700 bg-transparent shadow-none";
            const activeClasses = "flex-1 flex flex-col items-center justify-center py-2.5 rounded-md text-[12px] font-bold transition bg-white shadow-sm text-[#2F5F76]";

            // Reset all tabs to inactive state
            const tabs = ['student', 'teacher', 'parent'];
            tabs.forEach(t => {
                const el = document.getElementById('tab-' + t);
                el.className = inactiveClasses;
            });

            // Set active tab state
            const activeTab = document.getElementById('tab-' + role);
            activeTab.className = activeClasses;

            // Update form labels and text based on role
            const label = document.getElementById('identifier-label');
            const input = document.getElementById('identifier');

            if (role === 'student') {
                label.textContent = 'Matricule ou Email';
                input.placeholder = 'Ex: MAT12345';
            } else if (role === 'teacher') {
                label.textContent = 'Adresse Email ou Numéro de Téléphone';
                input.placeholder = 'Email ou Téléphone';
            } else if (role === 'parent') {
                label.textContent = 'Adresse Email ou Numéro de Téléphone';
                input.placeholder = 'Email ou Téléphone';
            }
        }
    </script>
</body>
</html>
