@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ activeTab: 'buildings' }">

    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Gestion des Salles et Bâtiments</h1>
            <p class="text-[13.5px] text-slate-500 mt-1">Gérez l'infrastructure de votre établissement.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif
    
    @if($errors->any())
    <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold">Il y a des erreurs dans le formulaire :</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Tabs -->
    <div class="flex border-b border-slate-200">
        <button @click="activeTab = 'buildings'" :class="{'border-[#2F5F76] text-[#2F5F76]': activeTab === 'buildings', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'buildings'}" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-[14px] transition flex items-center gap-2">
            <i class="ph-fill ph-buildings"></i> Bâtiments
        </button>
        <button @click="activeTab = 'rooms'" :class="{'border-[#2F5F76] text-[#2F5F76]': activeTab === 'rooms', 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300': activeTab !== 'rooms'}" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-[14px] transition flex items-center gap-2">
            <i class="ph-fill ph-door"></i> Salles
        </button>
    </div>

    <!-- Bâtiments Section -->
    <div x-show="activeTab === 'buildings'" class="space-y-6">
        
        <!-- Add Building Form -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph-fill ph-plus-circle text-primary-dynamic"></i> Ajouter un bâtiment
                </h2>
            </div>
            <div class="p-5">
                <form action="{{ route('school.academic.buildings.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="building_name" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom du bâtiment <span class="text-red-500">*</span></label>
                            <input type="text" id="building_name" name="name" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm" placeholder="Ex: Bâtiment A">
                        </div>
                        <div>
                            <label for="building_desc" class="block text-[13px] font-semibold text-slate-700 mb-1.5">Description (optionnel)</label>
                            <input type="text" id="building_desc" name="description" class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] focus:ring-1 focus:ring-[#2F5F76] transition shadow-sm" placeholder="Informations complémentaires">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                            <i class="ph-bold ph-floppy-disk"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Buildings List -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ isEmpty: {{ $buildings->isEmpty() ? 'true' : 'false' }} }">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph-fill ph-list-dashes text-primary-dynamic"></i> Liste des bâtiments
                </h2>
            </div>
            
            <div class="p-8" x-show="isEmpty">
                @include('SchoolDashboard::components.empty-state', [
                    'title' => 'Aucun bâtiment',
                    'description' => 'Vous n\'avez pas encore ajouté de bâtiment.',
                    'icon' => 'ph-fill ph-buildings'
                ])
            </div>

            <div class="overflow-x-auto" x-show="!isEmpty" style="display: none;">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-5 py-3">Nom</th>
                            <th class="px-5 py-3">Description</th>
                            <th class="px-5 py-3 text-center">Nbr. de Salles</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13px] font-medium text-slate-700">
                        @foreach($buildings as $building)
                        <tr class="hover:bg-slate-50/50 transition" x-data="{ editing: false }">
                            <td class="px-5 py-3">
                                <span x-show="!editing">{{ $building->name }}</span>
                                <input x-show="editing" form="edit-building-{{ $building->id }}" type="text" name="name" value="{{ $building->name }}" class="border rounded px-2 py-1 w-full text-sm">
                            </td>
                            <td class="px-5 py-3">
                                <span x-show="!editing">{{ $building->description ?: '-' }}</span>
                                <input x-show="editing" form="edit-building-{{ $building->id }}" type="text" name="description" value="{{ $building->description }}" class="border rounded px-2 py-1 w-full text-sm">
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="bg-[#2F5F76]/10 text-[#2F5F76] px-2 py-1 rounded-md text-[11px] font-bold">{{ $building->rooms->count() }}</span>
                            </td>
                            <td class="px-5 py-3 text-right flex justify-end gap-2">
                                <form id="edit-building-{{ $building->id }}" action="{{ route('school.academic.buildings.update', $building->id) }}" method="POST">
                                    @csrf @method('PUT')
                                </form>
                                <button x-show="editing" type="submit" form="edit-building-{{ $building->id }}" class="text-green-600 hover:text-green-800 bg-green-50 p-2 rounded-lg transition" title="Sauvegarder"><i class="ph-bold ph-check"></i></button>
                                <button x-show="editing" type="button" @click="editing = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 p-2 rounded-lg transition" title="Annuler"><i class="ph-bold ph-x"></i></button>
                                
                                <button x-show="!editing" @click="editing = true" class="text-[#2F5F76] hover:text-[#1E4357] bg-[#2F5F76]/10 hover:bg-[#2F5F76]/20 p-2 rounded-lg transition" title="Modifier"><i class="ph-fill ph-pencil-simple"></i></button>
                                
                                <form action="{{ route('school.academic.buildings.destroy', $building->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce bâtiment ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition" title="Supprimer">
                                        <i class="ph-fill ph-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- Salles Section -->
    <div x-show="activeTab === 'rooms'" class="space-y-6" style="display: none;">
        
        @if($buildings->isEmpty())
        <div class="p-4 mb-4 text-sm text-amber-800 rounded-lg bg-amber-50 flex items-center gap-2" role="alert">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-medium">Vous devez d'abord créer un bâtiment avant de pouvoir ajouter une salle.</span>
        </div>
        @else
        <!-- Add Room Form -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph-fill ph-plus-circle text-primary-dynamic"></i> Ajouter une salle
                </h2>
            </div>
            <div class="p-5">
                <form action="{{ route('school.academic.rooms.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Bâtiment <span class="text-red-500">*</span></label>
                            <select name="building_id" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] transition shadow-sm">
                                <option value="">Sélectionner un bâtiment</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building->id }}">{{ $building->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Nom de la salle <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] transition shadow-sm" placeholder="Ex: Salle 101">
                        </div>
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">Capacité (places) <span class="text-red-500">*</span></label>
                            <input type="number" name="capacity" min="1" value="30" required class="w-full bg-[#FAFBFC] border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#2F5F76] transition shadow-sm">
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-[#2F5F76] hover:bg-[#1E4357] text-white font-bold text-[13px] px-6 py-2.5 rounded-lg shadow-sm transition flex items-center justify-center gap-2">
                            <i class="ph-bold ph-floppy-disk"></i> Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        <!-- Rooms List -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden" x-data="{ isEmpty: {{ $rooms->isEmpty() ? 'true' : 'false' }} }">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-[15px] font-bold text-slate-800 flex items-center gap-2">
                    <i class="ph-fill ph-list-dashes text-primary-dynamic"></i> Liste des salles
                </h2>
            </div>
            
            <div class="p-8" x-show="isEmpty">
                @include('SchoolDashboard::components.empty-state', [
                    'title' => 'Aucune salle',
                    'description' => 'Vous n\'avez pas encore ajouté de salle.',
                    'icon' => 'ph-fill ph-door'
                ])
            </div>

            <div class="overflow-x-auto" x-show="!isEmpty" style="display: none;">
                <table class="w-full text-left border-collapse whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                            <th class="px-5 py-3">Bâtiment</th>
                            <th class="px-5 py-3">Nom</th>
                            <th class="px-5 py-3">Capacité</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-[13px] font-medium text-slate-700">
                        @foreach($rooms as $room)
                        <tr class="hover:bg-slate-50/50 transition" x-data="{ editing: false }">
                            <td class="px-5 py-3">
                                <span x-show="!editing">{{ $room->building->name ?? 'N/A' }}</span>
                                <select x-show="editing" form="edit-room-{{ $room->id }}" name="building_id" class="border rounded px-2 py-1 w-full text-sm">
                                    @foreach($buildings as $building)
                                        <option value="{{ $building->id }}" {{ $room->building_id == $building->id ? 'selected' : '' }}>{{ $building->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-5 py-3">
                                <span x-show="!editing">{{ $room->name }}</span>
                                <input x-show="editing" form="edit-room-{{ $room->id }}" type="text" name="name" value="{{ $room->name }}" class="border rounded px-2 py-1 w-full text-sm">
                            </td>
                            <td class="px-5 py-3">
                                <span x-show="!editing" class="bg-blue-50 text-blue-600 px-2 py-1 rounded text-xs font-bold">{{ $room->capacity }} places</span>
                                <input x-show="editing" form="edit-room-{{ $room->id }}" type="number" min="1" name="capacity" value="{{ $room->capacity }}" class="border rounded px-2 py-1 w-24 text-sm">
                            </td>
                            <td class="px-5 py-3 text-right flex justify-end gap-2">
                                <form id="edit-room-{{ $room->id }}" action="{{ route('school.academic.rooms.update', $room->id) }}" method="POST">
                                    @csrf @method('PUT')
                                </form>
                                <button x-show="editing" type="submit" form="edit-room-{{ $room->id }}" class="text-green-600 hover:text-green-800 bg-green-50 p-2 rounded-lg transition" title="Sauvegarder"><i class="ph-bold ph-check"></i></button>
                                <button x-show="editing" type="button" @click="editing = false" class="text-slate-400 hover:text-slate-600 bg-slate-50 p-2 rounded-lg transition" title="Annuler"><i class="ph-bold ph-x"></i></button>
                                
                                <button x-show="!editing" @click="editing = true" class="text-[#2F5F76] hover:text-[#1E4357] bg-[#2F5F76]/10 hover:bg-[#2F5F76]/20 p-2 rounded-lg transition" title="Modifier"><i class="ph-fill ph-pencil-simple"></i></button>
                                
                                <form action="{{ route('school.academic.rooms.destroy', $room->id) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette salle ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition" title="Supprimer">
                                        <i class="ph-fill ph-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
