@extends('SchoolDashboard::layouts.app')

@section('content')
@php
    $student = $scholarship->student;
    $type = $scholarship->type;
    $statusStyles = [
        'active' => ['bg-emerald-50 text-emerald-700', 'Actif'],
        'pending' => ['bg-violet-50 text-violet-700', 'En attente'],
        'suspended' => ['bg-red-50 text-red-700', 'Suspendu'],
        'rejected' => ['bg-slate-100 text-slate-500', 'Rejeté'],
        'expired' => ['bg-slate-100 text-slate-500', 'Expiré'],
    ];
    [$badgeClass, $badgeLabel] = $statusStyles[$scholarship->status] ?? ['bg-slate-100 text-slate-500', $scholarship->status];
@endphp
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a href="{{ route('school.finance.scholarships.students') }}" class="text-[13px] text-slate-500 hover:text-slate-700 font-semibold flex items-center gap-1 mb-2">
                <i class="ph-bold ph-arrow-left"></i> Retour à Gestion Boursiers
            </a>
            <h2 class="text-[28px] font-bold text-[#031C5B] tracking-tight">Dossier Boursier : {{ $student->first_name }} {{ $student->last_name }}</h2>
            <p class="text-slate-500 text-[13px] mt-1">ID: BRS-{{ str_pad($scholarship->id, 4, '0', STR_PAD_LEFT) }} • {{ $student->academicClass->name ?? '-' }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="px-3 py-2 rounded-full text-[12px] font-bold {{ $badgeClass }}">{{ strtoupper($badgeLabel) }}</span>

            @if($scholarship->status === 'active')
            <form action="{{ route('school.finance.scholarships.suspend', $scholarship->id) }}" method="POST" onsubmit="return confirm('Suspendre cette bourse ?');">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-white border border-red-200 text-red-600 font-bold text-[13px] rounded-xl hover:bg-red-50 transition flex items-center gap-1.5">
                    <i class="ph-bold ph-pause"></i> Suspendre
                </button>
            </form>
            @elseif($scholarship->status === 'suspended')
            <form action="{{ route('school.finance.scholarships.reactivate', $scholarship->id) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-white border border-emerald-200 text-emerald-600 font-bold text-[13px] rounded-xl hover:bg-emerald-50 transition flex items-center gap-1.5">
                    <i class="ph-bold ph-play"></i> Réactiver
                </button>
            </form>
            @endif

            <a href="{{ route('school.finance.scholarships.export', $scholarship->id) }}" class="px-4 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[13px] rounded-xl hover:bg-slate-50 transition flex items-center gap-1.5">
                <i class="ph-bold ph-download-simple"></i> Exporter Certificat
            </a>

            <form action="{{ route('school.finance.scholarships.renew', $scholarship->id) }}" method="POST" onsubmit="return confirm('Renouveler cette bourse pour une année supplémentaire ?');">
                @csrf
                <button type="submit" class="px-4 py-2.5 bg-[#031C5B] text-white font-bold text-[13px] rounded-xl hover:bg-[#031C5B]/90 transition flex items-center gap-1.5">
                    <i class="ph-bold ph-arrows-clockwise"></i> Renouveler
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-100">
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Info -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <div class="flex items-center gap-4">
                @if($student->photo_path)
                    <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-16 h-16 rounded-full object-cover border border-slate-200">
                @else
                    <div class="w-16 h-16 rounded-full bg-[#031C5B] text-white flex items-center justify-center font-bold text-xl">
                        {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h3 class="font-bold text-slate-900">{{ $student->first_name }} {{ $student->last_name }}</h3>
                    <p class="text-[12px] text-slate-500">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d/m/Y') : '-' }}</p>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-100 space-y-3 text-[13px]">
                <div class="flex justify-between"><span class="text-slate-500">Type de Bourse</span><span class="font-bold text-slate-800">{{ $type->name ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Montant Annuel</span><span class="font-bold text-slate-800">{{ number_format($scholarship->annual_amount, 0, ',', ' ') }} FCFA</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Date d'attribution</span><span class="font-bold text-slate-800">{{ $scholarship->awarded_date?->format('d M Y') ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">Fin de validité</span><span class="font-bold text-slate-800">{{ $scholarship->expiry_date?->format('d M Y') ?? '-' }}</span></div>
            </div>
        </div>

        <!-- Pieces Justificatives -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-slate-100 shadow-sm" x-data="{ open: false, docLabel: '' }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Pièces Justificatives</h3>
                <button @click="docLabel = ''; open = true" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                    <i class="ph-bold ph-plus"></i>
                </button>
            </div>

            <div class="space-y-2">
                @foreach($scholarship->documents as $doc)
                <div class="flex items-center justify-between p-3 border border-slate-100 rounded-xl">
                    <div class="flex items-center gap-2">
                        <i class="ph-fill ph-file-text text-slate-400 text-lg"></i>
                        <div>
                            <p class="text-[13px] font-semibold text-slate-800">{{ $doc->label }}</p>
                            <p class="text-[11px] text-slate-400 font-semibold">Ajouté le {{ $doc->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-download-simple"></i></a>
                        <form action="{{ route('school.finance.scholarships.documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Supprimer ce document ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600"><i class="ph-bold ph-trash"></i></button>
                        </form>
                    </div>
                </div>
                @endforeach

                @php
                    $uploadedLabels = $scholarship->documents->pluck('label')->map(fn($l) => strtolower(trim($l)))->all();
                    $missingLabels = collect($type?->requiredDocumentLabels() ?? [])->filter(fn($l) => !in_array(strtolower(trim($l)), $uploadedLabels));
                @endphp
                @foreach($missingLabels as $label)
                <div class="flex items-center justify-between p-3 border border-amber-100 bg-amber-50/50 rounded-xl">
                    <div class="flex items-center gap-2">
                        <i class="ph-fill ph-warning-circle text-amber-500 text-lg"></i>
                        <p class="text-[13px] font-semibold text-slate-800">{{ $label }}</p>
                    </div>
                    <button @click="docLabel = '{{ addslashes($label) }}'; open = true" class="text-amber-600 font-bold text-[12px] hover:underline">En attente de téléversement</button>
                </div>
                @endforeach

                @if($scholarship->documents->isEmpty() && $missingLabels->isEmpty())
                    <p class="text-slate-400 text-[13px] text-center py-6">Aucun document requis pour ce type de bourse.</p>
                @endif
            </div>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[16px] font-extrabold text-[#031C5B]">Ajouter un Document</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                    <form action="{{ route('school.finance.scholarships.documents.store', $scholarship->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                        @csrf
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-bold text-slate-700">Nom du document <span class="text-red-500">*</span></label>
                            <input type="text" name="label" x-model="docLabel" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-bold text-slate-700">Fichier <span class="text-red-500">*</span></label>
                            <input type="file" name="file" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">Annuler</button>
                            <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">Téléverser</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Suivi des Notes -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm" x-data="{ open: false }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Suivi des Notes</h3>
                <button @click="open = true" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                    <i class="ph-bold ph-plus"></i>
                </button>
            </div>
            <div class="space-y-3">
                @forelse($scholarship->grades as $grade)
                <div class="p-3 border border-slate-100 rounded-xl">
                    <div class="flex items-center justify-between">
                        <span class="text-[13px] font-semibold text-slate-700">{{ $grade->period }}</span>
                        <span class="text-lg font-extrabold {{ $type?->min_average && $grade->average < $type->min_average ? 'text-red-600' : 'text-slate-900' }}">{{ number_format($grade->average, 1) }}/20</span>
                    </div>
                    @if($type?->min_average)
                    <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2 overflow-hidden">
                        <div class="h-full {{ $grade->average < $type->min_average ? 'bg-red-500' : 'bg-emerald-500' }} rounded-full" style="width: {{ min(($grade->average / 20) * 100, 100) }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Seuil maintenu: {{ number_format($type->min_average, 1) }}/20</p>
                    @endif
                </div>
                @empty
                <p class="text-slate-400 text-[13px] text-center py-6">Aucune note enregistrée.</p>
                @endforelse
            </div>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[16px] font-extrabold text-[#031C5B]">Ajouter une Note</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                    <form action="{{ route('school.finance.scholarships.grades.store', $scholarship->id) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-bold text-slate-700">Période <span class="text-red-500">*</span></label>
                            <input type="text" name="period" placeholder="Ex: 1er Trimestre" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-[13px] font-bold text-slate-700">Moyenne (/20) <span class="text-red-500">*</span></label>
                                <input type="number" name="average" min="0" max="20" step="0.1" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[13px] font-bold text-slate-700">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="recorded_at" value="{{ date('Y-m-d') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">Annuler</button>
                            <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Historique Versements -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm" x-data="{ open: false, payOpen: null }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Historique Versements</h3>
                <button @click="open = true" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 transition">
                    <i class="ph-bold ph-plus"></i>
                </button>
            </div>
            <div class="space-y-3">
                @forelse($scholarship->disbursements as $d)
                <div class="flex items-center justify-between p-3 border border-slate-100 rounded-xl">
                    <div>
                        <p class="text-[13px] font-bold text-slate-800">{{ $d->label }}</p>
                        <p class="text-[11px] text-slate-500">{{ $d->due_date->format('d M Y') }}@if($d->method) • {{ \App\Modules\Finance\Domain\Models\Payment::METHODS[$d->method] ?? $d->method }} @endif</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-slate-800 text-[14px]">{{ number_format($d->amount, 0, ',', ' ') }} FCFA</span>
                        @if($d->status === 'paid')
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-[11px] font-bold rounded-full">PAYÉ</span>
                        @else
                            <button @click="payOpen = {{ $d->id }}" class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded-full hover:bg-slate-200 transition">EN ATTENTE</button>
                        @endif
                    </div>
                </div>

                <div x-show="payOpen === {{ $d->id }}" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                    <div @click.away="payOpen = null" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="text-[16px] font-extrabold text-[#031C5B]">Marquer "{{ $d->label }}" payée</h3>
                            <button type="button" @click="payOpen = null" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                        </div>
                        <form action="{{ route('school.finance.scholarships.disbursements.pay', $d->id) }}" method="POST" class="p-6 space-y-4">
                            @csrf
                            <div class="space-y-1.5">
                                <label class="block text-[13px] font-bold text-slate-700">Méthode <span class="text-red-500">*</span></label>
                                <select name="method" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                                    @foreach(\App\Modules\Finance\Domain\Models\Payment::METHODS as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[13px] font-bold text-slate-700">Référence</label>
                                <input type="text" name="reference" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                            </div>
                            <div class="flex justify-end gap-3 pt-2">
                                <button type="button" @click="payOpen = null" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">Annuler</button>
                                <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">Confirmer le paiement</button>
                            </div>
                        </form>
                    </div>
                </div>
                @empty
                <p class="text-slate-400 text-[13px] text-center py-6">Aucun versement enregistré.</p>
                @endforelse
            </div>

            <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-[16px] font-extrabold text-[#031C5B]">Ajouter une Tranche</h3>
                        <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                    </div>
                    <form action="{{ route('school.finance.scholarships.disbursements.store', $scholarship->id) }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-bold text-slate-700">Libellé <span class="text-red-500">*</span></label>
                            <input type="text" name="label" placeholder="Ex: Tranche 1 (T1)" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-[13px] font-bold text-slate-700">Montant (FCFA) <span class="text-red-500">*</span></label>
                                <input type="number" name="amount" min="1" step="1" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                            </div>
                            <div class="space-y-1.5">
                                <label class="block text-[13px] font-bold text-slate-700">Échéance <span class="text-red-500">*</span></label>
                                <input type="date" name="due_date" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="open = false" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">Annuler</button>
                            <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all">Ajouter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
