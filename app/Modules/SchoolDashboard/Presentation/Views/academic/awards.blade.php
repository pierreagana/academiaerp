@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Récompenses &amp; Diplômes</h2>
            <p class="text-slate-500 text-sm mt-1">Distinctions attribuées aux élèves, enseignants et personnel.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('school.academic.awards.models.index') }}" class="flex items-center gap-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-50 transition">
                <i class="ph ph-list-bullets"></i>
                Mes Modèles
            </a>
            @if(auth()->user()->canAccess('academic.awards.manage', 'update'))
            <a href="{{ route('school.academic.awards.template.edit') }}" class="flex items-center gap-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 px-4 py-2 rounded-xl text-sm font-medium hover:bg-slate-50 transition">
                <i class="ph ph-palette"></i>
                Modèle de Diplôme
            </a>
            @endif
            @if(auth()->user()->canAccess('academic.awards.manage', 'create'))
            <a href="{{ route('school.academic.awards.create') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph ph-medal"></i>
                Attribuer une récompense
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-2 overflow-x-auto">
        @foreach(['' => 'Tous', 'student' => 'Élèves', 'teacher' => 'Enseignants', 'staff' => 'Personnel'] as $key => $label)
            <a href="{{ route('school.academic.awards.index', array_filter(['recipient_type' => $key])) }}" class="px-4 py-2 rounded-xl text-[13px] font-bold whitespace-nowrap transition {{ ($recipientType ?? '') === $key ? 'bg-[#031C5B] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Destinataire</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Récompense</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Catégorie</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Récompense concrète</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100">Date</th>
                        <th class="px-5 py-3 text-[11px] font-bold text-slate-500 uppercase tracking-wider border-b border-slate-100 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($awards as $award)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4 text-[13.5px]">
                            <p class="font-bold text-slate-700">{{ $award->recipientName }}</p>
                            <p class="text-[11px] text-slate-400">{{ \App\Modules\Academic\Domain\Models\Award::RECIPIENT_TYPES[$award->recipient_type] ?? $award->recipient_type }}</p>
                        </td>
                        <td class="px-5 py-4 text-[13.5px] font-semibold text-slate-700">{{ $award->type->name ?? '—' }}</td>
                        <td class="px-5 py-4 text-[12.5px] text-slate-500">{{ $award->type->category ?? '—' }}</td>
                        <td class="px-5 py-4 text-[12.5px] text-slate-500">{{ $award->material_reward ?? '—' }}</td>
                        <td class="px-5 py-4 text-[12.5px] text-slate-500">{{ $award->awarded_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('school.academic.awards.print', $award->id) }}" target="_blank" class="text-slate-400 hover:text-[#031C5B]" title="Imprimer le diplôme"><i class="ph-bold ph-printer"></i></a>
                                @if(auth()->user()->canAccess('academic.awards.manage', 'delete'))
                                <form action="{{ route('school.academic.awards.destroy', $award->id) }}" method="POST" onsubmit="return confirm('Supprimer cette récompense ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-600"><i class="ph-bold ph-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500 text-[13px]">Aucune récompense attribuée pour le moment.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($awards->hasPages())
        <div class="p-4 border-t border-slate-100">{{ $awards->links() }}</div>
        @endif
    </div>
</div>
@endsection
