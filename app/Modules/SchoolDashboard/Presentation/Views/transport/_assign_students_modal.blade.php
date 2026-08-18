@php $assignedIds = $stop->students->pluck('id')->all(); @endphp
<div x-show="assignOpen === {{ $stop->id }}" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div @click.outside="assignOpen = null" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" x-data="{ search: '' }">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[17px] font-bold text-[#031C5B]">Élèves — {{ $stop->name }}</h3>
            <button @click="assignOpen = null" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
        </div>

        <input type="text" x-model="search" placeholder="Rechercher un élève..." class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B] mb-3">

        <form method="POST" action="{{ route('school.transport.stops.students.store', $stop->id) }}">
            @csrf
            <div class="max-h-72 overflow-y-auto space-y-1 mb-4">
                @forelse($students as $student)
                    @php $rowText = strtolower($student->first_name . ' ' . $student->last_name . ' ' . $student->roll_number); @endphp
                    <label x-show="!search || {{ \Illuminate\Support\Js::from($rowText) }}.includes(search.toLowerCase())" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" {{ in_array($student->id, $assignedIds) ? 'checked' : '' }} class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                        <span class="text-[13px] font-semibold text-slate-700">{{ $student->first_name }} {{ $student->last_name }}</span>
                        <span class="text-[11px] text-slate-400">#{{ $student->roll_number }}</span>
                    </label>
                @empty
                    <p class="text-slate-400 text-[13px] text-center py-6">Aucun élève enregistré.</p>
                @endforelse
            </div>
            <button type="submit" class="w-full px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
                Enregistrer les Assignations
            </button>
        </form>
    </div>
</div>
