@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="w-full mx-auto pb-20" x-data="{ 
    step: 1,
    isEditing: {{ isset($teacher) ? 'true' : 'false' }},
    firstName: '{{ addslashes(old('first_name', $teacher->first_name ?? '')) }}',
    lastName: '{{ addslashes(old('last_name', $teacher->last_name ?? '')) }}',
    loginId: '{{ addslashes(old('login_id', $teacher->login_id ?? '')) }}',
    password: '{{ isset($teacher) && $teacher->password ? '********' : '' }}',
    employeeId: '{{ addslashes(old('employee_id', $teacher->employee_id ?? ('EA-' . date('Y') . '-' . rand(1000, 9999)))) }}',
    init() {
        this.$watch('firstName', () => this.autoUpdateLogin());
        this.$watch('lastName', () => this.autoUpdateLogin());
        if (!this.loginId && !this.isEditing) {
            this.autoUpdateLogin();
        }
    },
    autoUpdateLogin() {
        if (this.isEditing && this.loginId && this.loginId !== 'Généré automatiquement') return;
        this.generateLoginId();
    },
    generateLoginId() {
        let fName = this.firstName || '';
        let lName = this.lastName || '';
        if (!fName && !lName) {
            this.loginId = 'Généré automatiquement';
            return;
        }
        let login = (fName.trim().toLowerCase() + '.' + lName.trim().toLowerCase()).replace(/[^a-z0-9.]/g, '');
        if (login.startsWith('.')) login = login.substring(1);
        if (login.endsWith('.')) login = login.slice(0, -1);
        this.loginId = login || 'Généré automatiquement';
    },
    generateEmployeeId() {
        this.employeeId = 'EA-' + new Date().getFullYear() + '-' + Math.floor(1000 + Math.random() * 9000);
    },
    generatePassword() {
        const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        let pass = '';
        for (let i = 0; i < 10; i++) {
            pass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        this.password = pass;
    }
}">
    
    <!-- Header Section -->
    <div class="mb-8 relative z-10">
        <h1 class="text-[36px] font-extrabold text-[#031C5B] tracking-tight">
            {{ isset($teacher) ? 'Modifier l\'Enseignant' : 'Ajouter un Professeur' }}
        </h1>
        <p class="text-[15px] text-slate-500 mt-1 max-w-xl">
            Complétez les informations ci-dessous pour intégrer un nouvel enseignant au système.<br>
            L'IA générera automatiquement ses identifiants.
        </p>
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

    <form id="teacher-form" x-ref="teacherForm" action="{{ isset($teacher) ? route('school.academic.teachers.update', $teacher->id) : route('school.academic.teachers.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col lg:flex-row gap-8">
        @csrf
        @if(isset($teacher))
            @method('PUT')
        @endif
        
        <!-- Left Sidebar (Stepper & IA Assistant) -->
        <div class="w-full lg:w-[280px] shrink-0 space-y-6">
            <!-- Stepper -->
            <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl overflow-hidden">
                <div class="px-5 py-4 border-b border-[#E2E8F0] bg-[#F1F5F9]">
                    <h3 class="font-extrabold text-[#031C5B] text-[16px]">Progression</h3>
                </div>
                <div class="p-5 relative">
                    <!-- Step 1 -->
                    <div class="flex items-start gap-4 mb-8 relative">
                        <!-- Vertical line connecting steps -->
                        <div class="absolute left-3 top-8 bottom-[-32px] w-[2px] bg-[#1E40AF]"></div>
                        
                        <div class="relative z-10 w-6 h-6 rounded-full bg-[#1E40AF] text-white flex items-center justify-center font-bold text-[11px] ring-4 ring-[#F8FAFC] mt-0.5 shrink-0">
                            1
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-[#1E40AF] leading-tight">Informations<br>Personnelles</div>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="flex items-start gap-4 mb-8 relative">
                        <div class="absolute left-3 top-8 bottom-[-32px] w-[2px] bg-[#E2E8F0]"></div>
                        
                        <div class="relative z-10 w-6 h-6 rounded-full bg-[#E2E8F0] text-slate-500 flex items-center justify-center font-bold text-[11px] ring-4 ring-[#F8FAFC] mt-0.5 shrink-0">
                            2
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-slate-500 leading-tight">Informations<br>Académiques</div>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="flex items-start gap-4 relative">
                        <div class="relative z-10 w-6 h-6 rounded-full bg-[#E2E8F0] text-slate-500 flex items-center justify-center font-bold text-[11px] ring-4 ring-[#F8FAFC] mt-0.5 shrink-0">
                            3
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-slate-500 leading-tight">Paramètres du Compte</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- IA Assistant Widget -->
            <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-xl p-5 shadow-sm">
                <div class="flex items-start gap-3 mb-2">
                    <i class="ph-fill ph-sparkle text-[#9333EA] text-xl"></i>
                    <h3 class="font-extrabold text-slate-800 text-[14px]">Assistant IA</h3>
                </div>
                <p id="teacherHoursSuggestionText" class="text-[13px] text-slate-600 font-medium leading-relaxed pl-8">
                    Sélectionnez une spécialité pour recevoir une suggestion d'allocation d'heures basée sur les données réelles de l'établissement.
                </p>
            </div>
        </div>

        <!-- Right Content Area -->
        <div class="flex-1 space-y-8">
            
            <!-- Section 1: Informations Personnelles -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
                    <i class="ph-bold ph-user text-[#031C5B] text-xl"></i>
                    <h2 class="text-[18px] font-extrabold text-[#031C5B]">Informations Personnelles</h2>
                </div>
                
                <div class="p-8">
                    <!-- Photo Upload Style exact to screenshot -->
                    <div class="flex items-center gap-6 mb-8" x-data="{ fileName: '{{ isset($teacher) && $teacher->photo_path ? basename($teacher->photo_path) : '' }}', filePreview: '{{ isset($teacher) && $teacher->photo_path ? asset('storage/' . $teacher->photo_path) : '' }}' }">
                        <div class="relative">
                            <div class="w-24 h-24 rounded-full border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center text-slate-400 overflow-hidden">
                                <template x-if="filePreview">
                                    <img :src="filePreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!filePreview">
                                    <i class="ph-bold ph-camera-plus text-[28px]"></i>
                                </template>
                            </div>
                            <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/jpg,image/gif" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                @change="
                                    fileName = $event.target.files[0] ? $event.target.files[0].name : '';
                                    if($event.target.files[0]) {
                                        let reader = new FileReader();
                                        reader.onload = (e) => { filePreview = e.target.result; };
                                        reader.readAsDataURL($event.target.files[0]);
                                    } else {
                                        filePreview = '';
                                    }
                                ">
                        </div>
                        <div>
                            <div class="text-[13px] font-bold text-slate-800 mb-0.5">Photo de profil</div>
                            <div class="text-[12px] text-slate-500 font-medium mb-2">JPG, PNG ou GIF. Max 2MB.</div>
                            <button type="button" class="text-[13px] font-bold text-[#1E40AF] hover:underline" @click="document.getElementById('photo').click()">Parcourir les fichiers</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                        <!-- Prénom -->
                        <div class="space-y-1.5">
                            <label for="first_name" class="block text-[13px] font-bold text-slate-700">Prénom <span class="text-red-500">*</span></label>
                            <input type="text" id="first_name" name="first_name" x-model="firstName" placeholder="Ex: Aminata" required class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all placeholder:text-slate-400">
                        </div>
                        
                        <!-- Nom -->
                        <div class="space-y-1.5">
                            <label for="last_name" class="block text-[13px] font-bold text-slate-700">Nom <span class="text-red-500">*</span></label>
                            <input type="text" id="last_name" name="last_name" x-model="lastName" placeholder="Ex: Diallo" required class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all placeholder:text-slate-400">
                        </div>

                        <!-- Email -->
                        <div class="space-y-1.5">
                            <label for="email" class="block text-[13px] font-bold text-slate-700">Adresse Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email', $teacher->email ?? '') }}" placeholder="aminata.diallo@eduafrica.com" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all placeholder:text-slate-400">
                        </div>

                        <!-- Téléphone -->
                        <div class="space-y-1.5">
                            <label for="phone_number" class="block text-[13px] font-bold text-slate-700">Numéro de Téléphone</label>
                            @php [$teacherPhoneCode, $teacherPhoneNumber] = \App\Modules\SuperAdmin\Domain\Models\Country::splitPhone($teacher->phone ?? null); @endphp
                            @include('SchoolDashboard::components.phone-input', [
                                'selectedCode' => $teacherPhoneCode,
                                'selectedNumber' => $teacherPhoneNumber,
                                'selectClass' => 'w-[110px] bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[13px] rounded-lg px-2 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all cursor-pointer',
                                'inputClass' => 'flex-1 bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all placeholder:text-slate-400',
                            ])
                        </div>

                        <!-- Adresse -->
                        <div class="space-y-1.5 col-span-1 md:col-span-2">
                            <label for="address" class="block text-[13px] font-bold text-slate-700">Adresse Postale</label>
                            <textarea id="address" name="address" placeholder="Saisissez l'adresse complète..." rows="2" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all placeholder:text-slate-400 resize-none">{{ old('address', $teacher->address ?? '') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Informations Académiques -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
                    <i class="ph-bold ph-graduation-cap text-[#031C5B] text-xl"></i>
                    <h2 class="text-[18px] font-extrabold text-[#031C5B]">Informations Académiques</h2>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                        <!-- Matricule Employé (Read Only) -->
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-bold text-slate-700">Matricule Employé</label>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">#</span>
                                    <input type="text" name="employee_id" x-model="employeeId" readonly class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] font-semibold rounded-lg pl-9 pr-4 py-2.5 opacity-80 cursor-not-allowed">
                                </div>
                                <button type="button" @click="generateEmployeeId()" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-[13px] font-bold transition-colors whitespace-nowrap" title="Générer à nouveau">
                                    <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                                </button>
                            </div>
                            <p class="text-[10px] font-bold text-slate-400">Généré automatiquement</p>
                        </div>
                        
                        <!-- Date d'embauche -->
                        <div class="space-y-1.5">
                            <label for="hire_date" class="block text-[13px] font-bold text-slate-700">Date d'embauche <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="date" id="hire_date" name="hire_date" value="{{ old('hire_date', $teacher->hire_date ?? '') }}" required class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all appearance-none">
                                <i class="ph-bold ph-calendar-blank absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Salaire -->
                        <div class="space-y-1.5 mt-2">
                            <label for="salary" class="block text-[13px] font-bold text-slate-700">Salaire Mensuel</label>
                            <div class="relative">
                                <input type="number" step="0.01" id="salary" name="salary" value="{{ old('salary', isset($teacher->salary) ? (float) $teacher->salary : '') }}" placeholder="Ex: 500000" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg pl-4 pr-16 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-[12px]">FCFA</span>
                            </div>
                        </div>

                        <!-- Spécialité / Matière (Multiple) -->
                        <div class="space-y-1.5 col-span-1 mt-2">
                            <label class="block text-[13px] font-bold text-slate-700">Spécialité / Matière <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="{ 
                                open: false, 
                                selectedItems: [],
                                updateSelection() {
                                    let checked = Array.from(this.$root.querySelectorAll('input[type=checkbox]:checked'));
                                    this.selectedItems = checked.map(cb => cb.nextElementSibling.textContent.trim());
                                }
                            }" x-init="updateSelection()">
                                <button type="button" @click="open = !open" @click.away="open = false" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-3 py-2 min-h-[44px] outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all flex items-center justify-between">
                                    <div class="flex flex-wrap gap-1.5 items-center">
                                        <span x-show="selectedItems.length === 0" class="text-slate-500">Sélectionner les matières...</span>
                                        <template x-for="(item, index) in selectedItems" :key="index">
                                            <span class="bg-[#3B82F6] text-white text-[12px] font-bold px-2.5 py-1 rounded-md shadow-sm" x-text="item"></span>
                                        </template>
                                    </div>
                                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 shrink-0 ml-2" :class="{ 'rotate-180': open }"></i>
                                </button>
                                
                                <div x-show="open" x-transition.opacity class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto p-2" style="display: none;">
                                    <div class="flex flex-col gap-1">
                                        @foreach($subjects as $subject)
                                            <label class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-50 cursor-pointer group transition-colors">
                                                <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}" @change="updateSelection()"
                                                    class="rounded border-slate-300 text-[#1E40AF] focus:ring-[#1E40AF]"
                                                    {{ (is_array(old('subject_ids')) && in_array($subject->id, old('subject_ids'))) || (isset($teacher) && $teacher->subjects->contains($subject->id)) ? 'checked' : '' }}>
                                                <span class="text-[13px] font-semibold text-slate-700 group-hover:text-[#1E40AF]">{{ $subject->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Classes Assignées (Multiple) -->
                        <div class="space-y-1.5 col-span-1 mt-2">
                            <label class="block text-[13px] font-bold text-slate-700">Classes Assignées <span class="text-red-500">*</span></label>
                            <div class="relative" x-data="{ 
                                open: false, 
                                selectedItems: [],
                                updateSelection() {
                                    let checked = Array.from(this.$root.querySelectorAll('input[type=checkbox]:checked'));
                                    this.selectedItems = checked.map(cb => cb.nextElementSibling.textContent.trim());
                                }
                            }" x-init="updateSelection()">
                                <button type="button" @click="open = !open" @click.away="open = false" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-3 py-2 min-h-[44px] outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all flex items-center justify-between">
                                    <div class="flex flex-wrap gap-1.5 items-center">
                                        <span x-show="selectedItems.length === 0" class="text-slate-500">Sélectionner les classes...</span>
                                        <template x-for="(item, index) in selectedItems" :key="index">
                                            <span class="bg-[#3B82F6] text-white text-[12px] font-bold px-2.5 py-1 rounded-md shadow-sm" x-text="item"></span>
                                        </template>
                                    </div>
                                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 shrink-0 ml-2" :class="{ 'rotate-180': open }"></i>
                                </button>
                                
                                <div x-show="open" x-transition.opacity class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto p-2" style="display: none;">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($classes as $class)
                                            <label class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-50 cursor-pointer group transition-colors">
                                                <input type="checkbox" name="academic_class_ids[]" value="{{ $class->id }}" @change="updateSelection()"
                                                    class="rounded border-slate-300 text-[#1E40AF] focus:ring-[#1E40AF]"
                                                    {{ (is_array(old('academic_class_ids')) && in_array($class->id, old('academic_class_ids'))) || (isset($teacher) && $teacher->classes->contains($class->id)) ? 'checked' : '' }}>
                                                <span class="text-[13px] font-semibold text-slate-700 group-hover:text-[#1E40AF]">{{ $class->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professeur Principal De (Optionnel) -->
                        <div class="space-y-1.5 col-span-1 mt-2">
                            <label class="block text-[13px] font-bold text-slate-700">Professeur Principal De <span class="text-slate-400 font-normal">(Optionnel)</span></label>
                            <div class="relative" x-data="{ 
                                open: false, 
                                selectedItems: [],
                                updateSelection() {
                                    let checked = Array.from(this.$root.querySelectorAll('input[type=checkbox]:checked'));
                                    this.selectedItems = checked.map(cb => cb.nextElementSibling.textContent.trim());
                                }
                            }" x-init="updateSelection()">
                                <button type="button" @click="open = !open" @click.away="open = false" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-3 py-2 min-h-[44px] outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all flex items-center justify-between">
                                    <div class="flex flex-wrap gap-1.5 items-center">
                                        <span x-show="selectedItems.length === 0" class="text-slate-500">Aucune classe principale...</span>
                                        <template x-for="(item, index) in selectedItems" :key="index">
                                            <span class="bg-[#10B981] text-white text-[12px] font-bold px-2.5 py-1 rounded-md shadow-sm" x-text="item"></span>
                                        </template>
                                    </div>
                                    <i class="ph-bold ph-caret-down text-slate-400 transition-transform duration-200 shrink-0 ml-2" :class="{ 'rotate-180': open }"></i>
                                </button>
                                
                                <div x-show="open" x-transition.opacity class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto p-2" style="display: none;">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                        @foreach($classes as $class)
                                            <label class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-50 cursor-pointer group transition-colors">
                                                <input type="checkbox" name="head_teacher_class_ids[]" value="{{ $class->id }}" @change="updateSelection()"
                                                    class="rounded border-slate-300 text-[#10B981] focus:ring-[#10B981]"
                                                    {{ (is_array(old('head_teacher_class_ids')) && in_array($class->id, old('head_teacher_class_ids'))) || (isset($teacher) && $teacher->headTeacherClasses->contains($class->id)) ? 'checked' : '' }}>
                                                <span class="text-[13px] font-semibold text-slate-700 group-hover:text-[#10B981]">{{ $class->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Type d'emploi -->
                        <div class="space-y-1.5 mt-2">
                            <label for="employment_type" class="block text-[13px] font-bold text-slate-700">Type d'emploi <span class="text-red-500">*</span></label>
                            <div class="relative">
                                @php $currentEmploymentType = old('employment_type', $teacher->employment_type ?? 'full_time'); @endphp
                                <select id="employment_type" name="employment_type" required class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all appearance-none cursor-pointer">
                                    <option value="full_time" {{ $currentEmploymentType == 'full_time' ? 'selected' : '' }}>Temps plein</option>
                                    <option value="part_time" {{ $currentEmploymentType == 'part_time' ? 'selected' : '' }}>Vacataire</option>
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Statut -->
                        <div class="space-y-1.5 mt-2">
                            <label for="status" class="block text-[13px] font-bold text-slate-700">Statut <span class="text-red-500">*</span></label>
                            <div class="relative">
                                @php $currentStatus = old('status', $teacher->status ?? 'active'); @endphp
                                <select id="status" name="status" required class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all appearance-none cursor-pointer">
                                    <option value="active" {{ $currentStatus == 'active' ? 'selected' : '' }}>Actif</option>
                                    <option value="on_leave" {{ $currentStatus == 'on_leave' ? 'selected' : '' }}>En Congé</option>
                                    <option value="inactive" {{ $currentStatus == 'inactive' ? 'selected' : '' }}>Inactif</option>
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Type de Contrat -->
                        <div class="space-y-1.5 mt-2">
                            <label for="contract_type" class="block text-[13px] font-bold text-slate-700">Type de Contrat</label>
                            <div class="relative">
                                @php $currentContractType = old('contract_type', $teacher->contract_type ?? 'cdi'); @endphp
                                <select id="contract_type" name="contract_type" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all appearance-none cursor-pointer">
                                    @foreach(\App\Modules\Academic\Domain\Models\Teacher::CONTRACT_TYPES as $value => $label)
                                        <option value="{{ $value }}" {{ $currentContractType == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Fin de Contrat -->
                        <div class="space-y-1.5 mt-2">
                            <label for="contract_end_date" class="block text-[13px] font-bold text-slate-700">Fin de Contrat <span class="text-slate-400 font-medium">(si CDD/Prestataire)</span></label>
                            <input type="date" id="contract_end_date" name="contract_end_date" value="{{ old('contract_end_date', isset($teacher) && $teacher->contract_end_date ? $teacher->contract_end_date->format('Y-m-d') : '') }}" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Paramètres du Compte -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="ph-bold ph-shield-check text-[#031C5B] text-xl"></i>
                        <h2 class="text-[18px] font-extrabold text-[#031C5B]">Paramètres du Compte</h2>
                    </div>
                    <span class="px-2 py-1 bg-slate-100 text-slate-600 text-[10px] font-bold tracking-wider rounded uppercase">SÉCURISÉ</span>
                </div>
                
                <div class="p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-6">
                        <!-- Identifiant Connexion -->
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-bold text-slate-700">Identifiant Connexion</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="login_id" x-model="loginId" readonly class="w-full bg-[#F1F5F9] border border-slate-200 text-slate-500 text-[14px] rounded-lg px-4 py-2.5 cursor-not-allowed font-medium">
                                <button type="button" @click="generateLoginId()" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-[13px] font-bold transition-colors whitespace-nowrap" title="Générer à nouveau">
                                    <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Mot de Passe -->
                        <div class="space-y-1.5">
                            <label class="block text-[13px] font-bold text-slate-700">Mot de Passe</label>
                            <div class="flex items-center gap-2">
                                <input type="text" name="password" x-model="password" readonly class="w-full bg-[#F1F5F9] border border-slate-200 text-slate-500 text-[14px] rounded-lg px-4 py-2.5 cursor-not-allowed font-medium" placeholder="Généré automatiquement">
                                <button type="button" @click="generatePassword()" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-[13px] font-bold transition-colors whitespace-nowrap" title="Générer à nouveau">
                                    <i class="ph-bold ph-arrows-clockwise text-lg"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Rôle Système -->
                        <div class="space-y-1.5">
                            <label for="role" class="block text-[13px] font-bold text-slate-700">Rôle Système</label>
                            <div class="relative">
                                <select id="role" name="role" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all appearance-none cursor-pointer">
                                    <option value="Enseignant Titulaire" {{ old('role', $teacher->role ?? '') == 'Enseignant Titulaire' ? 'selected' : '' }}>Enseignant Titulaire</option>
                                    <option value="Vacataire" {{ old('role', $teacher->role ?? '') == 'Vacataire' ? 'selected' : '' }}>Vacataire</option>
                                    <option value="Chef de département" {{ old('role', $teacher->role ?? '') == 'Chef de département' ? 'selected' : '' }}>Chef de département</option>
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                        </div>

                        <!-- Rôle d'Accès (Permissions) -->
                        <div class="space-y-1.5">
                            <label for="role_id" class="block text-[13px] font-bold text-slate-700">Rôle d'Accès (Permissions)</label>
                            <div class="relative">
                                @php $currentRoleId = old('role_id', isset($teacher) ? $teacher->portalUser?->role_id : null); @endphp
                                <select id="role_id" name="role_id" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all appearance-none cursor-pointer">
                                    <option value="">Aucun accès au portail</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ (string) $currentRoleId === (string) $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                <i class="ph-bold ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                            </div>
                            @if($roles->isEmpty())
                                <p class="text-[12px] text-slate-500">Aucun rôle défini. <a href="{{ route('school.roles') }}" class="text-[#031C5B] font-bold hover:underline">Créez-en un</a> pour donner accès au portail.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<!-- Fixed Bottom Action Bar -->
<div class="fixed bottom-0 left-0 lg:left-64 right-0 bg-white border-t border-slate-200 p-4 z-50 flex items-center justify-between md:justify-end gap-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
    <a href="{{ route('school.academic.teachers') }}" class="px-6 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-lg hover:bg-slate-50 transition-all">
        Annuler
    </a>
    <button type="button" onclick="document.getElementById('teacher-form').requestSubmit()" class="px-6 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-lg shadow-sm hover:bg-[#031C5B]/90 transition-all flex items-center gap-2">
        <i class="ph-bold ph-check-circle"></i>
        {{ isset($teacher) ? 'Enregistrer les modifications' : 'Créer le profil' }}
    </button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let debounceTimer = null;
        const textEl = document.getElementById('teacherHoursSuggestionText');

        document.querySelectorAll('input[name="subject_ids[]"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(fetchHoursSuggestion, 500);
            });
        });

        function fetchHoursSuggestion() {
            const ids = Array.from(document.querySelectorAll('input[name="subject_ids[]"]:checked')).map(cb => cb.value);
            if (ids.length === 0) {
                textEl.innerText = "Sélectionnez une spécialité pour recevoir une suggestion d'allocation d'heures basée sur les données réelles de l'établissement.";
                return;
            }

            textEl.innerText = "Analyse des données de l'établissement en cours...";

            fetch('{{ route("school.academic.teachers.ai-suggest-hours") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ subject_ids: ids })
            })
            .then(res => res.json())
            .then(data => {
                textEl.innerText = data.success ? data.suggestion : (data.error || "Suggestion IA indisponible pour le moment.");
            })
            .catch(() => {
                textEl.innerText = "Erreur de communication avec le serveur.";
            });
        }
    });
</script>
@endsection
