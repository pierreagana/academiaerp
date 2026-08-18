@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div>
        <a href="{{ route($assignment->type === 'devoir_maison' ? 'school.academic.homework.homework' : 'school.academic.homework.tests') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-[#031C5B] inline-flex items-center gap-1 mb-2">
            <i class="ph-bold ph-arrow-left"></i> {{ $assignment->type === 'devoir_maison' ? 'Devoirs Maison' : 'Interrogations & Contrôles' }}
        </a>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">{{ $assignment->title }}</h1>
        <p class="text-[13.5px] text-slate-500 mt-1">{{ $assignment->subject->name ?? '—' }} &middot; {{ $assignment->academicClass->name ?? '—' }} &middot; Noté sur {{ rtrim(rtrim(number_format($assignment->max_score, 2), '0'), '.') }} &middot; Suivi des copies et notation.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <form action="{{ route('school.academic.homework.submissions.store', $assignment->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="divide-y divide-slate-100">
                @forelse($students as $student)
                    @php $existing = $existingSubmissions->get($student->id); @endphp
                    <div x-data="{ status: '{{ $existing?->status ?? 'non_remis' }}' }" class="p-5 flex flex-col md:flex-row md:items-center gap-4">
                        <div class="flex items-center gap-3 md:w-64 shrink-0">
                            <div class="w-9 h-9 rounded-full bg-[#EEF2F6] text-[#334155] font-bold text-[13px] flex items-center justify-center">
                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-[13.5px] font-bold text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</p>
                                <p class="text-[11.5px] text-slate-500">{{ $student->roll_number }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 bg-slate-50 rounded-lg p-1 shrink-0">
                            <label class="cursor-pointer">
                                <input type="radio" name="entries[{{ $student->id }}][status]" value="non_remis" x-model="status" class="hidden peer">
                                <span class="block px-3 py-1.5 rounded-md text-[12px] font-bold text-slate-500 peer-checked:bg-slate-500 peer-checked:text-white transition">Non remis</span>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="entries[{{ $student->id }}][status]" value="remis" x-model="status" class="hidden peer">
                                <span class="block px-3 py-1.5 rounded-md text-[12px] font-bold text-slate-500 peer-checked:bg-emerald-500 peer-checked:text-white transition">Remis</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-3 flex-1">
                            <input type="number" name="entries[{{ $student->id }}][score]" min="0" max="{{ $assignment->max_score }}" step="0.25" value="{{ $existing?->score }}" placeholder="/{{ rtrim(rtrim(number_format($assignment->max_score, 2), '0'), '.') }}" class="w-20 bg-white border border-slate-200 text-[13px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B]">
                            <input type="text" name="entries[{{ $student->id }}][feedback]" value="{{ $existing?->feedback }}" placeholder="Commentaire (optionnel)" class="flex-1 bg-white border border-slate-200 text-[13px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B]">
                            <label class="shrink-0 cursor-pointer text-[11.5px] font-bold text-[#031C5B] flex items-center gap-1">
                                <i class="ph-bold ph-paperclip"></i>
                                <span x-data="{}" x-text="document.querySelector('input[name=\'entries[{{ $student->id }}][file]\']')?.files[0]?.name || (@js($existing?->file_path ? basename($existing->file_path) : 'Joindre'))"></span>
                                <input type="file" name="entries[{{ $student->id }}][file]" class="hidden" onchange="this.closest('label').querySelector('span').textContent = this.files[0]?.name || 'Joindre'">
                            </label>
                        </div>
                    </div>
                @empty
                    <p class="px-5 py-8 text-center text-slate-500 text-[13px]">Aucun élève actif dans cette classe.</p>
                @endforelse
            </div>

            @if($students->isNotEmpty())
            <div class="p-5 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
                    <i class="ph-bold ph-floppy-disk"></i> Enregistrer les rendus
                </button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection
