@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Extensions</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">
            Modules payants non inclus dans votre forfait actuel{{ $packageName ? " ($packageName)" : '' }}. Leur activation fait l'objet de frais supplémentaires.
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

    @if(!$hasPackage)
        <div class="p-6 bg-blue-50 border border-blue-100 rounded-2xl text-blue-900 text-sm font-medium flex items-start gap-3">
            <i class="ph-fill ph-info text-xl shrink-0"></i>
            <div>
                Votre établissement n'a pas de forfait reconnu associé — dans ce cas, tous les modules de la plateforme vous restent accessibles sans restriction, et il n'y a donc aucune extension à activer séparément.
            </div>
        </div>
    @elseif($extensions->isEmpty())
        <div class="p-6 bg-emerald-50 border border-emerald-100 rounded-2xl text-emerald-900 text-sm font-medium flex items-start gap-3">
            <i class="ph-fill ph-check-circle text-xl shrink-0"></i>
            <div>
                Votre forfait « {{ $packageName }} » inclut déjà tous les modules disponibles sur la plateforme.
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($extensions as $ext)
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 flex flex-col">
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                        <i class="ph {{ $ext->icon }} text-xl"></i>
                    </div>
                    <h3 class="text-[16px] font-bold text-slate-900">{{ $ext->name }}</h3>
                    <p class="text-[13px] text-slate-500 mt-1.5 flex-1 leading-relaxed">{{ $ext->description }}</p>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-[20px] font-extrabold text-slate-900">
                            {{ number_format($ext->price, 0, ',', ' ') }} <span class="text-[13px] font-semibold text-slate-500">{{ $systemCurrency }} / an</span>
                        </p>
                    </div>

                    @if($ext->status === 'approved')
                        <span class="mt-4 inline-flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold px-4 py-2.5 rounded-xl">
                            <i class="ph-fill ph-check-circle"></i> Activé
                        </span>
                    @elseif($ext->status === 'pending')
                        <span class="mt-4 inline-flex items-center justify-center gap-2 bg-amber-50 text-amber-700 border border-amber-200 text-xs font-bold px-4 py-2.5 rounded-xl">
                            <i class="ph-fill ph-clock"></i> Demande en attente
                        </span>
                    @else
                        <form action="{{ route('school.extensions.store') }}" method="POST" class="mt-4">
                            @csrf
                            <input type="hidden" name="module_name" value="{{ $ext->name }}">
                            <button type="submit" class="w-full bg-[#031C5B] text-white text-xs font-bold px-4 py-2.5 rounded-xl hover:bg-[#031C5B]/90 transition">
                                Demander l'activation
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
