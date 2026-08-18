@extends('ParentPortal::layout')

@section('title', 'Ajouter un enfant')

@section('content')
<div class="max-w-md mx-auto">
    <h1 class="text-[22px] font-bold text-slate-900 mb-1">Ajouter un enfant</h1>
    <p class="text-[13.5px] text-slate-500 mb-6">Renseignez le code établissement et le matricule de l'élève. Ces informations figurent sur son bulletin ou sa carte d'élève.</p>

    @if($errors->any())
    <div class="p-3 mb-4 text-[13px] text-red-800 rounded-lg bg-red-50 border border-red-100">
        @foreach($errors->all() as $error) <p>{{ $error }}</p> @endforeach
    </div>
    @endif

    <form action="{{ route('parent.children.add') }}" method="POST" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Code établissement</label>
            <input type="text" name="school_code" value="{{ old('school_code') }}" required autofocus placeholder="Ex: ACAD-ECO7278"
                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
        </div>
        <div>
            <label class="block text-[12.5px] font-bold text-slate-600 mb-1.5">Matricule de l'élève</label>
            <input type="text" name="matricule" value="{{ old('matricule') }}" required placeholder="Ex: STU-0001"
                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-xl px-4 py-3 outline-none focus:border-[#031C5B]">
        </div>
        <button type="submit" class="w-full bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[14px] py-3 rounded-xl transition">
            Ajouter cet enfant
        </button>
    </form>
    <p class="text-[12px] text-slate-400 mt-4 text-center">
        L'enfant n'apparaîtra que si le numéro de téléphone de votre compte correspond à celui déjà enregistré par l'établissement.
    </p>
</div>
@endsection
