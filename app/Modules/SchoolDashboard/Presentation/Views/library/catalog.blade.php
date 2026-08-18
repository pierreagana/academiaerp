@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::library._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Catalogue</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez et suivez l'inventaire de votre bibliothèque.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center bg-slate-100 rounded-xl p-1">
                <a href="{{ route('school.library.catalog', array_merge($filters, ['view' => 'list'])) }}" class="p-2 rounded-lg transition {{ $view === 'list' ? 'bg-white shadow-sm text-[#031C5B]' : 'text-slate-400' }}">
                    <i class="ph-bold ph-list-bullets text-lg"></i>
                </a>
                <a href="{{ route('school.library.catalog', array_merge($filters, ['view' => 'grid'])) }}" class="p-2 rounded-lg transition {{ $view === 'grid' ? 'bg-white shadow-sm text-[#031C5B]' : 'text-slate-400' }}">
                    <i class="ph-bold ph-squares-four text-lg"></i>
                </a>
            </div>
            @include('SchoolDashboard::library._book_modal')
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
        <form method="GET" action="{{ route('school.library.catalog') }}" class="flex flex-col md:flex-row md:items-center gap-3">
            <input type="hidden" name="view" value="{{ $view }}">
            <div class="relative flex-1">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Rechercher par titre, auteur ou ISBN..." class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg pl-9 pr-3 py-2.5 outline-none focus:border-[#031C5B]">
            </div>
            <select name="category_id" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-[13px] font-semibold rounded-lg px-3 py-2.5">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ (string) $filters['category_id'] === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" class="bg-slate-50 border border-slate-200 text-slate-700 text-[13px] font-semibold rounded-lg px-3 py-2.5">
                <option value="">Tous les statuts</option>
                <option value="available" {{ $filters['status'] === 'available' ? 'selected' : '' }}>Disponible</option>
                <option value="low_stock" {{ $filters['status'] === 'low_stock' ? 'selected' : '' }}>Stock Faible</option>
                <option value="checked_out" {{ $filters['status'] === 'checked_out' ? 'selected' : '' }}>Emprunté</option>
            </select>
            <button type="submit" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-[13px] font-bold transition">Filtrer</button>
        </form>
    </div>

    <!-- AI Insight -->
    <div class="bg-purple-50/60 border border-purple-100 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 shrink-0"><i class="ph-fill ph-lightbulb text-lg"></i></div>
        <div>
            <p class="text-[11px] font-extrabold text-purple-600 uppercase tracking-wider mb-1">Insight IA</p>
            <p class="text-[13px] text-slate-700">La demande d'ouvrages liés au programme scolaire en cours peut fluctuer fortement selon le calendrier des cours. Pensez à vérifier régulièrement les niveaux de stock des titres les plus demandés.</p>
        </div>
    </div>

    @php $statusStyles = ['available' => ['bg-emerald-100 text-emerald-700', 'Disponible'], 'checked_out' => ['bg-slate-200 text-slate-600', 'Emprunté'], 'low_stock' => ['bg-red-100 text-red-700', 'Stock Faible']]; @endphp

    @if($view === 'grid')
    <!-- Grid View -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @forelse($books as $book)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4">
            <div class="w-full h-32 rounded-xl bg-slate-100 flex items-center justify-center overflow-hidden mb-3">
                @if($book->cover_path)
                    <img src="{{ asset('storage/' . $book->cover_path) }}" class="w-full h-full object-cover">
                @else
                    <i class="ph-fill ph-book text-slate-400 text-3xl"></i>
                @endif
            </div>
            <p class="text-[13.5px] font-bold text-slate-800 leading-snug">{{ $book->title }}</p>
            <p class="text-[12px] text-slate-500 mt-0.5">{{ $book->author ?? '-' }}</p>
            <div class="flex items-center justify-between mt-3">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-[10.5px] font-bold {{ $statusStyles[$book->status][0] }}">{{ $statusStyles[$book->status][1] }}</span>
                <a href="{{ route('school.library.catalog', array_merge($filters, ['view' => 'list'])) }}" class="p-1.5 text-slate-400 hover:text-[#031C5B] rounded-lg transition" title="Modifier (vue liste)">
                    <i class="ph-bold ph-pencil-simple"></i>
                </a>
            </div>
        </div>
        @empty
        <p class="text-slate-400 text-[13px] col-span-full text-center py-10">Aucun livre trouvé.</p>
        @endforelse
    </div>
    @else
    <!-- List View -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-[#F8FAFC]">
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Détails du Livre</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">ISBN</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Quantité</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">Statut</th>
                        <th class="px-5 py-3 text-[11px] font-extrabold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($books as $book)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-11 rounded-md bg-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                                    @if($book->cover_path)
                                        <img src="{{ asset('storage/' . $book->cover_path) }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="ph-fill ph-book text-slate-400"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-[13.5px] font-bold text-slate-800">{{ $book->title }}</p>
                                    <p class="text-[12px] text-slate-500">{{ $book->author ?? '-' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $book->category->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-semibold text-slate-600">{{ $book->isbn ?? '-' }}</td>
                        <td class="px-5 py-4 text-[13px] font-bold text-slate-700">
                            {{ $book->quantity_available }} <span class="text-slate-400 font-medium">/ {{ $book->quantity_total }}</span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusStyles[$book->status][0] }}">{{ $statusStyles[$book->status][1] }}</span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div x-data="{ open: false }" class="inline-flex items-center gap-1">
                                <button type="button" @click="open = true" class="p-1.5 text-slate-400 hover:text-[#031C5B] rounded-lg transition"><i class="ph-bold ph-pencil-simple"></i></button>
                                <form action="{{ route('school.library.catalog.destroy', $book->id) }}" method="POST" onsubmit="return confirm('Supprimer ce livre du catalogue ?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 rounded-lg transition"><i class="ph-bold ph-trash"></i></button>
                                </form>

                                <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
                                    <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden text-left">
                                        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                                            <h3 class="text-[18px] font-extrabold text-[#031C5B]">Modifier le Livre</h3>
                                            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
                                        </div>
                                        <form action="{{ route('school.library.catalog.update', $book->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                                            @csrf
                                            @method('PUT')
                                            <div class="space-y-1.5">
                                                <label class="block text-[13px] font-bold text-slate-700">Titre <span class="text-red-500">*</span></label>
                                                <input type="text" name="title" value="{{ $book->title }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="space-y-1.5">
                                                    <label class="block text-[13px] font-bold text-slate-700">Auteur</label>
                                                    <input type="text" name="author" value="{{ $book->author }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="block text-[13px] font-bold text-slate-700">ISBN</label>
                                                    <input type="text" name="isbn" value="{{ $book->isbn }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                                                </div>
                                            </div>
                                            <div class="grid grid-cols-2 gap-4">
                                                <div class="space-y-1.5">
                                                    <label class="block text-[13px] font-bold text-slate-700">Catégorie</label>
                                                    <select name="category_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                                                        <option value="">Aucune</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}" {{ $book->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="space-y-1.5">
                                                    <label class="block text-[13px] font-bold text-slate-700">Quantité totale <span class="text-red-500">*</span></label>
                                                    <input type="number" name="quantity_total" min="0" value="{{ $book->quantity_total }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                                                    <p class="text-[11px] text-slate-400">{{ $book->quantity_total - $book->quantity_available }} actuellement emprunté(s)</p>
                                                </div>
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="block text-[13px] font-bold text-slate-700">Couverture</label>
                                                <input type="file" name="cover" accept="image/*" class="w-full text-[13px] text-slate-600">
                                            </div>
                                            <div class="flex justify-end gap-3 pt-2">
                                                <button type="button" @click="open = false" class="px-5 py-2.5 border border-slate-200 rounded-xl text-[13px] font-bold text-slate-600 hover:bg-slate-50 transition">Annuler</button>
                                                <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Enregistrer</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500 font-medium">Aucun livre trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <span class="text-[13px] text-slate-500 font-semibold">
            Affichage {{ $books->firstItem() ?? 0 }} à {{ $books->lastItem() ?? 0 }} sur {{ number_format($books->total(), 0, ',', ' ') }} entrées
        </span>
        <div class="flex items-center">
            {{ $books->links('pagination::tailwind') }}
        </div>
    </div>
</div>
@endsection
