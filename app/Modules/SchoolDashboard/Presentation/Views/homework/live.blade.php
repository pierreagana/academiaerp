@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <div>
        <a href="{{ route('school.academic.homework.tests') }}" class="text-[12.5px] font-bold text-slate-500 hover:text-[#031C5B] inline-flex items-center gap-1 mb-2">
            <i class="ph-bold ph-arrow-left"></i> Interrogations & Contrôles
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        @if($assignment->liveStatus() === 'in_progress')
                            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                            <span class="text-[11px] font-bold text-red-600 uppercase tracking-wider">En cours</span>
                        @elseif($assignment->liveStatus() === 'completed')
                            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Terminée</span>
                        @else
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Planifiée</span>
                        @endif
                    </div>
                    <h1 class="text-[24px] font-extrabold text-slate-900">{{ $assignment->title }}</h1>
                    <p class="text-[13px] text-slate-500">{{ $assignment->subject->name ?? '—' }} &middot; {{ $assignment->academicClass->name ?? '—' }}</p>
                </div>

                @if($assignment->liveStatus() === 'scheduled')
                    <form action="{{ route('school.academic.homework.start', $assignment->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
                            <i class="ph-bold ph-play"></i> Démarrer
                        </button>
                    </form>
                @elseif($assignment->liveStatus() === 'in_progress')
                    <div class="flex items-center gap-3">
                        <div class="bg-slate-900 text-white rounded-xl px-5 py-3 text-center">
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Temps Restant</p>
                            <p id="countdown" class="text-[26px] font-extrabold tabular-nums" data-ends-at="{{ $assignment->started_at->copy()->addMinutes($assignment->duration_minutes)->toIso8601String() }}">--:--</p>
                        </div>
                        <form action="{{ route('school.academic.homework.stop', $assignment->id) }}" method="POST" onsubmit="return confirm('Arrêter l\'interrogation maintenant ?');">
                            @csrf
                            <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center gap-2">
                                <i class="ph-bold ph-stop"></i> Stopper
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between">
                    <span class="text-[13px] font-semibold text-slate-600">Présents</span>
                    <span id="present-count" class="text-[20px] font-extrabold text-emerald-600">{{ $counts['present'] }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl p-4 flex items-center justify-between">
                    <span class="text-[13px] font-semibold text-slate-600">Absents</span>
                    <span id="absent-count" class="text-[20px] font-extrabold text-red-500">{{ $counts['absent'] }}</span>
                </div>
            </div>

            <h3 class="text-[14px] font-bold text-slate-800 mb-3 flex items-center justify-between">
                Suivi des copies
                <span class="text-[11.5px] font-medium text-slate-400">{{ $existingSubmissions->where('status', 'remis')->count() }}/{{ $students->count() }} Remises</span>
            </h3>
            <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                @forelse($students as $student)
                    @php
                        $attendance = $existingAttendance->get($student->id);
                        $submission = $existingSubmissions->get($student->id);
                    @endphp
                    <div class="flex items-center justify-between px-4 py-3 gap-3" data-student-row="{{ $student->id }}">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#EEF2F6] text-[#334155] font-bold text-[12px] flex items-center justify-center">
                                {{ substr($student->first_name, 0, 1) }}{{ substr($student->last_name, 0, 1) }}
                            </div>
                            <span class="text-[13px] font-bold text-slate-800">{{ $student->first_name }} {{ $student->last_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex items-center gap-1 bg-slate-50 rounded-lg p-1">
                                <button type="button" onclick="markAttendance({{ $student->id }}, 'present', this)" class="attendance-btn px-2.5 py-1 rounded-md text-[11px] font-bold transition {{ $attendance?->status === 'present' ? 'bg-emerald-500 text-white' : 'text-slate-500 hover:bg-slate-200' }}">P</button>
                                <button type="button" onclick="markAttendance({{ $student->id }}, 'late', this)" class="attendance-btn px-2.5 py-1 rounded-md text-[11px] font-bold transition {{ $attendance?->status === 'late' ? 'bg-amber-500 text-white' : 'text-slate-500 hover:bg-slate-200' }}">R</button>
                                <button type="button" onclick="markAttendance({{ $student->id }}, 'absent', this)" class="attendance-btn px-2.5 py-1 rounded-md text-[11px] font-bold transition {{ $attendance?->status === 'absent' ? 'bg-red-500 text-white' : 'text-slate-500 hover:bg-slate-200' }}">A</button>
                            </div>
                            <button type="button" onclick="markSubmission({{ $student->id }}, this)" data-status="{{ $submission?->status ?? 'non_remis' }}" class="submission-btn px-3 py-1.5 rounded-lg text-[11.5px] font-bold transition {{ $submission?->status === 'remis' ? 'bg-[#031C5B] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                {{ $submission?->status === 'remis' ? 'Remis' : 'Marquer Remis' }}
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-8 text-center text-slate-400 text-[13px]">Aucun élève actif dans cette classe.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-5">
            @if(auth()->user()->teacher)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-[14px] font-bold text-slate-800 mb-3">Planifier une nouvelle interrogation</h3>
                <a href="{{ route('school.academic.homework.tests.create') }}" class="block text-center bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[12.5px] py-2.5 rounded-lg transition">
                    Nouvelle évaluation &rarr;
                </a>
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="text-[14px] font-bold text-slate-800 mb-3">Historique</h3>
                <div class="space-y-2">
                    @foreach($history->take(5) as $item)
                        <div class="flex items-center justify-between text-[12.5px]">
                            <div>
                                <p class="font-bold text-slate-700">{{ $item->title }}</p>
                                <p class="text-[11px] text-slate-400">{{ $item->academicClass->name ?? '—' }} &middot; {{ $item->scheduled_at->translatedFormat('d M') }}</p>
                            </div>
                            <a href="{{ route('school.academic.homework.submissions', $item->id) }}" class="text-[11.5px] font-bold text-[#031C5B] hover:underline shrink-0 ml-2">Copies</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const attendanceUrl = @json(route('school.academic.homework.attendance', $assignment->id));
const submissionUrl = @json(route('school.academic.homework.submission-mark', $assignment->id));
const countsUrl = @json(route('school.academic.homework.attendance.refresh', $assignment->id));
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;

function markAttendance(studentId, status, btn) {
    fetch(attendanceUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ student_id: studentId, status: status }),
    }).then(res => res.ok ? res.json() : Promise.reject()).then(() => {
        btn.closest('.flex.items-center.gap-1').querySelectorAll('.attendance-btn').forEach(b => {
            b.classList.remove('bg-emerald-500', 'bg-amber-500', 'bg-red-500', 'text-white');
            b.classList.add('text-slate-500');
        });
        btn.classList.remove('text-slate-500');
        btn.classList.add(status === 'present' ? 'bg-emerald-500' : status === 'late' ? 'bg-amber-500' : 'bg-red-500', 'text-white');
        refreshCounts();
    });
}

