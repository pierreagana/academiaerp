@extends('SchoolDashboard::layouts.app')

@section('title', 'Mon Profil')

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800">Mon Profil</h2>
    </div>

    @if (session('success'))
        <div class="bg-green-50 text-green-700 p-4 rounded-xl font-medium border border-green-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <form action="{{ route('school.profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informations personnelles -->
                <div class="md:col-span-2 border-b border-slate-100 pb-4 mb-2">
                    <h3 class="text-lg font-bold text-slate-800">Informations Personnelles</h3>
                    <p class="text-sm text-slate-500">Mettez à jour vos informations de base et votre adresse email.</p>
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nom complet *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                    @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Sécurité -->
                <div class="md:col-span-2 border-b border-slate-100 pb-4 mt-4 mb-2">
                    <h3 class="text-lg font-bold text-slate-800">Sécurité & Mot de passe</h3>
                    <p class="text-sm text-slate-500">Laissez les champs vides si vous ne souhaitez pas modifier votre mot de passe.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                    @error('current_password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Nouveau mot de passe</label>
                    <input type="password" name="new_password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                    @error('new_password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-1">
                    <label class="block text-sm font-bold text-slate-700 mb-1">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="new_password_confirmation" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 outline-none focus:border-blue-600 focus:bg-white transition">
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-primary-dynamic text-white font-bold hover:opacity-95 transition shadow-sm flex items-center gap-2">
                    <i class="ph ph-check-circle text-lg"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
