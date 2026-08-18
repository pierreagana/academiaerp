<div x-show="logOpen" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div @click.outside="logOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" x-data="{ status: 'complete' }">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[17px] font-bold text-[#031C5B]">Journaliser un Trajet</h3>
            <button @click="logOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
        </div>
        <form method="POST" action="{{ route('school.transport.trips.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Route</label>
                    <select name="route_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                        <option value="">—</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Bus</label>
                    <select name="bus_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                        <option value="">—</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}">{{ $bus->bus_number }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Tournée</label>
                    <select name="shift" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                        @foreach(\App\Modules\Transport\Domain\Models\TripLog::SHIFTS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Date</label>
                    <input type="date" name="trip_date" required value="{{ now()->toDateString() }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Heure Prévue</label>
                    <input type="time" name="scheduled_start" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Distance (km)</label>
                    <input type="number" step="0.1" min="0" name="distance_km" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
            </div>

            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Présence (nombre d'élèves)</label>
                <input type="number" min="0" name="attendance_count" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            </div>

            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Statut</label>
                <select name="status" x-model="status" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    @foreach(\App\Modules\Transport\Domain\Models\TripLog::STATUSES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="status === 'incident'" x-cloak>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Notes sur l'Incident</label>
                <textarea name="incident_notes" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]"></textarea>
            </div>

            <button type="submit" class="w-full mt-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
                Enregistrer le Trajet
            </button>
        </form>
    </div>
</div>