function markSubmission(studentId, btn) {
    const next = btn.dataset.status === 'remis' ? 'non_remis' : 'remis';
    fetch(submissionUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ student_id: studentId, status: next }),
    }).then(res => res.ok ? res.json() : Promise.reject()).then(() => {
        btn.dataset.status = next;
        if (next === 'remis') {
            btn.textContent = 'Remis';
            btn.classList.add('bg-[#031C5B]', 'text-white');
            btn.classList.remove('bg-slate-100', 'text-slate-600');
        } else {
            btn.textContent = 'Marquer Remis';
            btn.classList.remove('bg-[#031C5B]', 'text-white');
            btn.classList.add('bg-slate-100', 'text-slate-600');
        }
    });
}

function refreshCounts() {
    fetch(countsUrl, { headers: { 'Accept': 'application/json' } }).then(r => r.json()).then(data => {
        document.getElementById('present-count').textContent = data.present;
        document.getElementById('absent-count').textContent = data.absent;
    });
}

const countdownEl = document.getElementById('countdown');
if (countdownEl) {
    const endsAt = new Date(countdownEl.dataset.endsAt).getTime();
    const tick = () => {
        const remaining = Math.max(0, Math.floor((endsAt - Date.now()) / 1000));
        const m = Math.floor(remaining / 60).toString().padStart(2, '0');
        const s = (remaining % 60).toString().padStart(2, '0');
        countdownEl.textContent = `${m}:${s}`;
        if (remaining <= 0) clearInterval(interval);
    };
    tick();
    const interval = setInterval(tick, 1000);
    setInterval(refreshCounts, 20000);
}
</script>
@endsection
