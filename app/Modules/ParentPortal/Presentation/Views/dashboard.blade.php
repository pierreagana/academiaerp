@extends('ParentPortal::layout')

@section('title', "Vue d'ensemble")

@section('content')

@php
    $feeBadge = [
        'paid'          => ['label' => 'Scolarité à jour',      'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60'],
        'late'          => ['label' => 'Scolarité en retard',   'class' => 'bg-rose-50 text-rose-700 border-rose-200/60'],
        'partial'       => ['label' => 'Paiement partiel',      'class' => 'bg-amber-50 text-amber-700 border-amber-200/60'],
        'pending'       => ['label' => 'En attente',            'class' => 'bg-slate-100 text-slate-600 border-slate-200/60'],
        'unconfigured'  => ['label' => 'Non configuré',         'class' => 'bg-slate-100 text-slate-400 border-slate-200/60'],
    ];
@endphp

<!-- TOP DASHBOARD HEADER -->
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">Vue d'ensemble</h1>
        <p class="text-sm font-medium text-slate-500 mt-1">
            @if($children->isNotEmpty())
                Suivi stratégique pour {{ $children->pluck('first_name')->join(' et ') }}.
            @else
                Suivi stratégique de la scolarité de vos enfants.
            @endif
        </p>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="flex items-center gap-3">
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 bg-blue-100/80 hover:bg-blue-200/80 text-[#061536] font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-xs">
            <span class="material-symbols-outlined text-[17px]">download</span>
            <span>Télécharger Bilan</span>
        </button>

        <a href="{{ route('parent.notifications') }}"
           class="inline-flex items-center gap-2 bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-md shadow-blue-950/20 relative">
            <span class="material-symbols-outlined text-[17px]">add_comment</span>
            <span>Messages</span>
            @if(($unreadNotificationsCount ?? 0) > 0)
                <span class="absolute -top-1.5 -right-1.5 bg-rose-500 text-white text-[9px] font-black w-5 h-5 rounded-full flex items-center justify-center shadow">
                    {{ min(99, $unreadNotificationsCount) }}
                </span>
            @endif
        </a>
    </div>
</div>

@if($children->isEmpty())
<!-- EMPTY STATE -->
<div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-12 text-center">
    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-4">
        <i class="ph-bold ph-users-three text-3xl"></i>
    </div>
    <h3 class="text-lg font-bold text-slate-800 mb-1">Aucun enfant rattaché</h3>
    <p class="text-sm text-slate-500 max-w-md mx-auto mb-6">Ajoutez facilement vos enfants pour suivre leurs notes, présences, devoirs et services scolaires en temps réel.</p>
    <a href="{{ route('parent.children.add-form') }}" class="inline-flex items-center gap-2 bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs px-5 py-3 rounded-xl transition shadow-md">
        <i class="ph-bold ph-user-plus text-base"></i>
        <span>Rattacher un enfant</span>
    </a>
</div>
@else

<!-- CHILDREN CARDS GRID -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    @foreach($children as $kid)
    <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6 hover:shadow-lg transition-all">

        <!-- Top row: Avatar + Name + Class + Chevron -->
        <div class="flex items-center justify-between gap-4 mb-5">
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="relative shrink-0">
                    <div class="w-13 h-13 rounded-2xl overflow-hidden shadow-sm bg-gradient-to-tr from-slate-800 to-slate-950 flex items-center justify-center text-white font-bold text-base border border-slate-100">
                        @if($kid->photo_path)
                            <img src="{{ asset('storage/' . $kid->photo_path) }}" alt="{{ $kid->first_name }}" class="w-full h-full object-cover">
                        @else
                            <span class="w-12 h-12 flex items-center justify-center bg-blue-50 text-[#061536] font-black text-sm rounded-2xl">
                                {{ substr($kid->first_name, 0, 1) }}{{ substr($kid->last_name, 0, 1) }}
                            </span>
                        @endif
                    </div>
                    {{-- Real RFID badge status dot --}}
                    @if($kid->accessStatus === 'in_school')
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white shadow-sm" title="En classe"></span>
                    @elseif($kid->accessStatus === 'out_of_school')
                        <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-slate-400 rounded-full border-2 border-white shadow-sm" title="Hors école"></span>
                    @endif
                </div>
                <div class="min-w-0">
                    <h2 class="text-lg font-black text-slate-900 leading-tight truncate">{{ $kid->first_name }} {{ $kid->last_name }}</h2>
                    <p class="text-[12px] font-semibold text-slate-500 mt-0.5">{{ $kid->academicClass->name ?? 'Non assignée' }}</p>
                    {{-- Access status label --}}
                    @if($kid->accessStatus === 'in_school')
                        <span class="inline-flex items-center gap-1 text-[10.5px] font-bold text-emerald-700 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                            En classe · {{ $kid->lastAccessAt?->format('H:i') }}
                        </span>
                    @elseif($kid->accessStatus === 'out_of_school')
                        <span class="inline-flex items-center gap-1 text-[10.5px] font-bold text-slate-400 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></span>
                            Hors école · {{ $kid->lastAccessAt?->format('H:i') }}
                        </span>
                    @endif
                </div>
            </div>

            <a href="{{ route('parent.bulletin', $kid->id) }}"
               class="w-10 h-10 rounded-2xl bg-slate-50 hover:bg-blue-50 text-slate-400 hover:text-[#061536] flex items-center justify-center transition shrink-0"
               title="Voir le détail de {{ $kid->first_name }}">
                <span class="material-symbols-outlined text-[24px]">chevron_right</span>
            </a>
        </div>

        <!-- 2 Metric Cards Grid -->
        <div class="grid grid-cols-2 gap-3.5 mb-4">

            <!-- Moyenne Générale -->
            <div class="bg-[#F8FAFC] rounded-2xl p-4 border border-slate-100 flex flex-col justify-between">
                <div>
                    <span class="text-[11.5px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Moyenne Générale</span>
                    <div class="text-2xl font-black text-slate-900 tracking-tight">
                        @if($kid->average !== null)
                            {{ number_format($kid->average, 1) }}<span class="text-sm font-bold text-slate-400">/20</span>
                        @else
                            <span class="text-slate-400 text-lg">Non calculée</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 flex items-center gap-1.5">
                    @if($kid->average !== null)
                        @php $trend = $kid->averageTrend; @endphp
                        @if($trend !== null)
                            @if($trend > 0)
                                <span class="inline-flex items-center gap-1 text-[11.5px] font-bold text-emerald-600">
                                    <span class="material-symbols-outlined text-[16px]">trending_up</span>
                                    <span>+{{ number_format($trend, 1) }} pts</span>
                                </span>
                            @elseif($trend < 0)
                                <span class="inline-flex items-center gap-1 text-[11.5px] font-bold text-rose-600">
                                    <span class="material-symbols-outlined text-[16px]">trending_down</span>
                                    <span>{{ number_format($trend, 1) }} pts</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11.5px] font-bold text-blue-600">
                                    <span class="material-symbols-outlined text-[16px]">trending_flat</span>
                                    <span>Stable</span>
                                </span>
                            @endif
                        @else
                            @if($kid->average >= 14)
                                <span class="inline-flex items-center gap-1 text-[11.5px] font-bold text-emerald-600">
                                    <span class="material-symbols-outlined text-[16px]">trending_up</span>
                                    <span>Très bien</span>
                                </span>
                            @elseif($kid->average >= 10)
                                <span class="inline-flex items-center gap-1 text-[11.5px] font-bold text-blue-600">
                                    <span class="material-symbols-outlined text-[16px]">trending_flat</span>
                                    <span>Satisfaisant</span>
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[11.5px] font-bold text-rose-600">
                                    <span class="material-symbols-outlined text-[16px]">trending_down</span>
                                    <span>À améliorer</span>
                                </span>
                            @endif
                        @endif
                    @else
                        <span class="text-[11px] font-medium text-slate-400">En attente des examens</span>
                    @endif
                </div>
            </div>

            <!-- Assiduité -->
            <div class="bg-[#F8FAFC] rounded-2xl p-4 border border-slate-100 flex flex-col justify-between relative">
                @if($kid->attendanceRate !== null && $kid->attendanceRate < 90)
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 absolute top-3.5 right-3.5 ring-4 ring-rose-100"></span>
                @endif

                <div>
                    <span class="text-[11.5px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Assiduité</span>
                    <div class="text-2xl font-black text-slate-900 tracking-tight">
                        @if($kid->attendanceRate !== null)
                            {{ $kid->attendanceRate }}%
                        @else
                            <span class="text-slate-400 text-lg">—</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    @if($kid->attendanceRate !== null)
                        @if($kid->attendanceRate >= 95)
                            <span class="inline-block text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100/70 text-emerald-800">Excellente</span>
                        @elseif($kid->attendanceRate >= 85)
                            <span class="inline-block text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-100/70 text-blue-800">Bonne assiduité</span>
                        @else
                            <span class="inline-block text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800">Attention Requise</span>
                        @endif
                        {{-- Real absences & retards count --}}
                        <div class="flex items-center gap-2 mt-2 text-[10.5px] font-semibold text-slate-500">
                            @if(($kid->unjustifiedAbsences ?? 0) > 0)
                                <span class="text-rose-600">{{ $kid->unjustifiedAbsences }} abs.</span>
                            @endif
                            @if(($kid->lateCount ?? 0) > 0)
                                <span class="text-amber-600">{{ $kid->lateCount }} retard(s)</span>
                            @endif
                            @if(!($kid->unjustifiedAbsences ?? 0) && !($kid->lateCount ?? 0))
                                <span class="text-emerald-600">Aucun incident</span>
                            @endif
                        </div>
                    @else
                        <span class="text-[11px] font-medium text-slate-400">Aucun enregistrement</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Secondary status pills & quick links -->
        <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-[12px]">
            <div class="flex items-center gap-2">
                <span class="font-bold px-2.5 py-1 rounded-xl border text-[11px] {{ $feeBadge[$kid->feeStatus]['class'] }}">
                    {{ $feeBadge[$kid->feeStatus]['label'] }}
                </span>

                @if($kid->latestAward)
                    <a href="{{ route('parent.diplomes', $kid->id) }}" class="inline-flex items-center gap-1 font-bold px-2.5 py-1 rounded-xl bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition text-[11px]">
                        <i class="ph-fill ph-medal text-[13px]"></i>
                        <span>{{ \Illuminate\Support\Str::limit($kid->latestAward->type->name ?? 'Diplôme', 16) }}</span>
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-1 font-bold text-slate-500">
                <a href="{{ route('parent.bulletin', $kid->id) }}"    class="px-2 py-1 rounded-lg hover:bg-slate-100 hover:text-slate-900 transition">Bulletin</a>
                <a href="{{ route('parent.homework', $kid->id) }}"   class="px-2 py-1 rounded-lg hover:bg-slate-100 hover:text-slate-900 transition">Devoirs</a>
                <a href="{{ route('parent.canteen', $kid->id) }}"    class="px-2 py-1 rounded-lg hover:bg-slate-100 hover:text-slate-900 transition">Cantine</a>
                <a href="{{ route('parent.transport', $kid->id) }}"  class="px-2 py-1 rounded-lg hover:bg-slate-100 hover:text-slate-900 transition">Bus</a>
                <a href="{{ route('parent.school-access') }}?student={{ $kid->id }}" class="px-2 py-1 rounded-lg hover:bg-slate-100 hover:text-slate-900 transition">Badge</a>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endif


