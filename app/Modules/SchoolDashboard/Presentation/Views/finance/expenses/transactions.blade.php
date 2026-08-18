@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::finance.expenses._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Transactions</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez et suivez l'historique détaillé des dépenses de l'établissement.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.finance.expenses.transactions.export.excel', request()->query()) }}" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                <i class="ph-bold ph-download-simple text-lg"></i> Exporter Excel
            </a>
            <a href="{{ route('school.finance.expenses.transactions.export.pdf') }}" target="_blank" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-600 px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition">
                <i class="ph-bold ph-file-pdf text-lg"></i> Exporter PDF
            </a>
            <a href="{{ route('school.finance.expenses.create') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph-bold ph-plus text-lg"></i> Ajouter
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Filters -->
    <form action="{{ route('school.finance.expenses.transactions') }}" method="GET" class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Période</label>
            <select name="period" onchange="this.form.submit()" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Ce mois-ci</option>
                <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Mois dernier</option>
                <option value="this_year" {{ $period === 'this_year' ? 'selected' : '' }}>Cette année</option>
                <option value="all" {{ $period === 'all' ? 'selected' : '' }}>Toutes les dates</option>
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Catégorie</label>
            <select name="category" onchange="this.form.submit()" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>
        <div class="space-y-1.5">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider">Statut</label>
            <select name="status" onchange="this.form.submit()" class="w-full bg-[#F8FAFC] border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                <option value="">Tous les statuts</option>
                @foreach(\App\Modules\Finance\Domain\Models\Expense::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full px-5 py-2.5 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition">Appliquer</button>
        </div>
    </form>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Date</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Libellé</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Bénéficiaire</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Montant</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-4 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $e)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $e->expense_date->format('d M Y') }}</td>
                        <td class="px-5 py-4">
                            <div class="font-extrabold text-[#0F172A] text-[14px]">{{ $e->title }}</div>
                            <div class="text-[11px] text-slate-400">Réf: {{ $e->reference }}</div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded">
                                <i class="ph-fill {{ \App\Modules\Finance\Domain\Models\Expense::iconFor($e->category) }}"></i> {{ $e->category }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $e->payee ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-bold text-slate-800">{{ number_format($e->amount, 0, ',', ' ') }} FCFA</td>
                        <td class="px-5 py-4">
                            @php
                                $statusStyles = ['approved' => 'bg-emerald-50 text-emerald-700', 'pending' => 'bg-violet-50 text-violet-700', 'rejected' => 'bg-red-50 text-red-700'];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[12px] font-bold {{ $statusStyles[$e->status] ?? '' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span> {{ \App\Modules\Finance\Domain\Models\Expense::STATUSES[$e->status] ?? $e->status }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($e->proof_path)
                                <a href="{{ asset('storage/' . $e->proof_path) }}" target="_blank" class="text-slate-400 hover:text-slate-600" title="Voir la preuve"><i class="ph-bold ph-paperclip"></i></a>
                                @endif
                                @if($e->status === 'pending')
                                <form action="{{ route('school.finance.expenses.approve', $e->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-emerald-500 hover:text-emerald-700" title="Valider"><i class="ph-bold ph-check-circle"></i></button>
                                </form>
                                <form action="{{ route('school.finance.expenses.reject', $e->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-red-500 hover:text-red-700" title="Rejeter"><i class="ph-bold ph-x-circle"></i></button>
                                </form>
                                @endif
                                <form action="{{ route('school.finance.expenses.destroy', $e->id) }}" method="POST" class="inline" onsubmit="return confirm('Supprimer cette dépense ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-red-600" title="Supprimer"><i class="ph-bold ph-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-500 font-medium">Aucune dépense trouvée.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-5 border-t border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="text-[13px] text-slate-500 font-semibold">
                Affichage {{ $transactions->firstItem() ?? 0 }} à {{ $transactions->lastItem() ?? 0 }} sur {{ number_format($transactions->total(), 0, ',', ' ') }} transactions
            </div>
            <div class="flex items-center">
                {{ $transactions->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection
