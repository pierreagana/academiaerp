@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::canteen._tabs')

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Planification Hebdomadaire</h2>
            <p class="text-slate-600 text-[15px] font-medium mt-1">Semaine du {{ $current->translatedFormat('d') }} au {{ $current->copy()->addDays(4)->translatedFormat('d F Y') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('school.canteen.planning.print', ['week' => $week]) }}" target="_blank" class="flex items-center gap-2 bg-white border border-slate-200 text-slate-700 px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-slate-50 transition shadow-sm">
                <i class="ph-bold ph-printer text-lg"></i> Fiches Cuisine
            </a>
            <form action="{{ route('school.canteen.planning.publish') }}" method="POST">
                @csrf
                <input type="hidden" name="week" value="{{ $week }}">
                <button type="submit" class="flex items-center gap-2 bg-[#031C5B] text-white px-4 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
                    <i class="ph-bold ph-upload-simple text-lg"></i> Publier le Menu
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if($menuWeek->published_at)
    <div class="p-3 text-[12.5px] text-blue-700 bg-blue-50 rounded-xl border border-blue-100 flex items-center gap-2">
        <i class="ph-fill ph-info"></i> Menu publié le {{ $menuWeek->published_at->format('d/m/Y à H:i') }}.
    </div>
    @endif

    <div class="flex items-center justify-center gap-3">
        <a href="{{ route('school.canteen.planning', ['week' => $prevWeek]) }}" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition"><i class="ph-bold ph-caret-left"></i></a>
        <span class="text-[13px] font-bold text-slate-600">Semaine du {{ $current->format('d/m/Y') }}</span>
        <a href="{{ route('school.canteen.planning', ['week' => $nextWeek]) }}" class="w-9 h-9 flex items-center justify-center rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition"><i class="ph-bold ph-caret-right"></i></a>
    </div>

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Left Sidebar -->
        <div class="w-full lg:w-72 shrink-0 space-y-4">
            <div class="bg-gradient-to-br from-[#F5F3FF] to-purple-50/50 border border-purple-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <i class="ph-fill ph-sparkle text-purple-600 text-lg"></i>
                    <h3 class="font-extrabold text-slate-800 text-[14px]">Assistant IA</h3>
                </div>
                <p class="text-[12.5px] text-slate-600 font-medium leading-relaxed">
                    Basé sur les produits locaux de saison et les recommandations diététiques, pensez à varier les sources de protéines et à surveiller les produits bientôt périmés dans votre stock.
                </p>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-[14px] font-bold text-slate-800 mb-3">Stock Critique</h3>
                <div class="space-y-2.5">
                    @forelse($criticalProducts as $product)
                        <div class="flex items-center justify-between">
                            <span class="text-[12.5px] font-semibold text-slate-600 truncate">{{ $product->name }} ({{ $product->unit }})</span>
                            <span class="text-[12.5px] font-bold {{ $product->status === 'critical' ? 'text-red-600' : 'text-amber-600' }} shrink-0 ml-2">Reste {{ $product->quantity }}</span>
                        </div>
                    @empty
                        <p class="text-[12.5px] text-slate-400">Aucun produit critique.</p>
                    @endforelse
                </div>
            </div>

            <!-- Manage Tags -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" x-data="{ adding: false }">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[14px] font-bold text-slate-800">Étiquettes</h3>
                    <button type="button" @click="adding = !adding" class="w-6 h-6 rounded-full bg-[#031C5B] text-white flex items-center justify-center hover:bg-[#031C5B]/90 transition"><i class="ph-bold ph-plus text-[12px]"></i></button>
                </div>
                <div x-show="adding" x-cloak class="mb-3">
                    <form action="{{ route('school.canteen.planning.tags.store') }}" method="POST" class="flex items-center gap-1.5">
                        @csrf
                        <input type="text" name="name" required placeholder="Ex: Halal" class="flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-[12.5px] rounded-lg px-2.5 py-1.5 outline-none focus:border-[#031C5B]">
                        <button type="submit" class="px-2.5 py-1.5 bg-[#031C5B] text-white rounded-lg text-[11.5px] font-bold">OK</button>
                    </form>
                </div>
                <div class="space-y-1.5">
                    @forelse($tags as $tag)
                        <div class="flex items-center justify-between">
                            <span class="text-[12.5px] font-semibold text-slate-600 truncate">{{ $tag->name }}</span>
                            <form action="{{ route('school.canteen.planning.tags.destroy', $tag->id) }}" method="POST" onsubmit="return confirm('Retirer cette étiquette ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition"><i class="ph-bold ph-x text-[12px]"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-[12.5px] text-slate-400">Aucune étiquette.</p>
                    @endforelse
                </div>
            </div>

            <!-- Manage Allergens -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" x-data="{ adding: false }">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-[14px] font-bold text-slate-800">Allergènes</h3>
                    <button type="button" @click="adding = !adding" class="w-6 h-6 rounded-full bg-[#031C5B] text-white flex items-center justify-center hover:bg-[#031C5B]/90 transition"><i class="ph-bold ph-plus text-[12px]"></i></button>
                </div>
                <div x-show="adding" x-cloak class="mb-3">
                    <form action="{{ route('school.canteen.planning.allergens.store') }}" method="POST" class="flex items-center gap-1.5">
                        @csrf
                        <input type="text" name="name" required placeholder="Ex: Sésame" class="flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-[12.5px] rounded-lg px-2.5 py-1.5 outline-none focus:border-[#031C5B]">
                        <button type="submit" class="px-2.5 py-1.5 bg-[#031C5B] text-white rounded-lg text-[11.5px] font-bold">OK</button>
                    </form>
                </div>
                <div class="space-y-1.5">
                    @forelse($allergens as $allergen)
                        <div class="flex items-center justify-between">
                            <span class="text-[12.5px] font-semibold text-slate-600 truncate">{{ $allergen->name }}</span>
                            <form action="{{ route('school.canteen.planning.allergens.destroy', $allergen->id) }}" method="POST" onsubmit="return confirm('Retirer cet allergène ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition"><i class="ph-bold ph-x text-[12px]"></i></button>
                            </form>
                        </div>
                    @empty
                        <p class="text-[12.5px] text-slate-400">Aucun allergène.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="flex-1 overflow-x-auto">
            @php
                $slots = ['breakfast' => 'Petit Déjeuner', 'starter' => 'Entrée', 'main' => 'Plat', 'dessert' => 'Dessert'];
                $days = [];
                for ($i = 0; $i < 5; $i++) { $days[] = $current->copy()->addDays($i); }
            @endphp
            <div class="min-w-[760px] bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                <div class="grid grid-cols-5 border-b border-slate-100 bg-[#F8FAFC]">
                    @foreach($days as $day)
                        <div class="px-3 py-3 text-center border-r border-slate-100 last:border-r-0">
                            <p class="text-[11px] font-extrabold text-slate-500 uppercase tracking-wider">{{ $day->translatedFormat('D') }}</p>
                            <p class="text-[15px] font-extrabold text-[#031C5B]">{{ $day->format('d') }}</p>
                        </div>
                    @endforeach
                </div>

                @foreach($slots as $slotKey => $slotLabel)
                <div class="grid grid-cols-5 border-b border-slate-100 last:border-b-0">
                    @foreach($days as $day)
                        @php
                            $cellKey = $day->format('Y-m-d') . '-' . $slotKey;
                            $cellItems = $itemsByCell->get($cellKey, collect());
                        @endphp
                        <div class="p-2.5 border-r border-slate-100 last:border-r-0 min-h-[110px] space-y-1.5">
                            @foreach($cellItems as $existingItem)
                                <div x-data="{ open: false }">
                                    <div @click="open = true" class="p-2.5 rounded-xl bg-blue-50/60 border border-blue-100 cursor-pointer hover:bg-blue-50 transition">
                                        <p class="text-[13px] font-bold text-slate-800 leading-snug">{{ $existingItem->title }}</p>
                                        @if($existingItem->description)
                                            <p class="text-[11px] text-slate-500 mt-1">{{ $existingItem->description }}</p>
                                        @endif
                                        @if($existingItem->tags)
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach($existingItem->tags as $tag)
                                                    <span class="px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-emerald-100 text-emerald-700">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($existingItem->allergens)
                                            <div class="flex flex-wrap gap-1 mt-1.5">
                                                @foreach($existingItem->allergens as $allergen)
                                                    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[9.5px] font-bold bg-amber-100 text-amber-700">
                                                        <i class="ph-fill ph-warning text-[10px]"></i> {{ $allergen }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    @include('SchoolDashboard::canteen._menu_item_modal', ['item' => $existingItem])
                                </div>
                            @endforeach

                            <div x-data="{ open: false }">
                                <button type="button" @click="open = true" class="w-full rounded-xl border-2 border-dashed border-slate-200 text-slate-400 hover:border-[#031C5B] hover:text-[#031C5B] transition flex flex-col items-center justify-center gap-1 py-3 {{ $cellItems->isEmpty() ? 'h-[100px]' : '' }}">
                                    <i class="ph-bold ph-plus text-lg"></i>
                                    <span class="text-[11px] font-bold">Ajouter</span>
                                </button>
                                @include('SchoolDashboard::canteen._menu_item_modal', ['item' => null])
                            </div>
                        </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