<!-- SCHOOL TRACK CARD -->
@php
    $stActive = $schoolTrackStatus['active'] ?? false;
    $stEnabled = $schoolTrackStatus['moduleEnabled'] ?? false;
    $stLocked = $stEnabled && !$stActive;
@endphp
<div id="school-track-card" class="bg-gradient-to-r from-[#061536] to-[#0d2764] rounded-3xl p-6 text-white shadow-lg relative overflow-hidden"
     x-data="{ open: {{ request()->query('school_track') === 'locked' ? 'true' : 'false' }} }"
     @if(request()->query('school_track') === 'locked') x-init="$nextTick(() => $el.scrollIntoView({ behavior: 'smooth', block: 'center' }))" @endif>
    <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-blue-300 shrink-0 text-2xl border border-white/10">
                <i class="ph-fill ph-compass"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h3 class="text-base font-extrabold text-white">School Track</h3>
                    @if($stActive)
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Actif</span>
                    @elseif(!$stEnabled)
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-white/10 text-slate-300">Indisponible</span>
                    @else
                        <span class="text-[10px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">Découverte</span>
                    @endif
                </div>
                <p class="text-xs text-blue-200/80 mt-1 max-w-xl">
                    @if($stActive)
                        Votre abonnement est actif jusqu'au {{ \Carbon\Carbon::parse($schoolTrackStatus['expiresAt'])->translatedFormat('d M Y') }}. Comparez et découvrez les établissements depuis votre application mobile.
                    @else
                        Découvrez, comparez les programmes et trouvez les meilleurs établissements scolaires pour vos enfants.
                    @endif
                </p>
            </div>
        </div>

        @if($stLocked)
        <button type="button" @click="open = true" 
                class="shrink-0 bg-white hover:bg-blue-50 text-[#061536] font-extrabold text-xs px-4 py-2.5 rounded-xl transition shadow-md">
            Souscrire School Track
        </button>
        @endif
    </div>

    @if($stLocked)
    <!-- Modal Subscription -->
    <div x-show="open" style="display:none;" class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-50 flex items-center justify-center p-4 text-slate-800" x-on:keydown.escape.window="open=false">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl" x-on:click.outside="open=false">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#061536] flex items-center justify-center font-bold">
                        <i class="ph-bold ph-compass text-lg"></i>
                    </div>
                    <h3 class="text-base font-extrabold text-slate-900">Abonnement School Track</h3>
                </div>
                <button type="button" x-on:click="open=false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
            </div>
            <p class="text-xs text-slate-500 mb-5">Accédez au comparateur d'écoles, aux taux de réussite vérifiés, aux équipements et aux avis parents directement sur mobile.</p>

            <form action="{{ route('parent.school-track.subscribe') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-[12px] font-bold text-slate-700 block mb-2">Choisir une formule</label>
                    <div class="space-y-2">
                        @foreach($schoolTrackStatus['plans'] ?? [] as $planKey => $plan)
                        <label class="flex items-center justify-between gap-3 border border-slate-200 hover:border-blue-500 rounded-2xl p-3.5 cursor-pointer transition">
                            <span class="flex items-center gap-3">
                                <input type="radio" name="plan" value="{{ $planKey }}" {{ $loop->first ? 'checked' : '' }} class="accent-[#061536]">
                                <span class="text-xs font-bold text-slate-800">{{ $plan['label'] }}</span>
                            </span>
                            <span class="text-xs font-black text-[#061536]">{{ number_format($plan['price'], 0, ',', ' ') }} FCFA</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="text-[12px] font-bold text-slate-700 block mb-2">Moyen de paiement</label>
                    <select name="payment_method" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-xs font-bold text-slate-800 outline-none focus:border-blue-500">
                        @foreach($schoolTrackStatus['paymentMethods'] ?? [] as $methodKey => $methodLabel)
                        <option value="{{ $methodKey }}" {{ $methodKey === 'cash' ? 'selected' : '' }}>{{ $methodLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs py-3 rounded-xl transition shadow-md">
                    Confirmer l'abonnement
                </button>
            </form>
        </div>
    </div>
    @endif
</div>

<!-- BOTTOM 2 COLUMNS: UPCOMING HOMEWORK & SCHOOL NEWS -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    
    <!-- Upcoming homework & exams -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-blue-600">assignment</span>
                <h3 class="text-sm font-extrabold text-slate-900">Devoirs & Examens imminents</h3>
            </div>
            <span class="text-[11px] font-bold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">{{ count($upcoming) }} à venir</span>
        </div>

        <div class="space-y-3">
            @forelse($upcoming as $item)
            <a href="{{ route('parent.homework', $item->studentId) }}" class="flex items-start justify-between gap-3 p-3 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100 group">
                <div class="flex items-start gap-3 min-w-0">
                    <span class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0 {{ $item->type === 'interrogation' ? 'bg-rose-500' : 'bg-blue-500' }}"></span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold text-slate-800 group-hover:text-blue-700 transition truncate">{{ $item->title }}</p>
                        <p class="text-[11px] text-slate-500 font-medium mt-0.5">
                            {{ $item->studentFirstName }} &bull; {{ $item->subject->name ?? 'Matière' }}
                        </p>
                    </div>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-[11px] font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-lg block">
                        {{ $item->scheduled_at ? $item->scheduled_at->translatedFormat('D d M') : 'Date non fixée' }}
                    </span>
                </div>
            </a>
            @empty
            <div class="py-8 text-center text-slate-400 text-xs">
                <i class="ph-bold ph-calendar-check text-2xl text-slate-300 mb-1 block"></i>
                Aucun devoir ou examen programmé pour le moment.
            </div>
            @endforelse
        </div>
    </div>

    <!-- School news and announcements -->
    <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[20px] text-indigo-600">campaign</span>
                <h3 class="text-sm font-extrabold text-slate-900">Dernières actualités</h3>
            </div>
            <a href="{{ route('parent.notifications') }}" class="text-[11.5px] font-bold text-blue-600 hover:underline">Voir tout</a>
        </div>

        <div class="space-y-4">
            @forelse($news as $event)
            <div class="p-3 rounded-2xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10.5px] font-extrabold uppercase tracking-wider text-blue-600">{{ $event->school->name ?? 'Établissement' }}</span>
                    <span class="text-[11px] font-semibold text-slate-400">{{ $event->start_at->translatedFormat('d M Y') }}</span>
                </div>
                <h4 class="text-xs font-bold text-slate-800 mt-1">{{ $event->title }}</h4>
                @if($event->description)
                    <p class="text-[11.5px] text-slate-500 mt-1 leading-relaxed">{{ \Illuminate\Support\Str::limit($event->description, 100) }}</p>
                @endif
            </div>
            @empty
            <div class="py-8 text-center text-slate-400 text-xs">
                <i class="ph-bold ph-newspaper text-2xl text-slate-300 mb-1 block"></i>
                Aucune actualité publiée pour le moment.
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection
