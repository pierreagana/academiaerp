@extends('SuperAdmin::layouts.app')

@section('content')
    @php
        $id = $requestItem->id ?? 1;
        $reqCode = '#REQ-' . str_pad($id, 4, '0', STR_PAD_LEFT);
        $schoolName = $requestItem->school_name ?? $requestItem->schoolName ?? 'Établissement';
        $applicantName = $requestItem->applicant_name ?? $requestItem->applicantName ?? 'M. Amadou Sow';
        $email = $requestItem->email ?? 'contact@ecole.sn';
        $phone = $requestItem->phone ?? '+221 77 123 45 67';
        $region = $requestItem->region ?? 'Dakar, Sénégal';
        $status = strtolower($requestItem->status ?? 'en attente');
        $plan = $requestItem->plan_requested ?? $requestItem->packageRequested ?? 'IA-Premium';
        $notes = $requestItem->notes ?? '';
    @endphp

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl shadow-sm">
            <i class="ph ph-check-circle text-emerald-600 text-xl"></i>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">{{ $schoolName }}</h2>
            <div class="flex items-center gap-3 mt-2">
                <span class="bg-[#F1F5F9] text-[#475569] text-[11px] font-bold px-3 py-1.5 rounded-md tracking-wider">ID: {{ $reqCode }}</span>
                @if($status == 'en attente')
                    <span class="inline-flex items-center gap-1.5 bg-[#FFFBEB] text-[#D97706] text-[12px] font-semibold px-3 py-1.5 rounded-full border border-[#FEF3C7]">
                        <i class="ph ph-dots-three-circle text-[#D97706] text-sm"></i> En attente
                    </span>
                @elseif(in_array($status, ['validée', 'approuvé', 'approved', 'approuvée']))
                    <span class="inline-flex items-center gap-1.5 bg-[#DCFCE7] text-[#16A34A] text-[12px] font-semibold px-3 py-1.5 rounded-full border border-[#BBF7D0]">
                        <i class="ph ph-check-circle text-[#16A34A] text-sm"></i> Approuvé
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 bg-[#FEE2E2] text-[#DC2626] text-[12px] font-semibold px-3 py-1.5 rounded-full border border-[#FCA5A5]">
                        <i class="ph ph-x-circle text-[#DC2626] text-sm"></i> Rejeté
                    </span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('superadmin.registration-requests') }}" class="flex items-center gap-2 text-slate-600 hover:text-slate-900 font-medium text-sm transition">
                <i class="ph ph-arrow-left text-lg font-bold"></i> Retour à la liste
            </a>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column -->
        <div class="lg:col-span-7 flex flex-col gap-6">
            
            <!-- Informations de l'Établissement -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <i class="ph ph-buildings text-[24px] text-[#031C5B]"></i>
                    <h3 class="text-xl font-bold text-[#031C5B]">Informations de l'Établissement</h3>
                </div>
                
                <div class="flex flex-col sm:flex-row gap-6 mb-8">
                    <!-- Image Placeholder -->
                    <div class="w-full sm:w-[160px] h-[160px] bg-slate-100 rounded-xl shrink-0 overflow-hidden border border-slate-200 relative">
                        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?q=80&w=400&auto=format&fit=crop'); opacity: 0.9;"></div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-6 w-full">
                        <div>
                            <p class="text-[12px] font-bold text-slate-500 mb-1">Nom de l'Institution</p>
                            <p class="text-[15px] font-medium text-slate-900 leading-snug">{{ $schoolName }}</p>
                        </div>
                        <div>
                            <p class="text-[12px] font-bold text-slate-500 mb-1">Région / Localisation</p>
                            <p class="text-[15px] font-medium text-slate-900 flex items-center gap-1.5">
                                <i class="ph ph-globe-hemisphere-west text-slate-400"></i> {{ $region }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[12px] font-bold text-slate-500 mb-1">Type d'Établissement</p>
                            <p class="text-[15px] font-medium text-slate-900">Enseignement Général / Technique</p>
                        </div>
                        <div>
                            <p class="text-[12px] font-bold text-slate-500 mb-1">Effectif Estimé</p>
                            <p class="text-[15px] font-medium text-slate-900">800 - 1,500 Élèves</p>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100 mb-6">

                <h4 class="text-[16px] font-bold text-slate-900 mb-6">Contact Principal</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-6">
                    <div>
                        <p class="text-[12px] font-bold text-slate-500 mb-1">Nom du Responsable</p>
                        <p class="text-[15px] font-medium text-slate-900">{{ $applicantName }}</p>
                    </div>
                    <div>
                        <p class="text-[12px] font-bold text-slate-500 mb-1">Fonction</p>
                        <p class="text-[15px] font-medium text-slate-900">Demandeur / Représentant Légale</p>
                    </div>
                    <div>
                        <p class="text-[12px] font-bold text-slate-500 mb-1">Email Professionnel</p>
                        <a href="mailto:{{ $email }}" class="text-[15px] font-medium text-blue-700 hover:underline">{{ $email }}</a>
                    </div>
                    <div>
                        <p class="text-[12px] font-bold text-slate-500 mb-1">Téléphone</p>
                        <p class="text-[15px] font-medium text-slate-900">{{ $phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Notes Internes & Évaluation -->
            <div class="bg-[#F8FAFC] rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8">
                <div class="flex items-center gap-3 mb-6">
                    <i class="ph ph-note-pencil text-[24px] text-[#0f172a]"></i>
                    <h3 class="text-xl font-bold text-[#0f172a]">Notes Internes & Évaluation</h3>
                </div>

                <div class="mb-4">
                    <p class="text-[12px] font-bold text-slate-700 mb-3">Score de Fiabilité IA</p>
                    <div class="flex items-center gap-4 mb-2">
                        <div class="flex-1 h-2 bg-[#DBEAFE] rounded-full overflow-hidden">
                            <div class="h-full bg-[#059669] rounded-full" style="width: 90%;"></div>
                        </div>
                        <span class="text-[16px] font-bold text-[#059669]">90/100</span>
                    </div>
                    <p class="text-[14px] text-slate-600 font-medium">Domaine vérifié, coordonnées de contact valides.</p>
                </div>

                <div class="mt-6">
                    <p class="text-[12px] font-bold text-slate-700 mb-2">Commentaires de l'Administrateur</p>
                    <textarea rows="4" placeholder="Ajouter une note concernant cette demande..." class="w-full bg-white border border-slate-300 rounded-xl p-4 text-[14px] focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none resize-none transition shadow-sm">{{ $notes }}</textarea>
                </div>
            </div>

        </div>

        <!-- Right Column -->
        <div class="lg:col-span-5 flex flex-col gap-6">
            
            <!-- Détails de l'Abonnement -->
            <div class="bg-[#FCFDFE] rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8 relative overflow-hidden">
                <div class="absolute -top-12 -right-12 w-48 h-48 bg-purple-100 rounded-full blur-3xl opacity-50 pointer-events-none"></div>

                <div class="flex items-center gap-3 mb-6 relative z-10">
                    <i class="ph ph-medal text-[24px] text-[#7C3AED]"></i>
                    <h3 class="text-xl font-bold text-[#7C3AED]">Détails de l'Abonnement</h3>
                </div>

                <div class="border border-slate-200 rounded-xl p-5 mb-8 bg-white relative z-10 shadow-sm">
                    <div class="flex justify-between items-start mb-2">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest">Plan Demande</p>
                        <span class="bg-[#2563EB] text-white text-[10px] font-bold px-3 py-1 rounded-full">Annuel</span>
                    </div>
                    <h4 class="text-[22px] font-extrabold text-[#031C5B] mb-1">{{ $plan }}</h4>
                    <p class="text-[14px] text-slate-600 font-medium">Formule sélectionnée lors de l'inscription.</p>
                </div>

                <div class="mb-8 relative z-10">
                    <h4 class="text-[15px] font-bold text-slate-900 mb-4">Modules Demandés</h4>
                    <ul class="space-y-4">
                        <li class="flex items-start gap-3">
                            <i class="ph ph-check-circle text-[#059669] text-[20px] shrink-0"></i>
                            <span class="text-[14px] font-medium text-slate-700">Gestion des Élèves & Notes</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="ph ph-check-circle text-[#059669] text-[20px] shrink-0"></i>
                            <span class="text-[14px] font-medium text-slate-700">Facturation & Paiements Mobile Money</span>
                        </li>
                        <li class="flex items-start gap-3 bg-[#F5F3FF] p-3 rounded-lg border border-purple-100 -mx-3">
                            <i class="ph ph-sparkle text-purple-600 text-[20px] shrink-0"></i>
                            <span class="text-[14px] font-medium text-slate-900">IA: Assistant Pédagogique & Suivi</span>
                        </li>
                    </ul>
                </div>

                <hr class="border-slate-100 mb-6">

                <div class="relative z-10">
                    <h4 class="text-[15px] font-bold text-slate-900 mb-4">Statut du Paiement Initial</h4>
                    
                    <div class="border border-slate-200 rounded-xl p-4 flex items-center justify-between bg-white hover:bg-slate-50 cursor-pointer transition shadow-sm group">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-[#E0E7FF] flex items-center justify-center text-[#4338CA] shrink-0">
                                <i class="ph ph-file-text text-[20px]"></i>
                            </div>
                            <div>
                                <p class="text-[14px] font-semibold text-slate-800 group-hover:text-blue-600 transition">Dossier_Inscription.pdf</p>
                                <p class="text-[12px] font-medium text-slate-500">Document joint</p>
                            </div>
                        </div>
                        <div class="w-8 h-8 flex items-center justify-center text-[#4338CA]">
                            <i class="ph ph-download-simple text-[20px] font-bold"></i>
                        </div>
                    </div>
                </div>

            </div>
            
            <!-- Action Buttons -->
            @if($status == 'en attente' || $status == 'en cours d\'analyse')
                <div class="flex gap-4 mt-4">
                    <form action="{{ route('superadmin.registration-requests.reject', $id) }}" method="POST" class="flex-1" onsubmit="return confirm('Rejeter cette demande ?');">
                        @csrf
                        <button type="submit" class="w-full flex flex-col items-center justify-center bg-white border border-[#FCA5A5] hover:bg-red-50 text-[#B91C1C] py-4 rounded-xl transition shadow-sm">
                            <i class="ph ph-x-circle text-[22px] mb-1"></i>
                            <span class="text-[14px] font-semibold">Rejeter la<br>demande</span>
                        </button>
                    </form>
                    <form action="{{ route('superadmin.registration-requests.approve', $id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full flex flex-col items-center justify-center bg-[#031C5B] hover:bg-blue-900 text-white py-4 rounded-xl transition shadow-md">
                            <i class="ph ph-check-circle text-[22px] mb-1"></i>
                            <span class="text-[14px] font-semibold">Approuver &<br>Activer</span>
                        </button>
                    </form>
                </div>
            @else
                <div class="mt-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-center text-slate-600 text-sm font-medium">
                    Demande traitée (Statut : <span class="capitalize font-bold text-slate-900">{{ $status }}</span>)
                </div>
            @endif
            
        </div>
    </div>
@endsection
