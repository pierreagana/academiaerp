@php
    $countries = \App\Modules\SuperAdmin\Domain\Models\Country::orderBy('order')->get();
    $fieldName = $name ?? 'country';
    $selectedValue = old($fieldName, $selected ?? "Côte d'Ivoire");
    $selectClass = $selectClass ?? 'w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition shadow-sm cursor-pointer';
@endphp
<select id="{{ $id ?? $fieldName }}" name="{{ $fieldName }}" class="{{ $selectClass }}" {!! $extraAttrs ?? '' !!}>
    @if($includeEmpty ?? false)
        <option value="">{{ $emptyLabel ?? 'Tous les pays' }}</option>
    @endif
    @foreach($countries as $c)
        <option value="{{ $c->name }}" {{ $selectedValue === $c->name ? 'selected' : '' }}>{{ $c->flag_emoji }} {{ $c->name }}</option>
    @endforeach
</select>
