@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('school.teacher.classes') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-[#031C5B] inline-flex items-center gap-1 mb-2">
            <i class="ph-bold ph-arrow-left"></i> Mes Classes
        </a>
        <h2 class="text-[28px] font-bold text-[#031C5B] tracking-tight">Emploi du temps — {{ $class->name }}</h2>
        <p class="text-slate-600 text-[14px] font-medium mt-1">Créneaux publiés pour cette classe.</p>
    </div>

    @php $dayLabels = ['lundi' => 'Lundi', 'mardi' => 'Mardi', 'mercredi' => 'Mercredi', 'jeudi' => 'Jeudi', 'vendredi' => 'Vendredi']; @endphp

    @if($slotsByDay->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-8">
            @include('SchoolDashboard::components.empty-state', [
                'title' => 'Aucun horaire publié',
                'description' => 'Aucun créneau n\'a encore été publié pour cette classe.',
                'icon' => 'ph-fill ph-calendar-x'
            ])
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($days as $day)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h3 class="text-[14px] font-extrabold text-[#031C5B] mb-3 pb-2 border-b border-slate-100">{{ $dayLabels[$day] }}</h3>
                    <div class="space-y-2">
                        @forelse($slotsByDay->get($day, collect()) as $slot)
                            <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                                <p class="text-[12px] font-bold text-slate-500">{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }} – {{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}</p>
                                <p class="text-[13px] font-bold text-slate-800 mt-0.5">{{ $slot->subject->name ?? '—' }}</p>
                                <p class="text-[11.5px] text-slate-400 mt-0.5">{{ $slot->teacher->first_name ?? '' }} {{ $slot->teacher->last_name ?? '' }}@if($slot->room) &middot; {{ $slot->room->name }} @endif</p>
                            </div>
                        @empty
                            <p class="text-[12.5px] text-slate-400 py-2">Aucun cours ce jour.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
