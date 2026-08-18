@php
    $isEdit = $route !== null;
    $showFlag = $isEdit ? "editOpen === {$route->id}" : 'createOpen';
    $closeExpr = $isEdit ? 'editOpen = null' : 'createOpen = false';
    $formAction = $isEdit ? route('school.transport.routes.update', $route->id) : route('school.transport.routes.store');
    $scheduleType = $isEdit ? ($route->schedule_type ?? 'recurring') : 'recurring';
    $recurringDays = $isEdit ? ($route->recurring_days ?? []) : [];
@endphp
<div x-show="{{ $showFlag }}" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4 overflow-y-auto" style="display: none;">
    <div @click.outside="{{ $closeExpr }}" x-data="{ scheduleType: '{{ $scheduleType }}' }" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 my-8">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[17px] font-bold text-[#031C5B]">{{ $isEdit ? 'Modifier la Route' : 'Nouvelle Route' }}</h3>
            <button @click="{{ $closeExpr }}" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
        </div>
        <form method="POST" action="{{ $formAction }}" class="space-y-4">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Nom de la Route</label>
                <input type="text" name="name" required value="{{ $isEdit ? $route->name : '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            </div>
            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Zone</label>
                <input type="text" name="zone" value="{{ $isEdit ? $route->zone : '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            </div>
            @if($isEdit)
            <p class="text-[11.5px] text-slate-400 flex items-center gap-1.5">
                <i class="ph ph-map-trifold"></i> Distance : {{ $route->distance_km ? $route->distance_km . ' km' : 'calculée automatiquement dès que 2 arrêts sont positionnés' }}
            </p>
            @endif
            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Bus Assigné</label>
                <select name="bus_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    <option value="">Non assigné</option>
                    @foreach($buses as $bus)
                        <option value="{{ $bus->id }}" {{ $isEdit && $route->bus_id === $bus->id ? 'selected' : '' }}>{{ $bus->bus_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Statut</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    @foreach(\App\Modules\Transport\Domain\Models\Route::STATUSES as $value => $label)
                        <option value="{{ $value }}" {{ $isEdit && $route->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Heure d'Arrivée au Premier Arrêt</label>
                    <input type="time" name="first_stop_time" value="{{ $isEdit ? $route->first_stop_time : '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Temps d'Arrêt à Arrêt (min)</label>
                    <input type="number" min="1" name="stop_interval_minutes" value="{{ $isEdit ? $route->stop_interval_minutes : '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Fréquence</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(\App\Modules\Transport\Domain\Models\Route::SCHEDULE_TYPES as $value => $label)
                        <label class="flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2.5 cursor-pointer transition" :class="scheduleType === '{{ $value }}' ? 'border-[#031C5B] bg-blue-50/50' : ''">
                            <input type="radio" name="schedule_type" value="{{ $value }}" x-model="scheduleType" {{ $scheduleType === $value ? 'checked' : '' }} class="text-[#031C5B] focus:ring-[#031C5B]">
                            <span class="text-[12.5px] font-semibold text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div x-show="scheduleType === 'recurring'" x-cloak>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Jours de Passage</label>
                <div class="grid grid-cols-7 gap-1.5">
                    @foreach(\App\Modules\Transport\Domain\Models\Route::DAYS as $value => $label)
                        <label class="flex flex-col items-center gap-1 border border-slate-200 rounded-lg py-2 cursor-pointer hover:border-[#031C5B] has-[:checked]:border-[#031C5B] has-[:checked]:bg-blue-50/50">
                            <input type="checkbox" name="recurring_days[]" value="{{ $value }}" {{ in_array($value, $recurringDays) ? 'checked' : '' }} class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                            <span class="text-[10.5px] font-bold text-slate-600">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <button type="submit" class="w-full mt-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
                {{ $isEdit ? 'Enregistrer' : 'Créer la Route' }}
            </button>
        </form>
    </div>
</div>
