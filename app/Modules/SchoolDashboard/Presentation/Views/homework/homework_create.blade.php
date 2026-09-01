@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div>
        <a href="{{ route('school.academic.homework.homework') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-[#031C5B] inline-flex items-center gap-1 mb-2">
            <i class="ph-bold ph-arrow-left"></i> Devoirs Maison
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Nouveau Devoir</h1>
        <p class="text-[13.5px] text-slate-500 mt-1">Créez un devoir maison pour une de vos classes.</p>
    </div>

    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6" x-data="homeworkForm()">
        <form action="{{ route('school.academic.homework.homework.store') }}" method="POST" class="space-y-5">
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

            {{-- Option pour appliquer ou non la note sur le bulletin --}}
            <div class="bg-[#F8FAFC] border border-slate-200 rounded-xl p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <i class="ph-fill ph-chart-polar text-blue-600 text-lg"></i>
                        <div>
                            <p class="text-[13.5px] font-bold text-slate-800">Appliquer cette note sur le bulletin</p>
                            <p class="text-[12px] text-slate-500">Cochez si vous désirez que ce devoir compte dans la moyenne officielle du bulletin.</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" x-model="applyToBulletin" class="sr-only peer" @change="onToggleBulletin()">
                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#031C5B]"></div>
                    </label>
                </div>

                @if($evaluationTypes->isNotEmpty())
                <div x-show="applyToBulletin" x-transition class="pt-2 border-t border-slate-200/80 space-y-2">
                    <label class="block text-[12.5px] font-semibold text-slate-700">
                        Type d'évaluation dans le bulletin <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="evaluation_type_id"
                        id="hw_evaluation_type_select"
                        x-model="selectedTypeId"
                        :required="applyToBulletin"
                        :disabled="!applyToBulletin"
                        @change="onTypeChange()"
                        class="w-full bg-white border border-slate-200 text-slate-900 text-[13.5px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]"
                    >
                        <option value="" data-name="" data-coefficient="">— Choisir le type de devoir —</option>
                        @foreach($evaluationTypes as $type)
                            <option
                                value="{{ $type->id }}"
                                data-coefficient="{{ $type->coefficient }}"
                                data-name="{{ $type->name }}"
                                {{ old('evaluation_type_id') == $type->id ? 'selected' : '' }}
                            >{{ $type->name }} (coeff. {{ rtrim(rtrim(number_format($type->coefficient, 2), '0'), '.') }})</option>
                        @endforeach
                    </select>

                    <div x-show="selectedTypeId && coefficient" x-transition class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 text-[11.5px] font-bold px-2.5 py-1 rounded-md border border-blue-200">
                            <i class="ph-bold ph-tag"></i>
                            <span x-text="'Coefficient : ' + coefficient"></span>
                        </span>
                        <span class="text-[11.5px] text-slate-500">Comptabilisé dans la moyenne trimestrielle.</span>
                    </div>
                </div>
                @else
                <div x-show="applyToBulletin" x-transition class="pt-2 border-t border-slate-200/80">
                    <p class="text-[12px] text-amber-700 bg-amber-50 p-2.5 rounded-lg border border-amber-200">
                        Aucun type lié au « Devoir Maison » n'est configuré dans <a href="{{ route('school.academic.bulletins.evaluation-types') }}" class="underline font-bold">Types d'évaluation</a>.
                    </p>
                </div>
                @endif
            </div>

            {{-- Titre --}}
            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Titre du Devoir <span class="text-red-500">*</span></label>
                <input
                    type="text"
                    name="title"
                    id="hw_title_input"
                    required
                    x-model="title"
                    placeholder="Ex: Devoir Maison N°1"
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]"
                >
            </div>

            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Description / Consignes</label>
                <textarea name="description" rows="3" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]" placeholder="Consignes pour les élèves...">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Date limite de remise <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="scheduled_at" required value="{{ old('scheduled_at') }}" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Barème (Noté sur) <span class="text-red-500">*</span></label>
                    <input type="number" name="max_score" min="1" max="1000" step="0.5" required value="{{ old('max_score', 20) }}" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
            </div>
            <p class="text-[11.5px] text-slate-400 -mt-2">La note sera automatiquement ramenée sur /20 lors de l'application sur le Bulletin.</p>

            <div class="flex justify-end pt-2">
                <button type="submit" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-8 py-3 rounded-xl shadow-sm transition flex items-center gap-2">
                    <i class="ph-bold ph-check-circle"></i> Créer le devoir
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function homeworkForm() {
    const typeNames = @json($evaluationTypes->pluck('name')->values());
    const defaultTitle = '{{ old('title', 'Devoir Maison') }}';
    const oldTypeId = '{{ old('evaluation_type_id', '') }}';

    return {
        applyToBulletin: oldTypeId !== '',
        selectedTypeId: oldTypeId,
        coefficient: '',
        title: defaultTitle,

        init() {
            if (this.selectedTypeId) {
                this.onTypeChange();
            }
        },

        onToggleBulletin() {
            if (!this.applyToBulletin) {
                this.selectedTypeId = '';
                this.coefficient = '';
            } else {
                // S'il n'y a qu'un seul type disponible, le pré-sélectionner
                const select = document.getElementById('hw_evaluation_type_select');
                if (select && select.options.length === 2) {
                    this.selectedTypeId = select.options[1].value;
                    this.onTypeChange();
                }
            }
        },

        onTypeChange() {
            const select = document.getElementById('hw_evaluation_type_select');
            if (!select) return;

            const opt = select.options[select.selectedIndex];
            this.coefficient = opt?.dataset?.coefficient || '';
            const typeName = opt?.dataset?.name || '';

            const isDefault = this.title === '' || this.title === 'Devoir Maison' || typeNames.includes(this.title);
            if (isDefault && typeName) {
                this.title = typeName;
            }
        }
    };
}
</script>
@endsection
