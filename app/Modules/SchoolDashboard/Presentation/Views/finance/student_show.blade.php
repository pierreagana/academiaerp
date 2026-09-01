@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Dossier Financier — {{ \App\Modules\Finance\Domain\Models\FeeLevel::TYPES[$type] }}</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gestion et suivi des paiements individuels.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.finance.fees.payments', ['type' => $type]) }}" class="px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[13.5px] rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
                <i class="ph-bold ph-arrow-left"></i> Retour
            </a>
            <a href="{{ route('school.finance.fees.students.export', $student->id) }}?type={{ $type }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                <i class="ph-bold ph-download-simple text-lg"></i>
                Exporter Relevé
            </a>
            @include('SchoolDashboard::finance._payment_modal', ['fixedStudent' => $student, 'buttonLabel' => 'Nouveau Paiement'])
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Student Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm text-center">
            @if($student->photo_path)
                <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-20 h-20 rounded-full object-cover mx-auto border border-slate-200">
            @else
                <div class="w-20 h-20 rounded-full bg-[#031C5B] text-white flex items-center justify-center font-bold text-2xl mx-auto">
                    {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                </div>
            @endif
            <h3 class="text-lg font-bold text-slate-900 mt-4">{{ $student->first_name }} {{ $student->last_name }}</h3>
            <p class="text-[13px] text-slate-500">{{ $student->academicClass->name ?? '-' }}</p>

            @php
                $statusStyles = [
                    'paid' => ['bg-emerald-50 text-emerald-700', 'Scolarité à jour'],
                    'partial' => ['bg-violet-50 text-violet-700', 'Paiement partiel'],
                    'late' => ['bg-red-50 text-red-700', 'Paiement en retard'],
                    'pending' => ['bg-slate-100 text-slate-600', 'Aucun paiement'],
                    'unconfigured' => ['bg-slate-100 text-slate-500', 'Structure non configurée'],
                ];
                [$badgeClass, $badgeLabel] = $statusStyles[$summary['status']] ?? ['bg-slate-100 text-slate-500', $summary['status']];
            @endphp
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold mt-2 {{ $badgeClass }}">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ $badgeLabel }}
            </span>

            <div class="grid grid-cols-2 gap-3 mt-5 pt-5 border-t border-slate-100 text-left">
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase">Matricule</p>
                    <p class="text-[13px] font-semibold text-slate-700">{{ $student->roll_number ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase">Année</p>
                    <p class="text-[13px] font-semibold text-slate-700">{{ $student->academic_year ?? '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Reste à payer -->
        <div class="bg-[#031C5B] rounded-2xl p-6 text-white relative overflow-hidden">
            <p class="text-[11px] font-bold text-white/70 uppercase tracking-wider mb-2">Reste à Payer (Total)</p>
            <h3 class="text-3xl font-extrabold">{{ number_format($summary['remaining'], 0, ',', ' ') }}</h3>
            <p class="text-white/70 text-[13px] font-semibold">FCFA</p>

            @if($summary['total'] > 0)
            <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between text-[12px]">
                <span class="text-white/70">Sur un total annuel de {{ number_format($summary['total'], 0, ',', ' ') }} FCFA</span>
                <span class="px-2 py-1 bg-white/10 rounded font-bold">{{ round((($summary['paid'] + $summary['scholarshipCredit']) / $summary['total']) * 100) }}% Couvert</span>
            </div>
            @endif
            @if(($summary['scholarshipCredit'] ?? 0) > 0)
            <div class="mt-2 flex items-center gap-1.5 text-[12px] text-emerald-300 font-semibold">
                <i class="ph-fill ph-medal"></i> Dont {{ number_format($summary['scholarshipCredit'], 0, ',', ' ') }} FCFA couverts par bourse
            </div>
            @endif
        </div>

        <!-- Prochaine échéance -->
        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Prochaine Échéance</p>
            @php $nextLine = collect($summary['schedule'])->firstWhere('status', 'due'); @endphp
            @if($nextLine)
                <h3 class="text-xl font-extrabold text-slate-900">{{ $nextLine['due_date']->translatedFormat('d F Y') }}</h3>
                @if($nextLine['due_date']->isPast())
                    <p class="text-red-600 text-[12px] font-semibold mt-1"><i class="ph-fill ph-warning-circle"></i> En retard de {{ (int) floor(now()->diffInDays($nextLine['due_date'], true)) }} jours</p>
                @else
                    <p class="text-amber-600 text-[12px] font-semibold mt-1"><i class="ph ph-clock"></i> Dans {{ (int) ceil(now()->diffInDays($nextLine['due_date'], true)) }} jours</p>
                @endif
                <p class="text-[12px] text-slate-500 mt-3">Montant attendu</p>
                <p class="text-lg font-extrabold text-slate-900">{{ number_format($nextLine['amount'], 0, ',', ' ') }} FCFA</p>
            @else
                <p class="text-slate-400 text-[14px] mt-2">Aucune échéance en attente.</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Plan de Paiement -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Plan de Paiement</h3>
            </div>
            <div class="p-5 space-y-5">
                @forelse($summary['schedule'] as $line)
                    @php
                        $lineStyles = [
                            'paid' => ['ph-check-circle text-emerald-500', 'bg-emerald-50 text-emerald-700', 'Payé'],
                            'due' => ['ph-circle text-blue-500', 'bg-blue-50 text-blue-700', 'En Attente'],
                            'upcoming' => ['ph-circle text-slate-300', 'bg-slate-100 text-slate-500', 'À Venir'],
                        ];
                        [$iconClass, $badgeClass, $badgeLabel] = $lineStyles[$line['status']];
                    @endphp
                    <div class="flex items-start gap-3">
                        <i class="ph-fill {{ $iconClass }} text-xl mt-0.5"></i>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-slate-800 text-[14px]">{{ $line['label'] }}</p>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            </div>
                            <p class="text-[12px] text-slate-500">{{ $line['due_date']->translatedFormat('d F Y') }}</p>
                            <p class="text-[13px] font-semibold text-slate-700">{{ number_format($line['amount'], 0, ',', ' ') }} FCFA</p>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-[13px] text-center py-6">Aucune structure tarifaire configurée pour ce niveau.</p>
                @endforelse
            </div>
        </div>

        <!-- Historique des Transactions -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Historique des Transactions</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($transactions as $t)
                <div class="p-5 flex items-center justify-between">
                    <div>
                        <p class="text-[12px] text-slate-500 font-semibold">{{ $t->paid_at->format('d M Y') }}</p>
                        <div class="flex items-center gap-2 mt-0.5">
                            <i class="ph-fill ph-bank text-slate-400"></i>
                            <span class="font-bold text-slate-800 text-[14px]">{{ \App\Modules\Finance\Domain\Models\Payment::METHODS[$t->method] ?? $t->method }}</span>
                        </div>
                        @if($t->reference)
                            <p class="text-[11px] text-slate-400 mt-0.5"># {{ $t->reference }}</p>
                        @endif
                    </div>
                    <span class="font-extrabold text-emerald-600 text-[15px]">+ {{ number_format($t->amount, 0, ',', ' ') }}</span>
                </div>
                @empty
                <div class="p-10 text-center text-slate-400 text-[13px]">
                    <i class="ph ph-clock-countdown text-2xl mb-2"></i>
                    <p>Aucune transaction enregistrée pour cette année scolaire.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
