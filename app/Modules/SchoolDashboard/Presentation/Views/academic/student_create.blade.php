@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[1000px] w-full mx-auto space-y-10 pb-20" x-data="{ step: 1, maxStep: 2 }">
    
    <!-- Header Section -->
    <div class="text-center sm:text-left flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 relative z-10">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-600 text-[12px] font-bold tracking-wide uppercase mb-3">
                <i class="ph-bold ph-sparkle"></i> Nouveau Processus
            </div>
            <h1 class="text-[36px] font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-slate-900 via-[#1E40AF] to-[#4F46E5] tracking-tight">Inscription Étudiant</h1>
            <p class="text-[15px] text-slate-500 mt-2 max-w-xl leading-relaxed">Remplissez les informations ci-dessous pour intégrer un nouvel étudiant. Notre assistant de saisie validera vos données en temps réel.</p>
        </div>
        <a href="{{ route('school.academic.students') }}" class="px-5 py-2.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-bold text-[13.5px] rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
            <i class="ph-bold ph-arrow-left"></i> Retour à la liste
        </a>
    </div>

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

    <!-- Stepper (Interactive) -->
    <div class="relative max-w-3xl mx-auto sm:mx-0 mt-8 mb-12">
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1.5 bg-slate-100 rounded-full z-0 overflow-hidden">
            <div class="h-full bg-gradient-to-r from-[#1E40AF] to-[#4F46E5] rounded-full transition-all duration-700 ease-out" :style="'width: ' + ((step - 1) / (maxStep - 1) * 100) + '%'"></div>
        </div>
        
        <div class="relative z-10 flex justify-between">
            <!-- Step 1 -->
            <button type="button" @click="step = 1" class="flex flex-col items-center gap-3 group focus:outline-none">
                <div class="w-12 h-12 rounded-2xl font-bold text-[16px] flex items-center justify-center shadow-lg transition-all duration-500 ring-4 ring-white"
                     :class="step >= 1 ? 'bg-gradient-to-br from-[#1E40AF] to-indigo-600 text-white scale-110 shadow-indigo-200' : 'bg-white text-slate-400 border-2 border-slate-100 group-hover:border-slate-300'">
                    <i class="ph-bold ph-user" x-show="step < 2"></i>
                    <i class="ph-bold ph-check" x-show="step >= 2" style="display: none;"></i>
                </div>
                <span class="text-[13px] font-bold transition-colors duration-300" :class="step >= 1 ? 'text-[#1E40AF]' : 'text-slate-400'">Infos & Académique</span>
            </button>
            
            <!-- Step 2 -->
            <button type="button" @click="if($refs.studentForm.reportValidity()) step = 2" class="flex flex-col items-center gap-3 group focus:outline-none" :disabled="step < 1">
                <div class="w-12 h-12 rounded-2xl font-bold text-[16px] flex items-center justify-center shadow-lg transition-all duration-500 ring-4 ring-white"
                     :class="step >= 2 ? 'bg-gradient-to-br from-[#1E40AF] to-indigo-600 text-white scale-110 shadow-indigo-200' : 'bg-white text-slate-400 border-2 border-slate-100 group-hover:border-slate-300'">
                    <i class="ph-bold ph-first-aid" x-show="step < 2"></i>
                    <i class="ph-bold ph-check" x-show="step >= 2" style="display: none;"></i>
                </div>
                <span class="text-[13px] font-bold transition-colors duration-300" :class="step >= 2 ? 'text-[#1E40AF]' : 'text-slate-400'">Fiche Médicale</span>
            </button>
        </div>
    </div>

    <form x-ref="studentForm" action="{{ isset($student) ? route('school.academic.students.update', $student->id) : route('school.academic.students.store') }}" method="POST" enctype="multipart/form-data" class="relative">
        @csrf
        @if(isset($student))
            @method('PUT')
        @endif
        
        <!-- Field to store status, defaulted to active for now -->
        <input type="hidden" name="status" value="{{ old('status', $student->status ?? 'active') }}">
        @csrf
        
        <!-- STEP 1: Personal Information -->
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300 absolute top-0 w-full" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-8" class="space-y-6">
            
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative group">
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-gradient-to-br from-blue-50/50 via-indigo-50/20 to-transparent rounded-bl-[100px] -mr-40 -mt-40 pointer-events-none transition-transform duration-700 group-hover:scale-110"></div>
                
                <div class="p-8 sm:p-10 relative z-10">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner border border-blue-100/50">
                            <i class="ph-fill ph-identification-card text-[28px]"></i>
                        </div>
                        <div>
                            <h2 class="text-[22px] font-extrabold text-slate-800 tracking-tight">Informations Personnelles</h2>
                            <p class="text-[13.5px] text-slate-500 font-medium mt-0.5">Identité de base de l'étudiant.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Prénom -->
                        <div class="space-y-2" x-data="{ focused: false }">
                            <label for="first_name" class="block text-[13.5px] font-bold text-slate-700 transition-colors" :class="focused ? 'text-[#1E40AF]' : ''">Prénom <span class="text-red-500">*</span></label>
                            <div class="relative flex items-center">
                                <i class="ph-bold ph-user absolute left-4 text-[18px] transition-colors" :class="focused ? 'text-[#1E40AF]' : 'text-slate-400'"></i>
                                <input @focus="focused = true" @blur="focused = false" type="text" id="first_name" name="first_name" value="{{ old('first_name', $student->first_name ?? '') }}" placeholder="Ex: Koffi" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-12 pr-4 py-3.5 outline-none focus:border-[#1E40AF] focus:ring-4 focus:ring-[#1E40AF]/10 transition-all shadow-sm placeholder:text-slate-400 placeholder:font-normal">
                            </div>
                        </div>
                        
                        <!-- Nom -->
                        <div class="space-y-2" x-data="{ focused: false }">
                            <label for="last_name" class="block text-[13.5px] font-bold text-slate-700 transition-colors" :class="focused ? 'text-[#1E40AF]' : ''">Nom de famille <span class="text-red-500">*</span></label>
                            <div class="relative flex items-center">
                                <i class="ph-bold ph-text-aa absolute left-4 text-[18px] transition-colors" :class="focused ? 'text-[#1E40AF]' : 'text-slate-400'"></i>
                                <input @focus="focused = true" @blur="focused = false" type="text" id="last_name" name="last_name" value="{{ old('last_name', $student->last_name ?? '') }}" placeholder="Ex: Mensah" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-12 pr-4 py-3.5 outline-none focus:border-[#1E40AF] focus:ring-4 focus:ring-[#1E40AF]/10 transition-all shadow-sm placeholder:text-slate-400 placeholder:font-normal">
                            </div>
                        </div>
                        
                        <!-- Date de naissance -->
                        <div class="space-y-2" x-data="{ focused: false }">
                            <label for="dob" class="block text-[13.5px] font-bold text-slate-700 transition-colors" :class="focused ? 'text-[#1E40AF]' : ''">Date de naissance <span class="text-red-500">*</span></label>
                            <div class="relative flex items-center">
                                <i class="ph-bold ph-calendar-blank absolute left-4 text-[18px] transition-colors z-10" :class="focused ? 'text-[#1E40AF]' : 'text-slate-400'"></i>
                                <input @focus="focused = true" @blur="focused = false" type="date" id="dob" name="dob" value="{{ old('dob', $student->dob ?? '') }}" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-12 pr-4 py-3.5 outline-none focus:border-[#1E40AF] focus:ring-4 focus:ring-[#1E40AF]/10 transition-all shadow-sm appearance-none cursor-pointer">
                            </div>
                        </div>
                        
                        <!-- Genre -->
                        <div class="space-y-2" x-data="{ focused: false }">
                            <label for="gender" class="block text-[13.5px] font-bold text-slate-700 transition-colors" :class="focused ? 'text-[#1E40AF]' : ''">Genre <span class="text-red-500">*</span></label>
                            <div class="relative flex items-center">
                                <i class="ph-bold ph-gender-intersex absolute left-4 text-[18px] transition-colors z-10" :class="focused ? 'text-[#1E40AF]' : 'text-slate-400'"></i>
                                <select @focus="focused = true" @blur="focused = false" id="gender" name="gender" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-12 pr-10 py-3.5 appearance-none outline-none focus:border-[#1E40AF] focus:ring-4 focus:ring-[#1E40AF]/10 transition-all shadow-sm cursor-pointer">
                                    <option value="" disabled {{ !isset($student) && !old('gender') ? 'selected' : '' }}>Sélectionnez le genre</option>
                                    <option value="male" {{ old('gender', $student->gender ?? '') == 'male' ? 'selected' : '' }}>Masculin</option>
                                    <option value="female" {{ old('gender', $student->gender ?? '') == 'female' ? 'selected' : '' }}>Féminin</option>
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-4 text-slate-400 pointer-events-none transition-transform" :class="focused ? 'rotate-180 text-[#1E40AF]' : ''"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Photo Upload -->
                    <div class="mt-10 pt-8 border-t border-slate-100">
                        <label class="block text-[13.5px] font-bold text-slate-700 mb-3 flex items-center justify-between">
                            <span>Photo de profil</span>
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-100 px-2 py-1 rounded-md">Optionnel</span>
                        </label>
                        <div x-data="{ fileName: '{{ isset($student) && $student->photo_path ? basename($student->photo_path) : '' }}', filePreview: '{{ isset($student) && $student->photo_path ? asset('storage/' . $student->photo_path) : '' }}' }" class="w-full border-2 border-dashed border-slate-300 rounded-3xl p-10 flex flex-col items-center justify-center hover:bg-[#F8FAFC] hover:border-[#1E40AF] hover:shadow-[0_0_20px_rgba(30,64,175,0.05)] transition-all duration-300 cursor-pointer group bg-slate-50/50 relative overflow-hidden">
                            
                            <!-- Hidden input file -->
                            <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/jpg" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20"
                                @change="
                                    fileName = $event.target.files[0] ? $event.target.files[0].name : '';
                                    if ($event.target.files[0]) {
                                        let reader = new FileReader();
                                        reader.onload = (e) => { filePreview = e.target.result; };
                                        reader.readAsDataURL($event.target.files[0]);
                                    } else {
                                        filePreview = '';
                                    }
                                ">

                            <!-- Animated background circle -->
                            <div class="absolute w-32 h-32 bg-[#1E40AF]/5 rounded-full scale-0 group-hover:scale-150 transition-transform duration-700 ease-out pointer-events-none"></div>

                            <template x-if="!filePreview">
                                <div class="w-16 h-16 rounded-2xl bg-white border border-slate-200 text-[#1E40AF] flex items-center justify-center mb-5 group-hover:-translate-y-2 group-hover:shadow-lg transition-all duration-300 relative z-10">
                                    <i class="ph-bold ph-camera text-[32px]"></i>
                                </div>
                            </template>
                            
                            <template x-if="filePreview">
                                <div class="w-20 h-20 rounded-full border-4 border-white shadow-lg mb-4 overflow-hidden relative z-10">
                                    <img :src="filePreview" class="w-full h-full object-cover">
                                </div>
                            </template>

                            <p x-show="!fileName" class="text-[15px] font-medium text-slate-600 mb-2 relative z-10 text-center">
                                Glissez-déposez la photo ici ou <span class="text-[#1E40AF] font-bold underline decoration-2 underline-offset-4 decoration-indigo-200 hover:decoration-indigo-500 transition-colors">parcourez vos fichiers</span>
                            </p>
                            <p x-show="fileName" x-text="fileName" class="text-[15px] font-bold text-[#1E40AF] mb-2 relative z-10 text-center truncate max-w-[80%]"></p>
                            
                            <p class="text-[12px] font-medium text-slate-400 relative z-10">Formats supportés: JPEG, PNG (Max 5MB)</p>
                        </div>
                    </div>

                    <!-- Séparateur visuel -->
                    <div class="my-10 border-t border-slate-100 relative">
                        <div class="absolute left-1/2 -translate-x-1/2 -top-3 bg-white px-4 text-[12px] font-bold text-slate-400 uppercase tracking-wider">
                            Informations Académiques
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Classe -->
                        <div class="space-y-2" x-data="{ focused: false }">
                            <label for="academic_class_id" class="block text-[13.5px] font-bold text-slate-700 transition-colors" :class="focused ? 'text-[#4F46E5]' : ''">Classe <span class="text-red-500">*</span></label>
                            <div class="relative flex items-center">
                                <i class="ph-bold ph-chalkboard absolute left-4 text-[18px] transition-colors z-10" :class="focused ? 'text-[#4F46E5]' : 'text-slate-400'"></i>
                                <select @focus="focused = true" @blur="focused = false" id="academic_class_id" name="academic_class_id" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-12 pr-10 py-3.5 appearance-none outline-none focus:border-[#4F46E5] focus:ring-4 focus:ring-[#4F46E5]/10 transition-all shadow-sm cursor-pointer">
                                    <option value="" disabled {{ !isset($student) && !old('academic_class_id') ? 'selected' : '' }}>Sélectionnez la classe</option>
                                    @foreach($classes as $class)
                                        <option value="{{ $class->id }}" {{ old('academic_class_id', $student->academic_class_id ?? '') == $class->id ? 'selected' : '' }}>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-4 text-slate-400 pointer-events-none transition-transform" :class="focused ? 'rotate-180 text-[#4F46E5]' : ''"></i>
                            </div>
                        </div>
                        
                        <!-- Année académique -->
                        <div class="space-y-2" x-data="{ focused: false }">
                            <label for="academic_year" class="block text-[13.5px] font-bold text-slate-700 transition-colors" :class="focused ? 'text-[#4F46E5]' : ''">Année Académique <span class="text-red-500">*</span></label>
                            <div class="relative flex items-center">
                                <i class="ph-bold ph-calendar-check absolute left-4 text-[18px] transition-colors z-10" :class="focused ? 'text-[#4F46E5]' : 'text-slate-400'"></i>
                                <select @focus="focused = true" @blur="focused = false" id="academic_year" name="academic_year" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-12 pr-10 py-3.5 appearance-none outline-none focus:border-[#4F46E5] focus:ring-4 focus:ring-[#4F46E5]/10 transition-all shadow-sm cursor-pointer">
                                    <option value="2024-2025" {{ old('academic_year', $student->academic_year ?? '') == '2024-2025' ? 'selected' : '' }}>2024 - 2025</option>
                                    <option value="2023-2024" {{ old('academic_year', $student->academic_year ?? '') == '2023-2024' ? 'selected' : '' }}>2023 - 2024</option>
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-4 text-slate-400 pointer-events-none transition-transform" :class="focused ? 'rotate-180 text-[#4F46E5]' : ''"></i>
                            </div>
                        </div>
                        
                        <!-- Matricule -->
                        <div class="md:col-span-2 space-y-2 mt-2">
                            <label class="block text-[13.5px] font-bold text-slate-700">Numéro de matricule</label>
                            
                            <div class="relative flex items-center opacity-75 bg-slate-50 rounded-xl border border-slate-200 p-1">
                                <i class="ph-bold ph-hash absolute left-4 text-[18px] text-slate-400"></i>
                                <input type="text" value="{{ $student->roll_number ?? 'Généré automatiquement à la validation' }}" disabled class="w-full bg-transparent text-slate-600 text-[14.5px] font-bold pl-11 pr-4 py-2.5 outline-none cursor-not-allowed">
                                <div class="absolute right-3 text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2 py-1.5 rounded uppercase tracking-wider">Automatique</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions Step 1 -->
            <div class="flex items-center justify-end gap-4 pt-4">
                <button type="button" @click="if($refs.studentForm.reportValidity()) step = 2" class="px-8 py-3.5 bg-gradient-to-r from-[#0F172A] to-slate-800 hover:from-slate-800 hover:to-slate-700 text-white font-bold text-[14.5px] rounded-xl transition-all shadow-[0_4px_14px_0_rgba(15,23,42,0.39)] hover:shadow-[0_6px_20px_rgba(15,23,42,0.23)] hover:-translate-y-0.5 flex items-center gap-2 group">
                    Continuer vers Fiche Médicale
                    <i class="ph-bold ph-arrow-right group-hover:translate-x-1 transition-transform"></i>
                </button>
            </div>
        </div>

        <!-- STEP 2: Medical Details -->
        <div x-show="step === 2" style="display: none;" x-transition:enter="transition ease-out duration-500 delay-100" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-300 absolute top-0 w-full" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-8" class="space-y-6">

            <!-- Tuteurs -->
            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative group"
                x-data="{
                    open: false,
                    saving: false,
                    error: null,
                    form: { name: '', relation: '', phone: '', email: '' },
                    async submit() {
                        this.saving = true;
                        this.error = null;
                        try {
                            const res = await fetch('{{ route('school.academic.parents.quick-create') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                                },
                                body: JSON.stringify(this.form),
                            });
                            const data = await res.json();
                            if (!res.ok) {
                                this.error = data.message || 'Une erreur est survenue.';
                                return;
                            }
                            const labels = { pere: 'Père', mere: 'Mère', tuteur: 'Tuteur légal' };
                            const list = document.getElementById('guardian-list');
                            const label = document.createElement('label');
                            label.className = 'flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-100 hover:bg-slate-50 cursor-pointer';
                            label.innerHTML = `<input type=\'checkbox\' name=\'guardian_ids[]\' value=\'${data.guardian.id}\' checked class=\'w-4 h-4 rounded border-slate-300 text-[#1E40AF] focus:ring-[#1E40AF]/20\'>
                                <span class=\'text-[13px] font-semibold text-slate-700\'>${data.guardian.name} <span class=\'text-slate-400 font-normal\'>(${labels[data.guardian.relation] || data.guardian.relation})</span></span>`;
                            list.appendChild(label);
                            document.getElementById('no-guardian-note')?.remove();
                            this.form = { name: '', relation: '', phone: '', email: '' };
                            this.open = false;
                        } catch (e) {
                            this.error = 'Impossible de contacter le serveur.';
                        } finally {
                            this.saving = false;
                        }
                    }
                }">
                <div class="p-8 sm:p-10 relative z-10">
                    <div class="flex items-center justify-between gap-4 mb-8">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner border border-blue-100/50">
                                <i class="ph-fill ph-users-three text-[28px]"></i>
                            </div>
                            <div>
                                <h2 class="text-[22px] font-extrabold text-slate-800 tracking-tight">Parents / Tuteurs</h2>
                                <p class="text-[13.5px] text-slate-500 font-medium mt-0.5">Associez un ou plusieurs tuteurs à cet étudiant (Optionnel).</p>
                            </div>
                        </div>
                        <button type="button" @click="open = true" class="shrink-0 flex items-center gap-1.5 px-4 py-2.5 bg-[#1E40AF] hover:bg-[#1E40AF]/90 text-white font-bold text-[13px] rounded-xl transition-all shadow-sm">
                            <i class="ph-bold ph-plus"></i> Nouveau tuteur
                        </button>
                    </div>

                    @php $linkedGuardianIds = old('guardian_ids', isset($student) ? $student->guardians->pluck('id')->all() : []); @endphp
                    <div id="guardian-list" class="grid grid-cols-1 md:grid-cols-2 gap-2 max-h-64 overflow-y-auto">
                        @foreach($guardians as $g)
                        <label class="flex items-center gap-2 px-3 py-2 rounded-lg border border-slate-100 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" name="guardian_ids[]" value="{{ $g->id }}" {{ in_array($g->id, $linkedGuardianIds) ? 'checked' : '' }} class="w-4 h-4 rounded border-slate-300 text-[#1E40AF] focus:ring-[#1E40AF]/20">
                            <span class="text-[13px] font-semibold text-slate-700">{{ $g->name }} <span class="text-slate-400 font-normal">({{ ['pere' => 'Père', 'mere' => 'Mère', 'tuteur' => 'Tuteur légal'][$g->relation] ?? $g->relation }})</span></span>
                        </label>
                        @endforeach
                    </div>
                    @if($guardians->isEmpty())
                        <p id="no-guardian-note" class="text-[13px] text-slate-500">Aucun tuteur enregistré pour le moment. Utilisez le bouton "Nouveau tuteur" ci-dessus pour en créer un.</p>
                    @endif
                </div>

                <!-- Quick-create modal -->
                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;">
                    <div class="absolute inset-0 bg-slate-900/40" @click="open = false"></div>
                    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.stop>
                        <h3 class="text-[17px] font-extrabold text-slate-800 mb-1">Nouveau tuteur</h3>
                        <p class="text-[12.5px] text-slate-500 mb-5">Créé et ajouté à la sélection sans quitter cette page.</p>

                        <div x-show="error" x-text="error" class="p-3 mb-4 text-[13px] text-red-800 rounded-lg bg-red-50 border border-red-100"></div>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Nom complet</label>
                                <input type="text" x-model="form.name" placeholder="Ex: Jean Dupont" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13.5px] rounded-lg px-3 py-2.5 outline-none focus:border-[#1E40AF]">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Relation</label>
                                <select x-model="form.relation" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13.5px] rounded-lg px-3 py-2.5 outline-none focus:border-[#1E40AF]">
                                    <option value="">Sélectionner...</option>
                                    <option value="pere">Père</option>
                                    <option value="mere">Mère</option>
                                    <option value="tuteur">Tuteur légal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Téléphone</label>
                                <input type="text" x-model="form.phone" placeholder="Ex: 0102030405" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13.5px] rounded-lg px-3 py-2.5 outline-none focus:border-[#1E40AF]">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Email (optionnel)</label>
                                <input type="email" x-model="form.email" placeholder="jean.dupont@exemple.com" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[13.5px] rounded-lg px-3 py-2.5 outline-none focus:border-[#1E40AF]">
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-6">
                            <button type="button" @click="open = false" class="flex-1 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-[13px] rounded-xl transition">Annuler</button>
                            <button type="button" @click="submit()" :disabled="saving || !form.name || !form.relation || !form.phone" :class="saving || !form.name || !form.relation || !form.phone ? 'opacity-50 cursor-not-allowed' : ''" class="flex-1 px-4 py-2.5 bg-[#1E40AF] hover:bg-[#1E40AF]/90 text-white font-bold text-[13px] rounded-xl transition">
                                <span x-show="!saving">Créer</span>
                                <span x-show="saving">Création...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden relative group">
                <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-gradient-to-br from-red-50/50 via-rose-50/20 to-transparent rounded-bl-[100px] -mr-40 -mt-40 pointer-events-none transition-transform duration-700 group-hover:scale-110"></div>
                
                <div class="p-8 sm:p-10 relative z-10">
                    <div class="flex items-center gap-4 mb-10">
                        <div class="w-14 h-14 rounded-2xl bg-red-50 flex items-center justify-center text-red-600 shadow-inner border border-red-100/50">
                            <i class="ph-fill ph-first-aid text-[28px]"></i>
                        </div>
                        <div>
                            <h2 class="text-[22px] font-extrabold text-slate-800 tracking-tight">Fiche Médicale</h2>
                            <p class="text-[13.5px] text-slate-500 font-medium mt-0.5">Informations de santé de l'étudiant (Optionnel).</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-8">
                        <!-- Blood Group -->
                        <div class="space-y-2 md:w-1/2" x-data="{ focused: false }">
                            <label for="blood_group" class="block text-[13.5px] font-bold text-slate-700 transition-colors" :class="focused ? 'text-red-500' : ''">Groupe Sanguin</label>
                            <div class="relative flex items-center">
                                <i class="ph-bold ph-drop absolute left-4 text-[18px] transition-colors z-10" :class="focused ? 'text-red-500' : 'text-slate-400'"></i>
                                <select @focus="focused = true" @blur="focused = false" id="blood_group" name="blood_group" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-12 pr-10 py-3.5 appearance-none outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition-all shadow-sm cursor-pointer">
                                    <option value="" {{ !isset($student) && !old('blood_group') ? 'selected' : '' }}>Inconnu</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                                        <option value="{{ $bg }}" {{ old('blood_group', $student->blood_group ?? '') == $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                    @endforeach
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-4 text-slate-400 pointer-events-none transition-transform" :class="focused ? 'rotate-180 text-red-500' : ''"></i>
                            </div>
                        </div>

                        <!-- Allergies -->
                        <div class="space-y-2" x-data="{ focused: false }">
                            <label for="allergies" class="block text-[13.5px] font-bold text-slate-700 transition-colors" :class="focused ? 'text-red-500' : ''">Allergies connues</label>
                            <div class="relative flex items-start">
                                <i class="ph-bold ph-plant absolute left-4 top-4 text-[18px] transition-colors" :class="focused ? 'text-red-500' : 'text-slate-400'"></i>
                                <textarea @focus="focused = true" @blur="focused = false" id="allergies" name="allergies" rows="3" placeholder="Ex: Arachides, Pénicilline..." class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-12 pr-4 py-3.5 outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition-all shadow-sm placeholder:text-slate-400 placeholder:font-normal">{{ old('allergies', $student->allergies ?? '') }}</textarea>
                            </div>
                        </div>
                        
                        <!-- Medical Conditions -->
                        <div class="space-y-2" x-data="{ focused: false }">
                            <label for="medical_conditions" class="block text-[13.5px] font-bold text-slate-700 transition-colors" :class="focused ? 'text-red-500' : ''">Conditions Médicales Particulières</label>
                            <div class="relative flex items-start">
                                <i class="ph-bold ph-heartbeat absolute left-4 top-4 text-[18px] transition-colors" :class="focused ? 'text-red-500' : 'text-slate-400'"></i>
                                <textarea @focus="focused = true" @blur="focused = false" id="medical_conditions" name="medical_conditions" rows="3" placeholder="Ex: Asthme, Diabète..." class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-12 pr-4 py-3.5 outline-none focus:border-red-500 focus:ring-4 focus:ring-red-500/10 transition-all shadow-sm placeholder:text-slate-400 placeholder:font-normal">{{ old('medical_conditions', $student->medical_conditions ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions Step 2 -->
            <div class="flex items-center justify-between pt-4">
                <button type="button" @click="step = 1" class="px-6 py-3.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-bold text-[14.5px] rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2 group">
                    <i class="ph-bold ph-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                    Retour
                </button>
                <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-[#0F172A] to-slate-800 hover:from-slate-800 hover:to-slate-700 text-white font-bold text-[14.5px] rounded-xl transition-all shadow-[0_4px_14px_0_rgba(15,23,42,0.39)] hover:shadow-[0_6px_20px_rgba(15,23,42,0.23)] hover:-translate-y-0.5 flex items-center gap-2 group">
                    {{ isset($student) ? 'Enregistrer les modifications' : 'Finaliser l\'inscription' }}
                    <i class="ph-bold ph-check group-hover:translate-x-1 transition-transform"></i>
                </button>
            </div>
        </div>

    </form>
</div>
@endsection
