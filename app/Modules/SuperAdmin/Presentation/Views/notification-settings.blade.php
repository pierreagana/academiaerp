@extends('SuperAdmin::layouts.app')

@section('content')
    <div class="mb-8">
        <h2 class="text-[28px] font-extrabold text-[#111827]">Paramètres de Notification</h2>
        <p class="text-[15px] text-slate-500 mt-1">Configurez Firebase pour l'envoi des notifications push vers l'application mobile et le web.</p>
    </div>

    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-warning-circle text-red-600 text-xl font-bold"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-800 text-lg font-bold">✕</button>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-5 mb-6">
            <h3 class="text-lg font-extrabold text-[#111827]">Réglage des notifications Firebase</h3>
            @if($serviceAccountConfigured)
                <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-full">
                    <i class="ph-fill ph-check-circle"></i> Fichier de service configuré
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 text-xs font-bold px-3 py-1.5 rounded-full">
                    <i class="ph-fill ph-warning"></i> Fichier de service non configuré
                </span>
            @endif
        </div>

        <form action="{{ route('superadmin.notification-settings.update') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Clé API Firebase</label>
                <input type="text" name="firebase_api_key" value="{{ old('firebase_api_key', $settings['firebase_api_key']) }}" placeholder="Clé API Firebase" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Domaine d'authentification Firebase</label>
                <input type="text" name="firebase_auth_domain" value="{{ old('firebase_auth_domain', $settings['firebase_auth_domain']) }}" placeholder="Domaine d'authentification Firebase" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Bucket de stockage Firebase</label>
                <input type="text" name="firebase_storage_bucket" value="{{ old('firebase_storage_bucket', $settings['firebase_storage_bucket']) }}" placeholder="Bucket de stockage Firebase" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">ID expéditeur de messagerie Firebase</label>
                <input type="text" name="firebase_messaging_sender_id" value="{{ old('firebase_messaging_sender_id', $settings['firebase_messaging_sender_id']) }}" placeholder="ID de l'expéditeur de messagerie Firebase" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">ID d'application Firebase</label>
                <input type="text" name="firebase_app_id" value="{{ old('firebase_app_id', $settings['firebase_app_id']) }}" placeholder="ID d'application Firebase" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">ID de mesure Firebase</label>
                <input type="text" name="firebase_measurement_id" value="{{ old('firebase_measurement_id', $settings['firebase_measurement_id']) }}" placeholder="ID de mesure de Firebase" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">ID de projet Firebase <span class="text-red-500">*</span></label>
                <input type="text" name="firebase_project_id" required value="{{ old('firebase_project_id', $settings['firebase_project_id']) }}" placeholder="ID de projet Firebase" class="w-full bg-slate-50 border border-slate-200 text-slate-700 rounded-lg px-4 py-2.5 outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-medium transition">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Fichier de service Firebase <span class="text-indigo-500 font-medium text-xs">(fichier .json uniquement)</span>
                </label>
                <div class="flex items-center gap-2">
                    <input type="file" name="firebase_service_account" accept="application/json,.json" class="w-full bg-slate-50 border border-slate-200 text-slate-500 text-sm rounded-lg file:mr-3 file:py-2.5 file:px-4 file:border-0 file:bg-[#031C5B] file:text-white file:text-xs file:font-bold file:rounded-l-lg cursor-pointer">
                </div>
            </div>

            <div class="md:col-span-2 flex items-center justify-between pt-2">
                <a href="{{ route('superadmin.notification-settings.sample') }}" class="inline-flex items-center gap-2 text-xs font-bold text-indigo-600 border border-indigo-200 hover:bg-indigo-50 px-4 py-2.5 rounded-xl transition">
                    <i class="ph ph-file-text"></i> Fichier de service d'exemple
                </a>
                <button type="submit" class="flex items-center gap-2 bg-[#031C5B] text-white px-8 py-3 rounded-xl text-sm font-bold hover:bg-blue-900 transition shadow-sm cursor-pointer">
                    <i class="ph ph-floppy-disk text-lg font-bold"></i> Soumettre
                </button>
            </div>
        </form>
    </div>
@endsection
