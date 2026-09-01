@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[32px] font-extrabold text-[#111827]">Gestion des Sauvegardes</h2>
            <p class="text-[15px] text-slate-500 mt-1">Gérez les snapshots du système et la configuration du stockage cloud (Base SQL).</p>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <form action="{{ route('superadmin.backups.trigger') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center gap-2 bg-[#7C3AED] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-purple-700 transition shadow-sm cursor-pointer">
                    <i class="ph ph-cloud-arrow-up text-lg font-bold"></i> + Déclencher Sauvegarde (BD SQL)
                </button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i class="ph ph-check-circle text-xl text-emerald-600 font-bold"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
        </div>
    @endif

    <!-- Main Grid Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- LEFT COLUMN (1 Col on lg) -->
        <div class="space-y-6">
            
            <!-- Stockage Cloud Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-extrabold text-slate-900">Stockage Cloud S3</h3>
                    <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center text-[#7C3AED] font-bold">
                        <i class="ph ph-cloud text-lg"></i>
                    </div>
                </div>

                <div class="space-y-3.5 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Fournisseur</span>
                        <span class="font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="ph ph-cloud text-indigo-500"></i> {{ $storage['provider'] }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Statut Connexion</span>
                        <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-2.5 py-0.5 rounded-full border border-emerald-200/60">
                            {{ $storage['status'] }}
                        </span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-slate-500 font-medium">Utilisation (Total: {{ $storage['total_tb'] }} TB)</span>
                            <span class="font-extrabold text-slate-900 text-sm">{{ $storage['used_tb'] }} TB</span>
                        </div>
                        <!-- Progress Bar -->
                        <div class="w-full h-2 bg-purple-100 rounded-full overflow-hidden">
                            <div class="h-full bg-[#7C3AED] rounded-full" style="width: {{ $storage['used_percent'] }}%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Planification Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <div class="flex items-center gap-2 mb-4">
                    <i class="ph ph-clock text-[#031C5B] text-lg font-bold"></i>
                    <h3 class="text-base font-extrabold text-slate-900">Planification (Base SQL)</h3>
                </div>

                <form action="{{ route('superadmin.backups.settings') }}" method="POST" class="space-y-4">
                    @csrf
                    <!-- Fréquence Complète -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Fréquence des Sauvegardes Complètes</label>
                        <input type="text" name="full_frequency" value="{{ $schedule['full_frequency'] }}" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
                    </div>

                    <!-- Sauvegardes Différentielles -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Sauvegardes Différentielles</label>
                        <select name="differential_frequency" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
                            <option value="Toutes les 6 heures" {{ ($schedule['differential_frequency'] ?? '') === 'Toutes les 6 heures' ? 'selected' : '' }}>Toutes les 6 heures</option>
                            <option value="Toutes les 12 heures" {{ ($schedule['differential_frequency'] ?? '') === 'Toutes les 12 heures' ? 'selected' : '' }}>Toutes les 12 heures</option>
                            <option value="Quotidienne (Chaque jour)" {{ ($schedule['differential_frequency'] ?? '') === 'Quotidienne (Chaque jour)' || ($schedule['differential_frequency'] ?? '') === 'Quotidienne' ? 'selected' : '' }}>Quotidienne (Chaque jour)</option>
                            <option value="Hebdomadaire (Chaque semaine)" {{ ($schedule['differential_frequency'] ?? '') === 'Hebdomadaire (Chaque semaine)' ? 'selected' : '' }}>Hebdomadaire (Chaque semaine)</option>
                            <option value="Mensuelle (Chaque mois)" {{ ($schedule['differential_frequency'] ?? '') === 'Mensuelle (Chaque mois)' ? 'selected' : '' }}>Mensuelle (Chaque mois)</option>
                        </select>
                    </div>

                    <!-- Rétention -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Rétention des Snapshots</label>
                        <select name="retention_days" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
                            <option value="30 Jours" {{ ($schedule['retention_days'] ?? '') === '30 Jours' ? 'selected' : '' }}>30 Jours</option>
                            <option value="60 Jours" {{ ($schedule['retention_days'] ?? '') === '60 Jours' ? 'selected' : '' }}>60 Jours</option>
                            <option value="90 Jours" {{ ($schedule['retention_days'] ?? '') === '90 Jours' ? 'selected' : '' }}>90 Jours</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-[#031C5B] text-white py-2.5 rounded-xl text-xs font-bold hover:bg-blue-900 transition shadow-xs cursor-pointer">
                        Mettre à jour les paramètres SQL
                    </button>
                </form>
            </div>

        </div>

        <!-- RIGHT COLUMN (Occupies 2 Cols on lg) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Analyse IA du Stockage Box -->
            <div class="bg-gradient-to-br from-purple-50 via-white to-purple-50/40 rounded-2xl border border-purple-100 p-6 shadow-xs">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-[#7C3AED] flex items-center justify-center shrink-0 mt-0.5 font-bold">
                        <i class="ph ph-brain text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 mb-1.5">État Réel des Sauvegardes</h3>
                        @if($lastBackupAt)
                            <p class="text-xs text-slate-600 leading-relaxed mb-3">
                                {{ $backupCount }} sauvegarde(s) enregistrée(s), dernière le {{ $lastBackupAt->format('d/m/Y à H:i') }}.
                                Fréquence configurée : {{ $schedule['full_frequency'] }} · Rétention : {{ $schedule['retention_days'] }}.
                            </p>
                        @else
                            <p class="text-xs text-slate-600 leading-relaxed mb-3">
                                Aucune sauvegarde enregistrée pour le moment. Fréquence configurée : {{ $schedule['full_frequency'] }}.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Historique des Points de Restauration Table -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="p-6 flex items-center justify-between border-b border-slate-100 bg-[#FCFDFE]">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900">Historique des Points de Restauration (BD SQL)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Snapshots de sauvegarde disponibles</p>
                        </div>
                        <span class="text-xs font-bold text-slate-600 bg-slate-100 px-3 py-1 rounded-full">
                            {{ count($snapshots) }} snapshot(s)
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                            <thead>
                                <tr class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest bg-slate-50 border-b border-slate-100">
                                    <th class="py-3.5 px-6">DATE / HEURE</th>
                                    <th class="py-3.5 px-4">TYPE</th>
                                    <th class="py-3.5 px-4">TAILLE</th>
                                    <th class="py-3.5 px-4">STATUT</th>
                                    <th class="py-3.5 px-6 text-right">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs font-medium">
                                @forelse($snapshots as $snap)
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <!-- Date / Heure & ID -->
                                        <td class="py-4 px-6">
                                            <p class="font-bold text-slate-900 text-xs leading-snug">{{ $snap['datetime'] }}</p>
                                            <p class="text-[11px] text-slate-400 font-mono mt-0.5">ID: {{ $snap['snap_id'] }} ({{ $snap['filename'] }})</p>
                                        </td>

                                        <!-- Type -->
                                        <td class="py-4 px-4 font-semibold text-slate-800">
                                            {{ $snap['type'] }}
                                        </td>

                                        <!-- Taille -->
                                        <td class="py-4 px-4 font-bold text-slate-700">
                                            {{ $snap['size'] }}
                                        </td>

                                        <!-- Statut -->
                                        <td class="py-4 px-4">
                                            @if($snap['status_type'] === 'success')
                                                <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[11px] font-bold px-3 py-1 rounded-full border border-emerald-200/60">
                                                    <i class="ph ph-check-circle text-xs font-bold"></i> Succès
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-800 text-[11px] font-bold px-3 py-1 rounded-full border border-amber-200/60">
                                                    <i class="ph ph-arrows-clockwise text-xs font-bold"></i> En cours
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-4 px-6 text-right">
                                            <div class="flex items-center justify-end gap-2 text-slate-500">
                                                <!-- Download -->
                                                <a href="{{ route('superadmin.backups.download', $snap['id']) }}" target="_blank" title="Télécharger le fichier .sql.gz" class="p-1.5 rounded-lg bg-blue-50 text-[#031C5B] hover:bg-[#031C5B] hover:text-white transition font-bold">
                                                    <i class="ph ph-download-simple text-base"></i>
                                                </a>

                                                <!-- Restore -->
                                                <form action="{{ route('superadmin.backups.restore', $snap['id']) }}" method="POST" class="inline" onsubmit="return confirm('ATTENTION: Êtes-vous sûr de vouloir restaurer la base de données système à partir du snapshot {{ addslashes($snap['snap_id']) }} ?');">
                                                    @csrf
                                                    <button type="submit" title="Restaurer la base de données" class="p-1.5 rounded-lg bg-purple-50 text-[#7C3AED] hover:bg-[#7C3AED] hover:text-white transition font-bold cursor-pointer">
                                                        <i class="ph ph-arrows-counter-clockwise text-base"></i>
                                                    </button>
                                                </form>

                                                <!-- Delete -->
                                                <form action="{{ route('superadmin.backups.delete', $snap['id']) }}" method="POST" class="inline" onsubmit="return confirm('Voulez-vous vraiment supprimer définitivement ce snapshot ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="Supprimer la sauvegarde" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition font-bold cursor-pointer">
                                                        <i class="ph ph-trash text-base"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-slate-400 text-sm">
                                            Aucun snapshot de sauvegarde enregistré.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
