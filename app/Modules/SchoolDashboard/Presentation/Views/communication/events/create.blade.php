@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="w-full mx-auto pb-20">
    @include('SchoolDashboard::communication.events._tabs')

    <div class="mb-8">
        <h1 class="text-[28px] font-extrabold text-[#031C5B] tracking-tight">
            {{ isset($event) ? 'Modifier l\'Événement' : 'Créer un Nouvel Événement' }}
        </h1>
        <p class="text-[14px] text-slate-500 mt-1 max-w-xl">Configurez les détails de votre événement.</p>
    </div>

    @if($errors->any())
    <div class="p-4 mb-6 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
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

    <form action="{{ isset($event) ? route('school.communication.events.update', $event->id) : route('school.communication.events.store') }}" method="POST"
        class="flex flex-col lg:flex-row gap-8"
        x-data="{
            locationType: '{{ old('location_type', $event->location_type ?? 'internal') }}',
            audienceType: '{{ old('audience_type', $event->audience_type ?? 'all') }}',
            equipment: {{ \Illuminate\Support\Js::from(old('equipment_needs', $event->equipment_needs ?? [])) }},
            newEquipment: '',
            addEquipment() {
                const v = this.newEquipment.trim();
                if (v && !this.equipment.includes(v)) { this.equipment.push(v); }
                this.newEquipment = '';
            }
        }">
        @csrf
        @if(isset($event))
            @method('PUT')
        @endif

        <!-- Left Content -->
        <div class="flex-1 space-y-6">
            <!-- Informations générales -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                    <i class="ph-bold ph-info text-[#031C5B] text-lg"></i>
                    <h2 class="text-[16px] font-extrabold text-[#031C5B]">Informations générales</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label for="title" class="block text-[13px] font-bold text-slate-700 mb-1.5">Titre de l'événement <span class="text-red-500">*</span></label>
                        <input type="text" id="title" name="title" value="{{ old('title', $event->title ?? '') }}" required placeholder="Ex: Cérémonie de Remise des Diplômes 2026" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all">
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="type" class="block text-[13px] font-bold text-slate-700 mb-1.5">Type d'événement <span class="text-red-500">*</span></label>
                            <select id="type" name="type" required class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 appearance-none cursor-pointer">
                                @php $currentType = old('type', $event->type ?? ''); @endphp
                                <option value="scolaire" {{ $currentType === 'scolaire' ? 'selected' : '' }}>Scolaire</option>
                                <option value="sportif" {{ $currentType === 'sportif' ? 'selected' : '' }}>Sportif</option>
                                <option value="culturel" {{ $currentType === 'culturel' ? 'selected' : '' }}>Culturel</option>
                                <option value="administratif" {{ $currentType === 'administratif' ? 'selected' : '' }}>Administratif</option>
                            </select>
                        </div>
                        <div>
                            <label for="organizer_name" class="block text-[13px] font-bold text-slate-700 mb-1.5">Organisateur Responsable</label>
                            <input type="text" id="organizer_name" name="organizer_name" value="{{ old('organizer_name', $event->organizer_name ?? '') }}" placeholder="Nom du professeur ou département" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all">
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-[13px] font-bold text-slate-700 mb-1.5">Description</label>
                        <textarea id="description" name="description" rows="3" placeholder="Détaillez le programme et les objectifs de cet événement..." class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all resize-none">{{ old('description', $event->description ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Date et Lieu -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                    <i class="ph-bold ph-clock text-[#031C5B] text-lg"></i>
                    <h2 class="text-[16px] font-extrabold text-[#031C5B]">Date et Lieu</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="start_at" class="block text-[13px] font-bold text-slate-700 mb-1.5">Date et Heure de début <span class="text-red-500">*</span></label>
                            <input type="datetime-local" id="start_at" name="start_at" required value="{{ old('start_at', isset($event) ? $event->start_at->format('Y-m-d\TH:i') : '') }}" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10">
                        </div>
                        <div>
                            <label for="end_at" class="block text-[13px] font-bold text-slate-700 mb-1.5">Date et Heure de fin</label>
                            <input type="datetime-local" id="end_at" name="end_at" value="{{ old('end_at', isset($event) && $event->end_at ? $event->end_at->format('Y-m-d\TH:i') : '') }}" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-slate-700 mb-2">Type de localisation</label>
                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="location_type" value="internal" x-model="locationType" class="text-[#1E40AF] focus:ring-[#1E40AF]">
                                <span class="text-[13.5px] font-semibold text-slate-700">Salle interne</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="location_type" value="external" x-model="locationType" class="text-[#1E40AF] focus:ring-[#1E40AF]">
                                <span class="text-[13.5px] font-semibold text-slate-700">Localisation externe</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="locationType === 'internal'">
                        <label for="room_id" class="block text-[13px] font-bold text-slate-700 mb-1.5">Sélectionner la salle</label>
                        <select id="room_id" name="room_id" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 appearance-none cursor-pointer">
                            <option value="">Sélectionner...</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" {{ (string) old('room_id', $event->room_id ?? '') === (string) $room->id ? 'selected' : '' }}>{{ $room->name }} (Capacité: {{ $room->capacity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="locationType === 'external'">
                        <label for="external_address" class="block text-[13px] font-bold text-slate-700 mb-1.5">Adresse</label>
                        <input type="text" id="external_address" name="external_address" value="{{ old('external_address', $event->external_address ?? '') }}" placeholder="Ex: Musée National des Arts" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10">
                    </div>
                </div>
            </div>

            <!-- Public Cible -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                    <i class="ph-bold ph-users-three text-[#031C5B] text-lg"></i>
                    <h2 class="text-[16px] font-extrabold text-[#031C5B]">Public Cible</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="relative flex flex-col gap-1 p-4 rounded-xl border-2 cursor-pointer transition" :class="audienceType === 'all' ? 'border-[#1E40AF] bg-blue-50/50' : 'border-slate-200'">
                            <input type="radio" name="audience_type" value="all" x-model="audienceType" class="sr-only">
                            <span class="text-[13.5px] font-bold text-slate-800 flex items-center gap-1.5">Toutes les classes <i x-show="audienceType === 'all'" class="ph-fill ph-check-circle text-[#1E40AF]"></i></span>
                            <span class="text-[12px] text-slate-500">Ouvert à l'ensemble de l'établissement.</span>
                        </label>
                        <label class="relative flex flex-col gap-1 p-4 rounded-xl border-2 cursor-pointer transition" :class="audienceType === 'specific_classes' ? 'border-[#1E40AF] bg-blue-50/50' : 'border-slate-200'">
                            <input type="radio" name="audience_type" value="specific_classes" x-model="audienceType" class="sr-only">
                            <span class="text-[13.5px] font-bold text-slate-800 flex items-center gap-1.5">Classes Spécifiques <i x-show="audienceType === 'specific_classes'" class="ph-fill ph-check-circle text-[#1E40AF]"></i></span>
                            <span class="text-[12px] text-slate-500">Sélectionner des niveaux.</span>
                        </label>
                        <label class="relative flex flex-col gap-1 p-4 rounded-xl border-2 cursor-pointer transition" :class="audienceType === 'parents_only' ? 'border-[#1E40AF] bg-blue-50/50' : 'border-slate-200'">
                            <input type="radio" name="audience_type" value="parents_only" x-model="audienceType" class="sr-only">
                            <span class="text-[13.5px] font-bold text-slate-800 flex items-center gap-1.5">Parents Uniquement <i x-show="audienceType === 'parents_only'" class="ph-fill ph-check-circle text-[#1E40AF]"></i></span>
                            <span class="text-[12px] text-slate-500">Réunions ou assemblées générales.</span>
                        </label>
                    </div>

                    <div x-show="audienceType === 'specific_classes'" class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-1">
                        @php $linkedClassIds = old('academic_class_ids', isset($event) ? $event->academicClasses->pluck('id')->all() : []); @endphp
                        @foreach($classes as $class)
                            <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-100 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="academic_class_ids[]" value="{{ $class->id }}" {{ in_array($class->id, $linkedClassIds) ? 'checked' : '' }} class="rounded border-slate-300 text-[#1E40AF] focus:ring-[#1E40AF]">
                                <span class="text-[12.5px] font-semibold text-slate-700">{{ $class->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Budget & Logistique -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                    <i class="ph-bold ph-archive text-[#031C5B] text-lg"></i>
                    <h2 class="text-[16px] font-extrabold text-[#031C5B]">Budget & Logistique</h2>
                </div>
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="estimated_budget" class="block text-[13px] font-bold text-slate-700 mb-1.5">Estimation des coûts (FCFA)</label>
                            <input type="number" step="0.01" id="estimated_budget" name="estimated_budget" value="{{ old('estimated_budget', $event->estimated_budget ?? '') }}" placeholder="0" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10">
                        </div>
                        <div>
                            <label for="participation_fee" class="block text-[13px] font-bold text-slate-700 mb-1.5">Participation demandée aux familles (FCFA)</label>
                            <input type="number" step="0.01" id="participation_fee" name="participation_fee" value="{{ old('participation_fee', $event->participation_fee ?? '') }}" placeholder="0 = gratuit" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Besoins en matériel</label>
                        <div class="flex flex-wrap items-center gap-2 p-2.5 bg-[#F8FAFC] border border-slate-200 rounded-lg min-h-[46px]">
                            <template x-for="(item, index) in equipment" :key="index">
                                <span class="inline-flex items-center gap-1.5 bg-white border border-slate-200 text-slate-700 text-[12.5px] font-semibold px-2.5 py-1 rounded-md">
                                    <span x-text="item"></span>
                                    <button type="button" @click="equipment.splice(index, 1)" class="text-slate-400 hover:text-red-500">
                                        <i class="ph-bold ph-x text-[11px]"></i>
                                    </button>
                                    <input type="hidden" name="equipment_needs[]" :value="item">
                                </span>
                            </template>
                            <input type="text" x-model="newEquipment" @keydown.enter.prevent="addEquipment()" @blur="addEquipment()" placeholder="Ajouter un équipement..." class="flex-1 min-w-[140px] bg-transparent text-[13px] outline-none px-1 py-1">
                        </div>
                    </div>

                    <div>
                        <label for="catering_notes" class="block text-[13px] font-bold text-slate-700 mb-1.5">Restauration / Collation</label>
                        <textarea id="catering_notes" name="catering_notes" rows="2" placeholder="Précisez les besoins en traiteur..." class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 resize-none">{{ old('catering_notes', $event->catering_notes ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label for="bus_count" class="block text-[13px] font-bold text-slate-700 mb-1.5">Bus affectés</label>
                            <input type="number" min="0" id="bus_count" name="bus_count" value="{{ old('bus_count', $event->bus_count ?? '') }}" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10">
                        </div>
                        <div>
                            <label for="bus_capacity" class="block text-[13px] font-bold text-slate-700 mb-1.5">Places totales</label>
                            <input type="number" min="0" id="bus_capacity" name="bus_capacity" value="{{ old('bus_capacity', $event->bus_capacity ?? '') }}" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10">
                        </div>
                        <div>
                            <label for="bus_status" class="block text-[13px] font-bold text-slate-700 mb-1.5">Statut transport</label>
                            @php $currentBusStatus = old('bus_status', $event->bus_status ?? ''); @endphp
                            <select id="bus_status" name="bus_status" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 appearance-none cursor-pointer">
                                <option value="">-</option>
                                <option value="pending" {{ $currentBusStatus === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="confirmed" {{ $currentBusStatus === 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="catering_count" class="block text-[13px] font-bold text-slate-700 mb-1.5">Nombre de paniers</label>
                            <input type="number" min="0" id="catering_count" name="catering_count" value="{{ old('catering_count', $event->catering_count ?? '') }}" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10">
                        </div>
                        <div>
                            <label for="catering_status" class="block text-[13px] font-bold text-slate-700 mb-1.5">Statut traiteur</label>
                            @php $currentCateringStatus = old('catering_status', $event->catering_status ?? ''); @endphp
                            <select id="catering_status" name="catering_status" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 appearance-none cursor-pointer">
                                <option value="">-</option>
                                <option value="awaiting" {{ $currentCateringStatus === 'awaiting' ? 'selected' : '' }}>En attente traiteur</option>
                                <option value="pending" {{ $currentCateringStatus === 'pending' ? 'selected' : '' }}>En attente</option>
                                <option value="confirmed" {{ $currentCateringStatus === 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-[13px] font-bold text-slate-700 mb-1.5">Encadrement (Professeurs)</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-40 overflow-y-auto p-1 border border-slate-100 rounded-lg">
                            @php $linkedTeacherIds = old('teacher_ids', isset($event) ? $event->teachers->pluck('id')->all() : []); @endphp
                            @forelse($teachers as $teacher)
                                <label class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-slate-50 cursor-pointer">
                                    <input type="checkbox" name="teacher_ids[]" value="{{ $teacher->id }}" {{ in_array($teacher->id, $linkedTeacherIds) ? 'checked' : '' }} class="rounded border-slate-300 text-[#1E40AF] focus:ring-[#1E40AF]">
                                    <span class="text-[12.5px] font-semibold text-slate-700">{{ $teacher->first_name }} {{ $teacher->last_name }}</span>
                                </label>
                            @empty
                                <p class="text-[12.5px] text-slate-400 col-span-full">Aucun enseignant enregistré.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="w-full lg:w-80 shrink-0 space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6">
                <label for="status" class="block text-[13px] font-bold text-slate-700 mb-1.5">Statut de l'événement</label>
                @php $currentStatus = old('status', $event->status ?? 'draft'); @endphp
                <select id="status" name="status" required class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 appearance-none cursor-pointer">
                    <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>Brouillon</option>
                    <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>En attente de validation</option>
                    <option value="confirmed" {{ $currentStatus === 'confirmed' ? 'selected' : '' }}>Confirmé</option>
                    <option value="cancelled" {{ $currentStatus === 'cancelled' ? 'selected' : '' }}>Annulé</option>
                </select>
            </div>

            <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <i class="ph-fill ph-sparkle text-purple-600 text-lg"></i>
                    <h3 class="font-extrabold text-slate-800 text-[14px]">Assistant Logistique IA</h3>
                </div>
                <p class="text-[12.5px] text-slate-600 font-medium leading-relaxed">
                    EduAfrica AI peut pré-remplir les besoins matériels et estimer le budget en se basant sur les événements similaires de l'établissement.
                </p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-3">
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-[#031C5B] text-white font-bold text-[14px] rounded-lg shadow-sm hover:bg-[#031C5B]/90 transition-all">
                    <i class="ph-bold ph-check-circle"></i>
                    {{ isset($event) ? 'Enregistrer les modifications' : 'Créer l\'événement' }}
                </button>
                <a href="{{ isset($event) ? route('school.communication.events.show', $event->id) : route('school.communication.events.calendar') }}" class="w-full inline-flex items-center justify-center px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-lg hover:bg-slate-50 transition-all">
                    Annuler
                </a>
            </div>
        </div>
    </form>
</div>
@endsection
