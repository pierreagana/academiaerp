@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Mes Modèles de Récompense</h2>
            <p class="text-slate-500 text-sm mt-1">Ajoutez vos propres récompenses en plus du catalogue standard. Elles apparaîtront dans la liste "Récompense" lors de l'attribution.</p>
        </div>
        <a href="{{ route('school.academic.awards.index') }}" class="px-4 py-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-bold text-[13px] rounded-xl hover:bg-slate-50 transition shrink-0">
            <i class="ph-bold ph-arrow-left"></i> Retour
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50" role="alert">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(auth()->user()->canAccess('academic.awards.manage', 'create'))
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <h3 class="text-[14px] font-bold text-slate-800 mb-3">Créer un nouveau modèle</h3>
        <form method="POST" action="{{ route('school.academic.awards.models.store') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <select name="category" required class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                <option value="">Catégorie...</option>
                @foreach(\App\Modules\Academic\Domain\Models\AwardType::CATEGORIES as $category)
                    <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                @endforeach
            </select>
            <input type="text" name="name" required value="{{ old('name') }}" placeholder="Ex: Tableau d'Honneur du Fondateur" class="flex-[2] bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white rounded-lg text-[13px] font-bold hover:bg-[#031C5B]/90 transition shrink-0">Créer le modèle</button>
        </form>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden divide-y divide-slate-100">
        @forelse($awardTypes as $category => $types)
        <div class="p-5">
            <h3 class="text-[12px] font-bold text-slate-500 uppercase tracking-wider mb-3">{{ $category }}</h3>
            <div class="space-y-1.5">
                @foreach($types as $type)
                <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-blue-50/50">
                    <div class="flex items-center gap-2">
                        <span class="text-[13px] font-semibold text-slate-700">{{ $type->name }}</span>
                        @if($type->diplomaTemplate)
                            <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 flex items-center gap-1"><i class="ph-bold ph-check"></i> Diplôme configuré</span>
                        @else
                            <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 flex items-center gap-1"><i class="ph-bold ph-warning"></i> Pas de diplôme</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        @if(auth()->user()->canAccess('academic.awards.manage', 'update'))
                        <a href="{{ route('school.academic.awards.template.edit', ['award_type_id' => $type->id]) }}" class="text-[11.5px] font-bold text-[#031C5B] hover:underline whitespace-nowrap">
                            {{ $type->diplomaTemplate ? 'Modifier le diplôme' : 'Configurer le diplôme' }}
                        </a>
                        @endif
                        @if(auth()->user()->canAccess('academic.awards.manage', 'delete'))
                        <form action="{{ route('school.academic.awards.models.destroy', $type->id) }}" method="POST" onsubmit="return confirm('Supprimer ce modèle ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600"><i class="ph-bold ph-trash"></i></button>
                        </form>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @empty
        <p class="p-10 text-center text-slate-400 text-[13px]">Vous n'avez pas encore créé de modèle personnalisé.</p>
        @endforelse
    </div>
</div>
@endsection
