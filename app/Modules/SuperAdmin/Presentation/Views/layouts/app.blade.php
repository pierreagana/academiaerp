<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academia ERP - Portail Super Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $themePrimary = \App\Modules\SuperAdmin\Domain\Models\GlobalSetting::where('key', 'primary_theme_color')->value('value') ?? '#031C5B';
    @endphp

    <style>
        :root {
            --primary-color: {{ $themePrimary }};
        }
        body { font-family: 'Poppins', sans-serif; background-color: #F8FAFC; }
        
        .bg-primary-dynamic { background-color: var(--primary-color) !important; }
        .text-primary-dynamic { color: var(--primary-color) !important; }
        .border-primary-dynamic { border-color: var(--primary-color) !important; }
        .ring-primary-dynamic { --tw-ring-color: var(--primary-color) !important; }
    </style>

    @include('SchoolDashboard::components.searchable-select')

    @stack('styles')
</head>
<body class="text-slate-800 flex h-screen overflow-hidden">

    @include('SuperAdmin::layouts.sidebar')

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        @include('SuperAdmin::layouts.header')

        <!-- Scrollable content -->
        <div class="flex-1 overflow-y-auto p-8">
            @yield('content')
        </div>
    </main>

    <script>
        function toggleAccordion(button) {
            const icon = button.querySelector('.accordion-icon');
            const content = button.nextElementSibling;
            
            if (content.classList.contains('hidden')) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                content.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        function toggleDropdown(button) {
            const menu = button.nextElementSibling;
            if (!menu) return;
            const isHidden = menu.classList.contains('hidden');
            
            // Hide all other dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
            
            if (isHidden) {
                menu.classList.remove('hidden');
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.dropdown-container')) {
                document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
            }
        });

        // Apply primary theme color dynamically
        const initialThemeColor = "{{ $themePrimary }}";
        if (initialThemeColor) {
            document.documentElement.style.setProperty('--primary-color', initialThemeColor);
        }
    </script>
    @stack('scripts')
</body>
</html>
