@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Gestion des Parents</h2>
            <p class="text-slate-500 text-sm mt-1">Gérez les comptes, les communications et les accès des parents d'élèves.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.academic.parents.create') }}" class="flex items-center gap-2 bg-[#031C5B] text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-[#031C5B]/90 transition shadow-sm">
                <i class="ph ph-plus"></i>
                Ajouter un parent
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 flex items-center gap-2" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card 1 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 uppercase tracking-wider">
                    +12% Ce mois
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#F8FAFC] border border-slate-100 flex items-center justify-center text-[#031C5B] mb-4">
                <i class="ph ph-users text-2xl"></i>
            </div>
            <h3 class="text-3xl font-bold text-slate-800 mb-1">{{ number_format($stats['total'], 0, ',', ' ') }}</h3>
            <p class="text-sm text-slate-500 font-medium">Total Parents Inscrits</p>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-600 uppercase tracking-wider">
                    Taux d'adoption 89%
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-[#F8FAFC] border border-slate-100 flex items-center justify-center text-emerald-600 mb-4">
                <i class="ph ph-check-circle text-2xl"></i>
            </div>
            <h3 class="text-3xl font-bold text-slate-800 mb-1">{{ number_format($stats['active'], 0, ',', ' ') }}</h3>
            <p class="text-sm text-slate-500 font-medium">Comptes Actifs</p>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[10px] font-bold bg-purple-50 text-purple-600 uppercase tracking-wider">
                    <i class="ph-fill ph-sparkle"></i> AI Relance suggérée
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-600 mb-4">
                <i class="ph ph-paper-plane-tilt text-2xl"></i>
            </div>
            <h3 class="text-3xl font-bold text-slate-800 mb-1">{{ number_format($stats['pending'], 0, ',', ' ') }}</h3>
            <p class="text-sm text-slate-500 font-medium mb-3">Invitations en attente</p>
            <a href="#" class="inline-flex items-center gap-1 text-sm font-semibold text-purple-600 hover:text-purple-700 transition">
                Lancer la relance automatique <i class="ph ph-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden flex flex-col">
        <!-- Toolbar -->
        <div class="p-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/50">
            <div class="relative w-full sm:max-w-xs">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="ph ph-magnifying-glass text-slate-400 text-lg"></i>
                </div>
                <input type="text" class="block w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl leading-5 bg-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#031C5B] focus:border-[#031C5B] sm:text-sm transition shadow-sm" placeholder="Rechercher par nom, email...">
            </div>
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <select class="block w-full sm:w-auto pl-3 pr-10 py-2 text-base border-slate-200 focus:outline-none focus:ring-[#031C5B] focus:border-[#031C5B] sm:text-sm rounded-xl bg-white shadow-sm border">
                    <option>Toutes les classes</option>
                </select>
                <select class="block w-full sm:w-auto pl-3 pr-10 py-2 text-base border-slate-200 focus:outline-none focus:ring-[#031C5B] focus:border-[#031C5B] sm:text-sm rounded-xl bg-white shadow-sm border">
                    <option>Tous les statuts</option>
                </select>
                <button class="p-2 border border-slate-200 rounded-xl bg-white hover:bg-slate-50 text-slate-600 transition shadow-sm">
                    <i class="ph ph-funnel text-lg"></i>
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[13px] uppercase tracking-wider font-semibold">
                        <th class="px-4 py-4 w-12 text-center">
                            <input type="checkbox" class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                        </th>
                        <th class="px-4 py-4">Parent</th>
                        <th class="px-4 py-4">Contact</th>
                        <th class="px-4 py-4">Élèves associés</th>
                        <th class="px-4 py-4">Statut</th>
                        <th class="px-4 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-sm">
                    @forelse($guardians as $guardian)
                    <tr class="hover:bg-slate-50 transition group">
                        <td class="px-4 py-4 text-center">
                            <input type="checkbox" class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#031C5B]/10 text-[#031C5B] flex items-center justify-center font-bold text-sm uppercase">
                                    {{ substr($guardian->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $guardian->name }}</div>
                                    <div class="text-xs text-slate-500 font-medium capitalize">{{ $guardian->relation }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <div class="text-slate-800 font-medium">{{ $guardian->email ?? 'N/A' }}</div>
                            <div class="text-slate-500 text-xs mt-0.5">{{ $guardian->phone }}</div>
                        </td>
                        <td class="px-4 py-4">
                            <ul class="text-slate-600 text-sm space-y-1">
                                @forelse($guardian->students as $student)
                                    <li>{{ $student->first_name }} {{ $student->last_name }}</li>
                                @empty
                                    <li class="text-slate-400 text-xs italic">Aucun élève lié pour le moment</li>
                                @endforelse
                            </ul>
                        </td>
                        <td class="px-4 py-4">
                            @if($guardian->status == 'active')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-1.5"></span> Actif
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span> En attente
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right">
                            <div class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="{{ route('school.academic.parents.edit', $guardian->id) }}" class="p-1.5 text-slate-400 hover:text-[#031C5B] hover:bg-blue-50 rounded-lg transition" title="Modifier">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </a>
                                <form action="{{ route('school.academic.parents.destroy', $guardian->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce parent ?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Supprimer">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500">
                            Aucun parent trouvé. <a href="{{ route('school.academic.parents.create') }}" class="text-[#031C5B] font-semibold hover:underline">Ajouter un parent</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white">
            <div class="text-sm text-slate-600 font-medium">
                Affichage de <span class="font-bold text-slate-800">{{ $guardians->firstItem() ?? 0 }}</span> à <span class="font-bold text-slate-800">{{ $guardians->lastItem() ?? 0 }}</span> sur <span class="font-bold text-slate-800">{{ number_format($guardians->total(), 0, ',', ' ') }}</span> parents
            </div>
            <div class="flex items-center gap-1">
                {{ $guardians->links('pagination::tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection
