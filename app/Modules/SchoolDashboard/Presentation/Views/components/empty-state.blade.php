<div class="flex flex-col items-center justify-center py-16 px-4 text-center bg-white border border-slate-200 border-dashed rounded-2xl">
    <div class="w-20 h-20 mb-5 rounded-2xl bg-indigo-50 flex items-center justify-center border border-indigo-100 shadow-inner">
        <i class="{{ $icon ?? 'ph-fill ph-folder-open' }} text-indigo-500 text-[32px]"></i>
    </div>
    <h3 class="text-[18px] font-bold text-slate-800 mb-2 tracking-tight">{{ $title ?? 'Aucune donnée disponible' }}</h3>
    <p class="text-[13.5px] text-slate-500 max-w-[350px] mb-6 leading-relaxed">
        {{ $description ?? 'Il n\'y a actuellement aucun élément à afficher. Cliquez sur le bouton ci-dessous pour commencer.' }}
    </p>
    @if(isset($actionText))
    <button {{ $attributes->merge(['class' => 'bg-[#1E40AF] hover:bg-[#1E3A8A] text-white font-bold text-[13px] px-5 py-2.5 rounded-xl shadow-sm transition flex items-center gap-2']) }}>
        <i class="ph-bold ph-plus"></i>
        {{ $actionText }}
    </button>
    @endif
</div>
