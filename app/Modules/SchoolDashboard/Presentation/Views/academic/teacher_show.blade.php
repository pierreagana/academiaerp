@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="w-full mx-auto pb-20">
    
    <!-- Header Section -->
    <div class="mb-8 flex items-center justify-between relative z-10">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('school.academic.teachers') }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
                    <i class="ph-bold ph-arrow-left"></i>
                </a>
                <h1 class="text-[32px] font-extrabold text-[#031C5B] tracking-tight">Profil Enseignant</h1>
            </div>
            <p class="text-[15px] text-slate-500 mt-1 ml-11 max-w-xl">
                Détails complets et informations académiques de cet enseignant.
            </p>
        </div>
        
        <div class="flex items-center gap-3">
            <a href="{{ route('school.academic.teachers.edit', $teacher->id) }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#1E40AF] hover:bg-[#1E3A8A] text-white text-[14px] font-bold rounded-lg transition-colors shadow-sm shadow-[#1E40AF]/20">
                <i class="ph-bold ph-pencil-simple text-lg"></i>
                Modifier
            </a>
        </div>
    </div>

    <!-- Main Profile Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left Sidebar: Profile Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-8 text-center relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-[#1E40AF] to-[#031C5B]"></div>
                
                <div class="relative z-10">
                    <div class="w-28 h-28 mx-auto bg-white rounded-full p-1 shadow-md mb-4 mt-6">
                        @if($teacher->photo_path)
                            <img src="{{ asset('storage/' . $teacher->photo_path) }}" alt="{{ $teacher->first_name }}" class="w-full h-full rounded-full object-cover">
                        @else
                            <div class="w-full h-full rounded-full bg-slate-100 flex items-center justify-center text-[#031C5B] text-3xl font-extrabold">
                                {{ substr($teacher->first_name, 0, 1) }}{{ substr($teacher->last_name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    
                    <h2 class="text-2xl font-extrabold text-[#0F172A]">{{ $teacher->first_name }} {{ $teacher->last_name }}</h2>
                    <p class="text-[#1E40AF] font-bold text-sm mb-3">Enseignant {{ $teacher->employment_type == 'full_time' ? 'Temps plein' : 'Vacataire' }}</p>
                    
                    <div class="flex justify-center mb-6">
                        @if($teacher->status == 'active')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-bold bg-[#A7F3D0] text-[#065F46]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#059669] mr-1.5"></span> Actif
                        </span>
                        @elseif($teacher->status == 'on_leave')
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-bold bg-[#FECDD3] text-[#9F1239]">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#E11D48] mr-1.5"></span> En Congé
                        </span>
                        @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-bold bg-slate-200 text-slate-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500 mr-1.5"></span> Inactif
                        </span>
                        @endif
                    </div>
                    
                    <div class="space-y-3 text-left border-t border-slate-100 pt-5">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-[#1E40AF]">
                                <i class="ph-bold ph-envelope-simple text-lg"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Email</p>
                                <p class="text-[14px] font-semibold text-slate-700">{{ $teacher->email }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-[#1E40AF]">
                                <i class="ph-bold ph-identification-badge text-lg"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Identifiant de connexion</p>
                                <p class="text-[14px] font-semibold text-slate-700">{{ $teacher->login_id ?? '-' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-[#1E40AF]">
                                <i class="ph-bold ph-shield-check text-lg"></i>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rôle système</p>
                                <p class="text-[14px] font-semibold text-slate-700 capitalize">{{ str_replace('_', ' ', $teacher->role) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Right Content: Academic Details -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Academic Information -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center gap-3">
                    <i class="ph-bold ph-graduation-cap text-[#031C5B] text-xl"></i>
                    <h2 class="text-[18px] font-extrabold text-[#031C5B]">Informations Académiques</h2>
                </div>
                
                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Employee ID -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Matricule Employé</p>
                            <p class="text-[15px] font-extrabold text-[#0F172A]">{{ $teacher->employee_id }}</p>
                        </div>
                        
                        <!-- Hire Date -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Date d'embauche</p>
                            <p class="text-[15px] font-extrabold text-[#0F172A]">{{ $teacher->hire_date ? \Carbon\Carbon::parse($teacher->hire_date)->format('d / m / Y') : '-' }}</p>
                        </div>
                        
                        <!-- Salary -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Salaire Mensuel</p>
                            <p class="text-[15px] font-extrabold text-[#0F172A]">
                                @if($teacher->salary)
                                    {{ number_format($teacher->salary, 0, ',', ' ') }} <span class="text-slate-400 text-sm">FCFA</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </p>
                        </div>
                        
                        <!-- Subjects -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 md:col-span-2">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3">Spécialités / Matières Enseignées</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse($teacher->subjects as $subject)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#E0E7FF] text-[#3730A3] text-[13px] font-bold border border-[#C7D2FE]">
                                        <i class="ph-bold ph-book-open mr-1.5 text-lg opacity-70"></i>
                                        {{ $subject->name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 font-medium text-[13px] italic">Aucune matière assignée.</span>
                                @endforelse
                            </div>
                        </div>
                        
                        <!-- Classes -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 md:col-span-2">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3">Classes Assignées</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse($teacher->classes as $class)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#F1F5F9] text-[#475569] text-[13px] font-bold border border-[#E2E8F0]">
                                        <i class="ph-bold ph-users-three mr-1.5 text-lg opacity-70"></i>
                                        {{ $class->name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 font-medium text-[13px] italic">Aucune classe assignée.</span>
                                @endforelse
                            </div>
                        </div>

                        <!-- Classes Principales -->
                        <div class="bg-slate-50 p-4 rounded-xl border border-slate-100 md:col-span-2">
                            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3">Professeur Principal De</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse($teacher->headTeacherClasses as $class)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#ECFDF5] text-[#047857] text-[13px] font-bold border border-[#A7F3D0]">
                                        <i class="ph-bold ph-star text-[#10B981] mr-1.5 text-lg"></i>
                                        {{ $class->name }}
                                    </span>
                                @empty
                                    <span class="text-slate-400 font-medium text-[13px] italic">N'est titulaire d'aucune classe.</span>
                                @endforelse
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection
