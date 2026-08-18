@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumbs -->
    <div class="flex items-center text-sm text-slate-500 gap-2">
        <a href="{{ route('school.academic.parents') }}" class="hover:text-slate-800 transition">Parents</a>
        <i class="ph ph-caret-right text-xs"></i>
        <span class="text-slate-800 font-medium">Nouvel Enregistrement</span>
    </div>

    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-slate-800">{{ isset($guardian) ? 'Modifier le Parent' : 'Enregistrer un Nouveau Parent' }}</h2>
        <p class="text-slate-500 mt-1">Saisissez les détails du parent ou tuteur et associez-le aux étudiants.</p>
    </div>

    @if($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold">Il y a des erreurs dans le formulaire :</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Layout Container -->
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Sidebar Steps -->
        <div class="w-full lg:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-6">Étapes d'enregistrement</h3>
                
                <div class="relative">
                    <!-- Vertical Line -->
                    <div class="absolute left-4 top-4 bottom-4 w-px bg-slate-200"></div>

                    <div class="space-y-6 relative">
                        <!-- Step 1 -->
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-[#031C5B] text-white flex items-center justify-center font-bold text-sm shrink-0 z-10 shadow-sm">
                                1
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm">Informations Personnelles</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-snug">Détails de contact et identité</p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm shrink-0 z-10">
                                2
                            </div>
                            <div class="opacity-70">
                                <h4 class="font-bold text-slate-600 text-sm">Association Étudiant</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-snug">Lier aux dossiers existants</p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex gap-4">
                            <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm shrink-0 z-10">
                                3
                            </div>
                            <div class="opacity-70">
                                <h4 class="font-bold text-slate-600 text-sm">Accès & Sécurité</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-snug">Identifiants du portail</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Assistance Box -->
                <div class="mt-8 bg-purple-50/80 rounded-xl p-4 border border-purple-100/50">
                    <div class="flex items-start gap-3">
                        <i class="ph-fill ph-sparkle text-purple-600 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Assistance AI</h4>
                            <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                                Les doublons potentiels seront signalés automatiquement lors de la saisie.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <div class="flex-1">
            <form action="{{ isset($guardian) ? route('school.academic.parents.update', $guardian->id) : route('school.academic.parents.store') }}" method="POST">
                @csrf
                @if(isset($guardian))
                    @method('PUT')
                @endif
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Form Header -->
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-3 text-[#031C5B]">
                        <i class="ph ph-user text-xl"></i>
                        <h3 class="text-lg font-bold text-slate-800">Informations Personnelles</h3>
                    </div>
                    <span class="bg-[#031C5B] text-white text-xs font-medium px-3 py-1 rounded-md shadow-sm">
                        Étape 1 sur 3
                    </span>
                </div>

                <!-- Form Body -->
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom Complet -->
                        <div>
                            <label for="name" class="block text-sm font-bold text-slate-700 mb-2">Nom Complet <span class="text-red-500">*</span></label>
                            <input type="text" id="name" name="name" required value="{{ old('name', $guardian->name ?? '') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#031C5B] focus:border-[#031C5B] transition placeholder:text-slate-400" placeholder="Ex: Jean Dupont">
                        </div>

                        <!-- Relation -->
                        <div>
                            <label for="relation" class="block text-sm font-bold text-slate-700 mb-2">Relation avec l'étudiant <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select id="relation" name="relation" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#031C5B] focus:border-[#031C5B] appearance-none transition text-slate-700">
                                    <option value="" disabled {{ !isset($guardian) && !old('relation') ? 'selected' : '' }}>Sélectionner...</option>
                                    <option value="pere" {{ old('relation', $guardian->relation ?? '') == 'pere' ? 'selected' : '' }}>Père</option>
                                    <option value="mere" {{ old('relation', $guardian->relation ?? '') == 'mere' ? 'selected' : '' }}>Mère</option>
                                    <option value="tuteur" {{ old('relation', $guardian->relation ?? '') == 'tuteur' ? 'selected' : '' }}>Tuteur légal</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                    <i class="ph ph-caret-down"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Numéro de Téléphone -->
                        <div>
                            <label for="phone" class="block text-sm font-bold text-slate-700 mb-2">Numéro de Téléphone <span class="text-red-500">*</span></label>
                            <input type="text" id="phone" name="phone" required value="{{ old('phone', $guardian->phone ?? '') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#031C5B] focus:border-[#031C5B] transition placeholder:text-slate-400" placeholder="Ex: 77 123 45 67">
                        </div>

                        <!-- Adresse Email -->
                        <div>
                            <label for="email" class="block text-sm font-bold text-slate-700 mb-2">Adresse Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $guardian->email ?? '') }}" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#031C5B] focus:border-[#031C5B] transition placeholder:text-slate-400" placeholder="jean.dupont@exemple.com">
                        </div>
                    </div>

                    <!-- Adresse Domicilière -->
                    <div>
                        <label for="address" class="block text-sm font-bold text-slate-700 mb-2">Adresse Domicilière</label>
                        <textarea id="address" name="address" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#031C5B] focus:border-[#031C5B] transition placeholder:text-slate-400 min-h-[100px] resize-y" placeholder="Saisissez l'adresse complète...">{{ old('address', $guardian->address ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Association Étudiant -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mt-6">
                <div class="p-6 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-3 text-[#031C5B]">
                        <i class="ph ph-users-three text-xl"></i>
                        <h3 class="text-lg font-bold text-slate-800">Association aux Étudiants</h3>
                    </div>
                    <span class="bg-[#031C5B] text-white text-xs font-medium px-3 py-1 rounded-md shadow-sm">
                        Étape 2 sur 3
                    </span>
                </div>
                <div class="p-6">
                    @php $linkedStudentIds = old('student_ids', isset($guardian) ? $guardian->students->pluck('id')->all() : []); @endphp
                    @if($students->isEmpty())
                        <p class="text-[13px] text-slate-500">Aucun étudiant enregistré pour le moment. <a href="{{ route('school.academic.students.create') }}" class="text-[#031C5B] font-bold hover:underline">Ajouter un étudiant</a> pour pouvoir l'associer ici.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-64 overflow-y-auto">
                            @foreach($students as $s)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-100 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="student_ids[]" value="{{ $s->id }}" {{ in_array($s->id, $linkedStudentIds) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]/20">
                                <span class="text-[13px] font-semibold text-slate-700">{{ $s->first_name }} {{ $s->last_name }}@if($s->academicClass) <span class="text-slate-400 font-normal">— {{ $s->academicClass->name }}</span>@endif</span>
                            </label>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-end gap-4">
                <a href="{{ route('school.academic.parents') }}" class="px-6 py-2.5 border border-slate-200 rounded-xl text-sm font-bold text-slate-600 hover:bg-slate-50 transition bg-white shadow-sm">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2.5 bg-[#031C5B] rounded-xl text-sm font-bold text-white hover:bg-[#031C5B]/90 transition shadow-sm flex items-center gap-2">
                    {{ isset($guardian) ? 'Enregistrer les modifications' : 'Enregistrer le parent' }} <i class="ph ph-check"></i>
                </button>
            </div>
            </form>
        </div>
    </div>
</div>
@endsection
