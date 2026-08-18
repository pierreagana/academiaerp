@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::library._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Circulation</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez les emprunts, les retours et suivez l'inventaire actif.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50 border border-red-100" role="alert">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- New Loan -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[16px] font-bold text-[#031C5B] flex items-center gap-2"><i class="ph-bold ph-plus-circle"></i> Nouvel Emprunt</h3>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-full text-[10.5px] font-bold uppercase tracking-wider">Assisté par IA</span>
            </div>
            <form action="{{ route('school.library.circulation.loans.store') }}" method="POST" class="p-5 space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Type d'emprunteur <span class="text-red-500">*</span></label>
                    <select name="borrower_type" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        <option value="student" {{ old('borrower_type') === 'student' ? 'selected' : '' }}>Élève</option>
                        <option value="teacher" {{ old('borrower_type') === 'teacher' ? 'selected' : '' }}>Enseignant</option>
                        <option value="staff" {{ old('borrower_type') === 'staff' ? 'selected' : '' }}>Personnel</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">ID ou Nom de l'emprunteur <span class="text-red-500">*</span></label>
                    <input type="text" name="borrower_identifier" value="{{ old('borrower_identifier') }}" required placeholder="ex. STU-2023-041 ou Jean Dupont" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Titre ou ISBN du livre <span class="text-red-500">*</span></label>
                    <input type="text" name="book_identifier" value="{{ old('book_identifier') }}" required placeholder="Scanner ou saisir..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                </div>
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Date d'échéance</label>
                    <div class="w-full bg-slate-100 border border-slate-200 text-slate-500 text-[14px] rounded-lg px-4 py-2.5 flex items-center justify-between">
                        <span>{{ now()->addDays($settings->loan_duration_days)->format('d/m/Y') }}</span>
                        <span class="text-[11px] font-bold text-[#031C5B]">+{{ $settings->loan_duration_days }} jours (standard)</span>
                    </div>
                </div>
                <button type="submit" class="w-full py-3 bg-[#031C5B] text-white font-bold text-[14px] rounded-xl hover:bg-[#031C5B]/90 transition shadow-sm">
                    Enregistrer l'Emprunt
                </button>
            </form>

            <div class="p-5 border-t border-slate-100 bg-slate-50/60">
                <h4 class="text-[13.5px] font-bold text-slate-700 flex items-center gap-2 mb-3"><i class="ph-bold ph-arrow-u-up-left"></i> Retour Rapide</h4>
                <form action="{{ route('school.library.circulation.quick-return') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="book_identifier" required placeholder="Titre ou ISBN du livre..." class="flex-1 bg-white border border-slate-200 text-slate-900 text-[13px] rounded-lg px-3 py-2.5 outline-none focus:border-[#031C5B]">
                    <button type="submit" class="px-4 py-2.5 bg-emerald-700 text-white rounded-lg text-[13px] font-bold hover:bg-emerald-800 transition shrink-0">Retour</button>
                </form>
            </div>
        </div>

        <!-- Active Loans -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-[16px] font-bold text-slate-900">Emprunts Actifs</h3>
                <p class="text-[12.5px] text-slate-500 mt-0.5">Suivi en temps réel de l'inventaire en circulation.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC]">
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Emprunteur</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Livre</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut / Échéance</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($loans as $loan)
                            @php
                                $borrowerName = $loan->borrower ? ($loan->borrower->first_name . ' ' . $loan->borrower->last_name) : 'Inconnu';
                                $borrowerLabel = ['student' => 'Élève', 'teacher' => 'Enseignant', 'staff' => 'Personnel'][$loan->borrower_type] ?? $loan->borrower_type;
                                $identifier = $loan->borrower->roll_number ?? $loan->borrower->employee_id ?? '-';
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-[11px] shrink-0">
                                            {{ substr($borrowerName, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-[13px] font-bold text-slate-800">{{ $borrowerName }}</p>
                                            <p class="text-[11px] text-slate-500">{{ $borrowerLabel }} - {{ $identifier }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-[13px] font-semibold text-slate-700">{{ $loan->book->title ?? 'Livre supprimé' }}</td>
                                <td class="px-5 py-4">
                                    @if($loan->status === 'overdue')
                                        @php $daysOverdue = (int) ceil($loan->due_at->diffInDays(today(), true)); @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-red-100 text-red-700">
                                            <i class="ph-fill ph-warning"></i> En retard de {{ $daysOverdue }} j.
                                        </span>
                                    @elseif($loan->due_at->isToday())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-700">
                                            <i class="ph-fill ph-calendar-check"></i> Aujourd'hui
                                        </span>
                                    @else
                                        @php $daysLeft = (int) ceil(today()->diffInDays($loan->due_at, true)); @endphp
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-blue-100 text-blue-700">
                                            <i class="ph-fill ph-clock"></i> {{ $daysLeft }} j.
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($loan->status === 'overdue')
                                        <form action="{{ route('school.library.circulation.loans.remind', $loan->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 border border-slate-200 rounded-lg text-[11.5px] font-bold text-slate-600 hover:bg-slate-50 transition" title="{{ $loan->reminded_at ? 'Dernier rappel : ' . $loan->reminded_at->format('d/m/Y H:i') : '' }}">
                                                {{ $loan->reminded_at ? 'Rappelé' : 'Notifier' }}
                                            </button>
                                        </form>
                                        @endif
                                        <form action="{{ route('school.library.circulation.loans.return', $loan->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 bg-slate-800 text-white rounded-lg text-[11.5px] font-bold hover:bg-slate-900 transition">Retour</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun emprunt actif.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[12.5px] text-slate-500 font-semibold">
                    {{ $loans->count() }} sur {{ number_format($loans->total(), 0, ',', ' ') }} emprunts actifs
                </span>
                <div class="flex items-center">
                    {{ $loans->links('pagination::tailwind') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
