@php $fieldId = 'doc_' . $name; @endphp
<div x-data="{ fileName: '' }">
    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">{{ $label }}</label>
    <label for="{{ $fieldId }}" class="flex items-center gap-3 border border-dashed border-slate-300 bg-slate-50 rounded-lg px-3 py-3 cursor-pointer hover:border-[#031C5B] transition">
        <i class="ph-bold ph-image text-slate-400 text-lg shrink-0"></i>
        <span class="text-[12.5px] font-semibold text-slate-500 truncate" x-text="fileName || 'Choisir un fichier (JPG, PNG)'"></span>
    </label>
    <input type="file" id="{{ $fieldId }}" name="{{ $name }}" accept="image/jpeg,image/png,image/jpg" class="hidden"
        @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
</div>
