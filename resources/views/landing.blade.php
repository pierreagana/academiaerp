<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings['platform_name'] }} - Solution SaaS ERP & IA de Gestion Scolaire Multi-Campus</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: {{ $settings['primary_theme_color'] ?? '#031C5B' }};
        }
        body { font-family: 'Poppins', sans-serif; background-color: #FAFAFC; }
        
        .bg-primary-dynamic { background-color: var(--primary-color) !important; }
        .text-primary-dynamic { color: var(--primary-color) !important; }
        .border-primary-dynamic { border-color: var(--primary-color) !important; }

        /* Keyframes for Floating & Pulsing Animations */
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(0.5deg); }
        }
        @keyframes floatReverse {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(10px) rotate(-0.5deg); }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 15px rgba(3, 28, 91, 0.2); }
            50% { box-shadow: 0 0 30px rgba(124, 58, 237, 0.4); }
        }
        @keyframes shimmer {
            100% { transform: translateX(100%); }
        }

        .animate-float { animation: floatSlow 6s ease-in-out infinite; }
        .animate-float-reverse { animation: floatReverse 7s ease-in-out infinite; }
        .animate-pulse-glow { animation: pulseGlow 4s ease-in-out infinite; }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
    </style>
    @include('SchoolDashboard::components.searchable-select')
