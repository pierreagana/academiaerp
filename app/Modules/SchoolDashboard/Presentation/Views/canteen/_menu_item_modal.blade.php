@php
    $itemTags = $item->tags ?? [];
    $itemAllergens = $item->allergens ?? [];
@endphp
<div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
    <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden text-left">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-[16px] font-extrabold text-[#031C5B]">{{ $slotLabel }} — {{ $day->translatedFormat('l d F') }}</h3>
            <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x text-lg"></i></button>
        </div>
        <form action="{{ route('school.canteen.planning.items.store') }}" method="POST" class="p-6 space-y-4 max-h-[70vh] overflow-y-auto">
            @csrf
            @if($item)
                <input type="hidden" name="id" value="{{ $item->id }}">
            @endif
            <input type="hidden" name="date" value="{{ $day->format('Y-m-d') }}">
            <input type="hidden" name="slot" value="{{ $slotKey }}">
            <input type="hidden" name="week" value="{{ $week }}">
            <div class="space-y-1.5">
                <label class="block text-[13px] font-bold text-slate-700">Nom du plat <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ $item->title ?? '' }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
            </div>
            <div class="space-y-1.5">
                <label class="block text-[13px] font-bold text-slate-700">Description</label>
                <input type="text" name="description" value="{{ $item->description ?? '' }}" placeholder="Ex: Riz et haricots verts" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
            </div>
            <div class="space-y-1.5">
                <label class="block text-[13px] font-bold text-slate-700">Étiquettes</label>
                @if($tags->isEmpty())
                    <p class="text-[12px] text-slate-400">Aucune étiquette. Ajoutez-en une ci-contre.</p>
                @else
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5">
                        @foreach($tags as $tag)
                            <label class="flex items-center gap-1.5 text-[12.5px] font-semibold text-slate-600">
                                <input type="checkbox" name="tags[]" value="{{ $tag->name }}" {{ in_array($tag->name, $itemTags) ? 'checked' : '' }} class="rounded border-slate-300 text-[#031C5B]"> {{ $tag->name }}
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="space-y-1.5">
                <label class="block text-[13px] font-bold text-slate-700">Allergènes</label>
                @if($allergens->isEmpty())
                    <p class="text-[12px] text-slate-400">Aucun allergène. Ajoutez-en un ci-contre.</p>
                @else
                    <div class="grid grid-cols-2 gap-x-3 gap-y-1.5">
                        @foreach($allergens as $allergen)
                            <label class="flex items-center gap-1.5 text-[12px] font-semibold text-slate-600">
                                <input type="checkbox" name="allergens[]" value="{{ $allergen->name }}" {{ in_array($allergen->name, $itemAllergens) ? 'checked' : '' }} class="rounded border-slate-300 text-amber-600"> {{ $allergen->name }}
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex items-center justify-between gap-3 pt-2">
                @if($item)
                <button type="button" onclick="document.getElementById('delete-item-{{ $item->id }}').submit()" class="text-[12.5px] font-bold text-red-500 hover:text-red-700">Supprimer</button>
                @else
                <span></span>
                @endif
                <div class="flex items-center gap-3">
                    <button type="button" @click="open = false" class="px-4 py-2.5 border border-slate-200 rounded-xl text-[13px] font-bold text-slate-600 hover:bg-slate-50 transition">Annuler</button>
                    <button type="submit" class="px-5 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Enregistrer</button>
                </div>
            </div>
        </form>
        @if($item)
        <form id="delete-item-{{ $item->id }}" action="{{ route('school.canteen.planning.items.destroy', $item->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
            <input type="hidden" name="week" value="{{ $week }}">
        </form>
        @endif
    </div>
</div>
