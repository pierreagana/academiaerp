@php
    $morningAssignedIds = $stop->students()->wherePivot('period', 'morning')->pluck('students.id')->all();
    $eveningAssignedIds = $stop->students()->wherePivot('period', 'evening')->pluck('students.id')->all();
    // A period-specific route (see Route::PERIODS) only ever runs the one
    // period — offering the other here would let an admin assign a student
    // to a trip that doesn't exist for this route.
    $showMorning = $routePeriod === null || $routePeriod === 'morning';
    $showEvening = $routePeriod === null || $routePeriod === 'evening';
    $defaultPeriod = $showMorning ? 'morning' : 'evening';
@endphp
<div x-show="assignOpen === {{ $stop->id }}" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div @click.outside="assignOpen = null" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" x-data="{ search: '', period: '{{ $defaultPeriod }}', sameEvening: false }">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[17px] font-bold text-[#031C5B]">Élèves — {{ $stop->name }}</h3>
            <button @click="assignOpen = null" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
        </div>

        @if($showMorning && $showEvening)
        {{-- Most students take the same route home as to school, but not always —
             this lets an admin assign a different stop for the evening leg by
             switching tabs and saving separately, instead of only ever writing
             a 'morning' enrollment (the old hardcoded behavior). --}}
        <div class="flex gap-2 mb-3">
            <button type="button" @click="period = 'morning'" :class="period === 'morning' ? 'bg-[#031C5B] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="flex-1 py-2 rounded-lg text-[12.5px] font-bold transition">
                <i class="ph-bold ph-sun-horizon"></i> Matin
            </button>
            <button type="button" @click="period = 'evening'" :class="period === 'evening' ? 'bg-[#031C5B] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'" class="flex-1 py-2 rounded-lg text-[12.5px] font-bold transition">
                <i class="ph-bold ph-moon-stars"></i> Soir
            </button>
        </div>
        @else
        {{-- This route only runs {{ $showMorning ? 'le matin' : 'le soir' }} — nothing to toggle. --}}
        <div class="mb-3 px-3 py-2 rounded-lg bg-slate-50 text-[12px] font-bold text-slate-500 flex items-center gap-2">
            <i class="ph-bold {{ $showMorning ? 'ph-sun-horizon' : 'ph-moon-stars' }}"></i>
            {{ $showMorning ? 'Matin' : 'Soir' }} uniquement — cette route n'a pas de trajet {{ $showMorning ? 'du soir' : 'du matin' }}.
        </div>
        @endif

        <input type="text" x-model="search" placeholder="Rechercher un élève..." class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B] mb-3">

        <form method="POST" action="{{ route('school.transport.stops.students.store', $stop->id) }}">
            @csrf
            <input type="hidden" name="period" :value="period">

            <template x-if="period === 'morning'">
                <div class="max-h-72 overflow-y-auto space-y-1 mb-2">
                    @forelse($students as $student)
                        @php $rowText = strtolower($student->first_name . ' ' . $student->last_name . ' ' . $student->roll_number); @endphp
                        <label x-show="!search || {{ \Illuminate\Support\Js::from($rowText) }}.includes(search.toLowerCase())" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" {{ in_array($student->id, $morningAssignedIds) ? 'checked' : '' }} class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                            <span class="text-[13px] font-semibold text-slate-700">{{ $student->first_name }} {{ $student->last_name }}</span>
                            <span class="text-[11px] text-slate-400">#{{ $student->roll_number }}</span>
                        </label>
                    @empty
                        <p class="text-slate-400 text-[13px] text-center py-6">Aucun élève enregistré.</p>
                    @endforelse
                </div>
            </template>
            <template x-if="period === 'evening'">
                <div class="max-h-72 overflow-y-auto space-y-1 mb-2">
                    @forelse($students as $student)
                        @php $rowText = strtolower($student->first_name . ' ' . $student->last_name . ' ' . $student->roll_number); @endphp
                        <label x-show="!search || {{ \Illuminate\Support\Js::from($rowText) }}.includes(search.toLowerCase())" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" {{ in_array($student->id, $eveningAssignedIds) ? 'checked' : '' }} class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                            <span class="text-[13px] font-semibold text-slate-700">{{ $student->first_name }} {{ $student->last_name }}</span>
                            <span class="text-[11px] text-slate-400">#{{ $student->roll_number }}</span>
                        </label>
                    @empty
                        <p class="text-slate-400 text-[13px] text-center py-6">Aucun élève enregistré.</p>
                    @endforelse
                </div>
            </template>

            @if($showEvening)
            <label x-show="period === 'morning'" class="flex items-center gap-2.5 mb-4 px-1 cursor-pointer">
                <input type="checkbox" name="same_evening" value="1" x-model="sameEvening" class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                <span class="text-[12.5px] font-medium text-slate-600">Même arrêt pour le trajet du soir</span>
            </label>
            @endif

            <button type="submit" class="w-full px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
                Enregistrer les Assignations
            </button>
        </form>
    </div>
</div>