</head>
<body class="text-slate-800 antialiased selection:bg-blue-600 selection:text-white overflow-x-hidden">

    <!-- ================= NAVBAR ================= -->
    <header class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200/80 transition-all">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="{{ route('landing') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-primary-dynamic text-white flex items-center justify-center font-extrabold text-xl shadow-md group-hover:scale-105 transition transform">
                    <i class="ph ph-graduation-cap"></i>
                </div>
                <div class="flex flex-col">
                    <span class="text-xl font-extrabold tracking-tight text-slate-900 leading-none">Academia<span class="text-primary-dynamic">ERP</span></span>
                    <span class="text-[10px] font-bold text-slate-400 tracking-widest uppercase mt-0.5">SaaS Platform</span>
                </div>
            </a>

            <!-- Navigation Links -->
            <nav class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="#forfaits" class="hover:text-primary-dynamic transition">Forfaits & Tarifs</a>
                <a href="#modules" class="hover:text-primary-dynamic transition">Catalogue Modules</a>
                <a href="#fonctionnalites" class="hover:text-primary-dynamic transition">Avantages ERP & IA</a>
                <a href="#temoignages" class="hover:text-primary-dynamic transition">Témoignages</a>
                <a href="#faq" class="hover:text-primary-dynamic transition">FAQ</a>
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-4">
                <a href="{{ route('login') }}" class="hidden sm:flex items-center gap-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 px-4 py-2.5 rounded-xl transition">
                    <i class="ph ph-user-circle text-base"></i> Connexion
                </a>
                <button onclick="openDemoModal()" class="flex items-center gap-2 text-xs font-bold text-white bg-primary-dynamic hover:opacity-95 px-5 py-2.5 rounded-xl shadow-sm transition transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="ph ph-paper-plane-tilt text-base font-bold"></i> Essai Gratuit (30 Jours)
                </button>
            </div>
        </div>
    </header>

    <!-- ================= HERO SECTION WITH ANIMATED ILLUSTRATION ================= -->
    <section class="pt-36 pb-24 relative overflow-hidden bg-gradient-to-b from-blue-50/60 via-white to-slate-50">
        <!-- Glow Orbs -->
        <div class="absolute top-10 left-1/4 w-96 h-96 bg-blue-300/30 rounded-full blur-3xl pointer-events-none animate-pulse"></div>
        <div class="absolute top-40 right-10 w-96 h-96 bg-purple-300/30 rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Left Column Text Content -->
                <div class="lg:col-span-6 text-center lg:text-left">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 bg-blue-100/90 border border-blue-200 px-4 py-1.5 rounded-full text-xs font-bold text-blue-900 mb-6 shadow-2xs">
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-600"></span>
                        </span>
                        {{ $settings['cms_hero_badge'] ?? 'La Référence SaaS ERP & IA de Gestion Scolaire Multi-Campus' }}
                    </div>

                    <!-- Main Title -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.15] mb-6">
                        {!! str_replace('Intendance & IA', '<span class="text-primary-dynamic">Intendance & IA</span>', e($settings['cms_hero_headline'] ?? 'Pilotez Vos Établissements avec Intendance & IA')) !!}
                    </h1>

                    <!-- Description -->
                    <p class="text-base sm:text-lg text-slate-600 font-medium leading-relaxed mb-8 max-w-xl mx-auto lg:mx-0">
                        {{ $settings['cms_hero_subtitle'] ?? 'De la gestion des inscriptions et du calcul des bulletins au paiement Mobile Money (Orange Money, Wave), en passant par la cantine, l\'infirmerie et le suivi GPS des bus.' }}
                    </p>

                    <!-- Call to action buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 mb-10">
                        <button onclick="openDemoModal()" class="w-full sm:w-auto flex items-center justify-center gap-2.5 text-sm font-bold text-white bg-primary-dynamic hover:opacity-95 px-7 py-4 rounded-2xl shadow-lg transition transform hover:-translate-y-0.5 cursor-pointer">
                            <i class="ph ph-rocket-launch text-lg font-bold"></i> {{ $settings['cms_hero_primary_cta'] ?? 'Essai Gratuit (30 Jours)' }}
                        </button>
                        <a href="#modules" class="w-full sm:w-auto flex items-center justify-center gap-2 text-sm font-bold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 px-7 py-4 rounded-2xl shadow-2xs transition">
                            <i class="ph ph-squares-four text-lg text-slate-500"></i> {{ $settings['cms_hero_secondary_cta'] ?? 'Explorer les 12 Modules' }}
                        </a>
                    </div>

                    <!-- Key Trust Indicators -->
                    <div class="flex items-center justify-center lg:justify-start gap-6 text-xs font-bold text-slate-500">
                        <span class="flex items-center gap-1.5"><i class="ph ph-check-circle-fill text-emerald-500 text-base"></i> Activation Immédiate</span>
                        <span class="flex items-center gap-1.5"><i class="ph ph-check-circle-fill text-emerald-500 text-base"></i> 100% Mobile Money</span>
                    </div>
                </div>

                <!-- Right Column Animated Illustration Mockup -->
                <div class="lg:col-span-6 relative">
                    <div class="relative mx-auto max-w-lg lg:max-w-none">
                        <!-- Floating Main Dashboard Illustration -->
                        <div class="relative z-10 rounded-3xl overflow-hidden shadow-2xl border-4 border-white/80 bg-white animate-float">
                            <img src="{{ asset(ltrim($settings['cms_hero_image'] ?? '/images/hero_dashboard.png', '/')) }}" alt="AcademiaERP Dashboard Illustration" class="w-full h-auto object-cover transform hover:scale-105 transition duration-500">
                        </div>

                        <!-- Floating Badge 1 (Mobile Payments) -->
                        <div class="absolute -top-6 -left-6 z-20 bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3 animate-float-reverse">
                            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xl">
                                <i class="ph ph-check-circle"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Paiement Reçu (Orange Money)</p>
                                <p class="text-[11px] font-semibold text-emerald-600">+ 150 000 FCFA • Frais Scolarité</p>
                            </div>
                        </div>

                        <!-- Floating Badge 2 (AI Student Alert) -->
                        <div class="absolute -bottom-6 -right-6 z-20 bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-xl border border-slate-100 flex items-center gap-3 animate-float">
                            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xl">
                                <i class="ph ph-sparkle"></i>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-900">Assistant IA EduAnalytics</p>
                                <p class="text-[11px] font-semibold text-purple-700">Taux de Réussite Prédictif : 94.8%</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- KPI Counters Bar -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pt-16 mt-16 border-t border-slate-200/80">
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs text-center transform hover:-translate-y-1 transition">
                    <p class="text-3xl font-extrabold text-slate-900">{{ $settings['cms_stat_schools'] ?? '48+' }}</p>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Établissements Actifs</p>
                </div>
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs text-center transform hover:-translate-y-1 transition">
                    <p class="text-3xl font-extrabold text-primary-dynamic">{{ $settings['cms_stat_students'] ?? '12 500+' }}</p>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Élèves & Apprenants Gérés</p>
                </div>
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs text-center transform hover:-translate-y-1 transition">
                    <p class="text-3xl font-extrabold text-emerald-600">{{ $settings['cms_stat_mobile_money'] ?? '100%' }}</p>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Mobile Money (OM, Wave, MTN)</p>
                </div>
                <div class="p-5 bg-white rounded-2xl border border-slate-200/80 shadow-2xs text-center transform hover:-translate-y-1 transition">
                    <p class="text-3xl font-extrabold text-blue-900">{{ $settings['cms_stat_sla'] ?? '99.9%' }}</p>
                    <p class="text-xs font-semibold text-slate-500 mt-1">Garantie Disponibilité SLA</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ================= FORFAITS SAAS & TARIFS (PRICING) ================= -->
    <section id="forfaits" class="py-24 bg-white border-y border-slate-200/60 relative">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-primary-dynamic mb-3">Grille Tarifaire SaaS</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">{{ $settings['cms_pricing_title'] ?? 'Des Forfaits Transparents Adaptés à Votre Établissement' }}</h3>
                <p class="text-slate-500 text-sm font-medium mt-3">{{ $settings['cms_pricing_subtitle'] ?? 'Facturation annuelle claire avec mises à jour et sauvegarde cloud incluses.' }}</p>
            </div>

            <!-- Pricing Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
                @foreach($packages as $package)
                <div class="relative bg-slate-50/70 rounded-3xl border {{ $package->is_popular ? 'border-primary-dynamic ring-4 ring-primary-dynamic/15 shadow-2xl bg-white lg:-translate-y-3 z-10' : 'border-slate-200 shadow-sm' }} p-8 flex flex-col justify-between transition hover:shadow-xl">
                    
                    @if($package->is_popular)
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-primary-dynamic text-white text-[11px] font-extrabold uppercase tracking-wider px-4 py-1.5 rounded-full shadow-md">
                        ⭐ Recommandé / Le plus Populaire
                    </div>
                    @endif

                    <div>
                        <div class="flex items-center justify-between mb-4 pt-2">
                            <h4 class="text-xl font-extrabold text-slate-900">{{ $package->name }}</h4>
                            <span class="text-xs font-bold text-slate-600 bg-slate-200/80 px-3 py-1 rounded-lg">
                                {{ $package->max_students > 0 ? $package->max_students . ' Élèves max' : 'Élèves Illimités' }}
                            </span>
                        </div>

                        <!-- Price -->
                        <div class="mb-6 pb-6 border-b border-slate-200">
                            <span class="text-3xl sm:text-4xl font-extrabold text-slate-900">{{ number_format($package->price, 0, ',', ' ') }}</span>
                            <span class="text-sm font-semibold text-slate-500">FCFA / an</span>
                            <p class="text-xs font-medium text-slate-400 mt-1.5">Mises à jour & Sauvegardes incluses</p>
                        </div>

                        <!-- Feature Checklist -->
                        <ul class="space-y-3.5 mb-8 text-xs font-semibold text-slate-700">
                            @if(!empty($package->features))
                                @foreach($package->features as $feature)
                                <li class="flex items-start gap-3">
                                    <i class="ph ph-check-circle text-emerald-500 text-base shrink-0 font-bold"></i>
                                    <span>{{ $feature }}</span>
                                </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>

                    <button onclick="selectPackage('{{ $package->name }}')" class="w-full py-4 px-4 rounded-xl text-xs font-bold transition shadow-sm cursor-pointer transform hover:-translate-y-0.5 {{ $package->is_popular ? 'bg-primary-dynamic text-white hover:opacity-95' : 'bg-white border border-slate-300 text-slate-800 hover:bg-slate-100' }}">
                        Sélectionner le Forfait {{ $package->name }}
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ================= CATALOGUE DES MODULES SAAS ================= -->
    <section id="modules" class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-primary-dynamic mb-3">Catalogue des Modules & Add-ons</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">{{ $settings['cms_modules_title'] ?? 'Une Suite Complète de 12 Modules Intégrés' }}</h3>
                <p class="text-slate-500 text-sm font-medium mt-3">{{ $settings['cms_modules_subtitle'] ?? 'Découvrez tous les modules spécialisés activables à la carte pour chaque établissement.' }}</p>
            </div>

            <!-- Modules Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($catalogItems as $index => $item)
                <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-2xs hover:shadow-lg transition transform hover:-translate-y-1 flex flex-col justify-between group {{ $index >= 6 ? 'hidden module-extra-card' : '' }}">
                    <div>
                        <div class="mb-4">
                            <div class="w-12 h-12 rounded-2xl {{ $item->icon_bg }} flex items-center justify-center text-2xl font-bold shadow-2xs group-hover:scale-110 transition transform">
                                <i class="ph {{ $item->icon }}"></i>
                            </div>
                        </div>
                        <h4 class="text-base font-extrabold text-slate-900 mb-2 group-hover:text-primary-dynamic transition">{{ $item->name }}</h4>
                        <p class="text-xs font-medium text-slate-500 leading-relaxed">{{ $item->description }}</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-end text-[11px] font-bold text-slate-400">
                        <i class="ph ph-arrow-right text-slate-400 group-hover:translate-x-1 transition font-bold"></i>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Voir Plus Button -->
            @if(count($catalogItems) > 6)
            <div class="mt-12 text-center">
                <button type="button" onclick="toggleExtraModules()" id="toggleModulesBtn" class="inline-flex items-center gap-2.5 bg-white border border-slate-300 hover:border-primary-dynamic text-slate-800 hover:text-primary-dynamic font-extrabold text-xs px-8 py-3.5 rounded-2xl shadow-2xs hover:shadow-md transition cursor-pointer transform hover:-translate-y-0.5">
                    <span id="toggleModulesText">Voir plus de modules ({{ count($catalogItems) - 6 }} restants)</span>
                    <i id="toggleModulesIcon" class="ph ph-caret-down text-base font-bold text-primary-dynamic"></i>
                </button>
            </div>
            @endif
        </div>
    </section>

    <!-- ================= POURQUOI CHOISIR ACADEMIAERP (ILLUSTRATED BLOCKS) ================= -->
    <section id="fonctionnalites" class="py-24 bg-white border-t border-slate-200/60">
        <div class="max-w-7xl mx-auto px-6 space-y-24">
            
            <!-- Block 1: Mobile Money Payment Illustration -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-6">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-primary-dynamic mb-3">Passerelle de Paiement Afrique</h2>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-6">
                        {{ $settings['cms_feature1_title'] ?? 'Encaissement Automatisé par Mobile Money (Orange Money & Wave)' }}
                    </h3>
                    <p class="text-sm font-medium text-slate-600 leading-relaxed mb-6">
                        {{ $settings['cms_feature1_desc'] ?? 'Dites adieu aux longues files d\'attente lors des rentrées scolaires. Les parents règlent directement les échéances de scolarité depuis leur téléphone portable.' }}
                    </p>

                    <ul class="space-y-3 text-xs font-bold text-slate-700 mb-8">
                        <li class="flex items-center gap-3"><i class="ph ph-check-circle-fill text-emerald-500 text-lg"></i> Génération automatique des reçus digitaux de paiement</li>
                        <li class="flex items-center gap-3"><i class="ph ph-check-circle-fill text-emerald-500 text-lg"></i> Notification SMS / WhatsApp instantanée transmise au tuteur</li>
                        <li class="flex items-center gap-3"><i class="ph ph-check-circle-fill text-emerald-500 text-lg"></i> Réconciliation bancaire comptable automatique sans erreur humaine</li>
                    </ul>

                    <button onclick="openDemoModal()" class="px-6 py-3.5 rounded-xl bg-primary-dynamic text-white font-bold text-xs shadow-sm hover:opacity-95 transition cursor-pointer flex items-center gap-2">
                        <i class="ph ph-device-mobile text-base"></i> Découvrir le Module Finance
                    </button>
                </div>

                <div class="lg:col-span-6">
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-slate-100 bg-white animate-float">
                        <img src="{{ asset(ltrim($settings['cms_feature1_image'] ?? '/images/mobile_payment.png', '/')) }}" alt="Mobile Money Payment Illustration" class="w-full h-auto object-cover transform hover:scale-105 transition duration-500">
                    </div>
                </div>
            </div>

            <!-- Block 2: AI EduAnalytics Illustration -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <div class="lg:col-span-6 order-2 lg:order-1">
                    <div class="relative rounded-3xl overflow-hidden shadow-2xl border-4 border-slate-100 bg-white animate-float-reverse">
                        <img src="{{ asset(ltrim($settings['cms_feature2_image'] ?? '/images/ai_analytics.png', '/')) }}" alt="AI EduAnalytics Illustration" class="w-full h-auto object-cover transform hover:scale-105 transition duration-500">
                    </div>
                </div>

                <div class="lg:col-span-6 order-1 lg:order-2">
                    <h2 class="text-xs font-extrabold uppercase tracking-widest text-purple-700 mb-3">Intelligence Artificielle Éducative</h2>
                    <h3 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight leading-tight mb-6">
                        {{ $settings['cms_feature2_title'] ?? 'Détection Prédictive du Décrochage Scolaire par l\'IA' }}
                    </h3>
                    <p class="text-sm font-medium text-slate-600 leading-relaxed mb-6">
                        {{ $settings['cms_feature2_desc'] ?? 'Nos algorithmes d\'IA analysent l\'évolution des notes et l\'assiduité des élèves pour signaler précocement les risques de décrochage à la direction pédagogique.' }}
                    </p>

                    <ul class="space-y-3 text-xs font-bold text-slate-700 mb-8">
                        <li class="flex items-center gap-3"><i class="ph ph-check-circle-fill text-purple-600 text-lg"></i> Alertes prédictives personnalisées envoyées aux enseignants</li>
                        <li class="flex items-center gap-3"><i class="ph ph-check-circle-fill text-purple-600 text-lg"></i> Recommandations de remédiation adaptées au profil de l'élève</li>
                        <li class="flex items-center gap-3"><i class="ph ph-check-circle-fill text-purple-600 text-lg"></i> Tuteur virtuel IA pour l'aide aux devoirs à domicile</li>
                    </ul>

                    <button onclick="openDemoModal()" class="px-6 py-3.5 rounded-xl bg-purple-700 text-white font-bold text-xs shadow-sm hover:bg-purple-800 transition cursor-pointer flex items-center gap-2">
                        <i class="ph ph-sparkle text-base"></i> Découvrir l'Assistant IA
                    </button>
                </div>
            </div>

        </div>
    </section>

    <!-- ================= TESTIMONIALS SECTION ================= -->
    <section id="temoignages" class="py-24 bg-gradient-to-b from-slate-900 to-slate-800 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-blue-400 mb-3">Témoignages & Avis Clients</h2>
                <h3 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">Ce que Disent les Fondateurs & Directeurs</h3>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @if(!empty($testimonials))
                    @foreach($testimonials as $t)
                    <div class="bg-slate-800/90 border border-slate-700/80 rounded-3xl p-8 shadow-xl flex flex-col justify-between">
                        <p class="text-sm font-medium text-slate-300 leading-relaxed italic mb-6">« {{ $t['quote'] }} »</p>
                        <div class="flex items-center justify-between border-t border-slate-700/80 pt-4">
                            <div>
                                <h4 class="text-sm font-bold text-white">{{ $t['name'] }}</h4>
                                <p class="text-xs font-semibold text-blue-400 mt-0.5">{{ $t['role'] }} — {{ $t['school'] }}</p>
                            </div>
                            <div class="text-amber-400 text-sm">★★★★★</div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>

    <!-- ================= FAQ SECTION ================= -->
    <section id="faq" class="py-24 bg-slate-50">
        <div class="max-w-4xl mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-primary-dynamic mb-3">Questions Fréquentes</h2>
                <h3 class="text-3xl font-extrabold text-slate-900">Tout ce que Vous Devez Savoir</h3>
            </div>

            <div class="space-y-4">
                @if(!empty($faqItems))
                    @foreach($faqItems as $faq)
                    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-2xs transition hover:shadow-md">
                        <h4 class="text-base font-extrabold text-slate-900 mb-2">{{ $faq['question'] }}</h4>
                        <p class="text-xs text-slate-500 leading-relaxed font-medium">{{ $faq['answer'] }}</p>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>

    <!-- ================= FOOTER ================= -->
    <footer class="bg-slate-900 text-white py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6 text-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-primary-dynamic flex items-center justify-center text-white font-bold">
                    <i class="ph ph-graduation-cap text-lg"></i>
                </div>
                <span class="font-extrabold text-sm">AcademiaERP SaaS</span>
            </div>
            <p class="text-slate-400 font-medium">© {{ date('Y') }} {{ $settings['platform_name'] }}. Tous droits réservés. Support: {{ $settings['support_email'] }} | {{ $settings['support_phone'] }}</p>
            <div class="flex items-center gap-4 text-slate-400 font-semibold">
                <a href="{{ route('login') }}" class="hover:text-white transition">Connexion</a>
                <a href="#forfaits" class="hover:text-white transition">Offres</a>
                <a href="#modules" class="hover:text-white transition">Modules</a>
            </div>
        </div>
    </footer>

    <!-- ================= MODAL DEMANDER UNE DÉMO ================= -->
    <div id="demoModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-5xl bg-white rounded-3xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-8 py-6 bg-primary-dynamic text-white flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold">Demande de Démo & Essai Gratuit</h3>
                    <p class="text-xs text-blue-100 mt-0.5">Testez AcademiaERP sans engagement pendant 30 jours</p>
                </div>
                <button onclick="closeDemoModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="p-6 md:p-8 space-y-4 text-xs font-semibold text-slate-700 max-h-[80vh] overflow-y-auto" id="demoFormScroll">
                @csrf

                @if ($errors->any())
                <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-[12.5px] space-y-1">
                    @foreach ($errors->all() as $error)
                        <p class="flex items-start gap-2"><i class="ph ph-warning-circle mt-0.5"></i> {{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <!-- Step Indicator -->
                <div class="flex items-center gap-3 pb-2">
                    <div class="flex items-center gap-2" id="demoStepDot1">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-primary-dynamic text-white">1</span>
                        <span class="text-slate-900">Vos Informations</span>
                    </div>
                    <div class="flex-1 h-px bg-slate-200"></div>
                    <div class="flex items-center gap-2" id="demoStepDot2">
                        <span class="w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-slate-200 text-slate-500" id="demoStepDot2Circle">2</span>
                        <span class="text-slate-400" id="demoStepDot2Label">Détails de l'Établissement</span>
                    </div>
                </div>

                <!-- Étape 1 : Vos Informations -->
                <div id="demoStep1">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block mb-1 font-bold">Nom de l'Établissement *</label>
                        <input type="text" id="demoSchoolName" name="school_name" value="{{ old('school_name') }}" required placeholder="ex: Groupe Scolaire Excellence" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block mb-1 font-bold">Nom du Responsable *</label>
                        <input type="text" id="demoContactName" name="name" value="{{ old('name') }}" required placeholder="ex: M. Diallo" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block mb-1 font-bold">Téléphone *</label>
                        @include('SchoolDashboard::components.phone-input', [
                            'required' => true,
                            'selectClass' => 'w-[130px] bg-slate-50 border border-slate-200 rounded-xl px-2 py-3 outline-none focus:border-blue-600 focus:bg-white transition cursor-pointer text-xs font-semibold text-slate-700',
                            'inputClass' => 'flex-1 bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition',
                        ])
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1 font-bold">Email Officiel *</label>
                        <input type="email" id="demoEmail" name="email" value="{{ old('email') }}" required placeholder="direction@ecole.com" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>

                    <div class="md:col-span-2 p-3 rounded-xl bg-blue-50 border border-blue-100 text-blue-700 text-[12px] font-semibold flex items-start gap-2">
                        <i class="ph ph-shield-check mt-0.5"></i>
                        Vos identifiants de connexion (mot de passe généré automatiquement) vous seront envoyés à cette adresse email.
                    </div>
                </div>
                </div>

                <!-- Étape 2 : Détails de l'Établissement -->
                <div id="demoStep2" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-bold">Forfait Souhaité</label>
                        <select id="demoPackage" name="plan_name" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition cursor-pointer">
                            <option value="Pro Excellence" {{ old('plan_name') === 'Pro Excellence' ? 'selected' : '' }}>Pro Excellence (Recommandé)</option>
                            <option value="Starter" {{ old('plan_name') === 'Starter' ? 'selected' : '' }}>Starter (Petit Établissement)</option>
                            <option value="Enterprise Multi-Campus" {{ old('plan_name') === 'Enterprise Multi-Campus' ? 'selected' : '' }}>Enterprise Multi-Campus</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-bold">Type d'Établissement</label>
                        <select id="demoType" name="type" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition cursor-pointer">
                            @foreach(['Secondaire (Lycée)', 'Collège', 'Primaire', 'Complexe Scolaire'] as $typeOpt)
                                <option value="{{ $typeOpt }}" {{ old('type') === $typeOpt ? 'selected' : '' }}>{{ $typeOpt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-bold">Secteur / Statut</label>
                        <select id="demoSector" name="sector" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition cursor-pointer">
                            @foreach($availableSectors ?? ['Privé', 'Public', 'Semi-privé'] as $sectorOpt)
                                <option value="{{ $sectorOpt }}" {{ old('sector') === $sectorOpt ? 'selected' : '' }}>{{ $sectorOpt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-bold">Régime Linguistique</label>
                        <select id="demoLanguageRegime" name="language_regime" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition cursor-pointer">
                            @foreach($availableLanguageRegimes ?? ['Monolingue (Français)', 'Bilingue (Français / Anglais)', 'International / Trilingue'] as $langOpt)
                                <option value="{{ $langOpt }}" {{ old('language_regime') === $langOpt ? 'selected' : '' }}>{{ $langOpt }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-1 font-bold">Nombre d'Élèves Estimé</label>
                        <input type="number" id="demoStudentsCount" name="students_count" value="{{ old('students_count') }}" min="0" placeholder="ex: 850" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1.5 font-bold flex items-center justify-between">
                            <span>Niveaux &amp; Cycles d'Enseignement</span>
                            <span class="text-[11px] font-normal text-slate-400">Sélectionnez les ordres dispensés</span>
                        </label>
                        <div class="flex flex-wrap gap-2 p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                            @foreach($availableLevels ?? ['Préscolaire', 'Primaire', 'Collège', 'Lycée'] as $lvl)
                                <label class="flex items-center gap-2 px-3 py-1.5 bg-white border border-slate-200/80 rounded-lg cursor-pointer hover:border-blue-600 text-xs font-bold text-slate-700 transition">
                                    <input type="checkbox" name="levels[]" value="{{ $lvl }}" {{ in_array($lvl, old('levels', [])) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-600">
                                    <span>{{ $lvl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block mb-1.5 font-bold flex items-center justify-between">
                            <span>Équipements &amp; Services Scolaires</span>
                            <span class="text-[11px] font-normal text-slate-400">Sélectionnez les commodités</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto p-3 bg-slate-50 border border-slate-200 rounded-xl">
                            @foreach($facilities ?? [] as $facility)
                                <label class="flex items-center gap-2 p-2 bg-white border border-slate-200/70 rounded-lg cursor-pointer hover:border-blue-600 transition text-xs font-semibold text-slate-800">
                                    <input type="checkbox" name="facilities[]" value="{{ $facility->id }}" {{ in_array($facility->id, old('facilities', [])) ? 'checked' : '' }} class="rounded text-blue-600 focus:ring-blue-600">
                                    <i class="ph {{ $facility->icon }} text-base text-blue-600 shrink-0"></i>
                                    <span class="truncate">{{ $facility->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block mb-1 font-bold">Slogan</label>
                        <input type="text" id="demoSlogan" name="slogan" value="{{ old('slogan') }}" placeholder="Votre slogan" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block mb-1 font-bold">Logo</label>
                        <input type="file" id="demoLogo" name="logo" accept="image/*" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-[9px] outline-none focus:border-blue-600 focus:bg-white transition file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:bg-slate-200 file:text-slate-700">
                    </div>

                    <div class="md:col-span-2 relative">
                        <label class="block mb-1 font-bold">Adresse / Position géographique</label>
                        <input type="text" id="demoAddressSearch" name="address" value="{{ old('address') }}" placeholder="Rechercher une ville, une rue..." autocomplete="off" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                        <ul id="demoAutocompleteResults" class="absolute z-[100] w-full bg-white border border-slate-200 rounded-lg shadow-xl mt-1 hidden max-h-48 overflow-y-auto"></ul>

                        <div id="demoMap" class="w-full h-48 mt-3 rounded-xl border border-slate-200 z-10 relative"></div>
                        <input type="hidden" id="demoLatitude" name="latitude" value="{{ old('latitude', '5.359951') }}">
                        <input type="hidden" id="demoLongitude" name="longitude" value="{{ old('longitude', '-4.008256') }}">
                    </div>
                </div>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100" id="demoFooterStep1">
                    <button type="button" onclick="closeDemoModal()" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold">Annuler</button>
                    <button type="button" onclick="goToDemoStep(2)" class="px-6 py-2.5 rounded-xl bg-primary-dynamic text-white font-bold hover:opacity-95 transition shadow-sm flex items-center gap-2 cursor-pointer">
                        Suivant <i class="ph ph-arrow-right font-bold"></i>
                    </button>
                </div>

                <div class="pt-4 hidden justify-end gap-3 border-t border-slate-100" id="demoFooterStep2">
                    <button type="button" onclick="goToDemoStep(1)" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold flex items-center gap-2">
                        <i class="ph ph-arrow-left font-bold"></i> Précédent
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-dynamic text-white font-bold hover:opacity-95 transition shadow-sm flex items-center gap-2 cursor-pointer">
                        <i class="ph ph-paper-plane-right font-bold"></i> Envoyer Ma Demande
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= MODAL CONFIRMATION DEMANDE ENVOYÉE ================= -->
    <div id="demoSuccessModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl border border-slate-200 p-8 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4">
                <i class="ph-fill ph-check-circle text-4xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-900">Demande envoyée !</h3>
            <p class="text-sm text-slate-500 mt-2 leading-relaxed">
                Merci ! Votre demande est en cours d'examen par notre équipe. Vous recevrez vos identifiants de connexion par email dès son approbation.
            </p>
            <button onclick="document.getElementById('demoSuccessModal').classList.add('hidden')" class="mt-6 px-6 py-2.5 rounded-xl bg-primary-dynamic text-white font-bold hover:opacity-95 transition shadow-sm cursor-pointer">
                Compris
            </button>
        </div>
    </div>
    @if(session('registration_submitted'))
    <script>document.addEventListener('DOMContentLoaded', () => document.getElementById('demoSuccessModal').classList.remove('hidden'));</script>
    @endif

    <script>
        let demoMap = null;
        let demoMarker = null;

        function initDemoMap() {
            if (demoMap) {
                // Map already initialized, just ensure it renders properly
                setTimeout(() => { demoMap.invalidateSize(); }, 200);
                return;
            }

            let lat = parseFloat(document.getElementById('demoLatitude').value) || 5.359951;
            let lng = parseFloat(document.getElementById('demoLongitude').value) || -4.008256;
            
            demoMap = L.map('demoMap').setView([lat, lng], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(demoMap);

            demoMarker = L.marker([lat, lng], {draggable: true}).addTo(demoMap);

            function fetchDemoAddressFromCoords(lat, lng) {
                fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.display_name) {
                            document.getElementById('demoAddressSearch').value = data.display_name;
                        }
                    })
                    .catch(e => console.error("Reverse geocoding error:", e));
            }

            demoMarker.on('dragend', function(e) {
                const position = demoMarker.getLatLng();
                document.getElementById('demoLatitude').value = position.lat;
                document.getElementById('demoLongitude').value = position.lng;
                fetchDemoAddressFromCoords(position.lat, position.lng);
            });

            demoMap.on('click', function(e) {
                demoMarker.setLatLng(e.latlng);
                document.getElementById('demoLatitude').value = e.latlng.lat;
                document.getElementById('demoLongitude').value = e.latlng.lng;
                fetchDemoAddressFromCoords(e.latlng.lat, e.latlng.lng);
            });

            setTimeout(() => { demoMap.invalidateSize(); }, 200);
        }

        document.addEventListener("DOMContentLoaded", function() {
            const addressInput = document.getElementById('demoAddressSearch');
            const resultsList = document.getElementById('demoAutocompleteResults');
            let timeout = null;

            addressInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const query = this.value;
                if (query.length < 3) {
                    resultsList.classList.add('hidden');
                    return;
                }
                timeout = setTimeout(() => {
                    fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            resultsList.innerHTML = '';
                            if (data.length > 0) {
                                resultsList.classList.remove('hidden');
                                data.slice(0, 5).forEach(item => {
                                    const li = document.createElement('li');
                                    li.className = 'px-4 py-2 hover:bg-slate-50 cursor-pointer text-[13px] text-slate-700 border-b border-slate-100 last:border-0';
                                    li.textContent = item.display_name;
                                    li.onclick = () => {
                                        addressInput.value = item.display_name;
                                        resultsList.classList.add('hidden');
                                        const newLat = parseFloat(item.lat);
                                        const newLng = parseFloat(item.lon);
                                        if (demoMap) {
                                            demoMap.setView([newLat, newLng], 15);
                                            demoMarker.setLatLng([newLat, newLng]);
                                        }
                                        document.getElementById('demoLatitude').value = newLat;
                                        document.getElementById('demoLongitude').value = newLng;
                                    };
                                    resultsList.appendChild(li);
                                });
                            } else {
                                resultsList.classList.add('hidden');
                            }
                        });
                }, 500);
            });

            document.addEventListener('click', function(e) {
                if (e.target !== addressInput && e.target !== resultsList) {
                    resultsList.classList.add('hidden');
                }
            });
        });

        function openDemoModal() {
            document.getElementById('demoModal').classList.remove('hidden');
            goToDemoStep(1);
        }
        function closeDemoModal() {
            document.getElementById('demoModal').classList.add('hidden');
        }
        function selectPackage(pkgName) {
            document.getElementById('demoPackage').value = pkgName;
            openDemoModal();
        }

        function goToDemoStep(step) {
            const step1 = document.getElementById('demoStep1');
            const step2 = document.getElementById('demoStep2');
            const footer1 = document.getElementById('demoFooterStep1');
            const footer2 = document.getElementById('demoFooterStep2');

            if (step === 2) {
                const requiredInputs = step1.querySelectorAll('[required]');
                for (const input of requiredInputs) {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        return;
                    }
                }
            }

            step1.classList.toggle('hidden', step !== 1);
            step2.classList.toggle('hidden', step !== 2);

            footer1.classList.remove('hidden', 'flex');
            footer1.classList.add(step === 1 ? 'flex' : 'hidden');
            footer2.classList.remove('hidden', 'flex');
            footer2.classList.add(step === 2 ? 'flex' : 'hidden');

            const dot1Circle = document.querySelector('#demoStepDot1 span:first-child');
            const dot2Circle = document.getElementById('demoStepDot2Circle');
            const dot2Label = document.getElementById('demoStepDot2Label');
            if (step === 2) {
                dot1Circle.className = 'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-emerald-500 text-white';
                dot1Circle.innerHTML = '<i class="ph ph-check text-xs font-bold"></i>';
                dot2Circle.className = 'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-primary-dynamic text-white';
                dot2Label.className = 'text-slate-900';
                setTimeout(initDemoMap, 50);
            } else {
                dot1Circle.className = 'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-primary-dynamic text-white';
                dot1Circle.textContent = '1';
                dot2Circle.className = 'w-6 h-6 rounded-full flex items-center justify-center text-[11px] font-bold bg-slate-200 text-slate-500';
                dot2Label.className = 'text-slate-400';
            }

            document.getElementById('demoFormScroll').scrollTop = 0;
        }

        function toggleExtraModules() {
            const extraCards = document.querySelectorAll('.module-extra-card');
            const btnText = document.getElementById('toggleModulesText');
            const btnIcon = document.getElementById('toggleModulesIcon');
            
            let willExpand = false;
            extraCards.forEach(card => {
                if (card.classList.contains('hidden')) {
                    card.classList.remove('hidden');
                    willExpand = true;
                } else {
                    card.classList.add('hidden');
                }
            });
            
            if (willExpand) {
                btnText.textContent = "Réduire la liste des modules";
                btnIcon.className = "ph ph-caret-up text-base font-bold text-primary-dynamic";
            } else {
                btnText.textContent = "Voir plus de modules (" + extraCards.length + " restants)";
                btnIcon.className = "ph ph-caret-down text-base font-bold text-primary-dynamic";
            }
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeDemoModal();
        });
        // Real submission: the form POSTs natively to {{ route('register') }},
        // which creates the school + admin account (password auto-generated
        // and emailed) and logs the user straight into their new dashboard.
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', openDemoModal);
        @endif
    </script>
</body>
</html>
