@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[13px] font-medium text-slate-500 mb-2">
                <a href="{{ route('school.academic.bulletins.dashboard') }}" class="hover:text-slate-800">Bulletins</a>
                <i class="ph ph-caret-right text-[11px]"></i>
                <span class="text-[#031C5B] font-bold">{{ $selectedClass->name ?? 'Validation' }}</span>
            </div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Validation des Bulletins</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">{{ $currentSemester?->name ?? '—' }} &middot; {{ count($ranking) }} élève(s) inscrit(s)</p>
        </div>
        @if($selectedClass && $currentSemester)
        <div class="flex items-center gap-3">
            <form action="{{ route('school.academic.bulletins.validation.validate') }}" method="POST">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <button type="submit" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                    <i class="ph-bold ph-check-circle text-lg"></i> Tout Valider
                </button>
            </form>
            <form action="{{ route('school.academic.bulletins.validation.publish') }}" method="POST">
                @csrf
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                <button type="submit" class="flex items-center gap-2 bg-[#031C5B] text-white px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition">
                    <i class="ph-bold ph-check-square text-lg"></i> Marquer comme Publié
                </button>
            </form>
        </div>
        @endif
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="flex items-center gap-1 border-b border-slate-200 overflow-x-auto">
        @foreach($classes as $class)
            <a href="{{ route('school.academic.bulletins.validation', ['class_id' => $class->id]) }}"
               class="px-4 py-2.5 text-[13.5px] font-bold whitespace-nowrap border-b-2 -mb-px transition
               {{ $selectedClass && $selectedClass->id === $class->id ? 'border-[#031C5B] text-[#031C5B]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                {{ $class->name }}
            </a>
        @endforeach
    </div>

    @if($selectedClass)
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
        <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC] border-b border-slate-200">
                            <th class="px-5 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Élève</th>
                            <th class="px-4 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Moyenne</th>
                            <th class="px-4 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Rang</th>
                            <th class="px-4 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Présence (30j)</th>
                            <th class="px-4 py-3.5 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ranking as $row)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('school.academic.bulletins.print', $row['student']->id) }}" target="_blank" class="font-bold text-slate-800 text-[13.5px] hover:text-[#031C5B]">
                                        {{ $row['student']->first_name }} {{ $row['student']->last_name }}
                                    </a>
                                </td>
                                <td class="px-4 py-3.5 font-bold text-[13.5px] text-slate-700">{{ $row['average'] !== null ? $row['average'] . '/20' : '—' }}</td>
                                <td class="px-4 py-3.5 text-[13px] text-slate-500">{{ $row['rank'] ? $row['rank'] . 'e' : '—' }}</td>
                                <td class="px-4 py-3.5 text-[13px] text-slate-500">{{ $row['attendance_rate'] !== null ? $row['attendance_rate'] . '%' : '—' }}</td>
                                <td class="px-4 py-3.5 text-right">
                                    @if($row['status'] === 'Validé')
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700">Validé</span>
                                    @elseif($row['status'] === 'À revoir')
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-amber-50 text-amber-700">À revoir</span>
                                    @else
                                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500">En attente</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 text-[13px]">Aucun élève actif dans cette classe.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-5">
            <h3 class="text-[15px] font-extrabold text-slate-900">Aperçu de Classe</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-blue-50/60 rounded-xl p-3 text-center">
                    <p class="text-[20px] font-extrabold text-[#031C5B]">{{ $classAverage ?? '—' }}</p>
                    <p class="text-[10px] font-bold text-slate-500">Moy. Générale</p>
                </div>
                <div class="bg-emerald-50/60 rounded-xl p-3 text-center">
                    <p class="text-[20px] font-extrabold text-emerald-700">{{ collect($ranking)->where('status', 'Validé')->count() }}</p>
                    <p class="text-[10px] font-bold text-slate-500">Validés / {{ count($ranking) }}</p>
                </div>
            </div>
            @if($publication)
                <div class="pt-4 border-t border-slate-100">
                    <p class="text-[11px] font-extrabold text-slate-400 uppercase tracking-wider mb-1">Statut de Publication</p>
                    @if($publication->status === 'published')
                        <p class="text-[13px] font-bold text-emerald-700">Publié le {{ $publication->published_at?->format('d M Y') }}</p>
                    @elseif($publication->status === 'validated')
                        <p class="text-[13px] font-bold text-blue-700">Validé le {{ $publication->validated_at?->format('d M Y') }}</p>
                    @else
                        <p class="text-[13px] font-bold text-slate-500">Brouillon</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
    @endif
</div>
@endsection
