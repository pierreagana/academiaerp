@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Paramètres Système Globaux</h2>
            <p class="text-[15px] text-slate-500 mt-1">Gérez l'infrastructure globale, les clés APIs, le serveur SMTP d'emails et la palette de couleurs SaaS (Base SQL).</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <button type="submit" form="globalSettingsForm" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-blue-900 transition shadow-sm cursor-pointer">
                <i class="ph ph-floppy-disk text-lg font-bold"></i> Enregistrer en BD SQL
            </button>
        </div>
    </div>

    <!-- Success Toast Alert -->
    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>
    @endif

    <!-- Main Settings Form -->
    <form action="{{ route('superadmin.global-settings.update') }}" method="POST" id="globalSettingsForm">
        @csrf
        
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-24">
            
            <!-- Left Column (General Settings, SMTP & APIs) -->
            <div class="xl:col-span-2 flex flex-col gap-6">
                
                <!-- General Institution Settings -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-[#031C5B] text-white flex items-center justify-center shrink-0 shadow-sm font-bold">
                            <i class="ph ph-wrench text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-[20px] font-extrabold text-[#111827]">Paramètres Généraux de l'Établissement</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Configuration de base de la plateforme SaaS multi-tenant</p>
                        </div>
                    </div>

                    <!-- Maintenance Mode -->
                    <div class="bg-[#FFF7ED] border border-[#FFEDD5] rounded-xl p-5 mb-6 flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <h4 class="text-[14px] font-bold text-[#9A3412] mb-1 flex items-center gap-2">
                                <i class="ph ph-warning text-lg font-bold"></i> Mode Maintenance Système
                            </h4>
                            <p class="text-[12px] font-medium text-[#C2410C] leading-relaxed">
                                Suspend momentanément l'accès de tous les établissements pour mise à jour. Seuls les Super Admins conservent l'accès.
                            </p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer shrink-0">
                            <input type="checkbox" name="maintenance_mode" value="1" class="sr-only peer" {{ !empty($settings['maintenance_mode']) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#9A3412]"></div>
                        </label>
                    </div>

                    <!-- Platform Name, Support Email, Support Phone -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-xs">
                        <div>
                            <label class="block font-bold text-[#111827] mb-1.5">Nom de la Plateforme *</label>
                            <input type="text" name="platform_name" value="{{ $settings['platform_name'] ?? 'AcademiaERP SaaS' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-bold text-[#111827] mb-1.5">Email du Support *</label>
                            <input type="email" name="support_email" value="{{ $settings['support_email'] ?? 'support@academiaerp.com' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition">
                        </div>
                        <div>
                            <label class="block font-bold text-[#111827] mb-1.5">Téléphone du Support</label>
                            <input type="text" name="support_phone" value="{{ $settings['support_phone'] ?? '+221 33 800 00 00' }}" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition">
                        </div>
                    </div>

                    <!-- Language & Timezone -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                        <div>
                            <label class="block font-bold text-[#111827] mb-2">Langue par Défaut du Système</label>
                            <select name="default_language" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-[#031C5B] focus:bg-white transition cursor-pointer">
                                <option value="fr" {{ ($settings['default_language'] ?? '') === 'fr' ? 'selected' : '' }}>Français (French)</option>
                                <option value="en" {{ ($settings['default_language'] ?? '') === 'en' ? 'selected' : '' }}>English (Anglais)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-[#111827] mb-2">Fuseau Horaire Global</label>
                            <select name="timezone" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-[#031C5B] focus:bg-white transition cursor-pointer">
                                <option value="Africa/Dakar" {{ ($settings['timezone'] ?? '') === 'Africa/Dakar' ? 'selected' : '' }}>GMT (UTC+0) - Dakar, Abidjan, Bamako</option>
                                <option value="Africa/Douala" {{ ($settings['timezone'] ?? '') === 'Africa/Douala' ? 'selected' : '' }}>WAT (UTC+1) - Douala, Yaoundé, Kinshasa</option>
                                <option value="Europe/Paris" {{ ($settings['timezone'] ?? '') === 'Europe/Paris' ? 'selected' : '' }}>CET (UTC+1) - Paris, Bruxelles</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SMTP Server Configuration (S'applique à TOUT LE PROJET) -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-900 text-white flex items-center justify-center shrink-0 shadow-sm font-bold">
                                <i class="ph ph-paper-plane-tilt text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-[20px] font-extrabold text-[#111827]">Configuration Serveur SMTP d'Envoi de Mails</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Appliqué dynamiquement à <span class="font-bold text-blue-900">l'ensemble du projet via AppServiceProvider</span></p>
                            </div>
                        </div>
                        <button type="button" onclick="openTestSmtpModal()" class="px-4 py-2 bg-blue-50 text-[#031C5B] hover:bg-[#031C5B] hover:text-white text-xs font-bold rounded-xl transition border border-blue-200 cursor-pointer flex items-center gap-1.5">
                            <i class="ph ph-envelope-simple font-bold"></i> Tester l'Envoi SMTP
                        </button>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-[#111827] mb-1.5">Hôte SMTP (Host) *</label>
                                <input type="text" name="smtp_host" value="{{ $settings['smtp_host'] ?? 'smtp.mailtrap.io' }}" required placeholder="ex: mail.academiaerp.com" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition font-mono">
                            </div>
                            <div>
                                <label class="block font-bold text-[#111827] mb-1.5">Port SMTP *</label>
                                <input type="number" name="smtp_port" value="{{ $settings['smtp_port'] ?? '587' }}" required placeholder="587, 465, 25" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition font-mono">
                            </div>
                            <div>
                                <label class="block font-bold text-[#111827] mb-1.5">Chiffrement (Encryption) *</label>
                                <select name="smtp_encryption" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-[#031C5B] focus:bg-white transition cursor-pointer">
                                    <option value="tls" {{ ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' }}>TLS (Recommandé - Port 587)</option>
                                    <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                    <option value="none" {{ ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' }}>Aucun chiffrement</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-[#111827] mb-1.5">Nom d'Utilisateur SMTP (Username) *</label>
                                <input type="text" name="smtp_username" value="{{ $settings['smtp_username'] ?? '' }}" required placeholder="ex: smtp_user@academia.com" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition font-mono">
                            </div>
                            <div>
                                <label class="block font-bold text-[#111827] mb-1.5">Mot de Passe SMTP (Password) *</label>
                                <div class="relative">
                                    <input type="password" name="smtp_password" value="{{ $settings['smtp_password'] ?? '' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl pl-3.5 pr-10 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition font-mono">
                                    <button type="button" onclick="togglePasswordVisibility(this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition cursor-pointer">
                                        <i class="ph ph-eye text-base font-bold"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block font-bold text-[#111827] mb-1.5">Adresse Email Expéditeur (From Address) *</label>
                                <input type="email" name="smtp_from_address" value="{{ $settings['smtp_from_address'] ?? 'noreply@academiaerp.com' }}" required placeholder="noreply@domain.com" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition">
                            </div>
                            <div>
                                <label class="block font-bold text-[#111827] mb-1.5">Nom d'Expéditeur (From Name) *</label>
                                <input type="text" name="smtp_from_name" value="{{ $settings['smtp_from_name'] ?? 'AcademiaERP Notification System' }}" required placeholder="AcademiaERP System" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API & Integrations -->
                <div class="bg-gradient-to-br from-[#F8F5FF] to-white border border-[#E9D5FF] rounded-2xl shadow-sm p-6 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-6 relative z-10 border-b border-purple-100 pb-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-[#E9D5FF] text-[#7C3AED] flex items-center justify-center shrink-0 shadow-sm font-bold border border-[#D8B4FE]">
                                <i class="ph ph-puzzle-piece text-xl"></i>
                            </div>
                            <div>
                                <h3 class="text-[20px] font-extrabold text-[#111827]">APIs & Clés d'Intégrations IA / Paiement</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Configuration des passerelles d'IA et de paiement</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6 relative z-10 text-xs">
                        <!-- OpenAI API Key -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="font-bold text-[#111827]">Clé API OpenAI / Gemini <span class="text-slate-500 font-medium">(Moteur IA Globale & Tuteur)</span></label>
                                <span class="inline-flex bg-[#A7F3D0] text-[#065F46] text-[10px] font-bold px-2 py-0.5 rounded-md">Connecté & Valide</span>
                            </div>
                            <div class="relative">
                                <i class="ph ph-key absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                                <input type="password" name="openai_api_key" value="{{ $settings['openai_api_key'] ?? '' }}" class="w-full bg-white border border-slate-200 text-slate-800 text-sm tracking-wider rounded-xl pl-10 pr-10 py-2.5 outline-none focus:border-[#7C3AED] transition shadow-xs font-mono">
                                <button type="button" onclick="togglePasswordVisibility(this)" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#7C3AED] hover:text-purple-800 transition cursor-pointer">
                                    <i class="ph ph-eye text-base font-bold"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Stripe Secret Key -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="font-bold text-[#111827]">Clé Secrète Stripe <span class="text-slate-500 font-medium">(Paiements Internationaux Carte CB)</span></label>
                            </div>
                            <div class="relative">
                                <i class="ph ph-credit-card absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                                <input type="password" name="stripe_secret_key" value="{{ $settings['stripe_secret_key'] ?? '' }}" class="w-full bg-white border border-slate-200 text-slate-800 text-sm tracking-wider rounded-xl pl-10 pr-4 py-2.5 outline-none focus:border-[#7C3AED] transition shadow-xs font-mono">
                            </div>
                        </div>

                        <!-- Orange Money / Mobile Money -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="font-bold text-[#111827]">Merchant Key Orange Money & Mobile Money <span class="text-slate-500 font-medium">(Passerelle Afrique)</span></label>
                                <span class="inline-flex bg-emerald-100 text-emerald-800 text-[10px] font-bold px-2 py-0.5 rounded-md">Actif</span>
                            </div>
                            <div class="relative">
                                <i class="ph ph-device-mobile absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                                <input type="text" name="orange_money_api" value="{{ $settings['orange_money_api'] ?? '' }}" placeholder="Cle marchand OM..." class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-xl pl-10 pr-4 py-2.5 outline-none focus:border-[#7C3AED] transition shadow-xs">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Right Column (Backup & Branding Palette) -->
            <div class="flex flex-col gap-6">
                
                <!-- System Backup Info -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-[#031C5B] text-white flex items-center justify-center shrink-0 shadow-sm font-bold">
                            <i class="ph ph-database text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-[18px] font-extrabold text-[#111827]">Sauvegardes Système</h3>
                            <p class="text-xs text-slate-500">Protection des données SaaS</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-center text-center mb-6 pt-2">
                        <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center mb-3">
                            <i class="ph ph-check-circle text-3xl text-emerald-600 font-bold"></i>
                        </div>
                        <h4 class="text-xs font-bold text-[#111827] mb-1">Dernière Sauvegarde Automatisée</h4>
                        <p class="text-xs text-slate-500 font-semibold font-mono">
                            {{ $settings['last_automated_backup'] ?? 'Aujourd\'hui' }}
                        </p>
                    </div>

                    <a href="{{ route('superadmin.backups') }}" class="w-full flex items-center justify-center gap-2 bg-[#031C5B] text-white px-4 py-3 rounded-xl text-xs font-bold hover:bg-blue-900 transition shadow-sm">
                        <i class="ph ph-cloud-arrow-up text-base font-bold"></i> Déclencher / Gérer les Sauvegardes
                    </a>
                </div>

                <!-- Global Branding & Palette de Couleurs -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-[#7C3AED] text-white flex items-center justify-center shrink-0 shadow-sm font-bold">
                            <i class="ph ph-palette text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-[18px] font-extrabold text-[#111827]">Identité Visuelle & Palette de Couleurs</h3>
                            <p class="text-xs text-slate-500">Branding & thème global de l'application</p>
                        </div>
                    </div>

                    <div class="mb-6 text-xs">
                        <h4 class="font-bold text-[#111827] mb-3">Palette de Couleurs Pre-définies</h4>
                        <input type="hidden" name="primary_theme_color" id="primaryThemeColorInput" value="{{ $settings['primary_theme_color'] ?? '#031C5B' }}">
                        
                        <div class="grid grid-cols-3 gap-3 mb-4">
                            <!-- Preset 1: Navy -->
                            <button type="button" onclick="selectThemeColor(this, '#031C5B')" class="theme-palette-card relative p-2.5 rounded-xl border-2 {{ ($settings['primary_theme_color'] ?? '') === '#031C5B' ? 'border-[#031C5B] bg-blue-50/50' : 'border-slate-200' }} flex flex-col items-center gap-1.5 cursor-pointer hover:border-[#031C5B] transition">
                                <div class="w-8 h-8 rounded-full bg-[#031C5B] shadow-2xs"></div>
                                <span class="text-[10px] font-bold text-slate-800">Navy Blue</span>
                            </button>

                            <!-- Preset 2: Purple -->
                            <button type="button" onclick="selectThemeColor(this, '#7C3AED')" class="theme-palette-card relative p-2.5 rounded-xl border-2 {{ ($settings['primary_theme_color'] ?? '') === '#7C3AED' ? 'border-[#7C3AED] bg-purple-50/50' : 'border-slate-200' }} flex flex-col items-center gap-1.5 cursor-pointer hover:border-[#7C3AED] transition">
                                <div class="w-8 h-8 rounded-full bg-[#7C3AED] shadow-2xs"></div>
                                <span class="text-[10px] font-bold text-slate-800">Purple IA</span>
                            </button>

                            <!-- Preset 3: Emerald -->
                            <button type="button" onclick="selectThemeColor(this, '#059669')" class="theme-palette-card relative p-2.5 rounded-xl border-2 {{ ($settings['primary_theme_color'] ?? '') === '#059669' ? 'border-[#059669] bg-emerald-50/50' : 'border-slate-200' }} flex flex-col items-center gap-1.5 cursor-pointer hover:border-[#059669] transition">
                                <div class="w-8 h-8 rounded-full bg-[#059669] shadow-2xs"></div>
                                <span class="text-[10px] font-bold text-slate-800">Émeraude</span>
                            </button>

                            <!-- Preset 4: Crimson Red -->
                            <button type="button" onclick="selectThemeColor(this, '#DC2626')" class="theme-palette-card relative p-2.5 rounded-xl border-2 {{ ($settings['primary_theme_color'] ?? '') === '#DC2626' ? 'border-[#DC2626] bg-red-50/50' : 'border-slate-200' }} flex flex-col items-center gap-1.5 cursor-pointer hover:border-[#DC2626] transition">
                                <div class="w-8 h-8 rounded-full bg-[#DC2626] shadow-2xs"></div>
                                <span class="text-[10px] font-bold text-slate-800">Crimson</span>
                            </button>

                            <!-- Preset 5: Amber Orange -->
                            <button type="button" onclick="selectThemeColor(this, '#D97706')" class="theme-palette-card relative p-2.5 rounded-xl border-2 {{ ($settings['primary_theme_color'] ?? '') === '#D97706' ? 'border-[#D97706] bg-amber-50/50' : 'border-slate-200' }} flex flex-col items-center gap-1.5 cursor-pointer hover:border-[#D97706] transition">
                                <div class="w-8 h-8 rounded-full bg-[#D97706] shadow-2xs"></div>
                                <span class="text-[10px] font-bold text-slate-800">Ambre</span>
                            </button>

                            <!-- Preset 6: Slate Dark -->
                            <button type="button" onclick="selectThemeColor(this, '#0F172A')" class="theme-palette-card relative p-2.5 rounded-xl border-2 {{ ($settings['primary_theme_color'] ?? '') === '#0F172A' ? 'border-[#0F172A] bg-slate-100' : 'border-slate-200' }} flex flex-col items-center gap-1.5 cursor-pointer hover:border-[#0F172A] transition">
                                <div class="w-8 h-8 rounded-full bg-[#0F172A] shadow-2xs"></div>
                                <span class="text-[10px] font-bold text-slate-800">Slate Dark</span>
                            </button>
                        </div>

                        <!-- Custom Hex Picker -->
                        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                            <label class="font-bold text-slate-700">Couleur Personnalisée (Hex)</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="hexPicker" value="{{ $settings['primary_theme_color'] ?? '#031C5B' }}" onchange="applyCustomHex(this.value)" class="w-8 h-8 rounded-lg border border-slate-300 cursor-pointer p-0.5">
                                <span id="hexCodeDisplay" class="font-mono font-bold text-slate-800 uppercase bg-slate-100 px-2.5 py-1 rounded-md text-[11px]">{{ $settings['primary_theme_color'] ?? '#031C5B' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Fixed Bottom Actions Bar -->
        <div class="fixed bottom-0 left-0 lg:left-64 right-0 bg-[#F8FAFC]/95 backdrop-blur-xs border-t border-slate-200 p-4 z-40">
            <div class="max-w-7xl mx-auto flex items-center justify-end gap-4">
                <a href="{{ route('superadmin.global-settings') }}" class="px-6 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-xs font-bold hover:bg-white transition shadow-2xs">
                    Annuler les modifications
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2 cursor-pointer">
                    <i class="ph ph-check-circle text-base font-bold"></i> Enregistrer les Paramètres (BD SQL)
                </button>
            </div>
        </div>
    </form>

    <!-- Modal : Test SMTP Email -->
    <div id="testSmtpModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center font-bold">
                        <i class="ph ph-paper-plane-tilt text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Tester l'Envoi SMTP Globale</h3>
                        <p class="text-xs text-blue-200 font-medium">Test de transmission sur tout le projet</p>
                    </div>
                </div>
                <button type="button" onclick="closeTestSmtpModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.global-settings.test-smtp') }}" method="POST" class="p-6 space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Adresse Email Destinataire du Test *</label>
                    <input type="email" name="test_email" required value="admin@academiaerp.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-slate-900 font-bold focus:outline-none focus:border-blue-600 focus:bg-white transition">
                </div>

                <p class="text-[11px] text-slate-500 leading-snug">
                    Un email de test sera transmis en utilisant les paramètres SMTP (Hôte: <span class="font-mono font-bold text-slate-800">{{ $settings['smtp_host'] }}</span>, Port: <span class="font-mono font-bold text-slate-800">{{ $settings['smtp_port'] }}</span>) configurés globalement.
                </p>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeTestSmtpModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 font-bold">Annuler</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#031C5B] text-white font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-1.5">
                        <i class="ph ph-paper-plane-right font-bold"></i> Envoyer l'Email de Test
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(btn) {
            const input = btn.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                btn.innerHTML = '<i class="ph ph-eye-slash text-base font-bold"></i>';
            } else {
                input.type = 'password';
                btn.innerHTML = '<i class="ph ph-eye text-base font-bold"></i>';
            }
        }

        function selectThemeColor(btn, color) {
            document.querySelectorAll('.theme-palette-card').forEach(b => {
                b.className = 'theme-palette-card relative p-2.5 rounded-xl border-2 border-slate-200 flex flex-col items-center gap-1.5 cursor-pointer hover:border-slate-400 transition';
            });
            btn.className = 'theme-palette-card relative p-2.5 rounded-xl border-2 border-blue-600 bg-blue-50/40 flex flex-col items-center gap-1.5 cursor-pointer transition';
            
            document.getElementById('primaryThemeColorInput').value = color;
            document.getElementById('hexPicker').value = color;
            document.getElementById('hexCodeDisplay').innerText = color.toUpperCase();
            
            // Live update --primary-color CSS variable on page
            document.documentElement.style.setProperty('--primary-color', color);
        }

        function applyCustomHex(color) {
            document.getElementById('primaryThemeColorInput').value = color;
            document.getElementById('hexCodeDisplay').innerText = color.toUpperCase();
            
            // Live update --primary-color CSS variable on page
            document.documentElement.style.setProperty('--primary-color', color);
        }

        function openTestSmtpModal() {
            const modal = document.getElementById('testSmtpModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeTestSmtpModal() {
            const modal = document.getElementById('testSmtpModal');
            if (modal) modal.classList.add('hidden');
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeTestSmtpModal();
        });
    </script>
@endsection
