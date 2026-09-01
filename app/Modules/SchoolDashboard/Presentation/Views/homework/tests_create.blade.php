@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('school.academic.homework.tests') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-[#031C5B] inline-flex items-center gap-1 mb-2">
            <i class="ph-bold ph-arrow-left"></i> Interrogations & Contrôles
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Planifier une Évaluation</h1>
        <p class="text-[13.5px] text-slate-500 mt-1">Créez une interrogation ou un contrôle pour une de vos classes.</p>
    </div>

    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form action="{{ route('school.academic.homework.tests.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Classe & Matière --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Classe <span class="text-red-500">*</span></label>
                    <select name="academic_class_id" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        <option value="">Sélectionner une classe</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ old('academic_class_id') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Matière <span class="text-red-500">*</span></label>
                    <select name="subject_id" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        <option value="">Sélectionner une matière</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Type d'évaluation — en premier pour pré-remplir le titre --}}
            <div x-data="evaluationTypePicker()" x-init="init()">
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">
                    Type d'évaluation
                    <span class="ml-1 text-[11px] font-normal text-slate-400">(optionnel — lié au module Bulletins)</span>
                </label>

                @if($evaluationTypes->isEmpty())
                    <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3">
                        <i class="ph-bold ph-warning text-amber-500 mt-0.5 flex-shrink-0"></i>
                        <p class="text-[13px] text-amber-800">
                            Aucun type d'évaluation de type <strong>Interrogation</strong> n'est configuré pour votre école.
                            <a href="{{ route('school.academic.bulletins.evaluation-types') }}" class="underline font-bold hover:text-amber-900">Configurer les types</a>
                        </p>
                    </div>
                @else
                    <select
                        name="evaluation_type_id"
                        id="evaluation_type_select"
                        x-model="selectedId"
                        @change="onTypeChange()"
                        class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]"
                    >
                        <option value="" data-name="" data-coefficient="">— Aucun type sélectionné —</option>
                        @foreach($evaluationTypes as $type)
                            <option
                                value="{{ $type->id }}"
                                data-coefficient="{{ $type->coefficient }}"
                                data-name="{{ $type->name }}"
                                {{ old('evaluation_type_id') == $type->id ? 'selected' : '' }}
                            >{{ $type->name }} (coeff. {{ rtrim(rtrim(number_format($type->coefficient, 2), '0'), '.') }})</option>
                        @endforeach
                    </select>

                    {{-- Badge coefficient --}}
                    <div x-show="selectedId" x-transition class="mt-2 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-[12px] font-bold px-3 py-1.5 rounded-full border border-blue-200">
                            <i class="ph-bold ph-tag"></i>
                            <span x-text="'Coefficient bulletin : ' + coefficient"></span>
                        </span>
                        <span class="text-[11.5px] text-slate-400">La note saisie sera pondérée par ce coefficient dans le calcul de la moyenne.</span>
                    </div>
                @endif
            </div>

            {{-- Titre — pré-rempli avec le nom du type sélectionné --}}
            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Titre <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    name="title"
                    id="title_input"
                    required
                    value="{{ old('title') }}"
                    placeholder="Sélectionnez un type ou saisissez un titre"
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]"
                >
                <p class="text-[11.5px] text-slate-400 mt-1">Pré-rempli avec le type sélectionné — modifiable librement.</p>
            </div>

            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Description</label>
                <textarea name="description" rows="3" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Date et heure <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="scheduled_at" required value="{{ old('scheduled_at') }}" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Durée (min) <span class="text-red-500">*</span></label>
                    <input type="number" name="duration_minutes" min="5" max="480" required value="{{ old('duration_minutes', 60) }}" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
            </div>

            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Noté sur <span class="text-red-500">*</span></label>
                <input type="number" name="max_score" id="max_score_input" min="1" max="1000" step="0.5" required value="{{ old('max_score', 20) }}" class="w-32 bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                <p class="text-[11.5px] text-slate-400 mt-1">Le barème utilisé pour cette évaluation. La note sera automatiquement convertie sur /20 dans le Bulletin.</p>
            </div>

            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Salle</label>
                <select name="room_id" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    <option value="">Non définie</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-8 py-3 rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="ph-bold ph-check-circle"></i> Planifier l'évaluation
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function evaluationTypePicker() {
    // Liste des noms de types pour détecter si le titre est encore "par défaut"
    const typeNames = @json($evaluationTypes->pluck('name')->values());

    return {
        selectedId: '{{ old('evaluation_type_id', '') }}',
        coefficient: '',

        init() {
            if (this.selectedId) {
                this.onTypeChange();
            }
        },

        onTypeChange() {
            const select = document.getElementById('evaluation_type_select');
            if (!select) return;

            const opt = select.options[select.selectedIndex];
            this.coefficient = opt?.dataset?.coefficient || '';

            const typeName = opt?.dataset?.name || '';
            const titleInput = document.getElementById('title_input');
            if (!titleInput) return;

            const currentTitle = titleInput.value.trim();

            // Pré-remplir si : le champ est vide OU contient encore un nom de type
            // → ne jamais écraser un titre personnalisé
            const isDefaultTitle = currentTitle === '' || typeNames.includes(currentTitle);
            if (isDefaultTitle) {
                titleInput.value = typeName;
            }
        }
    };
}
</script>
@endsection
