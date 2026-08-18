@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Gestion Intégrale du Contenu & Images (CMS Landing Page)</h2>
            <p class="text-[15px] text-slate-500 mt-1">Pilotez 100% des textes, images 3D, statistiques, fonctionnalités, témoignages et FAQ de la Landing Page.</p>
        </div>
        <div class="flex items-center shrink-0 gap-3">
            <a href="{{ route('landing') }}" target="_blank" class="flex items-center gap-2 bg-slate-100 text-slate-700 hover:bg-slate-200 px-4 py-2.5 rounded-xl text-xs font-bold transition">
                <i class="ph ph-arrow-square-out text-base font-bold"></i> Aperçu Landing Page
            </a>
            <button type="submit" form="landingCmsForm" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-blue-900 transition shadow-sm cursor-pointer">
                <i class="ph ph-floppy-disk text-base font-bold"></i> Enregistrer & Publier (BD SQL)
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

    <!-- Main CMS Form -->
    <form action="{{ route('superadmin.cms.update-landing') }}" method="POST" enctype="multipart/form-data" id="landingCmsForm">
        @csrf
        
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
            
            <!-- Left Column: Hero & Features CMS -->
            <div class="xl:col-span-2 space-y-6">
                
                <!-- 1. Hero Section -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-[#031C5B] text-white flex items-center justify-center shrink-0 font-bold">
                            <i class="ph ph-textbox text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-[#111827]">1. Section d'Accroche Principale (Hero Section)</h3>
                            <p class="text-xs text-slate-500">Textes, image et boutons en haut de la Landing Page</p>
                        </div>
                    </div>

                    <div class="space-y-5 text-xs">
                        <div>
                            <label class="block font-bold text-[#111827] mb-1.5">Badge d'En-tête (Hero Badge) *</label>
                            <input type="text" name="cms_hero_badge" value="{{ $cmsSettings['cms_hero_badge'] ?? '' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-4 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block font-bold text-[#111827] mb-1.5">Titre Principal (Headline H1) *</label>
                            <input type="text" name="cms_hero_headline" value="{{ $cmsSettings['cms_hero_headline'] ?? '' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-extrabold rounded-xl px-4 py-3 outline-none focus:border-[#031C5B] focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block font-bold text-[#111827] mb-1.5">Sous-Titre / Paragraphe de Description *</label>
                            <textarea name="cms_hero_subtitle" rows="3" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-medium rounded-xl p-4 outline-none focus:border-[#031C5B] focus:bg-white transition leading-relaxed">{{ $cmsSettings['cms_hero_subtitle'] ?? '' }}</textarea>
                        </div>

                        <!-- Hero Image Upload / Path -->
                        <div class="p-4 bg-blue-50/60 border border-blue-100 rounded-xl space-y-3">
                            <label class="block font-bold text-[#031C5B]">🖼️ Illustration Dashboard Hero (Aperçu & Upload)</label>
                            <div class="flex items-center gap-4">
                                <img src="{{ asset(ltrim($cmsSettings['cms_hero_image'] ?? '/images/hero_dashboard.png', '/')) }}" alt="Hero Preview" class="w-24 h-16 object-cover rounded-lg border border-slate-200 shadow-2xs">
                                <div class="flex-1 space-y-2">
                                    <input type="file" name="hero_image_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-[#031C5B] file:text-white hover:file:bg-blue-900 cursor-pointer">
                                    <input type="text" name="cms_hero_image" value="{{ $cmsSettings['cms_hero_image'] ?? '/images/hero_dashboard.png' }}" placeholder="ou chemin relatif URL ex: /images/hero_dashboard.png" class="w-full bg-white border border-slate-200 text-slate-700 text-[11px] font-mono rounded-lg px-3 py-1.5 outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-slate-100">
                            <div>
                                <label class="block font-bold text-[#111827] mb-1.5">Intitulé Bouton CTA 1 (Essai Gratuit) *</label>
                                <input type="text" name="cms_hero_primary_cta" value="{{ $cmsSettings['cms_hero_primary_cta'] ?? '' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition">
                            </div>
                            <div>
                                <label class="block font-bold text-[#111827] mb-1.5">Intitulé Bouton CTA 2 (Catalogue) *</label>
                                <input type="text" name="cms_hero_secondary_cta" value="{{ $cmsSettings['cms_hero_secondary_cta'] ?? '' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2.5 outline-none focus:border-[#031C5B] focus:bg-white transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Feature Blocks & Images -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 font-bold">
                            <i class="ph ph-image text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-[#111827]">2. Blocs Fonctionnalités & Illustrations 3D</h3>
                            <p class="text-xs text-slate-500">Personnalisez les textes et visuels des fonctionnalités clés</p>
                        </div>
                    </div>

                    <div class="space-y-6 text-xs">
                        <!-- Feature 1: Mobile Payment -->
                        <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-2xl space-y-3">
                            <h4 class="font-extrabold text-emerald-900 text-sm">📲 Bloc 1 : Mobile Money Payment</h4>
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Titre du Bloc *</label>
                                <input type="text" name="cms_feature1_title" value="{{ $cmsSettings['cms_feature1_title'] ?? '' }}" required class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2 outline-none focus:border-emerald-600 transition">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Description *</label>
                                <textarea name="cms_feature1_desc" rows="2" required class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-medium rounded-xl p-3 outline-none focus:border-emerald-600 transition">{{ $cmsSettings['cms_feature1_desc'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Illustration Mobile Money (Aperçu & Upload)</label>
                                <div class="flex items-center gap-4">
                                    <img src="{{ asset(ltrim($cmsSettings['cms_feature1_image'] ?? '/images/mobile_payment.png', '/')) }}" alt="Feature 1 Preview" class="w-20 h-16 object-cover rounded-lg border border-slate-200 shadow-2xs">
                                    <div class="flex-1 space-y-2">
                                        <input type="file" name="feature1_image_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">
                                        <input type="text" name="cms_feature1_image" value="{{ $cmsSettings['cms_feature1_image'] ?? '/images/mobile_payment.png' }}" class="w-full bg-white border border-slate-200 text-slate-700 text-[11px] font-mono rounded-lg px-3 py-1.5 outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Feature 2: AI EduAnalytics -->
                        <div class="p-4 bg-purple-50/50 border border-purple-100 rounded-2xl space-y-3">
                            <h4 class="font-extrabold text-purple-900 text-sm">🤖 Bloc 2 : Assistant IA EduAnalytics</h4>
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Titre du Bloc *</label>
                                <input type="text" name="cms_feature2_title" value="{{ $cmsSettings['cms_feature2_title'] ?? '' }}" required class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-bold rounded-xl px-3.5 py-2 outline-none focus:border-purple-600 transition">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Description *</label>
                                <textarea name="cms_feature2_desc" rows="2" required class="w-full bg-white border border-slate-200 text-slate-800 text-xs font-medium rounded-xl p-3 outline-none focus:border-purple-600 transition">{{ $cmsSettings['cms_feature2_desc'] ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-800 mb-1">Illustration IA EduAnalytics (Aperçu & Upload)</label>
                                <div class="flex items-center gap-4">
                                    <img src="{{ asset(ltrim($cmsSettings['cms_feature2_image'] ?? '/images/ai_analytics.png', '/')) }}" alt="Feature 2 Preview" class="w-20 h-16 object-cover rounded-lg border border-slate-200 shadow-2xs">
                                    <div class="flex-1 space-y-2">
                                        <input type="file" name="feature2_image_file" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-700 cursor-pointer">
                                        <input type="text" name="cms_feature2_image" value="{{ $cmsSettings['cms_feature2_image'] ?? '/images/ai_analytics.png' }}" class="w-full bg-white border border-slate-200 text-slate-700 text-[11px] font-mono rounded-lg px-3 py-1.5 outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Stats & Section Titles -->
            <div class="space-y-6">
                
                <!-- 3. Stat Counters -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-[#7C3AED] text-white flex items-center justify-center shrink-0 font-bold">
                            <i class="ph ph-chart-bar text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-[#111827]">3. Compteurs KPIs</h3>
                            <p class="text-xs text-slate-500">Statistiques publiques</p>
                        </div>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-[#111827] mb-1">Établissements Actifs *</label>
                            <input type="text" name="cms_stat_schools" value="{{ $cmsSettings['cms_stat_schools'] ?? '48+' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 font-bold rounded-xl px-3.5 py-2 outline-none focus:border-[#7C3AED] transition">
                        </div>
                        <div>
                            <label class="block font-bold text-[#111827] mb-1">Élèves Gérés *</label>
                            <input type="text" name="cms_stat_students" value="{{ $cmsSettings['cms_stat_students'] ?? '12 500+' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 font-bold rounded-xl px-3.5 py-2 outline-none focus:border-[#7C3AED] transition">
                        </div>
                        <div>
                            <label class="block font-bold text-[#111827] mb-1">Paiements Mobile Money *</label>
                            <input type="text" name="cms_stat_mobile_money" value="{{ $cmsSettings['cms_stat_mobile_money'] ?? '100%' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 font-bold rounded-xl px-3.5 py-2 outline-none focus:border-[#7C3AED] transition">
                        </div>
                        <div>
                            <label class="block font-bold text-[#111827] mb-1">Disponibilité SLA *</label>
                            <input type="text" name="cms_stat_sla" value="{{ $cmsSettings['cms_stat_sla'] ?? '99.9%' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 font-bold rounded-xl px-3.5 py-2 outline-none focus:border-[#7C3AED] transition">
                        </div>
                    </div>
                </div>

                <!-- 4. Section Headings -->
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                    <div class="flex items-center gap-4 mb-6 border-b border-slate-100 pb-4">
                        <div class="w-10 h-10 rounded-xl bg-blue-900 text-white flex items-center justify-center shrink-0 font-bold">
                            <i class="ph ph-text-h-one text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-[#111827]">4. Titres des Sections</h3>
                            <p class="text-xs text-slate-500">En-têtes Forfaits & Modules</p>
                        </div>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-[#111827] mb-1">Titre Section Forfaits *</label>
                            <input type="text" name="cms_pricing_title" value="{{ $cmsSettings['cms_pricing_title'] ?? '' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 font-bold rounded-xl px-3.5 py-2 outline-none focus:border-blue-900 transition">
                        </div>
                        <div>
                            <label class="block font-bold text-[#111827] mb-1">Titre Section Modules *</label>
                            <input type="text" name="cms_modules_title" value="{{ $cmsSettings['cms_modules_title'] ?? '' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-800 font-bold rounded-xl px-3.5 py-2 outline-none focus:border-blue-900 transition">
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>

    <!-- 5. Testimonials CMS Section -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm mb-8">
        <div class="p-6 flex items-center justify-between border-b border-slate-200 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                    <i class="ph ph-quotes text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-[#111827]">5. Témoignages Clients (Landing Page)</h3>
                    <p class="text-xs text-slate-500">Gérez les avis des fondateurs et directeurs d'écoles</p>
                </div>
            </div>
            <button type="button" onclick="openAddTestimonialModal()" class="px-4 py-2 bg-amber-600 text-white text-xs font-bold rounded-xl hover:bg-amber-700 transition flex items-center gap-1.5 cursor-pointer">
                <i class="ph ph-plus font-bold"></i> Ajouter un Témoignage
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-6 text-xs">
            @foreach($testimonials as $index => $t)
            <div class="p-5 bg-slate-50 rounded-xl border border-slate-200 flex flex-col justify-between relative">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-slate-900 text-sm">{{ $t['name'] }}</span>
                        <span class="text-amber-500">★★★★★</span>
                    </div>
                    <p class="text-[11px] font-bold text-blue-900 mb-2">{{ $t['role'] }} — {{ $t['school'] }}</p>
                    <p class="text-slate-600 italic leading-relaxed font-medium">« {{ $t['quote'] }} »</p>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-200 flex justify-end">
                    <form action="{{ route('superadmin.cms.delete-testimonial', $index) }}" method="POST" onsubmit="return confirm('Supprimer ce témoignage ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1 cursor-pointer">
                            <i class="ph ph-trash"></i> Supprimer
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- 6. FAQ CMS Section -->
    <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm mb-12">
        <div class="p-6 flex items-center justify-between border-b border-slate-200 bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-100 text-[#7C3AED] flex items-center justify-center font-bold">
                    <i class="ph ph-question text-lg"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-[#111827]">6. Questions Fréquentes FAQ (Landing Page)</h3>
                    <p class="text-xs text-slate-500">Ajoutez et modifiez les questions publiques du site</p>
                </div>
            </div>
            <button type="button" onclick="openAddFaqModal()" class="px-4 py-2 bg-[#031C5B] text-white text-xs font-bold rounded-xl hover:bg-blue-900 transition flex items-center gap-1.5 cursor-pointer">
                <i class="ph ph-plus font-bold"></i> Ajouter une Question FAQ
            </button>
        </div>

        <div class="divide-y divide-slate-100 text-xs">
            @foreach($faqItems as $index => $faq)
            <div class="p-5 flex items-start justify-between gap-4 hover:bg-slate-50/70 transition">
                <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center shrink-0 font-bold text-slate-700 mt-0.5">
                        Q{{ $index + 1 }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 text-sm mb-1">{{ $faq['question'] }}</p>
                        <p class="text-slate-600 font-medium leading-relaxed mb-2">{{ $faq['answer'] }}</p>
                        <span class="inline-flex items-center text-[10px] font-bold bg-purple-50 text-[#7C3AED] border border-purple-200 px-2 py-0.5 rounded-md">
                            {{ $faq['category'] ?? 'Général' }}
                        </span>
                    </div>
                </div>

                <form action="{{ route('superadmin.cms.delete-faq', $index) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette question de la FAQ publique ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-2 rounded-lg text-rose-500 hover:bg-rose-50 transition cursor-pointer" title="Supprimer">
                        <i class="ph ph-trash text-base font-bold"></i>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Modal : Ajouter un Témoignage -->
    <div id="addTestimonialModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 bg-amber-600 text-white flex items-center justify-between">
                <h3 class="text-base font-bold">Nouveau Témoignage Client</h3>
                <button type="button" onclick="closeAddTestimonialModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.cms.add-testimonial') }}" method="POST" class="p-6 space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                <div>
                    <label class="block mb-1 font-bold">Nom du Témoin *</label>
                    <input type="text" name="name" required placeholder="ex: M. Amadou Diallo" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 outline-none focus:border-amber-600 focus:bg-white transition">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-bold">Rôle / Poste *</label>
                        <input type="text" name="role" required placeholder="ex: Fondateur" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 outline-none focus:border-amber-600 focus:bg-white transition">
                    </div>
                    <div>
                        <label class="block mb-1 font-bold">Établissement *</label>
                        <input type="text" name="school" required placeholder="ex: Groupe Excellence" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 outline-none focus:border-amber-600 focus:bg-white transition">
                    </div>
                </div>
                <div>
                    <label class="block mb-1 font-bold">Citation / Témoignage *</label>
                    <textarea name="quote" rows="3" required placeholder="Avis détaillé du client..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none focus:border-amber-600 focus:bg-white transition"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeAddTestimonialModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold">Annuler</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 text-white font-bold hover:bg-amber-700 transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-check font-bold"></i> Ajouter le Témoignage
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal : Ajouter une Question FAQ -->
    <div id="addFaqModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <h3 class="text-base font-bold">Nouvelle Question FAQ</h3>
                <button type="button" onclick="closeAddFaqModal()" class="text-white/80 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <form action="{{ route('superadmin.cms.add-faq') }}" method="POST" class="p-6 space-y-4 text-xs font-semibold text-slate-700">
                @csrf
                <div>
                    <label class="block mb-1 font-bold">Question Public *</label>
                    <input type="text" name="question" required placeholder="ex: Comment fonctionne le paiement Mobile Money ?" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 outline-none focus:border-blue-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block mb-1 font-bold">Réponse *</label>
                    <textarea name="answer" rows="3" required placeholder="Explication claire et précise..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 outline-none focus:border-blue-600 focus:bg-white transition"></textarea>
                </div>

                <div>
                    <label class="block mb-1 font-bold">Catégorie</label>
                    <select name="category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 outline-none focus:border-blue-600 focus:bg-white transition cursor-pointer">
                        <option value="Installation">Installation & Onboarding</option>
                        <option value="Paiements">Paiements & Tarifs</option>
                        <option value="Modules">Modules & Fonctionnalités</option>
                        <option value="Sécurité">Sécurité & Backups</option>
                    </select>
                </div>

                <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeAddFaqModal()" class="px-4 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold">Annuler</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#031C5B] text-white font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <i class="ph ph-check font-bold"></i> Ajouter à la FAQ
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddTestimonialModal() {
            document.getElementById('addTestimonialModal').classList.remove('hidden');
        }
        function closeAddTestimonialModal() {
            document.getElementById('addTestimonialModal').classList.add('hidden');
        }
        function openAddFaqModal() {
            document.getElementById('addFaqModal').classList.remove('hidden');
        }
        function closeAddFaqModal() {
            document.getElementById('addFaqModal').classList.add('hidden');
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAddTestimonialModal();
                closeAddFaqModal();
            }
        });
    </script>
@endsection
