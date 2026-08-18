@php
    $feeTypeTabs = \App\Modules\Finance\Domain\Models\FeeLevel::TYPES;
@endphp
<div class="flex items-center gap-2 mb-6">
    @foreach($feeTypeTabs as $key => $label)
        <a href="{{ request()->fullUrlWithQuery(['type' => $key]) }}" class="px-4 py-2 rounded-lg text-[13px] font-bold transition {{ $type === $key ? 'bg-[#031C5B] text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
            {{ $label }}
        </a>
    @endforeach
</div>
