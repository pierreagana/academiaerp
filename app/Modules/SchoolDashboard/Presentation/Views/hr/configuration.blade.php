@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ gradeOpen: false, componentOpen: false }">
    @include('SchoolDashboard::hr._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Configuration RH & Avantages</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Définissez les échelons salariaux et les rubriques de paie.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @php $nonCompliant = $salaryGrades->filter(fn($g) => !$g->is_compliant); @endphp
    <div class="bg-gradient-to-br from-slate-50 to-blue-50/40 border border-slate-100 rounded-2xl p-5 shadow-sm flex items-start gap-4">
        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-[#031C5B] shrink-0"><i class="ph-fill ph-scales text-lg"></i></div>
        <div>
            <div class="flex items-center gap-2 mb-1">
                <h3 class="font-extrabold text-slate-800 text-[15px]">Vérification du Salaire Minimum</h3>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $nonCompliant->isEmpty() ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $nonCompliant->isEmpty() ? 'CONFORME' : $nonCompliant->count() . ' À CORRIGER' }}</span>
            </div>
            <p class="text-[13px] text-slate-600 leading-relaxed">Chaque échelon est comparé au salaire minimum de référence ({{ number_format(\App\Modules\HR\Domain\Models\SalaryGrade::MINIMUM_WAGE_CI, 0, ',', ' ') }} FCFA). {{ $nonCompliant->isEmpty() ? 'Tous les échelons configurés respectent ce seuil.' : $nonCompliant->pluck('name')->implode(', ') . ' — en dessous du seuil.' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Grilles Salariales -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2"><i class="ph-bold ph-buildings text-[#031C5B]"></i> Grilles Salariales par Échelon</h3>
                <button @click="gradeOpen = true" class="text-[#031C5B] font-bold text-[13px] hover:underline flex items-center gap-1"><i class="ph-bold ph-plus"></i> Ajouter un Échelon</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC]">
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Échelon</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Salaire de Base (FCFA)</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Conformité</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($salaryGrades as $grade)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-4 text-[13.5px] font-bold text-slate-800">{{ $grade->name }}</td>
                            <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ number_format($grade->base_salary, 0, ',', ' ') }}</td>
                            <td class="px-5 py-4">
                                @if($grade->is_compliant)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700"><i class="ph-bold ph-check"></i> Conforme</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-700"><i class="ph-bold ph-warning"></i> Sous le Seuil</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun échelon configuré.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Rubriques de Paie -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 h-fit">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-bold ph-receipt text-[#031C5B]"></i> Rubriques de Paie</h3>
                <button @click="componentOpen = true" class="text-[#031C5B] hover:text-[#031C5B]/70"><i class="ph-bold ph-plus"></i></button>
            </div>
            <div class="space-y-2.5">
                @forelse($components as $component)
                <form method="POST" action="{{ route('school.hr.configuration.components.toggle', $component->id) }}" class="flex items-center justify-between border border-slate-100 rounded-xl p-3">
                    @csrf
                    <div>
                        <p class="text-[13px] font-bold text-slate-800">{{ $component->name }}</p>
                        <p class="text-[11.5px] text-slate-500">
                            {{ $component->type === 'deduction' ? '-' : '+' }}{{ $component->rate_type === 'percentage' ? $component->rate_value . '%' : number_format($component->rate_value, 0, ',', ' ') . ' FCFA' }}
                        </p>
                    </div>
                    <button type="submit" class="w-10 h-6 rounded-full transition {{ $component->enabled ? 'bg-[#031C5B]' : 'bg-slate-200' }} relative shrink-0">
                        <span class="absolute top-0.5 {{ $component->enabled ? 'right-0.5' : 'left-0.5' }} w-5 h-5 bg-white rounded-full shadow transition"></span>
                    </button>
                </form>
                @empty
                <p class="text-slate-400 text-[13px] text-center py-6">Aucune rubrique configurée.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Modal: Ajouter un Échelon -->
    <div x-show="gradeOpen" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4" style="display: none;">
        <div @click.outside="gradeOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-[17px] font-bold text-[#031C5B]">Ajouter un Échelon</h3>
                <button @click="gradeOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
            </div>
            <form method="POST" action="{{ route('school.hr.configuration.grades.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Nom de l'Échelon</label>
                    <input type="text" name="name" required placeholder="Ex: Enseignant - N1" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Salaire de Base (FCFA)</label>
                    <input type="number" name="base_salary" min="0" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
                <button type="submit" class="w-full mt-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Ajouter</button>
            </form>
        </div>
    </div>

    <!-- Modal: Ajouter une Rubrique -->
    <div x-show="componentOpen" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4" style="display: none;">
        <div @click.outside="componentOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-[17px] font-bold text-[#031C5B]">Ajouter une Rubrique</h3>
                <button @click="componentOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
            </div>
            <form method="POST" action="{{ route('school.hr.configuration.components.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Nom</label>
                    <input type="text" name="name" required placeholder="Ex: Prime de Transport" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Type</label>
                    <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                        @foreach(\App\Modules\HR\Domain\Models\PayrollComponent::TYPES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Mode</label>
                        <select name="rate_type" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                            @foreach(\App\Modules\HR\Domain\Models\PayrollComponent::RATE_TYPES as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Valeur</label>
                        <input type="number" step="0.1" name="rate_value" min="0" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    </div>
                </div>
                <button type="submit" class="w-full mt-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Ajouter</button>
            </form>
        </div>
    </div>
</div>
@endsection
