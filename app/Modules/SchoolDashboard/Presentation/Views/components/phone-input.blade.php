@php
    $countries = \App\Modules\SuperAdmin\Domain\Models\Country::orderBy('order')->get();
    $codeField = $codeName ?? 'phone_country_code';
    $numberField = $numberName ?? 'phone_number';
    $codeValue = old($codeField, $selectedCode ?? '+225');
    $numberValue = old($numberField, $selectedNumber ?? '');
    $selectClass = $selectClass ?? 'w-[110px] bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-2 py-2.5 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition shadow-sm cursor-pointer';
    $inputClass = $inputClass ?? 'flex-1 bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition shadow-sm';
@endphp
<div class="flex gap-2">
    <select name="{{ $codeField }}" class="{{ $selectClass }}">
        @foreach($countries as $c)
            <option value="{{ $c->dial_code }}" {{ $codeValue === $c->dial_code ? 'selected' : '' }}>{{ $c->flag_emoji }} {{ $c->dial_code }} — {{ $c->name }}</option>
        @endforeach
    </select>
    <input type="text" id="{{ $numberField }}" name="{{ $numberField }}" value="{{ $numberValue }}"
        {{ ($required ?? false) ? 'required' : '' }}
        class="{{ $inputClass }}" placeholder="{{ $placeholder ?? 'Ex : 0102030405' }}">
</div>
