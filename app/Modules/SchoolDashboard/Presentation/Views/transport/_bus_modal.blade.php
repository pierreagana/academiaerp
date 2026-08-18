@php
    $isEdit = $bus !== null;
    $showFlag = $isEdit ? "editOpen === {$bus->id}" : 'createOpen';
    $closeExpr = $isEdit ? 'editOpen = null' : 'createOpen = false';
    $formAction = $isEdit ? route('school.transport.buses.update', $bus->id) : route('school.transport.buses.store');
    $driverValue = $isEdit && $bus->driver_type ? $bus->driver_type . ':' . $bus->driver_id : '';
@endphp
<div x-show="{{ $showFlag }}" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4" style="display: none;">
    <div @click.outside="{{ $closeExpr }}" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[17px] font-bold text-[#031C5B]">{{ $isEdit ? 'Modifier le Bus' : 'Ajouter un Bus' }}</h3>
            <button @click="{{ $closeExpr }}" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
        </div>
        <form method="POST" action="{{ $formAction }}" class="space-y-4">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Numéro du Bus</label>
                <input type="text" name="bus_number" required value="{{ $isEdit ? $bus->bus_number : '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            </div>
            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Plaque d'Immatriculation</label>
                <input type="text" name="plate_number" value="{{ $isEdit ? $bus->plate_number : '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Statut</label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                        @foreach(\App\Modules\Transport\Domain\Models\Bus::STATUSES as $value => $label)
                            <option value="{{ $value }}" {{ $isEdit && $bus->status === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Capacité</label>
                    <input type="number" name="capacity" min="0" required value="{{ $isEdit ? $bus->capacity : '' }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
            </div>
            <div>
                <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Chauffeur Assigné</label>
                <select name="driver_id" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    <option value="">Non assigné</option>
                    @forelse($drivers as $driver)
                        <option value="driver:{{ $driver->id }}" {{ $driverValue === 'driver:' . $driver->id ? 'selected' : '' }}>{{ $driver->first_name }} {{ $driver->last_name }}</option>
                    @empty
                        <option value="" disabled>Aucun chauffeur — ajoutez-en un dans l'onglet Chauffeurs</option>
                    @endforelse
                </select>
            </div>

            <button type="submit" class="w-full mt-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
                {{ $isEdit ? 'Enregistrer' : 'Ajouter le Bus' }}
            </button>
        </form>
    </div>
</div>
