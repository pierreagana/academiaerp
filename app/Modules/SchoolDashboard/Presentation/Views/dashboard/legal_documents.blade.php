@extends('SchoolDashboard::layouts.app')

@section('title', 'Documents Légaux')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-slate-800">Documents Légaux</h2>
        <a href="{{ route('school.establishment') }}" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-xl hover:bg-slate-300 transition">
            Retour
        </a>
    </div>

    <p class="text-sm text-slate-500">Ces documents apparaissent dans l'espace Paramètres des parents, qui peuvent les consulter et les signer numériquement (règlement intérieur, droit à l'image, etc.).</p>

    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- UPLOAD FORM -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
        <h3 class="text-sm font-bold text-slate-800 mb-4">Ajouter un document</h3>
        <form action="{{ route('school.legal-documents.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
            @csrf
            <div class="flex-1 w-full">
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Titre</label>
                <input type="text" name="title" required placeholder="Ex: Règlement Intérieur"
                       class="w-full border border-slate-200 rounded-xl px-4 py-2.5 text-sm outline-none focus:border-primary-dynamic">
            </div>
            <div class="flex-1 w-full">
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Fichier PDF</label>
                <input type="file" name="file" accept="application/pdf" required
                       class="w-full border border-slate-200 rounded-xl px-4 py-2 text-sm outline-none focus:border-primary-dynamic">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-primary-dynamic text-white font-bold rounded-xl hover:opacity-90 transition shrink-0">
                Publier
            </button>
        </form>
    </div>

    <!-- DOCUMENTS LIST -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="border-b border-slate-100 text-xs font-bold text-slate-400 uppercase">
                    <th class="px-5 py-3">Titre</th>
                    <th class="px-5 py-3">Ajouté le</th>
                    <th class="px-5 py-3">Signatures</th>
                    <th class="px-5 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($documents as $doc)
                <tr>
                    <td class="px-5 py-3 font-bold text-slate-800">{{ $doc->title }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $doc->created_at->translatedFormat('d/m/Y') }}</td>
                    <td class="px-5 py-3 text-slate-500">{{ $doc->signatures_count }}</td>
                    <td class="px-5 py-3 text-right">
                        <form action="{{ route('school.legal-documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Supprimer ce document ?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-5 py-8 text-center text-slate-400">Aucun document publié.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
