@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6" x-data="{ createOpen: false, typesOpen: false }">
    @include('SchoolDashboard::hr._tabs')

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Suivi des Contrats</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Créez et suivez les contrats du personnel, avec rappels d'échéance.</p>
        </div>
        <button @click="createOpen = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
            <i class="ph-bold ph-plus"></i> Nouveau Contrat
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Contrats Actifs</p>
            <h3 class="text-3xl font-bold text-slate-800">{{ $stats['active'] }}</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">À Rappeler</p>
            <h3 class="text-3xl font-bold text-amber-600">{{ $stats['needsReminder'] }}</h3>
        </div>
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Expirés</p>
            <h3 class="text-3xl font-bold text-red-600">{{ $stats['expired'] }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Contrats list -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-lg font-bold text-slate-900">Tous les Contrats</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F8FAFC]">
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Employé</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Type</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Échéance</th>
                            <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Rappel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $contract)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-5 py-4">
                                <p class="text-[13.5px] font-bold text-slate-800">{{ $contract->holder->first_name ?? '?' }} {{ $contract->holder->last_name ?? '' }}</p>
                                <p class="text-[11px] text-slate-500">{{ $contract->holder_type === 'teacher' ? 'Enseignant' : 'Personnel' }}</p>
                            </td>
                            <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $contract->contract_type }}</td>
                            <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">
                                {{ $contract->end_date ? $contract->end_date->format('d M Y') : 'N/A (Permanent)' }}
                                @if($contract->is_expired)
                                    <span class="block px-2 py-0.5 mt-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700 w-fit">Expiré</span>
                                @elseif($contract->status === 'terminated')
                                    <span class="block px-2 py-0.5 mt-1 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600 w-fit">Résilié</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($contract->needs_reminder)
                                    <form method="POST" action="{{ route('school.hr.contracts.acknowledge', $contract->id) }}" class="flex items-center gap-2">
                                        @csrf
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 whitespace-nowrap">Dans {{ (int) ceil(\Illuminate\Support\Carbon::today()->diffInDays($contract->end_date, true)) }}j</span>
                                        <button type="submit" class="text-[11.5px] font-bold text-[#031C5B] hover:underline whitespace-nowrap">Marquer Rappelé</button>
                                    </form>
                                @elseif($contract->reminder_acknowledged_at)
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">Rappelé</span>
                                @else
                                    <span class="text-[12.5px] text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun contrat enregistré.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Types de Contrat -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 h-fit">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-bold ph-tag text-[#031C5B]"></i> Types de Contrat</h3>
            </div>
            <div class="space-y-2 mb-4">
                @forelse($types as $type)
                <div class="flex items-center justify-between border border-slate-100 rounded-xl px-3 py-2.5">
                    <span class="text-[13px] font-semibold text-slate-700">{{ $type->name }}</span>
                    <form method="POST" action="{{ route('school.hr.contracts.types.destroy', $type->id) }}" onsubmit="return confirm('Supprimer ce type de contrat ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-red-600"><i class="ph-bold ph-x"></i></button>
                    </form>
                </div>
                @empty
                <p class="text-slate-400 text-[13px] text-center py-4">Aucun type configuré.</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('school.hr.contracts.types.store') }}" class="flex items-center gap-2">
                @csrf
                <input type="text" name="name" required placeholder="Ex: Stage" class="flex-1 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-[13px] outline-none focus:border-[#031C5B]">
                <button type="submit" class="w-9 h-9 shrink-0 flex items-center justify-center bg-[#031C5B] text-white rounded-lg hover:bg-[#031C5B]/90 transition"><i class="ph-bold ph-plus"></i></button>
            </form>
        </div>
    </div>

    <!-- Modal: Nouveau Contrat -->
    <div x-show="createOpen" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4 overflow-y-auto" style="display: none;">
        <div @click.outside="createOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 my-8">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-[17px] font-bold text-[#031C5B]">Nouveau Contrat</h3>
                <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
            </div>
            <form method="POST" action="{{ route('school.hr.contracts.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Employé</label>
                    <select name="holder" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                        <option value="">Sélectionner...</option>
                        <optgroup label="Enseignants">
                            @foreach($teachers as $teacher)
                                <option value="teacher:{{ $teacher->id }}">{{ $teacher->first_name }} {{ $teacher->last_name }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Personnel">
                            @foreach($staff as $member)
                                <option value="staff:{{ $member->id }}">{{ $member->first_name }} {{ $member->last_name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Type de Contrat</label>
                    <select name="contract_type" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                        @foreach($types as $type)
                            <option value="{{ $type->name }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Début</label>
                        <input type="date" name="start_date" required value="{{ now()->toDateString() }}" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Fin <span class="text-slate-400 font-medium">(vide si CDI)</span></label>
                        <input type="date" name="end_date" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    </div>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Rappel avant échéance (jours)</label>
                    <input type="number" name="reminder_days_before" min="1" max="365" value="30" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Notes</label>
                    <textarea name="notes" rows="2" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]"></textarea>
                </div>
                <button type="submit" class="w-full mt-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Créer le Contrat</button>
            </form>
        </div>
    </div>
</div>
@endsection
