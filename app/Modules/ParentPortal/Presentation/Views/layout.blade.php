<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Espace Parent') - Academia ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Poppins', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen">
    <header class="bg-white border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('parent.dashboard') }}" class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-xl bg-[#031C5B] text-white flex items-center justify-center font-extrabold">A</div>
                <span class="font-extrabold text-[#031C5B] text-[17px]">Espace Parent</span>
            </a>
            <div class="flex items-center gap-5">
                <a href="{{ route('parent.children.add-form') }}" class="text-[13px] font-bold text-slate-500 hover:text-[#031C5B] transition flex items-center gap-1.5">
                    <i class="ph-bold ph-user-plus"></i> Ajouter un enfant
                </a>
                <form action="{{ route('parent.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[13px] font-bold text-slate-500 hover:text-red-600 transition flex items-center gap-1.5">
                        <i class="ph-bold ph-sign-out"></i> Déconnexion
                    </button>
                </form>
            </div>
        </div>
    </header>

    @isset($child)
    <div class="bg-white border-b border-slate-100">
        <div class="max-w-5xl mx-auto px-6">
            <div class="flex items-center gap-1 overflow-x-auto">
                @php
                    $tabs = [
                        'parent.bulletin' => ['Bulletin', 'ph-scroll'],
                        'parent.attendance' => ['Présence', 'ph-calendar-check'],
                        'parent.homework' => ['Devoirs', 'ph-book-open'],
                        'parent.fees' => ['Frais', 'ph-money'],
                        'parent.canteen' => ['Cantine', 'ph-fork-knife'],
                        'parent.transport' => ['Transport', 'ph-bus'],
                    ];
                @endphp
                @foreach($tabs as $routeName => [$label, $icon])
                    <a href="{{ route($routeName, $child->id) }}"
                        class="shrink-0 px-4 py-3 text-[13px] font-bold border-b-2 transition flex items-center gap-1.5 {{ request()->routeIs($routeName) ? 'border-[#031C5B] text-[#031C5B]' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        <i class="ph-bold {{ $icon }}"></i> {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="max-w-5xl mx-auto px-6 pt-4">
        <p class="text-[13px] text-slate-500">
            <a href="{{ route('parent.dashboard') }}" class="hover:underline">Mes Enfants</a>
            &rarr; <span class="font-bold text-slate-700">{{ $child->first_name }} {{ $child->last_name }}</span>
        </p>
    </div>
    @endisset

    <main class="max-w-5xl mx-auto px-6 py-6">
        @if(session('success'))
        <div class="p-4 mb-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 border border-emerald-100 font-bold">{{ session('success') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
