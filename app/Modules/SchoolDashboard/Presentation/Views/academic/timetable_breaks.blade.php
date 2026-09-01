@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestion des Pauses</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Gérez les récréations et pauses déjeuner par classe et par jour, affichées dans l'éditeur d'emploi du temps.</p>
        </div>
        <a href="{{ route('school.academic.timetable.create', ['class_id' => $classId]) }}" class="text-[12.5px] font-semibold text-[#2F5F76] hover:underline flex items-center gap-1">
            <i class="ph-bold ph-arrow-left"></i> Retour à l'éditeur
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

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

    <!-- Class selector -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex items-center gap-3">
        <span class="text-[13px] font-semibold text-slate-700">Classe :</span>
        <form action="{{ route('school.academic.timetable.breaks') }}" method="GET" class="m-0">
            <select name="class_id" onchange="this.form.submit()" class="appearance-none bg-white border border-slate-200 text-slate-700 text-[13px] font-medium rounded-lg px-3 py-1.5 outline-none focus:border-[#2F5F76] min-w-[180px] cursor-pointer">
                @forelse($classes as $class)
                    <option value="{{ $class->id }}" {{ (string) $classId === (string) $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                @empty
                    <option value="">Aucune classe</option>
                @endforelse
            </select>
        </form>
    </div>

    <!-- Top Section: Add/Edit Break Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill {{ isset($editBreak) ? 'ph-pencil-simple' : 'ph-plus-circle' }} text-primary-dynamic"></i>
                {{ isset($editBreak) ? 'Modifier la pause' : 'Ajouter une pause' }}
            </h2>
            @if(isset($editBreak))
            <a href="{{ route('school.academic.timetable.breaks', ['class_id' => $classId]) }}" class="text-[12px] font-medium text-slate-500 hover:text-slate-800 transition">Annuler l'édition</a>
            @endif
        </div>
        <div class="p-5">
            <form action="{{ isset($editBreak) ? route('school.academic.timetable.breaks.update', $editBreak->id) : route('school.academic.timetable.breaks.store') }}" method="POST">
                @csrf
                @if(isset($editBreak))
                    @method('PUT')
                @endif
                <input type="hidden" name="academic_class_id" value="{{ old('academic_class_id', $editBreak->academic_class_id ?? $classId) }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">

                    <!-- Jour -->
                    <div>
                        <label for="day_of_week" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Jour <span class="text-red-500">*</span></label>
                        <select id="day_of_week" name="day_of_week" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                            @foreach($days as $value => $label)
                            <option value="{{ $value }}" {{ old('day_of_week', $editBreak->day_of_week ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" required value="{{ old('name', $editBreak->name ?? '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm"
                            placeholder="Ex: Récréation">
                    </div>

                    <!-- Heure début -->
                    <div>
                        <label for="start_time" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Heure de début <span class="text-red-500">*</span></label>
                        <input type="time" id="start_time" name="start_time" required value="{{ old('start_time', isset($editBreak) ? substr($editBreak->start_time, 0, 5) : '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                    </div>

                    <!-- Heure fin -->
                    <div>
                        <label for="end_time" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Heure de fin <span class="text-red-500">*</span></label>
                        <input type="time" id="end_time" name="end_time" required value="{{ old('end_time', isset($editBreak) ? substr($editBreak->end_time, 0, 5) : '') }}"
                            class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm">
                    </div>

                    <!-- Couleur -->
                    <div>
                        <label for="color" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Couleur <span class="text-red-500">*</span></label>
                        <select id="color" name="color" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm appearance-none">
                            @foreach($colors as $color)
                            <option value="{{ $color }}" {{ old('color', $editBreak->color ?? 'slate') == $color ? 'selected' : '' }}>{{ ucfirst($color) }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" {{ empty($classId) ? 'disabled' : '' }} class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="ph-bold ph-floppy-disk"></i>
                        {{ isset($editBreak) ? 'Mettre à jour' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bottom Section: Breaks List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mt-8" x-data="{ isEmpty: {{ $breaks->isEmpty() ? 'true' : 'false' }} }">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                <i class="ph-fill ph-list-dashes text-primary-dynamic"></i>
                Pauses de cette classe
            </h2>
        </div>

        <!-- Empty State -->
        <div class="p-8" x-show="isEmpty">
            @include('SchoolDashboard::components.empty-state', [
                'title' => 'Aucune pause trouvée',
                'description' => 'Cette classe n\'a pas encore de pause. Utilisez le formulaire ci-dessus pour enregistrer une récréation ou une pause déjeuner pour un jour donné.',
                'icon' => 'ph-fill ph-coffee'
            ])
        </div>

        <div class="overflow-x-auto" x-show="!isEmpty" style="display: none;">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">ID</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 w-12">Couleur</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Jour</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Nom</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Début</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Fin</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($breaks as $break)
                    <tr class="hover:bg-slate-50/50 transition group">
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-500">{{ $break->id }}</td>
                        <td class="px-5 py-4">
                            <div class="w-6 h-6 rounded border border-slate-200 shadow-sm bg-{{ $break->color }}-400"></div>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-bold text-slate-700">{{ $days[$break->day_of_week] ?? $break->day_of_week }}</td>
                        <td class="px-5 py-4 text-[14px] font-bold text-slate-700">{{ $break->name }}</td>
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-600">{{ substr($break->start_time, 0, 5) }}</td>
                        <td class="px-5 py-4 text-[13px] font-medium text-slate-600">{{ substr($break->end_time, 0, 5) }}</td>
                        <td class="px-5 py-4 text-right flex justify-end gap-2">
                            <a href="?class_id={{ $classId }}&edit={{ $break->id }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition inline-flex items-center justify-center" title="Éditer">
                                <i class="ph ph-pencil-simple text-[16px]"></i>
                            </a>
                            <form action="{{ route('school.academic.timetable.breaks.destroy', $break->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette pause ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition inline-flex items-center justify-center" title="Supprimer">
                                    <i class="ph ph-trash text-[16px]"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-slate-100 flex items-center justify-between" x-show="!isEmpty" style="display: none;">
            <span class="text-[13px] font-medium text-slate-500">Affichage de {{ $breaks->count() }} pause(s) au total pour cette classe</span>
        </div>
    </div>

</div>
@endsection
