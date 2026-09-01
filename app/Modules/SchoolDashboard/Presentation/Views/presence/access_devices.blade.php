@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[1400px] w-full mx-auto space-y-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-[26px] font-bold text-[#0F172A] tracking-tight">Appareils de Scan</h1>
            <p class="text-[14px] text-slate-500 mt-1">Terminaux "Academia Access Scanner" (portails, cantine, bus) — configuration fixée par l'établissement.</p>
        </div>
        <a href="{{ route('school.academic.presence.access') }}" class="bg-white hover:bg-slate-50 text-[#031C5B] font-bold text-[13px] px-5 py-2.5 rounded-xl shadow-sm border border-slate-200 transition flex items-center gap-2">
            <i class="ph-bold ph-arrow-left"></i>
            Contrôle d'Accès
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ type: 'portal_entry' }">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <h3 class="text-[15px] font-bold text-slate-900 mb-1">Nouvel Appareil de Scan</h3>
            <p class="text-[12.5px] text-slate-500 mb-4">
                Pour l'app <span class="font-semibold">Academia Access Scanner</span>. Code établissement : <span class="font-mono font-bold text-slate-700">{{ $schoolCode }}</span>
            </p>
            <form action="{{ route('school.academic.presence.access.devices.store') }}" method="POST" class="space-y-3">
                @csrf
                <input type="text" name="name" required placeholder="Nom de l'appareil (ex: tablette-portail-1)"
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[13.5px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                <input type="text" name="password" required minlength="4" placeholder="Mot de passe de l'appareil"
                    class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[13.5px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                <select name="access_type" x-model="type" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[13.5px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                    @foreach(\App\Modules\Presence\Domain\Models\AccessDevice::TYPES as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <div x-show="type === 'portal_entry' || type === 'portal_exit' || type === 'canteen'">
                    <select name="access_point_id" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[13.5px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                        <option value="">-- Portail / Point d'accès --</option>
                        @foreach($accessPoints as $point)
                            <option value="{{ $point->id }}">{{ $point->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="type === 'bus_board' || type === 'bus_alight'" class="space-y-3">
                    <select name="bus_id" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[13.5px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                        <option value="">-- Bus --</option>
                        @foreach($buses as $bus)
                            <option value="{{ $bus->id }}">{{ $bus->bus_number }}</option>
                        @endforeach
                    </select>
                    <select name="route_id" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[13.5px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76]">
                        <option value="">-- Trajet --</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}">{{ $route->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="w-full bg-[#031C5B] hover:bg-[#031C5B]/90 text-white font-bold text-[13px] px-5 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                    <i class="ph-bold ph-plus"></i>
                    Créer l'appareil
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-[15px] font-bold text-slate-900">Appareils de Scan</h3>
                <p class="text-[12.5px] text-slate-500 mt-0.5">Configuration fixe — non modifiable depuis l'application de scan elle-même.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Nom</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Affecté à</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Statut</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider">Dernière Utilisation</th>
                            <th class="px-4 py-3 text-[10.5px] font-bold text-slate-500 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($devices as $device)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-3 text-[13px] font-bold text-slate-800">{{ $device->name }}</td>
                            <td class="px-4 py-3 text-[12.5px] text-slate-600">{{ \App\Modules\Presence\Domain\Models\AccessDevice::TYPES[$device->access_type] ?? $device->access_type }}</td>
                            <td class="px-4 py-3 text-[12.5px] text-slate-600">{{ $device->label }}</td>
                            <td class="px-4 py-3">
                                @if($device->is_active)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-green-50 text-green-600 border border-green-100">Actif</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 border border-slate-200">Désactivé</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-[12.5px] text-slate-500">{{ $device->last_used_at?->format('d/m/Y H:i') ?? 'Jamais' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <form action="{{ route('school.academic.presence.access.devices.toggle', $device->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-slate-400 hover:text-slate-700" title="{{ $device->is_active ? 'Désactiver' : 'Réactiver' }}">
                                            <i class="ph-bold {{ $device->is_active ? 'ph-pause-circle' : 'ph-play-circle' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('school.academic.presence.access.devices.destroy', $device->id) }}" method="POST" onsubmit="return confirm('Supprimer cet appareil ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600"><i class="ph-bold ph-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-10 text-center text-slate-500 text-[13px]">Aucun appareil de scan configuré.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
