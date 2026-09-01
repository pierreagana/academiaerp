@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Forfait</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">
            Forfait actuel : <strong>{{ $currentPackage->name ?? 'Aucun forfait reconnu' }}</strong>. Le changement de forfait fait l'objet d'une validation par notre équipe pour la facturation.
        </p>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 mb-4 text-sm text-red-800 rounded-xl bg-red-50 flex items-center gap-2 border border-red-100" role="alert">
        <i class="ph-fill ph-warning-circle text-lg"></i>
        <span class="font-bold">{{ session('error') }}</span>
    </div>
    @endif

    @if($pendingRequest)
    <div class="p-4 bg-amber-50 border border-amber-100 rounded-2xl text-amber-800 text-sm font-medium flex items-start gap-3">
        <i class="ph-fill ph-clock text-xl shrink-0"></i>
        <div>Demande en cours vers le forfait « {{ $pendingRequest->requestedPackage->name }} » — envoyée le {{ $pendingRequest->created_at->format('d/m/Y') }}, en attente de traitement.</div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($packages as $package)
            @php $isCurrent = $currentPackage && $currentPackage->id === $package->id; @endphp
            <div class="bg-white rounded-2xl border {{ $isCurrent ? 'border-[#031C5B] ring-2 ring-[#031C5B]/10' : 'border-slate-200' }} shadow-sm p-6 flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-[17px] font-extrabold text-slate-900">{{ $package->name }}</h3>
                    @if($package->is_popular)
                        <span class="text-[10px] font-bold px-2 py-1 rounded-full bg-indigo-50 text-indigo-600">Populaire</span>
                    @endif
                </div>
                <p class="text-[13px] text-slate-500 leading-relaxed flex-1">{{ $package->description }}</p>

                <p class="text-[24px] font-extrabold text-slate-900 mt-4">
                    {{ number_format($package->price, 0, ',', ' ') }} <span class="text-[13px] font-semibold text-slate-500">FCFA / {{ $package->billing_cycle }}</span>
                </p>

                <div class="mt-3 space-y-1 text-[12.5px] text-slate-600">
                    @if($package->max_students)<p><i class="ph-fill ph-users mr-1.5 text-slate-400"></i> Jusqu'à {{ number_format($package->max_students, 0, ',', ' ') }} élèves</p>@endif
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 space-y-1.5 max-h-32 overflow-y-auto">
                    @foreach($package->features ?? [] as $feature)
                        <p class="text-[12px] text-slate-600 flex items-center gap-1.5"><i class="ph-fill ph-check-circle text-emerald-500"></i> {{ $feature }}</p>
                    @endforeach
                </div>

                @if($isCurrent)
                    <span class="mt-5 inline-flex items-center justify-center gap-2 bg-[#031C5B]/5 text-[#031C5B] border border-[#031C5B]/20 text-xs font-bold px-4 py-2.5 rounded-xl">
                        <i class="ph-fill ph-check-circle"></i> Forfait actuel
                    </span>
                @elseif($pendingRequest && $pendingRequest->requested_package_id === $package->id)
                    <span class="mt-5 inline-flex items-center justify-center gap-2 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold px-4 py-2.5 rounded-xl">
                        <i class="ph-fill ph-clock"></i> Demande en attente
                    </span>
                @else
                    <form action="{{ route('school.plans.request') }}" method="POST" class="mt-5">
                        @csrf
                        <input type="hidden" name="package_id" value="{{ $package->id }}">
                        <button type="submit" class="w-full bg-[#031C5B] text-white text-xs font-bold px-4 py-2.5 rounded-xl hover:bg-[#031C5B]/90 transition">
                            Demander ce forfait
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
