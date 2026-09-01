@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="max-w-[900px] w-full mx-auto space-y-8 pb-20" x-data="{
    isEditing: {{ isset($staffMember) ? 'true' : 'false' }},
    firstName: '{{ addslashes(old('first_name', $staffMember->first_name ?? '')) }}',
    lastName: '{{ addslashes(old('last_name', $staffMember->last_name ?? '')) }}',
    loginId: '{{ addslashes(old('login_id', $staffMember->portalUser?->login_id ?? '')) }}',
    password: '{{ isset($staffMember) && $staffMember->portalUser ? '********' : '' }}',
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
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
            <h1 class="text-[32px] font-extrabold text-[#031C5B] tracking-tight">
                {{ isset($staffMember) ? 'Modifier le Membre du Personnel' : 'Ajouter un Membre du Personnel' }}
            </h1>
            <p class="text-[15px] text-slate-500 mt-1 max-w-xl">Complétez les informations ci-dessous pour intégrer un nouveau membre du personnel administratif ou technique.</p>
        </div>
        <a href="{{ route('school.academic.personnel') }}" class="px-5 py-2.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-bold text-[13.5px] rounded-xl hover:bg-slate-50 transition-all shadow-sm flex items-center gap-2">
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

    <form action="{{ isset($staffMember) ? route('school.academic.personnel.update', $staffMember->id) : route('school.academic.personnel.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if(isset($staffMember))
            @method('PUT')
        @endif

        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100/60 overflow-hidden">
            <div class="p-8 sm:p-10">
                <div class="flex items-center gap-4 mb-10">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 shadow-inner border border-blue-100/50">
                        <i class="ph-fill ph-identification-badge text-[28px]"></i>
                    </div>
                    <div>
                        <h2 class="text-[22px] font-extrabold text-slate-800 tracking-tight">Informations du Personnel</h2>
                        <p class="text-[13.5px] text-slate-500 font-medium mt-0.5">Identité et poste du membre du personnel.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Prénom -->
                    <div class="space-y-2">
                        <label for="first_name" class="block text-[13.5px] font-bold text-slate-700">Prénom <span class="text-red-500">*</span></label>
                        <input type="text" id="first_name" name="first_name" x-model="firstName" placeholder="Ex: Awa" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <!-- Nom -->
                    <div class="space-y-2">
                        <label for="last_name" class="block text-[13.5px] font-bold text-slate-700">Nom de famille <span class="text-red-500">*</span></label>
                        <input type="text" id="last_name" name="last_name" x-model="lastName" placeholder="Ex: Diop" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <!-- Matricule -->
                    <div class="space-y-2">
                        <label for="employee_id" class="block text-[13.5px] font-bold text-slate-700">Matricule <span class="text-red-500">*</span></label>
                        <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id', $staffMember->employee_id ?? ('PE-' . date('Y') . '-' . rand(1000, 9999))) }}" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <!-- Statut -->
                    <div class="space-y-2">
                        <label for="status" class="block text-[13.5px] font-bold text-slate-700">Statut <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                            @php $currentStatus = old('status', $staffMember->status ?? 'active'); @endphp
                            <option value="active" {{ $currentStatus == 'active' ? 'selected' : '' }}>Actif</option>
                            <option value="on_leave" {{ $currentStatus == 'on_leave' ? 'selected' : '' }}>En Congé</option>
                            <option value="inactive" {{ $currentStatus == 'inactive' ? 'selected' : '' }}>Inactif</option>
                        </select>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <label for="email" class="block text-[13.5px] font-bold text-slate-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ old('email', $staffMember->email ?? '') }}" placeholder="Ex: awa.diop@ecole.sn" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <!-- Téléphone -->
                    <div class="space-y-2">
                        <label for="phone_number" class="block text-[13.5px] font-bold text-slate-700">Téléphone</label>
                        @php [$staffPhoneCode, $staffPhoneNumber] = \App\Modules\SuperAdmin\Domain\Models\Country::splitPhone($staffMember->phone ?? null); @endphp
                        @include('SchoolDashboard::components.phone-input', [
                            'selectedCode' => $staffPhoneCode,
                            'selectedNumber' => $staffPhoneNumber,
                            'selectClass' => 'w-[110px] bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[13px] font-medium rounded-xl px-2 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm cursor-pointer',
                            'inputClass' => 'flex-1 bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm',
                        ])
                    </div>

                    <!-- Poste -->
                    <div class="space-y-2">
                        <label for="role" class="block text-[13.5px] font-bold text-slate-700">Poste</label>
                        <input type="text" id="role" name="role" value="{{ old('role', $staffMember->role ?? '') }}" placeholder="Ex: Secrétaire, Comptable, Agent de sécurité" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <!-- Département -->
                    <div class="space-y-2">
                        <label for="department" class="block text-[13.5px] font-bold text-slate-700">Département</label>
                        <input type="text" id="department" name="department" value="{{ old('department', $staffMember->department ?? '') }}" placeholder="Ex: Administration, Comptabilité" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <!-- Type de Contrat -->
                    <div class="space-y-2">
                        <label for="contract_type" class="block text-[13.5px] font-bold text-slate-700">Type de Contrat</label>
                        <select id="contract_type" name="contract_type" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                            @php $currentContractType = old('contract_type', $staffMember->contract_type ?? 'cdi'); @endphp
                            @foreach(\App\Modules\Academic\Domain\Models\Staff::CONTRACT_TYPES as $value => $label)
                                <option value="{{ $value }}" {{ $currentContractType == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fin de Contrat -->
                    <div class="space-y-2">
                        <label for="contract_end_date" class="block text-[13.5px] font-bold text-slate-700">Fin de Contrat <span class="text-slate-400 font-medium">(si CDD/Prestataire)</span></label>
                        <input type="date" id="contract_end_date" name="contract_end_date" value="{{ old('contract_end_date', isset($staffMember) && $staffMember->contract_end_date ? $staffMember->contract_end_date->format('Y-m-d') : '') }}" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <!-- Date d'embauche -->
                    <div class="space-y-2">
                        <label for="hire_date" class="block text-[13.5px] font-bold text-slate-700">Date d'embauche</label>
                        <input type="date" id="hire_date" name="hire_date" value="{{ old('hire_date', $staffMember->hire_date ?? '') }}" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>

                    <!-- Salaire -->
                    <div class="space-y-2">
                        <label for="salary" class="block text-[13.5px] font-bold text-slate-700">Salaire Mensuel</label>
                        <div class="relative">
                            <input type="number" step="0.01" id="salary" name="salary" value="{{ old('salary', isset($staffMember->salary) ? (float) $staffMember->salary : '') }}" placeholder="Ex: 250000" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl pl-4 pr-16 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-[12px]">FCFA</span>
                        </div>
                    </div>

                    <!-- Photo -->
                    <div class="space-y-2 md:col-span-2">
                        <label for="photo" class="block text-[13.5px] font-bold text-slate-700">Photo</label>
                        <input type="file" id="photo" name="photo" accept="image/*" class="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white border border-slate-200 text-slate-900 text-[14.5px] font-medium rounded-xl px-4 py-3.5 outline-none focus:border-[#031C5B] focus:ring-4 focus:ring-[#031C5B]/10 transition-all shadow-sm">
                    </div>
                </div>
            </div>
        </div>

        <!-- Paramètres du Compte -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm mt-8">
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

                    <!-- Rôle d'Accès (Permissions) -->
                    <div class="space-y-1.5 md:col-span-2">
                        <label for="role_id" class="block text-[13px] font-bold text-slate-700">Rôle d'Accès (Permissions)</label>
                        <div class="relative">
                            @php $currentRoleId = old('role_id', isset($staffMember) ? $staffMember->portalUser?->role_id : null); @endphp
                            <select id="role_id" name="role_id" class="w-full bg-white border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#1E40AF] focus:ring-2 focus:ring-[#1E40AF]/10 transition-all appearance-none cursor-pointer">
                                <option value="">Aucun accès au portail</option>
                                @foreach($roles as $roleOption)
                                    <option value="{{ $roleOption->id }}" {{ (string) $currentRoleId === (string) $roleOption->id ? 'selected' : '' }}>{{ $roleOption->name }}</option>
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

            <div class="px-8 sm:px-10 py-6 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <a href="{{ route('school.academic.personnel') }}" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 font-bold text-[14px] rounded-xl hover:bg-slate-50 transition-all">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-3 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition-all shadow-sm flex items-center gap-2">
                    <i class="ph-bold ph-check"></i>
                    {{ isset($staffMember) ? 'Enregistrer les modifications' : 'Ajouter le Membre du Personnel' }}
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
