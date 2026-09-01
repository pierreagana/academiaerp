@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::canteen._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Scanner — Cantine</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Scannez ou saisissez le matricule de l'élève avant de servir le repas. Un élève sans inscription valide est refusé.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif
    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 flex items-center gap-2 border border-red-100" role="alert">
        <i class="ph-fill ph-warning-circle text-lg"></i>
        <span class="font-bold">{{ $errors->first() }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <form method="POST" action="{{ route('school.canteen.scanner.scan') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-[12px] font-bold text-slate-600 mb-2 block">Matricule de l'élève</label>
                    <input type="text" name="matricule" autofocus placeholder="Scannez le QR ou saisissez le matricule" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-[15px] font-bold" value="{{ old('matricule') }}">
                </div>
                <div>
                    <label class="text-[12px] font-bold text-slate-600 mb-2 block">Prix du repas (FCFA)</label>
                    <input type="number" name="price" step="1" min="0" placeholder="1500" class="w-full border border-slate-200 rounded-xl px-4 py-3 text-[13px] font-bold" value="{{ old('price') }}">
                </div>
                <button type="submit" class="w-full bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[14px] py-3.5 rounded-xl transition">Enregistrer le repas</button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-[15px] font-extrabold text-slate-800 mb-4">Repas du jour</h3>
            <div class="space-y-3">
                @forelse($recentMeals as $meal)
                <div class="flex items-center justify-between">
                    <p class="text-[13px] font-bold text-slate-800">{{ $meal->account?->holder ? $meal->account->holder->first_name . ' ' . $meal->account->holder->last_name : 'Compte #' . $meal->account_id }}</p>
                    <span class="text-[11px] text-slate-400">{{ number_format($meal->price, 0, ',', ' ') }} FCFA</span>
                </div>
                @empty
                <p class="text-[13px] text-slate-400">Aucun repas enregistré aujourd'hui.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
