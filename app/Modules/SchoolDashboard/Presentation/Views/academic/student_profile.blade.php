@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            @if($student->photo_path)
                <img src="{{ asset('storage/' . $student->photo_path) }}" class="w-16 h-16 rounded-xl object-cover border border-slate-200">
            @else
                <div class="w-16 h-16 rounded-xl bg-slate-100 flex items-center justify-center text-slate-400"><i class="ph-bold ph-user text-2xl"></i></div>
            @endif
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-[22px] font-extrabold text-[#0F172A]">{{ $student->first_name }} {{ $student->last_name }}</h2>
                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $student->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $student->status === 'active' ? 'Actif' : 'Inactif' }}</span>
                </div>
                <p class="text-[12.5px] text-slate-500 mt-0.5">
                    Matricule : {{ $student->roll_number }}
                    @if($student->academicClass) &middot; Classe : {{ $student->academicClass->name }} @endif
                    @if($student->academic_year) &middot; {{ $student->academic_year }} @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($student->academic_class_id)
            <a href="{{ route('school.academic.bulletins.print', $student->id) }}" target="_blank" class="flex items-center gap-2 bg-amber-50 text-amber-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-amber-100 transition">
                <i class="ph-bold ph-newspaper"></i> Bulletin
            </a>
            @endif
            <a href="{{ route('school.academic.students.edit', $student->id) }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition">
                <i class="ph-bold ph-pencil-simple"></i> Modifier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">

            <!-- Informations personnelles -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-[16px] font-extrabold text-slate-900 mb-4">Informations Personnelles</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-[13px]">
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Date de naissance</p><p class="font-semibold text-slate-800">{{ $student->dob ? \Carbon\Carbon::parse($student->dob)->format('d/m/Y') : '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Lieu de naissance</p><p class="font-semibold text-slate-800">{{ $student->birthplace ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Sexe</p><p class="font-semibold text-slate-800">{{ $student->gender ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Nationalité</p><p class="font-semibold text-slate-800">{{ $student->nationality ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Téléphone</p><p class="font-semibold text-slate-800">{{ $student->phone ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Email</p><p class="font-semibold text-slate-800">{{ $student->email ?? '—' }}</p></div>
                    <div class="md:col-span-3"><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Adresse</p><p class="font-semibold text-slate-800">{{ $student->address ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Groupe sanguin</p><p class="font-semibold text-slate-800">{{ $student->blood_group ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Allergies</p><p class="font-semibold text-slate-800">{{ $student->allergies ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Établissement</p><p class="font-semibold text-slate-800">{{ $student->branch->name ?? 'Siège' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Numéro de dossier</p><p class="font-semibold text-slate-800">{{ $student->dossier_number ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Statut d'inscription</p><p class="font-semibold text-slate-800">{{ \App\Modules\Academic\Domain\Models\Student::ENROLLMENT_TYPES[$student->enrollment_type] ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Régime</p><p class="font-semibold text-slate-800">{{ \App\Modules\Academic\Domain\Models\Student::REGIMES[$student->regime] ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Date d'inscription</p><p class="font-semibold text-slate-800">{{ $student->enrollment_date?->format('d/m/Y') ?? '—' }}</p></div>
                    <div><p class="text-slate-400 text-[11px] font-bold uppercase mb-1">Date d'entrée</p><p class="font-semibold text-slate-800">{{ $student->entry_date?->format('d/m/Y') ?? '—' }}</p></div>
                </div>
            </div>

            <!-- Documents Administratifs -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6" x-data="{ addDoc: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[16px] font-extrabold text-slate-900">Documents Administratifs</h3>
                    <button @click="addDoc = true" class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#031C5B] text-white rounded-xl text-[12.5px] font-bold hover:bg-[#031C5B]/90 transition">
                        <i class="ph-bold ph-plus"></i> Ajouter
                    </button>
                </div>

                <div class="space-y-2 mb-1">
                    @forelse($documents as $doc)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <i class="ph-fill ph-file-text text-slate-400 text-lg"></i>
                            <div>
                                <p class="text-[13px] font-bold text-slate-800">{{ $doc->label }}</p>
                                <p class="text-[11px] text-slate-500">{{ \App\Modules\Academic\Domain\Models\StudentDocument::TYPES[$doc->type] ?? $doc->type }} &middot; Déposé le {{ $doc->deposited_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('school.academic.students.documents.status', $doc->id) }}" method="POST">
                                @csrf @method('PUT')
                                <select name="status" onchange="this.form.submit()" class="text-[11px] font-bold rounded-full px-2.5 py-1 border-0 outline-none cursor-pointer {{ $doc->status === 'validated' ? 'bg-emerald-100 text-emerald-700' : ($doc->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                                    <option value="pending" {{ $doc->status === 'pending' ? 'selected' : '' }}>En attente</option>
                                    <option value="validated" {{ $doc->status === 'validated' ? 'selected' : '' }}>Validé</option>
                                    <option value="rejected" {{ $doc->status === 'rejected' ? 'selected' : '' }}>Rejeté</option>
                                </select>
                            </form>
                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-download-simple"></i></a>
                            <form action="{{ route('school.academic.students.documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Supprimer ce document ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-600"><i class="ph-bold ph-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-400 text-[13px] text-center py-6">Aucun document déposé.</p>
                    @endforelse
                </div>

                <div x-show="addDoc" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4">
                    <div @click.outside="addDoc = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-[17px] font-bold text-[#031C5B]">Ajouter un document</h3>
                            <button @click="addDoc = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
                        </div>
                        <form method="POST" action="{{ route('school.academic.students.documents.store', $student->id) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Type de document</label>
                                <select name="type" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                                    @foreach(\App\Modules\Academic\Domain\Models\StudentDocument::TYPES as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Libellé</label>
                                <input type="text" name="label" required placeholder="Ex: Acte de naissance" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Date de dépôt</label>
                                <input type="date" name="deposited_at" required value="{{ now()->format('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Fichier</label>
                                <input type="file" name="file" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Ajouter</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Distinctions & Récompenses -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[16px] font-extrabold text-slate-900">Distinctions &amp; Récompenses</h3>
                    @if(auth()->user()->canAccess('academic.awards.manage', 'create'))
                    <a href="{{ route('school.academic.awards.create', ['recipient_type' => 'student', 'recipient_id' => $student->id]) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#031C5B] text-white rounded-xl text-[12.5px] font-bold hover:bg-[#031C5B]/90 transition">
                        <i class="ph-bold ph-medal"></i> Attribuer
                    </a>
                    @endif
                </div>
                <div class="space-y-2">
                    @forelse($awards as $award)
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="ph-fill ph-medal"></i></span>
                            <div>
                                <p class="text-[13px] font-bold text-slate-800">{{ $award->type->name ?? '—' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $award->awarded_date->format('d/m/Y') }} @if($award->material_reward) &middot; {{ $award->material_reward }} @endif @if($award->reason) &middot; {{ $award->reason }} @endif</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-400 text-[13px] text-center py-4">Aucune distinction attribuée.</p>
                    @endforelse
                </div>
            </div>

            <!-- Sanctions -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6" x-data="{ addDisc: false }">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[16px] font-extrabold text-slate-900">Sanctions</h3>
                    <button @click="addDisc = true" class="inline-flex items-center gap-2 px-3.5 py-2 bg-[#031C5B] text-white rounded-xl text-[12.5px] font-bold hover:bg-[#031C5B]/90 transition">
                        <i class="ph-bold ph-plus"></i> Ajouter
                    </button>
                </div>

                <div class="space-y-2">
                    @forelse($disciplinaryRecords->where('category', 'sanction') as $record)
                    @php
                        $recordTypes = \App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::SANCTION_TYPES;
                    @endphp
                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full flex items-center justify-center bg-red-100 text-red-600">
                                <i class="ph-fill ph-warning"></i>
                            </span>
                            <div>
                                <p class="text-[13px] font-bold text-slate-800">{{ $recordTypes[$record->type] ?? $record->type }}</p>
                                <p class="text-[11px] text-slate-500">{{ $record->recorded_date->format('d/m/Y') }} @if($record->description) &middot; {{ $record->description }} @endif</p>
                            </div>
                        </div>
                        <form action="{{ route('school.academic.students.disciplinary.destroy', $record->id) }}" method="POST" onsubmit="return confirm('Supprimer cet enregistrement ?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600"><i class="ph-bold ph-trash"></i></button>
                        </form>
                    </div>
                    @empty
                    <p class="text-slate-400 text-[13px] text-center py-6">Aucune sanction enregistrée.</p>
                    @endforelse
                </div>

                <div x-show="addDisc" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4">
                    <div @click.outside="addDisc = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-[17px] font-bold text-[#031C5B]">Ajouter une sanction</h3>
                            <button @click="addDisc = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
                        </div>
                        <form method="POST" action="{{ route('school.academic.students.disciplinary.store', $student->id) }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="category" value="sanction">
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Type</label>
                                <select name="type" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                                    @foreach(\App\Modules\Academic\Domain\Models\StudentDisciplinaryRecord::SANCTION_TYPES as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Date</label>
                                <input type="date" name="recorded_date" required value="{{ now()->format('Y-m-d') }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                            </div>
                            <div>
                                <label class="block text-[12px] font-bold text-slate-600 mb-1">Description (optionnel)</label>
                                <textarea name="description" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]"></textarea>
                            </div>
                            <button type="submit" class="w-full px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Enregistrer</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Scolarité -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-[16px] font-extrabold text-slate-900">Scolarité — {{ $currentSemester->name ?? 'Aucun semestre actif' }}</h3>
                    @if($currentSemester)
                        <span class="text-[11px] font-bold px-2.5 py-1 rounded-full {{ $bulletinPublished ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ $bulletinPublished ? 'Bulletin publié' : 'Notes provisoires' }}</span>
                    @endif
                </div>

                @if($currentSemester && $subjectGrades->isNotEmpty())
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <p class="text-[10.5px] font-bold text-slate-500 uppercase">Moyenne</p>
                            <p class="text-[20px] font-extrabold text-[#031C5B]">{{ $average !== null ? number_format($average, 2) : '—' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <p class="text-[10.5px] font-bold text-slate-500 uppercase">Rang</p>
                            <p class="text-[20px] font-extrabold text-[#031C5B]">{{ $rank ? $rank . ' / ' . $classSize : '—' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-xl p-3 text-center">
                            <p class="text-[10.5px] font-bold text-slate-500 uppercase">Matières</p>
                            <p class="text-[20px] font-extrabold text-[#031C5B]">{{ $subjectGrades->count() }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-[13px]">
                            <thead>
                                <tr class="text-[11px] font-bold text-slate-500 uppercase border-b border-slate-100">
                                    <th class="py-2">Matière</th>
                                    <th class="py-2 text-center">Coefficient</th>
                                    <th class="py-2 text-right">Moyenne</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($subjectGrades as $g)
                                <tr>
                                    <td class="py-2.5 font-semibold text-slate-800">{{ $g->subject->name ?? '—' }}</td>
                                    <td class="py-2.5 text-center text-slate-500">{{ $g->subject->coefficient ?? '—' }}</td>
                                    <td class="py-2.5 text-right font-bold {{ $g->score < 10 ? 'text-red-600' : 'text-slate-800' }}">{{ number_format($g->score, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-slate-400 text-[13px] text-center py-8">Aucune note enregistrée pour la période en cours.</p>
                @endif
            </div>

            <!-- Historique -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-[16px] font-extrabold text-slate-900 mb-4">Historique de l'Élève</h3>
                @forelse($movements as $m)
                <div class="flex items-center justify-between py-2.5 border-b border-slate-50 last:border-0 text-[13px]">
                    <div>
                        <p class="font-semibold text-slate-800">{{ $m->type === 'transfer' ? 'Transfert' : 'Promotion' }} : {{ $m->fromClass->name ?? '—' }} → {{ $m->toClass->name ?? '—' }}</p>
                        <p class="text-[11.5px] text-slate-400">{{ $m->from_academic_year }} → {{ $m->to_academic_year }} @if($m->reason) &middot; {{ $m->reason }} @endif</p>
                    </div>
                    <span class="text-[11px] text-slate-400">{{ $m->created_at->format('d/m/Y') }}</span>
                </div>
                @empty
                <p class="text-slate-400 text-[13px] text-center py-6">Aucun mouvement de classe enregistré.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-6">

            <!-- Parents / Responsables -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-[15px] font-extrabold text-slate-900 mb-4">Parents / Responsables</h3>
                <div class="space-y-3">
                    @forelse($student->guardians as $guardian)
                    <div class="p-3 bg-slate-50 rounded-xl">
                        <p class="text-[13px] font-bold text-slate-800">{{ $guardian->name }}</p>
                        <p class="text-[11.5px] text-slate-500">{{ $guardian->relation ?? 'Responsable' }}</p>
                        <div class="mt-1.5 space-y-0.5 text-[11.5px] text-slate-500">
                            @if($guardian->phone)<p class="flex items-center gap-1.5"><i class="ph ph-phone"></i> {{ $guardian->phone }}</p>@endif
                            @if($guardian->email)<p class="flex items-center gap-1.5"><i class="ph ph-envelope"></i> {{ $guardian->email }}</p>@endif
                        </div>
                    </div>
                    @empty
                    <p class="text-slate-400 text-[13px] text-center py-4">Aucun responsable enregistré.</p>
                    @endforelse
                </div>
            </div>

            <!-- Présence -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-[15px] font-extrabold text-slate-900 mb-4">Présence</h3>
                <div class="grid grid-cols-2 gap-3 text-center mb-2">
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-[18px] font-extrabold text-[#031C5B]">{{ $attendanceRate !== null ? $attendanceRate . '%' : '—' }}</p>
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Taux de présence</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-[18px] font-extrabold text-red-600">{{ $unjustifiedAbsences }}</p>
                        <p class="text-[10.5px] font-bold text-slate-500 uppercase">Absences non just.</p>
                    </div>
                </div>
                <div class="space-y-1.5 text-[12.5px]">
                    <div class="flex justify-between"><span class="text-slate-500">Absences justifiées</span><span class="font-bold text-slate-800">{{ $justifiedAbsences }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Retards</span><span class="font-bold text-slate-800">{{ $lateCount }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-500">Jours observés</span><span class="font-bold text-slate-800">{{ $totalDays }}</span></div>
                </div>
            </div>

            <!-- Finances -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-[15px] font-extrabold text-slate-900 mb-4">Finances</h3>
                <div class="grid grid-cols-2 gap-3 text-center mb-3">
                    <div class="bg-emerald-50 rounded-xl p-3">
                        <p class="text-[15px] font-extrabold text-emerald-700">{{ number_format($totalPaid, 0, ',', ' ') }}</p>
                        <p class="text-[10.5px] font-bold text-emerald-600 uppercase">Payé (FCFA)</p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-3">
                        <p class="text-[15px] font-extrabold text-red-700">{{ number_format($totalRemaining, 0, ',', ' ') }}</p>
                        <p class="text-[10.5px] font-bold text-red-600 uppercase">Restant (FCFA)</p>
                    </div>
                </div>
                @php $totalScholarshipCredit = collect($feeSummaries)->sum('scholarshipCredit'); @endphp
                @if($totalScholarshipCredit > 0)
                <div class="flex items-center gap-1.5 text-[11.5px] text-indigo-600 font-semibold mb-3">
                    <i class="ph-fill ph-medal"></i> {{ number_format($totalScholarshipCredit, 0, ',', ' ') }} FCFA couverts par bourse (déjà déduits du restant)
                </div>
                @endif
                <div class="space-y-2">
                    @foreach($feeSummaries as $fee)
                    <div class="flex items-center justify-between text-[12.5px]">
                        <span class="text-slate-500">{{ $fee['label'] }}</span>
                        @if($fee['status'] === 'unconfigured')
                            <span class="text-slate-400 text-[11px]">Non configuré</span>
                        @else
                            <a href="{{ route('school.finance.fees.students.show', $student->id) }}?type={{ $fee['type'] }}" class="font-bold {{ $fee['status'] === 'paid' ? 'text-emerald-600' : ($fee['status'] === 'late' ? 'text-red-600' : 'text-slate-800') }} hover:underline">
                                {{ number_format($fee['paid'], 0, ',', ' ') }} / {{ number_format($fee['total'], 0, ',', ' ') }}
                            </a>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Bourse -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-[15px] font-extrabold text-slate-900 mb-4">Bourse</h3>
                @forelse($scholarships as $scholarship)
                @php
                    $scholarshipStatusColor = ['active' => 'bg-emerald-100 text-emerald-700', 'pending' => 'bg-amber-100 text-amber-700', 'suspended' => 'bg-orange-100 text-orange-700', 'rejected' => 'bg-red-100 text-red-700', 'expired' => 'bg-slate-100 text-slate-500'];
                @endphp
                <div class="p-3 bg-slate-50 rounded-xl mb-2 last:mb-0">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-[13px] font-bold text-slate-800">{{ $scholarship->type->name ?? 'Bourse' }}</p>
                        <span class="text-[10.5px] font-bold px-2 py-0.5 rounded-full {{ $scholarshipStatusColor[$scholarship->status] ?? 'bg-slate-100 text-slate-500' }}">{{ \App\Modules\Finance\Domain\Models\Scholarship::STATUSES[$scholarship->status] ?? $scholarship->status }}</span>
                    </div>
                    <p class="text-[11.5px] text-slate-500">{{ number_format($scholarship->monthly_amount, 0, ',', ' ') }} FCFA/mois @if($scholarship->status === 'active') &middot; {{ number_format($scholarship->annual_amount, 0, ',', ' ') }} FCFA/an déduits de la scolarité @endif</p>
                </div>
                @empty
                <p class="text-slate-400 text-[13px] text-center py-4">Aucune bourse enregistrée.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
