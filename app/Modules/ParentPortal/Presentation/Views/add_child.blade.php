@extends('ParentPortal::layout')

@section('title', 'Ajouter un enfant')

@section('content')
<div class="max-w-md mx-auto py-4">
    <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mx-auto mb-3 shadow-xs">
            <span class="material-symbols-outlined text-[28px]">person_add</span>
        </div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Rattacher un Enfant</h1>
        <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">Renseignez le code établissement et le matricule de l'élève figurant sur son bulletin ou sa carte scolaire.</p>
    </div>

    @if($errors->any())
    <div class="p-4 mb-5 text-xs text-rose-800 rounded-2xl bg-rose-50 border border-rose-200/80 font-semibold space-y-1">
        @foreach($errors->all() as $error) 
            <p>&bull; {{ $error }}</p> 
        @endforeach
    </div>
    @endif

    <form action="{{ route('parent.children.add') }}" method="POST" class="bg-white rounded-3xl border border-slate-100 shadow-[0_4px_20px_-4px_rgba(0,0,0,0.05)] p-6 sm:p-8 space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Code Établissement</label>
            <input type="text" name="school_code" value="{{ old('school_code') }}" required autofocus placeholder="Ex: ACAD-ECO7278"
                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold rounded-2xl px-4 py-3 outline-none focus:border-blue-500 focus:bg-white transition">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-700 mb-1.5">Matricule de l'Élève</label>
            <input type="text" name="matricule" value="{{ old('matricule') }}" required placeholder="Ex: STU-0001"
                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-sm font-semibold rounded-2xl px-4 py-3 outline-none focus:border-blue-500 focus:bg-white transition">
        </div>
        <button type="submit" class="w-full bg-[#061536] hover:bg-[#061536]/90 text-white font-bold text-xs py-3.5 rounded-2xl transition shadow-md shadow-blue-950/20 mt-2">
            Ajouter cet élève
        </button>
    </form>
    
    <p class="text-[11.5px] text-slate-400 mt-4 text-center leading-relaxed">
        L'enfant sera automatiquement validé si votre numéro de téléphone correspond au tuteur renseigné lors de l'inscription à l'école.
    </p>
</div>
@endsection
