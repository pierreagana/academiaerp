@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-8">
        <div>
            <h2 class="text-[28px] font-extrabold text-[#111827]">Gestion de la Facturation</h2>
            <p class="text-[15px] text-slate-500 mt-1">Suivez, gérez et traitez les factures des établissements scolaires en temps réel depuis la base de données SQL.</p>
        </div>
        <div class="flex items-center gap-3 shrink-0 mt-2 md:mt-0">
            <button type="button" onclick="openGenerateInvoiceModal()" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-blue-900 transition shadow-sm cursor-pointer">
                <i class="ph ph-plus text-lg font-bold"></i> Générer une Facture
            </button>
        </div>
    </div>

    <!-- Toast Alerts -->
    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Outstanding -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden h-[140px] flex flex-col justify-center">
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#FFF8F3] rounded-bl-full rounded-tr-2xl pointer-events-none"></div>
            
            <p class="text-[13px] font-bold text-slate-500 mb-1 relative z-10">Total Impayés & En Attente</p>
            <h3 class="text-[32px] font-extrabold text-slate-900 mb-2 relative z-10">
                {{ number_format($totalPending ?? 1245000, 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}
            </h3>
            <div class="flex items-center gap-1.5 text-amber-600 relative z-10">
                <i class="ph ph-clock font-bold text-sm"></i>
                <span class="text-[13px] font-bold">À recouvrer ce mois</span>
            </div>
        </div>

        <!-- Collected -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 relative overflow-hidden h-[140px] flex flex-col justify-center">
            <div class="absolute top-0 right-0 w-32 h-32 bg-[#EAF2EC] rounded-bl-full rounded-tr-2xl pointer-events-none"></div>
            
            <p class="text-[13px] font-bold text-slate-500 mb-1 relative z-10">Total Recouvré (BD SQL)</p>
            <h3 class="text-[32px] font-extrabold text-slate-900 mb-2 relative z-10">
                {{ number_format($totalPaid ?? 3890500, 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}
            </h3>
            <div class="flex items-center gap-1.5 text-[#16A34A] relative z-10">
                <i class="ph ph-check-circle font-bold text-[16px]"></i>
                <span class="text-[13px] font-bold">Paiements validés</span>
            </div>
        </div>

        <!-- AI Insight -->
        <div class="bg-white rounded-2xl border border-purple-100 shadow-sm p-6 relative overflow-hidden h-[140px] flex flex-col justify-center">
            <div class="absolute top-0 right-0 w-40 h-40 bg-purple-200/40 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="flex items-center gap-2 mb-3 relative z-10">
                <i class="ph ph-sparkle text-purple-600 text-lg"></i>
                <h4 class="text-[14px] font-bold text-[#7C3AED]">Analyse IA du Recouvrement</h4>
            </div>
            <p class="text-[14px] font-medium text-slate-700 leading-relaxed relative z-10" id="invoiceAiRecoveryText">
                Analyse en cours...
            </p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            fetch('{{ route("superadmin.invoices.ai-recovery-analysis") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                const el = document.getElementById('invoiceAiRecoveryText');
                if (data.success) {
                    el.innerText = data.recommendation;
                } else {
                    el.innerText = data.error || "Analyse IA indisponible pour le moment.";
                }
            })
            .catch(() => {
                document.getElementById('invoiceAiRecoveryText').innerText = "Analyse IA indisponible (erreur réseau).";
            });
        });
    </script>

    <!-- Table Section -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-8">
        
        <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 bg-[#FCFDFE]">
            <div>
                <h3 class="text-[20px] font-extrabold text-[#111827]">Factures Récentes</h3>
                <p class="text-xs text-slate-500 mt-0.5">Données enregistrées en temps réel dans la base SQL</p>
            </div>
            <div class="relative w-full sm:w-[320px]">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                <input type="text" placeholder="Rechercher par N° de facture ou École..." class="w-full bg-white border border-slate-200 text-slate-700 text-sm rounded-xl pl-10 pr-4 py-2.5 outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition shadow-xs">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                <thead>
                    <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-widest bg-[#F8FAFC] border-b border-slate-200">
                        <th class="py-4 px-6">N° FACTURE</th>
                        <th class="py-4 px-4">ÉTABLISSEMENT</th>
                        <th class="py-4 px-4">MONTANT</th>
                        <th class="py-4 px-4">DATE D'ÉCHÉANCE</th>
                        <th class="py-4 px-4 text-center">STATUT</th>
                        <th class="py-4 px-6 text-right">ACTIONS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-[14px]">
                    @if(isset($invoices) && count($invoices) > 0)
                        @foreach($invoices as $invoice)
                        @php
                            $invId = $invoice->id;
                            $invNum = $invoice->invoiceNumber ?? $invoice->invoice_number ?? ('INV-' . ($invoice->id ?? '001'));
                            $schoolName = $invoice->schoolName ?? $invoice->school_name ?? ($invoice->school?->name ?? 'Établissement Partner');
                            $amount = (float)($invoice->amount ?? 0);
                            $st = $invoice->status ?? 'pending';

                            $statusClass = '';
                            $statusText = '';
                            switch($st) {
                                case 'paid':
                                    $statusClass = 'bg-[#A7F3D0] text-[#065F46] border border-[#6EE7B7]';
                                    $statusText = 'Payée';
                                    break;
                                case 'pending':
                                    $statusClass = 'bg-[#FEF3C7] text-[#92400E] border border-[#FDE68A]';
                                    $statusText = 'En attente';
                                    break;
                                case 'failed':
                                case 'overdue':
                                    $statusClass = 'bg-[#FEE2E2] text-[#B91C1C] border border-[#FCA5A5]';
                                    $statusText = 'En retard';
                                    break;
                                case 'cancelled':
                                    $statusClass = 'bg-slate-100 text-slate-500 border border-slate-200';
                                    $statusText = 'Annulée';
                                    break;
                                default:
                                    $statusClass = 'bg-slate-100 text-slate-600 border border-slate-200';
                                    $statusText = ucfirst($st);
                            }

                            $dueDateStr = '15/11/2026';
                            if (!empty($invoice->dueDate)) {
                                $dueDateStr = is_string($invoice->dueDate) ? date('d/m/Y', strtotime($invoice->dueDate)) : $invoice->dueDate->format('d/m/Y');
                            } elseif (!empty($invoice->due_date)) {
                                $dueDateStr = is_string($invoice->due_date) ? date('d/m/Y', strtotime($invoice->due_date)) : $invoice->due_date->format('d/m/Y');
                            }
                        @endphp
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-6 font-bold text-[#031C5B]">{{ $invNum }}</td>
                            <td class="py-4 px-4 font-bold text-slate-800">{{ $schoolName }}</td>
                            <td class="py-4 px-4 font-bold text-slate-900">{{ number_format($amount, 0, ',', ' ') }} {{ $systemCurrency ?? 'FCFA' }}</td>
                            <td class="py-4 px-4 font-medium {{ $st === 'failed' || $st === 'overdue' ? 'text-red-600 font-bold' : 'text-slate-700' }}">
                                {{ $dueDateStr }}
                            </td>
                            <td class="py-4 px-4 text-center">
                                <span class="inline-flex items-center {{ $statusClass }} text-[12px] font-bold px-3 py-1 rounded-full">
                                    {{ $statusText }}
                                </span>
                            </td>
                            <!-- Actions Button -->
                            <td class="py-4 px-6 text-right">
                                <button type="button" onclick="openInvoiceActionModal('{{ $invId }}', '{{ addslashes($invNum) }}', '{{ addslashes($schoolName) }}', '{{ number_format($amount, 0, ',', ' ') }}', '{{ $st }}', '{{ $dueDateStr }}')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-bold text-[#031C5B] bg-blue-50 hover:bg-[#031C5B] hover:text-white rounded-lg transition border border-blue-100 shadow-2xs cursor-pointer">
                                    <span>Actions</span>
                                    <i class="ph ph-caret-down font-bold text-xs"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Footer -->
        <div class="px-6 py-4 bg-[#FCFDFE] border-t border-slate-200">
            @if(method_exists($invoices, 'links'))
                {{ $invoices->links() }}
            @endif
        </div>
    </div>

    <!-- Modal : Générer une Facture -->
    <div id="generateInvoiceModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-[#031C5B] ph-receipt text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold">Générer une Nouvelle Facture</h3>
                        <p class="text-xs text-blue-200 font-medium">Facturation Établissement Scolaire</p>
                    </div>
                </div>
                <button type="button" onclick="closeGenerateInvoiceModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('superadmin.invoices.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Établissement Client *</label>
                    <select name="school_id" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                        @if(isset($schools) && count($schools) > 0)
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->name }} ({{ $sch->location ?? 'Cameroun' }})</option>
                            @endforeach
                        @else
                            <option value="1">Lycée Technique de Yaoundé</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Montant de la Facture ({{ $systemCurrency ?? 'FCFA' }}) *</label>
                    <input type="number" name="amount" required value="350000" min="1000" step="5000" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Date d'Échéance de Paiement *</label>
                    <input type="date" name="due_date" required value="{{ date('Y-m-d', strtotime('+30 days')) }}" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Statut Initial *</label>
                    <select name="status" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-sm font-medium text-slate-900 focus:outline-none focus:border-indigo-500 focus:bg-white transition">
                        <option value="pending">En attente de règlement</option>
                        <option value="paid">Payée (Règlement immédiat)</option>
                    </select>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeGenerateInvoiceModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-50 transition cursor-pointer">
                        Annuler
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition shadow-sm flex items-center gap-2 cursor-pointer">
                        <i class="ph ph-check text-sm"></i> Créer la Facture
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal : Menu d'Actions Facture -->
    <div id="invoiceActionModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-receipt text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold" id="actionModalInvoiceNum">INV-2023-0891</h3>
                        <p class="text-xs text-blue-200 font-medium" id="actionModalSchoolName">Établissement</p>
                    </div>
                </div>
                <button type="button" onclick="closeInvoiceActionModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-lg font-bold"></i>
                </button>
            </div>

            <!-- Modal Menu Options -->
            <div class="p-3 space-y-1 divide-y divide-slate-100 text-xs">
                <!-- 1. Télécharger PDF -->
                <div class="pb-1 space-y-1">
                    <a id="actionLinkPdf" href="#" target="_blank" onclick="closeInvoiceActionModal()" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-50 text-slate-800 font-semibold transition">
                        <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center text-lg shrink-0">
                            <i class="ph ph-file-pdf font-bold"></i>
                        </div>
                        <div>
                            <span class="block text-slate-900 font-bold">Télécharger la Facture PDF</span>
                            <span class="block text-[11px] font-normal text-slate-400">Format officiel imprimable</span>
                        </div>
                    </a>

                    <!-- 2. Voir les Détails -->
                    <button type="button" onclick="openInvoiceDetailsModal()" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-50 text-slate-800 font-semibold transition text-left cursor-pointer">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                            <i class="ph ph-eye font-bold"></i>
                        </div>
                        <div>
                            <span class="block text-slate-900 font-bold">Voir les Détails de Facturation</span>
                            <span class="block text-[11px] font-normal text-slate-400">Montants & échéance</span>
                        </div>
                    </button>
                </div>

                <!-- 3. Relance & Payée -->
                <div class="py-1 space-y-1">
                    <!-- Renvoyer Relance -->
                    <form id="actionFormReminder" action="" method="POST" class="block">
                        @csrf
                        <button type="submit" onclick="closeInvoiceActionModal()" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-amber-50 text-slate-800 font-semibold transition text-left cursor-pointer">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-lg shrink-0">
                                <i class="ph ph-paper-plane-right font-bold"></i>
                            </div>
                            <div>
                                <span class="block text-amber-900 font-bold">Renvoyer Relance (SMS / Email)</span>
                                <span class="block text-[11px] font-normal text-amber-700">Rappel automatique d'échéance</span>
                            </div>
                        </button>
                    </form>

                    <!-- Marquer comme Payée -->
                    <form id="actionFormPay" action="" method="POST" class="block">
                        @csrf
                        <button type="submit" onclick="closeInvoiceActionModal()" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-emerald-50 text-slate-800 font-semibold transition text-left cursor-pointer">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0">
                                <i class="ph ph-check-circle font-bold"></i>
                            </div>
                            <div>
                                <span class="block text-emerald-900 font-bold">Marquer comme Payée</span>
                                <span class="block text-[11px] font-normal text-emerald-700">Valider le règlement Mobile Money</span>
                            </div>
                        </button>
                    </form>
                </div>

                <!-- 4. Annuler -->
                <div class="pt-1">
                    <form id="actionFormCancel" action="" method="POST" class="block" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette facture ?');">
                        @csrf
                        <button type="submit" onclick="closeInvoiceActionModal()" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-red-50 text-red-600 font-semibold transition text-left cursor-pointer">
                            <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center text-lg shrink-0">
                                <i class="ph ph-x-circle font-bold"></i>
                            </div>
                            <div>
                                <span class="block text-red-700 font-bold">Annuler la Facture</span>
                                <span class="block text-[11px] font-normal text-red-500">Invalider administrativement</span>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal : Voir les Détails de la Facture -->
    <div id="invoiceDetailsModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
            <div class="px-6 py-5 bg-[#031C5B] text-white flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <i class="ph ph-file-text text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold" id="detailsModalTitle">Fiche Facture</h3>
                        <p class="text-xs text-blue-200 font-medium" id="detailsModalSub">Academia ERP SaaS</p>
                    </div>
                </div>
                <button type="button" onclick="closeInvoiceDetailsModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer">
                    <i class="ph ph-x text-xl font-bold"></i>
                </button>
            </div>

            <div class="p-6 space-y-4 text-xs">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-2">
                    <div class="flex justify-between border-b border-slate-200/60 pb-2">
                        <span class="text-slate-500 font-medium">N° de Facture :</span>
                        <span class="font-bold text-[#031C5B]" id="detailsInvNum">INV-001</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200/60 pb-2">
                        <span class="text-slate-500 font-medium">Établissement Client :</span>
                        <span class="font-bold text-slate-800" id="detailsSchoolName">Lycée Yaoundé</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200/60 pb-2">
                        <span class="text-slate-500 font-medium">Montant Total :</span>
                        <span class="font-extrabold text-slate-900 text-sm" id="detailsAmount">350 000 FCFA</span>
                    </div>
                    <div class="flex justify-between border-b border-slate-200/60 pb-2">
                        <span class="text-slate-500 font-medium">Date d'Échéance :</span>
                        <span class="font-bold text-slate-800" id="detailsDueDate">15/11/2026</span>
                    </div>
                    <div class="flex justify-between pt-1">
                        <span class="text-slate-500 font-medium">Statut de la Facture :</span>
                        <span class="font-bold uppercase px-2.5 py-0.5 rounded-full text-[11px]" id="detailsStatus">En attente</span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="closeInvoiceDetailsModal()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition cursor-pointer">
                        Fermer
                    </button>
                    <a id="detailsPdfBtn" href="#" target="_blank" class="px-5 py-2 rounded-xl bg-[#031C5B] text-white text-xs font-bold hover:bg-blue-900 transition flex items-center gap-2">
                        <i class="ph ph-file-pdf"></i> Imprimer / PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentInvId = null;
        let currentInvNum = '';
        let currentSchoolName = '';
        let currentAmount = '';
        let currentStatus = '';
        let currentDueDate = '';

        function openGenerateInvoiceModal() {
            const modal = document.getElementById('generateInvoiceModal');
            if (modal) modal.classList.remove('hidden');
        }
        function closeGenerateInvoiceModal() {
            const modal = document.getElementById('generateInvoiceModal');
            if (modal) modal.classList.add('hidden');
        }

        function openInvoiceActionModal(id, invNum, schoolName, amount, status, dueDate) {
            currentInvId = id;
            currentInvNum = invNum;
            currentSchoolName = schoolName;
            currentAmount = amount;
            currentStatus = status;
            currentDueDate = dueDate;

            const numEl = document.getElementById('actionModalInvoiceNum');
            if (numEl) numEl.innerText = invNum + ' (' + amount + ' FCFA)';

            const schoolEl = document.getElementById('actionModalSchoolName');
            if (schoolEl) schoolEl.innerText = schoolName;

            // Update action URLs
            const pdfBtn = document.getElementById('actionLinkPdf');
            if (pdfBtn) pdfBtn.href = '/superadmin/invoices/' + id + '/pdf';

            const formRem = document.getElementById('actionFormReminder');
            if (formRem) formRem.action = '/superadmin/invoices/' + id + '/reminder';

            const formPay = document.getElementById('actionFormPay');
            if (formPay) formPay.action = '/superadmin/invoices/' + id + '/pay';

            const formCancel = document.getElementById('actionFormCancel');
            if (formCancel) formCancel.action = '/superadmin/invoices/' + id + '/cancel';

            const modal = document.getElementById('invoiceActionModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeInvoiceActionModal() {
            const modal = document.getElementById('invoiceActionModal');
            if (modal) modal.classList.add('hidden');
        }

        function openInvoiceDetailsModal() {
            closeInvoiceActionModal();

            const numEl = document.getElementById('detailsInvNum');
            if (numEl) numEl.innerText = currentInvNum;

            const schoolEl = document.getElementById('detailsSchoolName');
            if (schoolEl) schoolEl.innerText = currentSchoolName;

            const amountEl = document.getElementById('detailsAmount');
            if (amountEl) amountEl.innerText = currentAmount + ' FCFA';

            const dueEl = document.getElementById('detailsDueDate');
            if (dueEl) dueEl.innerText = currentDueDate;

            const stEl = document.getElementById('detailsStatus');
            if (stEl) stEl.innerText = currentStatus;

            const pdfBtn = document.getElementById('detailsPdfBtn');
            if (pdfBtn) pdfBtn.href = '/superadmin/invoices/' + currentInvId + '/pdf';

            const modal = document.getElementById('invoiceDetailsModal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeInvoiceDetailsModal() {
            const modal = document.getElementById('invoiceDetailsModal');
            if (modal) modal.classList.add('hidden');
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeGenerateInvoiceModal();
                closeInvoiceActionModal();
                closeInvoiceDetailsModal();
            }
        });
    </script>
@endsection
