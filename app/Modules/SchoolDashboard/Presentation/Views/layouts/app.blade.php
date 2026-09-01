<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Academia ERP - School Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Phosphor Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Searchable dropdowns -->
    @include('SchoolDashboard::components.searchable-select')

    @stack('styles')
    <style>
        :root {
            /* Using the purple/violet color from the AI Insights card in the mockup as primary, or a dark blue */
            --primary-color: #031C5B; 
            --accent-purple: #7C3AED;
        }
        body { font-family: 'Poppins', sans-serif; background-color: #F8FAFC; }
        
        .bg-primary-dynamic { background-color: var(--primary-color) !important; }
        .text-primary-dynamic { color: var(--primary-color) !important; }
        .border-primary-dynamic { border-color: var(--primary-color) !important; }
    </style>
</head>
<body class="text-slate-800 flex h-screen overflow-hidden">

    @include('SchoolDashboard::layouts.sidebar')

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-white/50 rounded-tl-3xl shadow-sm border-l border-t border-slate-200/60 mt-2">
        
        @include('SchoolDashboard::layouts.header')

        <!-- Scrollable content -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
</body>
</html>
