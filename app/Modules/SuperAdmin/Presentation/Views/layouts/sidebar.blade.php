    <!-- SIDEBAR -->
    <aside class="w-72 bg-white/50 backdrop-blur-xl border-r border-slate-200/60 flex flex-col h-full shrink-0">
        <!-- Logo -->
        <div class="p-6 pb-2">
            <h1 class="text-xl font-bold tracking-tight text-slate-900">Academia ERP</h1>
            <div class="flex items-center justify-between mt-1">
                <p class="text-[10px] font-semibold text-slate-400 tracking-widest uppercase">Portail Super Admin</p>
                <div class="flex gap-1">
                    <a href="{{ route('superadmin.lang.switch', 'fr') }}" class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ App::getLocale() == 'fr' ? 'bg-primary-dynamic text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">FR</a>
                    <a href="{{ route('superadmin.lang.switch', 'en') }}" class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ App::getLocale() == 'en' ? 'bg-primary-dynamic text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200' }}">EN</a>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-4 pb-4 space-y-2" id="sidebar-nav">

            @php
                $path = request()->path();
                // Determine which accordion is active based on current path
                $execRoutes   = ['superadmin', 'superadmin/network-health', 'superadmin/system-alerts'];
                $schoolRoutes = ['superadmin/schools', 'superadmin/registration-requests', 'superadmin/multi-campus', 'superadmin/specific-configuration'];
                $saasRoutes   = ['superadmin/packages', 'superadmin/service-catalog', 'superadmin/modules', 'superadmin/extension-requests', 'superadmin/invoices', 'superadmin/revenue'];
                $aiRoutes     = ['superadmin/revenue', 'superadmin/ai-analytics', 'superadmin/ai-models'];
                $commRoutes   = ['superadmin/broadcast'];
                $secRoutes    = ['superadmin/security-permissions', 'superadmin/system-logs', 'superadmin/backups'];
                $staffRoutes  = ['superadmin/staff', 'superadmin/security-permissions'];
                $sysRoutes    = ['superadmin/global-settings', 'superadmin/cms'];
                $suppRoutes   = ['superadmin/support'];

                // Use closure to prevent "Cannot redeclare" PHP fatal when sidebar is included repeatedly
                $isActive = function(array $routes, string $current): bool {
                    foreach ($routes as $route) {
                        if ($current === $route || str_starts_with($current, $route . '/')) {
                            return true;
                        }
                    }
                    return false;
                };

                $isRoute = fn(string $prefix): bool => $path === $prefix || str_starts_with($path, $prefix . '/');

                $execOpen   = true; // always open by default
                $schoolOpen = $isActive($schoolRoutes, $path);
                $saasOpen   = $isActive($saasRoutes, $path);
                $aiOpen     = $isActive($aiRoutes, $path);
                $commOpen   = $isActive($commRoutes, $path);
                $secOpen    = $isActive($secRoutes, $path);
                $staffOpen  = $isActive($staffRoutes, $path);
                $sysOpen    = $isActive($sysRoutes, $path);
                $suppOpen   = $isActive($suppRoutes, $path);
            @endphp

            <!-- ====== Vue Executive (always open) ====== -->
            <div class="accordion-group" data-always-open="true">
                <button type="button" class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 font-bold text-sm rounded-xl transition {{ $execOpen ? 'sidebar-active-btn' : 'text-slate-700 hover:bg-slate-50' }}" {!! $execOpen ? 'style="background-color: color-mix(in srgb, var(--primary-color) 12%, white); color: var(--primary-color);"' : '' !!}>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <i class="ph-fill ph-squares-four text-lg" {!! $execOpen ? 'style="color: var(--primary-color);"' : 'class="text-slate-500"' !!}></i>
                        {{ __('executive_view') }}
                    </div>
                    <i class="ph ph-caret-down transition-transform duration-200 accordion-icon {{ $execOpen ? 'rotate-180' : '' }}" {!! $execOpen ? 'style="color: var(--primary-color);"' : '' !!}></i>
                </button>
                <div class="accordion-content mt-1.5 space-y-0.5 pl-9 {{ $execOpen ? '' : 'hidden' }}">
                    <a href="/superadmin" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin') && $path !== 'superadmin/network-health' && $path !== 'superadmin/system-alerts' ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin') && $path !== 'superadmin/network-health' && $path !== 'superadmin/system-alerts' ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin') && $path !== 'superadmin/network-health' && $path !== 'superadmin/system-alerts' ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('strategic_dashboard') }}
                    </a>
                    <a href="/superadmin/network-health" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/network-health') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/network-health') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/network-health') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('network_health') }}
                    </a>
                    <a href="/superadmin/system-alerts" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/system-alerts') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/system-alerts') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/system-alerts') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('system_alerts') }}
                    </a>
                </div>
            </div>

            <!-- ====== Gestion des Établissements ====== -->
            <div class="accordion-group">
                <button type="button" class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 font-semibold text-sm rounded-xl transition {{ $schoolOpen ? 'sidebar-active-btn' : 'text-slate-700 hover:bg-slate-50' }}" {!! $schoolOpen ? 'style="background-color: color-mix(in srgb, var(--primary-color) 12%, white); color: var(--primary-color);"' : '' !!}>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <i class="ph ph-buildings text-lg" {!! $schoolOpen ? 'style="color: var(--primary-color);"' : 'class="text-slate-500"' !!}></i>
                        {{ __('school_management') }}
                    </div>
                    <i class="ph ph-caret-down transition-transform duration-200 accordion-icon {{ $schoolOpen ? 'rotate-180' : 'text-slate-400' }}" {!! $schoolOpen ? 'style="color: var(--primary-color);"' : '' !!}></i>
                </button>
                <div class="accordion-content mt-1.5 space-y-0.5 pl-9 {{ $schoolOpen ? '' : 'hidden' }}">
                    <a href="/superadmin/schools" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/schools') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/schools') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/schools') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('schools_directory') }}
                    </a>
                    <a href="/superadmin/registration-requests" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/registration-requests') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/registration-requests') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/registration-requests') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('registrations_onboarding') }}
                    </a>
                    <a href="/superadmin/multi-campus" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/multi-campus') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/multi-campus') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/multi-campus') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('multi_campus_management') }}
                    </a>
                    <a href="/superadmin/specific-configuration" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/specific-configuration') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/specific-configuration') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/specific-configuration') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('specific_configuration') }}
                    </a>
                </div>
            </div>

            <!-- ====== Monétisation SaaS ====== -->
            <div class="accordion-group">
                <button type="button" class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 font-semibold text-sm rounded-xl transition {{ $saasOpen ? 'sidebar-active-btn' : 'text-slate-700 hover:bg-slate-50' }}" {!! $saasOpen ? 'style="background-color: color-mix(in srgb, var(--primary-color) 12%, white); color: var(--primary-color);"' : '' !!}>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <i class="ph ph-money text-lg" {!! $saasOpen ? 'style="color: var(--primary-color);"' : 'class="text-slate-500"' !!}></i>
                        {{ __('saas_monetization') }}
                    </div>
                    <i class="ph ph-caret-down transition-transform duration-200 accordion-icon {{ $saasOpen ? 'rotate-180' : 'text-slate-400' }}" {!! $saasOpen ? 'style="color: var(--primary-color);"' : '' !!}></i>
                </button>
                <div class="accordion-content mt-1.5 space-y-0.5 pl-9 {{ $saasOpen ? '' : 'hidden' }}">
                    <a href="/superadmin/packages" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/packages') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/packages') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/packages') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('licenses_subscriptions') }}
                    </a>
                    <a href="/superadmin/service-catalog" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/service-catalog') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/service-catalog') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/service-catalog') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('service_catalog') }}
                    </a>
                    <a href="/superadmin/modules" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/modules') || $isRoute('superadmin/module-details') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/modules') || $isRoute('superadmin/module-details') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/modules') || $isRoute('superadmin/module-details') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('modules_management') }}
                    </a>
                    <a href="/superadmin/extension-requests" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/extension-requests') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/extension-requests') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/extension-requests') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        Demandes d'Extensions
                    </a>
                    <a href="/superadmin/invoices" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/invoices') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/invoices') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/invoices') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('billing_revenue') }}
                    </a>
                </div>
            </div>

            <!-- ====== IA & Analytics ====== -->
            <div class="accordion-group">
                <button type="button" class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 font-semibold text-sm rounded-xl transition {{ $aiOpen ? 'sidebar-active-btn' : 'text-slate-700 hover:bg-slate-50' }}" {!! $aiOpen ? 'style="background-color: color-mix(in srgb, var(--primary-color) 12%, white); color: var(--primary-color);"' : '' !!}>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <i class="ph ph-trend-up text-lg" {!! $aiOpen ? 'style="color: var(--primary-color);"' : 'class="text-slate-500"' !!}></i>
                        {{ __('ai_analytics') }}
                    </div>
                    <i class="ph ph-caret-down transition-transform duration-200 accordion-icon {{ $aiOpen ? 'rotate-180' : 'text-slate-400' }}" {!! $aiOpen ? 'style="color: var(--primary-color);"' : '' !!}></i>
                </button>
                <div class="accordion-content mt-1.5 space-y-0.5 pl-9 {{ $aiOpen ? '' : 'hidden' }}">
                    <a href="/superadmin/revenue" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/revenue') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/revenue') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/revenue') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('financial_analysis') }}
                    </a>
                    <a href="/superadmin/ai-analytics" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/ai-analytics') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/ai-analytics') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/ai-analytics') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('ai_strategic_management') }}
                    </a>
                    <a href="/superadmin/ai-models" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/ai-models') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/ai-models') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/ai-models') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('models_configuration') }}
                    </a>
                </div>
            </div>

            <!-- ====== Hub de Communication ====== -->
            <div class="accordion-group">
                <button type="button" class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 font-semibold text-sm rounded-xl transition {{ $commOpen ? 'sidebar-active-btn' : 'text-slate-700 hover:bg-slate-50' }}" {!! $commOpen ? 'style="background-color: color-mix(in srgb, var(--primary-color) 12%, white); color: var(--primary-color);"' : '' !!}>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <i class="ph ph-chats text-lg" {!! $commOpen ? 'style="color: var(--primary-color);"' : 'class="text-slate-500"' !!}></i>
                        {{ __('communication_hub') }}
                    </div>
                    <i class="ph ph-caret-down transition-transform duration-200 accordion-icon {{ $commOpen ? 'rotate-180' : 'text-slate-400' }}" {!! $commOpen ? 'style="color: var(--primary-color);"' : '' !!}></i>
                </button>
                <div class="accordion-content mt-1.5 space-y-0.5 pl-9 {{ $commOpen ? '' : 'hidden' }}">
                    <a href="/superadmin/broadcast" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/broadcast') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/broadcast') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/broadcast') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('broadcast_hub') }}
                    </a>
                </div>
            </div>

            <!-- ====== Sécurité & Gouvernance ====== -->
            <div class="accordion-group">
                <button type="button" class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 font-semibold text-sm rounded-xl transition {{ $secOpen ? 'sidebar-active-btn' : 'text-slate-700 hover:bg-slate-50' }}" {!! $secOpen ? 'style="background-color: color-mix(in srgb, var(--primary-color) 12%, white); color: var(--primary-color);"' : '' !!}>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <i class="ph ph-gavel text-lg" {!! $secOpen ? 'style="color: var(--primary-color);"' : 'class="text-slate-500"' !!}></i>
                        {{ __('security_governance') }}
                    </div>
                    <i class="ph ph-caret-down transition-transform duration-200 accordion-icon {{ $secOpen ? 'rotate-180' : 'text-slate-400' }}" {!! $secOpen ? 'style="color: var(--primary-color);"' : '' !!}></i>
                </button>
                <div class="accordion-content mt-1.5 space-y-0.5 pl-9 {{ $secOpen ? '' : 'hidden' }}">
                    <a href="/superadmin/security-permissions" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/security-permissions') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/security-permissions') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/security-permissions') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('security_permissions') }}
                    </a>
                    <a href="/superadmin/system-logs" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/system-logs') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/system-logs') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/system-logs') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('logs_audit') }}
                    </a>
                    <a href="/superadmin/backups" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/backups') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/backups') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/backups') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('backups') }}
                    </a>
                </div>
            </div>

            <!-- ====== Gestion du Personnel ====== -->
            <div class="accordion-group">
                <button type="button" class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 font-semibold text-sm rounded-xl transition {{ $staffOpen ? 'sidebar-active-btn' : 'text-slate-700 hover:bg-slate-50' }}" {!! $staffOpen ? 'style="background-color: color-mix(in srgb, var(--primary-color) 12%, white); color: var(--primary-color);"' : '' !!}>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <i class="ph ph-users text-lg" {!! $staffOpen ? 'style="color: var(--primary-color);"' : 'class="text-slate-500"' !!}></i>
                        {{ __('staff_management') }}
                    </div>
                    <i class="ph ph-caret-down transition-transform duration-200 accordion-icon {{ $staffOpen ? 'rotate-180' : 'text-slate-400' }}" {!! $staffOpen ? 'style="color: var(--primary-color);"' : '' !!}></i>
                </button>
                <div class="accordion-content mt-1.5 space-y-0.5 pl-9 {{ $staffOpen ? '' : 'hidden' }}">
                    <a href="/superadmin/security-permissions" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/security-permissions') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/security-permissions') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/security-permissions') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('roles_permissions') }}
                    </a>
                    <a href="/superadmin/staff" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/staff') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/staff') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/staff') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('staff') }}
                    </a>
                </div>
            </div>

            <!-- ====== Paramètres Système ====== -->
            <div class="accordion-group">
                <button type="button" class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 font-semibold text-sm rounded-xl transition {{ $sysOpen ? 'sidebar-active-btn' : 'text-slate-700 hover:bg-slate-50' }}" {!! $sysOpen ? 'style="background-color: color-mix(in srgb, var(--primary-color) 12%, white); color: var(--primary-color);"' : '' !!}>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <i class="ph ph-gear text-lg" {!! $sysOpen ? 'style="color: var(--primary-color);"' : 'class="text-slate-500"' !!}></i>
                        {{ __('system_settings') }}
                    </div>
                    <i class="ph ph-caret-down transition-transform duration-200 accordion-icon {{ $sysOpen ? 'rotate-180' : 'text-slate-400' }}" {!! $sysOpen ? 'style="color: var(--primary-color);"' : '' !!}></i>
                </button>
                <div class="accordion-content mt-1.5 space-y-0.5 pl-9 {{ $sysOpen ? '' : 'hidden' }}">
                    <a href="/superadmin/global-settings" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/global-settings') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/global-settings') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/global-settings') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('global_settings') }}
                    </a>
                    <a href="/superadmin/cms" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/cms') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/cms') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/cms') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('website_management_cms') }}
                    </a>
                </div>
            </div>

        </nav>

        <div class="px-4 pb-4">
            <!-- Group: Support -->
            <div class="accordion-group">
                <button type="button" class="accordion-btn w-full flex items-center justify-between px-3 py-2.5 font-semibold text-sm rounded-xl transition {{ $suppOpen ? 'sidebar-active-btn' : 'text-slate-700 hover:bg-slate-50' }}" {!! $suppOpen ? 'style="background-color: color-mix(in srgb, var(--primary-color) 12%, white); color: var(--primary-color);"' : '' !!}>
                    <div class="flex items-center gap-3 whitespace-nowrap">
                        <i class="ph ph-headset text-lg" {!! $suppOpen ? 'style="color: var(--primary-color);"' : 'class="text-slate-500"' !!}></i>
                        {{ __('support') }}
                    </div>
                    <i class="ph ph-caret-down transition-transform duration-200 accordion-icon {{ $suppOpen ? 'rotate-180' : 'text-slate-400' }}" {!! $suppOpen ? 'style="color: var(--primary-color);"' : '' !!}></i>
                </button>
                <div class="accordion-content mt-1.5 space-y-0.5 pl-9 {{ $suppOpen ? '' : 'hidden' }}">
                    <a href="/superadmin/support" class="flex items-start gap-2.5 px-3 py-1.5 text-[13.5px] leading-snug transition {{ $isRoute('superadmin/support') ? 'font-bold' : 'font-medium text-slate-500 hover:text-slate-800' }}" {!! $isRoute('superadmin/support') ? 'style="color: var(--primary-color);"' : '' !!}>
                        <div class="shrink-0 mt-[6px] w-1.5 h-1.5 rounded-full" {!! $isRoute('superadmin/support') ? 'style="background-color: var(--primary-color);"' : 'class="bg-slate-300"' !!}></div>
                        {{ __('support') }} & Ticketing
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Actions -->
        <div class="p-4 border-t border-slate-200">
            <button class="w-full flex items-center justify-center gap-2 text-white px-4 py-2.5 rounded-lg text-sm font-medium hover:opacity-90 transition shadow-sm mb-3" style="background: linear-gradient(135deg, var(--primary-color), color-mix(in srgb, var(--primary-color) 75%, black));">
                <i class="ph ph-sparkle-fill"></i>
                {{ __('ai_report_generator') }}
            </button>
            <form method="POST" action="{{ route('superadmin.logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 text-slate-600 font-medium text-sm hover:text-slate-900 transition">
                    <i class="ph ph-sign-out text-lg"></i>
                    {{ __('logout') }}
                </button>
            </form>
        </div>
    </aside>

    <script>
    (function () {
        // Accordion JS logic dynamically applying --primary-color styles
        const OPEN_ICON_CLASS = 'rotate-180';

        function openGroup(group) {
            const btn     = group.querySelector('.accordion-btn');
            const content = group.querySelector('.accordion-content');
            const icon    = group.querySelector('.accordion-icon');
            const mainIcon = group.querySelector('.accordion-btn i:first-child');

            content.classList.remove('hidden');
            icon.classList.add(OPEN_ICON_CLASS);
            
            btn.style.backgroundColor = 'color-mix(in srgb, var(--primary-color) 12%, white)';
            btn.style.color = 'var(--primary-color)';
            btn.classList.add('font-bold');
            if (icon) icon.style.color = 'var(--primary-color)';
            if (mainIcon) mainIcon.style.color = 'var(--primary-color)';
        }

        function closeGroup(group) {
            const btn     = group.querySelector('.accordion-btn');
            const content = group.querySelector('.accordion-content');
            const icon    = group.querySelector('.accordion-icon');
            const mainIcon = group.querySelector('.accordion-btn i:first-child');

            content.classList.add('hidden');
            icon.classList.remove(OPEN_ICON_CLASS);

            btn.style.backgroundColor = '';
            btn.style.color = '';
            btn.classList.remove('font-bold');
            if (icon) icon.style.color = '';
            if (mainIcon) mainIcon.style.color = '';
        }

        function isOpen(group) {
            return !group.querySelector('.accordion-content').classList.contains('hidden');
        }

        function isAlwaysOpen(group) {
            return group.dataset.alwaysOpen === 'true';
        }

        document.querySelectorAll('.accordion-group').forEach(function (group) {
            const btn = group.querySelector('.accordion-btn');
            if (!btn) return;

            btn.addEventListener('click', function () {
                if (isAlwaysOpen(group)) return;
                const willOpen = !isOpen(group);

                document.querySelectorAll('.accordion-group').forEach(function (g) {
                    if (!isAlwaysOpen(g)) closeGroup(g);
                });

                if (willOpen) {
                    openGroup(group);
                }
            });
        });
    })();
    </script>
