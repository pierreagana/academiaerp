@php
    $scholarshipTabs = [
        'school.finance.scholarships.dashboard' => 'Tableau de Bord',
        'school.finance.scholarships.students' => 'Élèves Boursiers',
        'school.finance.scholarships.criteria' => 'Critères d\'Éligibilité',
    ];
@endphp
<div class="flex items-center gap-1 border-b border-slate-200 mb-6 overflow-x-auto">
    @foreach($scholarshipTabs as $routeName => $label)
        <a href="{{ route($routeName) }}" class="px-4 py-3 text-[14px] font-bold border-b-2 whitespace-nowrap transition {{ request()->routeIs($routeName) ? 'border-[#031C5B] text-[#031C5B]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            {{ $label }}
        </a>
    @endforeach
</div>
