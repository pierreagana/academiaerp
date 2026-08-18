<div x-show="addOpen" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div @click.outside="addOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[17px] font-bold text-[#031C5B]">Ajouter un Point d'Arrêt</h3>
            <button @click="addOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
        </div>
        <form method="POST" action="{{ route('school.transport.stops.store') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="route_id" value="{{ $selectedRoute->id }}">

            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Nom de l'Arrêt</label>
                <input type="text" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            </div>
            <div x-data="{ suggestions: [], showSuggestions: false, searching: false }" @click.outside="showSuggestions = false" class="relative">
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Adresse</label>
                <input type="text" name="address" id="stop_address" autocomplete="off" placeholder="Rechercher une adresse à Abidjan..."
                    class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]"
                    @input.debounce.500ms="
                        const q = $event.target.value;
                        if (q.length < 3) { suggestions = []; showSuggestions = false; return; }
                        searching = true;
                        fetch('https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=ci&q=' + encodeURIComponent(q))
                            .then(r => r.json())
                            .then(data => { suggestions = data; showSuggestions = true; searching = false; })
                            .catch(() => { searching = false; });
                    "
                    @focus="if (suggestions.length) showSuggestions = true">
                <div x-show="showSuggestions" x-cloak class="absolute z-10 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                    <template x-if="searching">
                        <p class="px-3 py-2 text-[12px] text-slate-400">Recherche...</p>
                    </template>
                    <template x-if="!searching && suggestions.length === 0">
                        <p class="px-3 py-2 text-[12px] text-slate-400">Aucun résultat.</p>
                    </template>
                    <template x-for="(s, idx) in suggestions" :key="idx">
                        <button type="button"
                            @click="
                                document.getElementById('stop_address').value = s.display_name;
                                showSuggestions = false;
                                suggestions = [];
                                window.setAddStopLocation(parseFloat(s.lat), parseFloat(s.lon));
                            "
                            class="block w-full text-left px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 border-b border-slate-50 last:border-0"
                            x-text="s.display_name"></button>
                    </template>
                </div>
            </div>
            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Heure d'Arrivée Estimée</label>
                <input type="time" name="arrival_time" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            </div>
            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Emplacement — cliquez sur la carte ou déplacez le marqueur pour ajuster</label>
                <div id="add-stop-map" class="rounded-lg overflow-hidden border border-slate-200" style="height: 220px;"></div>
                <input type="hidden" id="stop_latitude" name="latitude">
                <input type="hidden" id="stop_longitude" name="longitude">
            </div>

            <button type="submit" class="w-full mt-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
                Ajouter l'Arrêt
            </button>
        </form>
    </div>
</div>
