@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-16 bg-white rounded-2xl border border-slate-200 shadow-sm p-8 text-center">
    <div class="w-14 h-14 rounded-2xl {{ $isCancel ? 'bg-slate-100 text-slate-500' : 'bg-emerald-50 text-emerald-600' }} flex items-center justify-center mx-auto mb-4">
        <i class="ph-fill {{ $isCancel ? 'ph-x-circle' : 'ph-clock-countdown' }} text-2xl"></i>
    </div>
    <h2 class="text-lg font-bold text-slate-900">{{ $title }}</h2>
    <p class="text-sm text-slate-500 mt-2">{{ $message }}</p>
    <a href="{{ route('school.billing') }}" class="mt-6 inline-flex items-center justify-center gap-2 bg-[#031C5B] text-white px-6 py-3 rounded-xl text-sm font-bold hover:bg-blue-900 transition">
        Retour à la facturation
    </a>
</div>
@endsection
