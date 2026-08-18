@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Prendre la présence</h1>
        <p class="text-[13.5px] text-slate-500 mt-1">Sélectionnez une classe et une date pour enregistrer la présence des élèves.</p>
        <a href="{{ route('school.academic.presence.attendance') }}" class="text-[12.5px] font-semibold text-[#031C5B] hover:underline inline-flex items-center gap-1 mt-2">
            <i class="ph-bold ph-arrow-left"></i> Retour au tableau de bord
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 text-sm text-red-800 rounded-lg bg-red-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-warning-circle text-lg"></i>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <form action="{{ route('school.academic.presence.attendance.take') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Classe</label>
                <select name="class_id" onchange="this.form.submit()" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                    <option value="">-- Sélectionner une classe --</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $selectedClass && $selectedClass->id === $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Date</label>
                <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
            </div>
        </form>
    </div>

    @if($selectedClass && $checkinWarning)
    <div class="bg-amber-50 border border-amber-100 rounded-xl p-5 flex items-start gap-3">
        <i class="ph-fill ph-lock-simple text-amber-500 text-xl shrink-0"></i>
        <div>
            <p class="text-[13.5px] font-bold text-amber-800">Pointage requis</p>
            <p class="text-[13px] text-amber-700 mt-0.5">{{ $checkinWarning }}</p>
        </div>
    </div>
    @elseif($selectedClass)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="{{ route('school.academic.presence.attendance.store') }}" method="POST">
            @csrf
            <input type="hidden" name="academic_class_id" value="{{ $selectedClass->id }}">
            <input type="hidden" name="date" value="{{ $date }}">

            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[15px] font-bold text-slate-800">{{ $selectedClass->name }} — {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}</h3>
                <button type="button" onclick="document.querySelectorAll('input[value=present]').forEach(r => r.checked = true)" class="text-[12.5px] font-bold text-[#031C5B] hover:underline">
                    Marquer tous présents
                </button>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($students as $student)
                @php
                    $existing = $existingStatuses->get($student->id);
                    $current = $existing?->status ?? 'present';
                @endphp
                <div x-data="{ status: '{{ $current }}' }" class="flex items-center justify-between px-5 py-3.5 gap-3">
                    <div class="flex items-center gap-3">
                        @if($student->photo_path)
                            <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-9 h-9 rounded-full object-cover">
                        @else
                            <div class="w-9 h-9 rounded-full bg-[#EEF2F6] text-[#334155] font-bold text-[13px] flex items-center justify-center">
                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                            </div>
                        @endif
                        <div>
                            <p class="text-[13.5px] font-bold text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</p>
                            <p class="text-[11.5px] text-slate-500">{{ $student->roll_number }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <template x-if="status === 'late'">
                            <div class="flex items-center gap-1.5">
                                <input type="number" name="late_minutes[{{ $student->id }}]" min="1" max="600" placeholder="min"
                                    value="{{ $existing?->late_minutes }}"
                                    class="w-16 bg-white border border-slate-200 text-[12px] rounded-md px-2 py-1 outline-none focus:border-orange-400">
                                <span class="text-[11px] text-slate-400">min. retard</span>
                            </div>
                        </template>
                        <template x-if="status === 'absent'">
                            <label class="flex items-center gap-1.5 cursor-pointer text-[11.5px] text-slate-500">
                                <input type="checkbox" name="justified[{{ $student->id }}]" value="1" {{ $existing?->justified ? 'checked' : '' }}
                                    class="w-3.5 h-3.5 rounded border-slate-300">
                                Justifiée
                            </label>
                        </template>
                        <div class="flex items-center gap-1 bg-slate-50 rounded-lg p-1">
                            <label class="cursor-pointer">
                                <input type="radio" name="statuses[{{ $student->id }}]" value="present" x-model="status" class="hidden peer">
                                <span class="block px-3 py-1.5 rounded-md text-[12px] font-bold text-slate-500 peer-checked:bg-green-500 peer-checked:text-white transition">Présent</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="statuses[{{ $student->id }}]" value="late" x-model="status" class="hidden peer">
                                <span class="block px-3 py-1.5 rounded-md text-[12px] font-bold text-slate-500 peer-checked:bg-orange-500 peer-checked:text-white transition">Retard</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="statuses[{{ $student->id }}]" value="absent" x-model="status" class="hidden peer">
                                <span class="block px-3 py-1.5 rounded-md text-[12px] font-bold text-slate-500 peer-checked:bg-red-500 peer-checked:text-white transition">Absent</span>
                            </label>
                        </div>
                    </div>
                </div>
                @empty
                <p class="px-5 py-8 text-center text-slate-500 text-[13px]">Aucun élève actif dans cette classe.</p>
                @endforelse
            </div>

            @if($students->isNotEmpty())
            <div class="p-5 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="ph-bold ph-floppy-disk"></i>
                    Enregistrer la présence
                </button>
            </div>
            @endif
        </form>
    </div>
    @endif
</div>
@endsection
