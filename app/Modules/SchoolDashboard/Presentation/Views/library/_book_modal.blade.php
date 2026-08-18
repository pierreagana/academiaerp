@php
    $hasBookErrors = $errors->hasAny(['title', 'author', 'isbn', 'category_id', 'quantity_total', 'cover']);
@endphp
<div x-data="{ open: {{ $hasBookErrors ? 'true' : 'false' }} }">
    <button @click="open = true" class="flex items-center gap-2 bg-[#031C5B] text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-[#031C5B]/90 transition shadow-sm">
        <i class="ph-bold ph-plus text-lg"></i>
        Ajouter un Livre
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" style="display: none;">
        <div @click.away="open = false" class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[18px] font-extrabold text-[#031C5B]">Ajouter un Nouveau Livre</h3>
                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <form action="{{ route('school.library.catalog.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="space-y-1.5">
                    <label class="block text-[13px] font-bold text-slate-700">Titre <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Auteur</label>
                        <input type="text" name="author" value="{{ old('author') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">ISBN</label>
                        <input type="text" name="isbn" value="{{ old('isbn') }}" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Catégorie</label>
                        <select name="category_id" class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
                            <option value="">Aucune</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Quantité <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity_total" min="1" value="{{ old('quantity_total', 1) }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-2 focus:ring-[#031C5B]/10">
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
