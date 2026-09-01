<aside class="w-64 bg-[#F8FAFC] flex flex-col h-full shrink-0">
    <!-- Logo -->
    <div class="p-6 pb-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-[#031C5B] rounded-xl flex items-center justify-center text-white shrink-0 shadow-md">
            <i class="ph-fill ph-graduation-cap text-2xl"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-tight text-slate-900 leading-tight">Academia<br>ERP</h1>
            <p class="text-[10px] font-medium text-slate-500 tracking-wider">Portail École</p>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto px-4 pb-4 space-y-6 mt-4" id="sidebar-nav">

        <!-- Main Dashboard Button -->
        <div>
            <a href="{{ route('school.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 font-semibold text-[14px] rounded-xl transition {{ request()->routeIs('school.dashboard') ? 'bg-[#031C5B] text-white shadow-sm' : 'bg-slate-200/60 text-slate-800 hover:bg-slate-200' }}">
                <i class="ph ph-squares-four text-lg"></i>
                Tableau de Bord
            </a>
            
            @if(auth()->user()->isFounder())
            <a href="{{ route('school.founder.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 mt-2 font-semibold text-[14px] rounded-xl transition {{ request()->routeIs('school.founder.dashboard') ? 'bg-[#031C5B] text-white shadow-sm' : 'bg-slate-200/60 text-slate-800 hover:bg-slate-200' }}">
                <i class="ph ph-crown text-lg"></i>
                Mes Établissements
            </a>
            @endif

            @if(auth()->user()->canAccess('establishment.manage'))
            <a href="{{ route('school.establishment') }}" class="flex items-center gap-3 px-4 py-2.5 mt-2 font-semibold text-[14px] rounded-xl transition {{ request()->routeIs('school.establishment') ? 'bg-[#031C5B] text-white shadow-sm' : 'bg-slate-200/60 text-slate-800 hover:bg-slate-200' }}">
                <i class="ph ph-buildings text-lg"></i>
                Mon Établissement
            </a>
            @endif

            @if(auth()->user()->canAccess('establishment.manage'))
            <a href="{{ route('school.school-track') }}" class="flex items-center gap-3 px-4 py-2.5 mt-2 font-semibold text-[14px] rounded-xl transition {{ request()->routeIs('school.school-track*') ? 'bg-[#031C5B] text-white shadow-sm' : 'bg-slate-200/60 text-slate-800 hover:bg-slate-200' }}">
                <i class="ph ph-compass text-lg"></i>
                Profil School Track
            </a>
            @endif

            @if(auth()->user()->canAccess('academic.exam-results.manage'))
            <a href="{{ route('school.exam-results.index') }}" class="flex items-center gap-3 px-4 py-2.5 mt-2 font-semibold text-[14px] rounded-xl transition {{ request()->routeIs('school.exam-results.*') ? 'bg-[#031C5B] text-white shadow-sm' : 'bg-slate-200/60 text-slate-800 hover:bg-slate-200' }}">
                <i class="ph ph-exam text-lg"></i>
                Résultats aux examens
            </a>
            @endif

            @if(auth()->user()->canAccess('branches.manage'))
            <a href="{{ route('school.branches') }}" class="flex items-center gap-3 px-4 py-2.5 mt-2 font-semibold text-[14px] rounded-xl transition {{ request()->routeIs('school.branches*') ? 'bg-[#031C5B] text-white shadow-sm' : 'bg-slate-200/60 text-slate-800 hover:bg-slate-200' }}">
                <i class="ph ph-git-branch text-lg"></i>
                Succursales
            </a>
            @endif
        </div>

        <!-- ESPACE ENSEIGNANT (self-service, ne pas confondre avec la section Présence ci-dessous) -->
        @if(auth()->user()->teacher)
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-4">Espace Enseignant</h3>
            <div class="space-y-1">
                <a href="{{ route('school.teacher.classes') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.teacher.classes*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-chalkboard-teacher text-[18px]"></i>
                    Mes Classes
                </a>
                <a href="{{ route('school.teacher.attendance-history') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.teacher.attendance-history*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-fingerprint text-[18px]"></i>
                    Ma Présence
                </a>
                @php $myDiplomaCount = \App\Modules\Academic\Domain\Models\Award::where('recipient_type', 'teacher')->where('recipient_id', auth()->user()->teacher->id)->count(); @endphp
                <a href="{{ route('school.teacher.diplomas') }}" class="flex items-center justify-between gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.teacher.diplomas*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <span class="flex items-center gap-3"><i class="ph ph-medal text-[18px]"></i> Mes Diplômes</span>
                    @if($myDiplomaCount > 0)
                        <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700">{{ $myDiplomaCount }}</span>
                    @endif
                </a>
            </div>
        </div>
        @endif

        <!-- GESTION ACADÉMIQUE -->
        @php
            $canSeeAcademicMenu = collect(['academic.semesters.manage', 'academic.classes.manage', 'academic.subjects.manage', 'academic.rooms.manage', 'academic.syllabuses.manage', 'academic.timetable.manage', 'academic.languages.manage', 'academic.bulletins.manage'])->contains(fn($slug) => auth()->user()->canAccess($slug));
            $canSeeStudentsMenu = collect(['academic.students.manage', 'academic.parents.manage'])->contains(fn($slug) => auth()->user()->canAccess($slug));
            $canSeeTeachersMenu = auth()->user()->canAccess('academic.teachers.manage');
        @endphp
        @if($canSeeAcademicMenu || $canSeeStudentsMenu || $canSeeTeachersMenu)
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-4">Gestion Académique</h3>
            <div class="space-y-1">

                <!-- Sous-menu: Académique (Accordion) -->
                @if($canSeeAcademicMenu)
                @php
                    $isAcademicActive = request()->routeIs('school.academic.semesters', 'school.academic.classes', 'school.academic.subjects', 'school.academic.syllabuses', 'school.academic.syllabuses.create', 'school.academic.timetable', 'school.academic.languages', 'school.academic.rooms', 'school.academic.bulletins.*');
                @endphp
                <div x-data="{ open: {{ $isAcademicActive ? 'true' : 'false' }} }" class="px-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isAcademicActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-books text-[18px]"></i>
                            Académique
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isAcademicActive ? '' : 'style="display: none;"' !!}>
                        @if(auth()->user()->canAccess('academic.semesters.manage'))
                        <a href="{{ route('school.academic.semesters') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.semesters') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Semestre</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.classes.manage'))
                        <a href="{{ route('school.academic.classes') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.classes') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Classe</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.subjects.manage'))
                        <a href="{{ route('school.academic.subjects') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.subjects') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Matière</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.rooms.manage'))
                        <a href="{{ route('school.academic.rooms') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.rooms') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Salles</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.syllabuses.manage'))
                        <a href="{{ route('school.academic.syllabuses') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.syllabuses', 'school.academic.syllabuses.create') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Programme de cours</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.timetable.manage'))
                        <a href="{{ route('school.academic.timetable') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.timetable') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Emploi du temps</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.languages.manage'))
                        <a href="{{ route('school.academic.languages') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.languages') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Langue</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.bulletins.manage'))
                        <a href="{{ route('school.academic.bulletins.dashboard') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.bulletins.*') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Bulletins</a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Sous-menu: Étudiants (Accordion) -->
                @if($canSeeStudentsMenu)
                @php
                    $isStudentsActive = request()->routeIs('school.academic.students', 'school.academic.students.*', 'school.academic.parents', 'school.academic.parents.*');
                @endphp
                <div x-data="{ open: {{ $isStudentsActive ? 'true' : 'false' }} }" class="px-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isStudentsActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-student text-[18px]"></i>
                            Étudiants
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isStudentsActive ? '' : 'style="display: none;"' !!}>
                        @if(auth()->user()->canAccess('academic.students.manage'))
                        <a href="{{ route('school.academic.students') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.students', 'school.academic.students.edit', 'school.academic.students.show') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Liste étudiants</a>
                        <a href="{{ route('school.academic.students.create') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.students.create') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Ajouter un étudiant</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.parents.manage'))
                        <a href="{{ route('school.academic.parents') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.parents', 'school.academic.parents.edit') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Liste tuteurs</a>
                        <a href="{{ route('school.academic.parents.create') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.parents.create') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Ajouter un tuteur</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.students.manage'))
                        <a href="{{ route('school.academic.students.transfer') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.students.transfer') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Étudiant transféré</a>
                        <a href="{{ route('school.academic.students.promote') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.students.promote') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Promouvoir les élèves</a>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Sous-menu: Professeur (Accordion) -->
                @if($canSeeTeachersMenu)
                @php
                    $isTeachersActive = request()->routeIs('school.academic.teachers', 'school.academic.teachers.*');
                @endphp
                <div x-data="{ open: {{ $isTeachersActive ? 'true' : 'false' }} }" class="px-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isTeachersActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-chalkboard-teacher text-[18px]"></i>
                            Professeur
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isTeachersActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.academic.teachers') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.teachers') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Liste des Professeurs</a>
                        <a href="{{ route('school.academic.teachers.create') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.teachers.create') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Ajouter un Professeur</a>
                        <a href="#" class="block px-2 py-1.5 text-[12.5px] font-medium text-slate-500 hover:text-slate-800 transition">Section de classe et enseignants</a>
                    </div>
                </div>
                @endif

                <!-- Sous-menu: Cartes & Diplômes (Accordion) -->
                @php
                    $isCardsActive = request()->routeIs('school.academic.cards.*');
                @endphp
                <div x-data="{ open: {{ $isCardsActive ? 'true' : 'false' }} }" class="px-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isCardsActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-identification-card text-[18px]"></i>
                            Cartes & Diplômes
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isCardsActive ? '' : 'style="display: none;"' !!}>
                        @if(auth()->user()->canAccess('academic.cards.manage'))
                        <a href="{{ route('school.academic.cards.show', 'student') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.cards.show') && request()->route('type') === 'student' ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Carte étudiant</a>
                        <a href="{{ route('school.academic.cards.show', 'staff') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.cards.show') && request()->route('type') === 'staff' ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Carte personnel</a>
                        @endif
                        @if(auth()->user()->canAccess('academic.awards.manage'))
                        <a href="{{ route('school.academic.awards.template.edit') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.awards.template.*') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Diplôme</a>
                        @endif
                    </div>
                </div>

                <!-- Sous-menu: Présence (Accordion) -->
                @if(auth()->user()->canAccess('academic.presence.manage'))
                @php
                    $isPresenceActive = request()->routeIs('school.academic.presence.*');
                @endphp
                <div x-data="{ open: {{ $isPresenceActive ? 'true' : 'false' }} }" class="px-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isPresenceActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-user-check text-[18px]"></i>
                            Présence
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isPresenceActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.academic.presence.attendance') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.presence.attendance*') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Présence Classe</a>
                        <a href="{{ route('school.academic.presence.access') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.presence.access') || (request()->routeIs('school.academic.presence.access.*') && !request()->routeIs('school.academic.presence.access.devices*')) ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Contrôle d'Accès</a>
                        <a href="{{ route('school.academic.presence.access.devices') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.presence.access.devices*') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Appareils de Scan</a>
                    </div>
                </div>
                @endif

                <!-- Sous-menu: Devoirs & Interrogations (Accordion) -->
                @if(auth()->user()->canAccess('academic.homework.manage'))
                @php
                    $isHomeworkActive = request()->routeIs('school.academic.homework.*');
                @endphp
                <div x-data="{ open: {{ $isHomeworkActive ? 'true' : 'false' }} }" class="px-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isHomeworkActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-exam text-[18px]"></i>
                            <span class="whitespace-nowrap text-[12px]">Devoirs & Interrogations</span>
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>

                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isHomeworkActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.academic.homework.homework') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.homework.homework*') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Devoirs Maison</a>
                        <a href="{{ route('school.academic.homework.tests') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.academic.homework.tests*') || request()->routeIs('school.academic.homework.live') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Interrogations & Contrôles</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- RESSOURCES HUMAINES -->
        @php
            $canSeePersonnelMenu = auth()->user()->canAccess('academic.personnel.manage');
            $canSeeHrMenu = auth()->user()->canAccess('hr.manage');
        @endphp
        @if($canSeeTeachersMenu || $canSeePersonnelMenu || $canSeeHrMenu)
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-4">Ressources Humaines</h3>
            <div class="space-y-1">
                @if($canSeeTeachersMenu)
                <a href="{{ route('school.academic.teachers') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.academic.teachers', 'school.academic.teachers.*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-chalkboard-teacher text-[18px]"></i>
                    Enseignants
                </a>
                @endif
                @if($canSeePersonnelMenu)
                <a href="{{ route('school.academic.personnel') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.academic.personnel', 'school.academic.personnel.*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-users text-[18px]"></i>
                    Personnel
                </a>
                @endif
                @if($canSeeHrMenu)
                @php
                    $isHrActive = request()->routeIs('school.hr.*');
                @endphp
                <div x-data="{ open: {{ $isHrActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isHrActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-money text-[18px]"></i>
                            Paie & RH
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isHrActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.hr.dashboard') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.hr.dashboard') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Pilotage RH</a>
                        <a href="{{ route('school.hr.directory') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.hr.directory') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Annuaire du Personnel</a>
                        <a href="{{ route('school.hr.contracts') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.hr.contracts') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Contrats</a>
                        <a href="{{ route('school.hr.payroll') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.hr.payroll') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Traitement de la Paie</a>
                        <a href="{{ route('school.hr.configuration') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.hr.configuration') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Configuration</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- ADMINISTRATION -->
        @if(auth()->user()->role === 'adminschool')
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-4">Administration</h3>
            <div class="space-y-1">
                <a href="{{ route('school.roles') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.roles', 'school.roles.*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-shield-check text-[18px]"></i>
                    Rôles & Permissions
                </a>
                <a href="{{ route('school.extensions') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.extensions', 'school.extensions.*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-puzzle-piece text-[18px]"></i>
                    Extensions
                </a>
                <a href="{{ route('school.plans') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.plans', 'school.plans.*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-crown-simple text-[18px]"></i>
                    Forfait
                </a>
                <a href="{{ route('school.billing') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.billing', 'school.billing.*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-receipt text-[18px]"></i>
                    Facturation
                </a>
                <a href="{{ route('school.wallet') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.wallet', 'school.wallet.*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-wallet text-[18px]"></i>
                    Portefeuille Academia Pay
                </a>
            </div>
        </div>
        @endif

        <!-- VIE SCOLAIRE -->
        @php
            $canSeeVieScolaireMenu = collect(['academic.awards.manage', 'infirmary.manage', 'canteen.manage', 'library.manage', 'report-card.manage', 'transport.manage'])->contains(fn($slug) => auth()->user()->canAccess($slug));
        @endphp
        @if($canSeeVieScolaireMenu)
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-4">Vie Scolaire</h3>
            <div class="space-y-1">
                @if(auth()->user()->canAccess('academic.awards.manage'))
                <a href="{{ route('school.academic.awards.index') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.academic.awards.*') ? 'text-[#031C5B] bg-blue-50/50 font-bold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-medal text-[18px]"></i>
                    Récompenses & Diplômes
                </a>
                @endif
                @if(auth()->user()->canAccess('infirmary.manage'))
                @php
                    $isInfirmaryActive = request()->routeIs('school.infirmary.*');
                @endphp
                <div x-data="{ open: {{ $isInfirmaryActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isInfirmaryActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-first-aid text-[18px]"></i>
                            Infirmerie
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isInfirmaryActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.infirmary.dashboard') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.infirmary.dashboard') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Aperçu du Jour</a>
                        <a href="{{ route('school.infirmary.interventions') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.infirmary.interventions') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Nouvelle Intervention</a>
                        <a href="{{ route('school.infirmary.students') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.infirmary.students') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Répertoire Étudiants</a>
                        <a href="{{ route('school.infirmary.pharmacy') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.infirmary.pharmacy') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Pharmacie</a>
                    </div>
                </div>
                @endif
                @if(auth()->user()->canAccess('canteen.manage'))
                @php
                    $isCanteenActive = request()->routeIs('school.canteen.*');
                @endphp
                <div x-data="{ open: {{ $isCanteenActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isCanteenActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-fork-knife text-[18px]"></i>
                            Cantine
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isCanteenActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.canteen.dashboard') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.canteen.dashboard') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Tableau de Bord</a>
                        <a href="{{ route('school.canteen.planning') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.canteen.planning') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Planification</a>
                        <a href="{{ route('school.canteen.inventory') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.canteen.inventory') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Stocks</a>
                        <a href="{{ route('school.canteen.reservations') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.canteen.reservations') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Réservations</a>
                        <a href="{{ route('school.canteen.requests') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.canteen.requests') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Demandes d'Inscription</a>
                        <a href="{{ route('school.canteen.scanner') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.canteen.scanner') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Scanner</a>
                    </div>
                </div>
                @endif
                @if(auth()->user()->canAccess('library.manage'))
                @php
                    $isLibraryActive = request()->routeIs('school.library.*');
                @endphp
                <div x-data="{ open: {{ $isLibraryActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isLibraryActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-books text-[18px]"></i>
                            Bibliothèque
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isLibraryActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.library.dashboard') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.library.dashboard') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Tableau de Bord</a>
                        <a href="{{ route('school.library.catalog') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.library.catalog') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Catalogue</a>
                        <a href="{{ route('school.library.circulation') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.library.circulation') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Circulation</a>
                        <a href="{{ route('school.library.settings') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.library.settings') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Paramètres</a>
                    </div>
                </div>
                @endif
                @if(auth()->user()->canAccess('report-card.manage'))
                @php
                    $isReportCardActive = request()->routeIs('school.report-card.*');
                @endphp
                <div x-data="{ open: {{ $isReportCardActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isReportCardActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-medal text-[18px]"></i>
                            Livret Scolaire
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isReportCardActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.report-card.dashboard') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.report-card.dashboard') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Aperçu Global</a>
                        <a href="{{ route('school.report-card.evaluation') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.report-card.evaluation') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Évaluation</a>
                        <a href="{{ route('school.report-card.referentials') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.report-card.referentials') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Référentiels</a>
                    </div>
                </div>
                @endif
                @if(auth()->user()->canAccess('transport.manage'))
                @php
                    $isTransportActive = request()->routeIs('school.transport.*');
                @endphp
                <div x-data="{ open: {{ $isTransportActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isTransportActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-bus text-[18px]"></i>
                            Transport
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isTransportActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.transport.fleet') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.transport.fleet') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Flotte de Bus</a>
                        <a href="{{ route('school.transport.drivers') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.transport.drivers') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Chauffeurs</a>
                        <a href="{{ route('school.transport.routes') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.transport.routes') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Itinéraires</a>
                        <a href="{{ route('school.transport.stops') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.transport.stops') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Arrêts de Bus</a>
                        <a href="{{ route('school.transport.trips') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.transport.trips') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Journal des Trajets</a>
                        <a href="{{ route('school.transport.map') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.transport.map') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Carte & Suivi</a>
                        <a href="{{ route('school.transport.requests') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.transport.requests') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Demandes d'Inscription</a>
                        <a href="{{ route('school.transport.scanner') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.transport.scanner') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Scanner</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- FINANCES -->
        @php
            $canSeeFinancesMenu = collect(['finance.fees.manage', 'finance.expenses.manage', 'finance.scholarships.manage'])->contains(fn($slug) => auth()->user()->canAccess($slug));
        @endphp
        @if($canSeeFinancesMenu)
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-4">Finances</h3>
            <div class="space-y-1">
                @if(auth()->user()->canAccess('finance.fees.manage'))
                @php
                    $isFeesActive = request()->routeIs('school.finance.fees.*');
                @endphp
                <div x-data="{ open: {{ $isFeesActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isFeesActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-receipt text-[18px]"></i>
                            Frais Scolaires
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isFeesActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.finance.fees.overview') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.finance.fees.overview') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Vue d'ensemble</a>
                        <a href="{{ route('school.finance.fees.payments') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.finance.fees.payments', 'school.finance.fees.students.show') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Suivi des Paiements</a>
                        <a href="{{ route('school.finance.fees.config') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.finance.fees.config') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Configuration des Frais</a>
                    </div>
                </div>
                @endif
                @if(auth()->user()->canAccess('finance.expenses.manage'))
                @php
                    $isExpensesActive = request()->routeIs('school.finance.expenses.*');
                @endphp
                <div x-data="{ open: {{ $isExpensesActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isExpensesActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-wallet text-[18px]"></i>
                            Dépenses
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isExpensesActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.finance.expenses.overview') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.finance.expenses.overview') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Aperçu</a>
                        <a href="{{ route('school.finance.expenses.transactions') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.finance.expenses.transactions', 'school.finance.expenses.create') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Transactions</a>
                        <a href="{{ route('school.finance.expenses.budgets') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.finance.expenses.budgets') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Budgets</a>
                    </div>
                </div>
                @endif
                @if(auth()->user()->canAccess('finance.scholarships.manage'))
                @php
                    $isScholarshipsActive = request()->routeIs('school.finance.scholarships.*');
                @endphp
                <div x-data="{ open: {{ $isScholarshipsActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isScholarshipsActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-student text-[18px]"></i>
                            Bourses
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isScholarshipsActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.finance.scholarships.dashboard') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.finance.scholarships.dashboard') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Tableau de Bord</a>
                        <a href="{{ route('school.finance.scholarships.students') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.finance.scholarships.students', 'school.finance.scholarships.show') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Élèves Boursiers</a>
                        <a href="{{ route('school.finance.scholarships.criteria') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.finance.scholarships.criteria') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Critères d'Éligibilité</a>
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- COMMUNICATION -->
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-4">Communication</h3>
            <div class="space-y-1">
                @if(auth()->user()->canAccess('communication.events.manage'))
                @php
                    $isEventsActive = request()->routeIs('school.communication.events.*');
                @endphp
                <div x-data="{ open: {{ $isEventsActive ? 'true' : 'false' }} }" class="px-2 -mx-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-2 py-2 text-[13.5px] font-medium rounded-lg transition {{ $isEventsActive ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-calendar-star text-[18px]"></i>
                            Événements
                        </div>
                        <i class="ph ph-caret-down text-[14px] transition-transform duration-200" :class="{ 'rotate-180': open }"></i>
                    </button>
                    <div x-show="open" x-collapse class="pl-9 pr-2 py-1 space-y-1" {!! $isEventsActive ? '' : 'style="display: none;"' !!}>
                        <a href="{{ route('school.communication.events.dashboard') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.communication.events.dashboard') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Vue d'ensemble</a>
                        <a href="{{ route('school.communication.events.calendar') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.communication.events.calendar', 'school.communication.events.show', 'school.communication.events.edit') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Calendrier</a>
                        <a href="{{ route('school.communication.events.create') }}" class="block px-2 py-1.5 text-[12.5px] font-medium transition {{ request()->routeIs('school.communication.events.create') ? 'text-[#031C5B] font-bold' : 'text-slate-500 hover:text-slate-800' }}">Créer un événement</a>
                    </div>
                </div>
                @endif
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                    <i class="ph ph-users-four text-[18px]"></i>
                    Parents
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                    <i class="ph ph-student text-[18px]"></i>
                    Élèves
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition">
                    <i class="ph ph-bell text-[18px]"></i>
                    Notifications
                </a>
            </div>
        </div>

        <!-- SUPPORT -->
        <div>
            <h3 class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-4">Support</h3>
            <div class="space-y-1">
                <a href="{{ route('school.support') }}" class="flex items-center gap-3 px-4 py-2 text-[13.5px] font-medium rounded-lg transition {{ request()->routeIs('school.support') ? 'text-[#031C5B] bg-blue-50/50' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                    <i class="ph ph-headset text-[18px]"></i>
                    Contacter le Support
                </a>
            </div>
        </div>

    </nav>
</aside>
