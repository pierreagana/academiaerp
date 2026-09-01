@extends('SuperAdmin::layouts.app')

@section('title', 'Gestion des Équipements & Infrastructures')

@section('content')
<div class="space-y-6" x-data="facilitiesManager()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">Équipements & Infrastructures</h1>
            <p class="text-[13.5px] font-medium text-slate-500 mt-1">Catalogue maître des équipements et commodités que les établissements scolaires peuvent déclarer.</p>
        </div>
        <button type="button" @click="openCreateModal()"
                class="inline-flex items-center gap-2 bg-[#2F5F76] text-white px-4 py-2.5 rounded-xl font-bold text-sm hover:bg-[#1E4357] shadow-sm transition">
            <i class="ph-bold ph-plus-circle text-lg"></i>
            Ajouter un équipement
        </button>
    </div>

    <!-- Alert Success -->
    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm font-semibold flex items-center gap-3">
            <i class="ph-fill ph-check-circle text-xl text-emerald-600"></i>
            {{ session('success') }}
        </div>
    @endif

    <!-- Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Équipements</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $totalCount }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#2F5F76] flex items-center justify-center text-2xl">
                <i class="ph-fill ph-buildings"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Actifs au catalogue</p>
                <p class="text-2xl font-black text-emerald-600 mt-1">{{ $activeCount }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                <i class="ph-fill ph-check-circle"></i>
            </div>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Désactivés</p>
                <p class="text-2xl font-black text-amber-600 mt-1">{{ $totalCount - $activeCount }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                <i class="ph-fill ph-prohibit"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <!-- Search bar -->
        <div class="p-4 border-b border-slate-100 flex items-center justify-between gap-4">
            <form action="{{ route('superadmin.facilities') }}" method="GET" class="relative flex-1 max-w-md">
                <i class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Rechercher un équipement, catégorie..."
                       class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-[#2F5F76] transition">
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-slate-100 text-[11.5px] font-bold uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5">Équipement & Icône</th>
                        <th class="px-6 py-3.5">Catégorie</th>
                        <th class="px-6 py-3.5">Établissements Équipés</th>
                        <th class="px-6 py-3.5">Statut</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[13.5px]">
                    @forelse($facilities as $facility)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xl shrink-0">
                                        <i class="ph {{ $facility->icon }}"></i>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-900">{{ $facility->name }}</span>
                                        @if($facility->description)
                                            <p class="text-xs text-slate-400 line-clamp-1 mt-0.5">{{ $facility->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ $facility->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-900">{{ $facility->schools_count }}</span>
                                <span class="text-xs text-slate-400">école(s)</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($facility->is_active)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button type="button" @click="openEditModal({{ json_encode($facility) }})"
                                            class="w-8 h-8 rounded-lg text-slate-600 hover:bg-slate-100 flex items-center justify-center transition" title="Modifier">
                                        <i class="ph-bold ph-pencil-simple text-base"></i>
                                    </button>
                                    <form action="{{ route('superadmin.facilities.toggle', $facility->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="w-8 h-8 rounded-lg text-slate-600 hover:bg-slate-100 flex items-center justify-center transition" 
                                                title="{{ $facility->is_active ? 'Désactiver' : 'Activer' }}">
                                            <i class="ph-bold {{ $facility->is_active ? 'ph-toggle-right text-emerald-600' : 'ph-toggle-left text-slate-400' }} text-xl"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('superadmin.facilities.destroy', $facility->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cet équipement du catalogue ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg text-red-500 hover:bg-red-50 flex items-center justify-center transition" title="Supprimer">
                                            <i class="ph-bold ph-trash text-base"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                Aucun équipement trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($facilities->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $facilities->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Create / Edit -->
    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
         x-cloak style="display: none;">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5" @click.away="showModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-lg font-black text-slate-900" x-text="isEditing ? 'Modifier l\'équipement' : 'Ajouter un équipement'"></h3>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-700 text-lg">
                    <i class="ph-bold ph-x"></i>
                </button>
            </div>

            <form :action="formAction" method="POST" class="space-y-4">
                @csrf
                <template x-if="isEditing">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nom de l'équipement <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="form.name" required placeholder="Ex: Piscine olympique, Cantine..."
                           class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-[#2F5F76] transition">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catégorie</label>
                        <select name="category" x-model="form.category"
                                class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-[#2F5F76] transition">
                            <option value="Restauration & Vie scolaire">Restauration & Vie scolaire</option>
                            <option value="Technologie & Numérique">Technologie & Numérique</option>
                            <option value="Logistique & Mobilité">Logistique & Mobilité</option>
                            <option value="Sports & Loisirs">Sports & Loisirs</option>
                            <option value="Pédagogie & Sciences">Pédagogie & Sciences</option>
                            <option value="Culture & Documentation">Culture & Documentation</option>
                            <option value="Santé & Bien-être">Santé & Bien-être</option>
                            <option value="Environnement & Énergie">Environnement & Énergie</option>
                            <option value="Général">Général</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Icône Phosphor</label>
                        <input type="text" name="icon" x-model="form.icon" placeholder="Ex: ph-swimming-pool"
                               class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-[#2F5F76] transition">
                    </div>
                </div>

                <!-- Icon Quick Picker Helper -->
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase mb-1.5">Icônes suggérées :</p>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="sugg in suggestedIcons" :key="sugg">
                            <button type="button" @click="form.icon = sugg" 
                                    :class="form.icon === sugg ? 'bg-[#2F5F76] text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-base transition">
                                <i class="ph" :class="sugg"></i>
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description (optionnelle)</label>
                    <textarea name="description" x-model="form.description" rows="2" placeholder="Description courte des caractéristiques de l'équipement..."
                              class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm outline-none focus:border-[#2F5F76] transition"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="showModal = false" class="px-4 py-2 text-sm font-bold text-slate-500 hover:text-slate-800 transition">
                        Annuler
                    </button>
                    <button type="submit" class="bg-[#2F5F76] text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-[#1E4357] transition shadow-sm">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function facilitiesManager() {
    return {
        showModal: false,
        isEditing: false,
        formAction: '{{ route('superadmin.facilities.store') }}',
        form: {
            id: null,
            name: '',
            category: 'Général',
            icon: 'ph-buildings',
            description: '',
            order: 0,
            is_active: true
        },
        suggestedIcons: [
            'ph-fork-knife', 'ph-wifi-high', 'ph-bus', 'ph-desktop', 'ph-swimming-pool', 
            'ph-flask', 'ph-books', 'ph-first-aid', 'ph-soccer-ball', 'ph-bed', 'ph-sun',
            'ph-music-notes', 'ph-camera', 'ph-tree', 'ph-paint-brush', 'ph-shield-check'
        ],
        openCreateModal() {
            this.isEditing = false;
            this.formAction = '{{ route('superadmin.facilities.store') }}';
            this.form = {
                id: null,
                name: '',
                category: 'Général',
                icon: 'ph-buildings',
                description: '',
                order: 0,
                is_active: true
            };
            this.showModal = true;
        },
        openEditModal(facility) {
            this.isEditing = true;
            this.formAction = `/superadmin/facilities/${facility.id}`;
            this.form = {
                id: facility.id,
                name: facility.name,
                category: facility.category || 'Général',
                icon: facility.icon || 'ph-buildings',
                description: facility.description || '',
                order: facility.order || 0,
                is_active: facility.is_active
            };
            this.showModal = true;
        }
    }
}
</script>
@endsection
