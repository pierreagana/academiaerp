<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('login_page_title') }}</title>
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Icônes Phosphor -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @php
        $themePrimary = \App\Modules\SuperAdmin\Domain\Models\GlobalSetting::where('key', 'primary_theme_color')->value('value') ?? '#031C5B';
    @endphp

    <style>
        :root {
            --primary-color: {{ $themePrimary }};
        }
        body { font-family: 'Poppins', sans-serif; }
        
        .bg-primary-dynamic { background-color: var(--primary-color) !important; }
        .text-primary-dynamic { color: var(--primary-color) !important; }
        .border-primary-dynamic { border-color: var(--primary-color) !important; }
    </style>
</head>
<body class="bg-[#F8FAFC] min-h-screen flex items-center justify-center relative overflow-hidden">
    
    <!-- Decorative background elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
        <div class="absolute -top-[10%] -left-[10%] w-[50%] h-[50%] rounded-full bg-blue-200/40 blur-[120px]"></div>
        <div class="absolute top-[60%] -right-[10%] w-[40%] h-[40%] rounded-full bg-indigo-200/40 blur-[100px]"></div>
    </div>

    <!-- Login Container -->
    <div class="relative z-10 w-full max-w-md px-6">
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <h1 class="text-[32px] font-extrabold tracking-tight" style="color: var(--primary-color);">Academia<span class="text-[#7C3AED]">ERP</span></h1>
            <p class="text-[13px] font-bold text-slate-500 uppercase tracking-widest mt-1">{{ __('super_admin_portal') }}</p>
        </div>

        <div class="bg-white rounded-[24px] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <!-- Header -->
            <div class="p-8 pb-6 text-center">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-5 shadow-lg text-white" style="background-color: var(--primary-color);">
                    <i class="ph ph-shield-check text-2xl text-white"></i>
                </div>
                <h2 class="text-[22px] font-extrabold text-[#111827] mb-1">{{ __('auth_required') }}</h2>
                <p class="text-[14px] text-slate-500 font-medium">{{ __('login_to_access') }}</p>
            </div>

            <!-- Form -->
            <div class="p-8 pt-0">
                @if (isset($errors) && $errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-[#FEF2F2] border border-[#FECACA] flex items-start gap-3">
                        <i class="ph-fill ph-warning-circle text-[#DC2626] text-lg mt-0.5"></i>
                        <div class="flex-1">
                            <h3 class="text-[13px] font-bold text-[#991B1B]">{{ __('login_error') }}</h3>
                            <p class="text-[12px] font-medium text-[#B91C1C] mt-1">{{ $errors->first() }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('superadmin.login.submit') }}">
                    @csrf
                    
                    <div class="mb-5">
                        <label for="email" class="block text-[13px] font-bold text-[#111827] mb-2">{{ __('email_address') }}</label>
                        <div class="relative">
                            <i class="ph ph-envelope-simple absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-xl pl-11 pr-4 py-3 outline-none focus:bg-white transition shadow-sm"
                                placeholder="{{ __('email_placeholder') }}">
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-[13px] font-bold text-[#111827]">{{ __('password') }}</label>
                            <a href="#" class="text-[12px] font-bold hover:underline" style="color: var(--primary-color);">{{ __('forgot_password') }}</a>
                        </div>
                        <div class="relative">
                            <i class="ph ph-lock-key absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input type="password" id="password" name="password" required
                                class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] font-medium rounded-xl pl-11 pr-11 py-3 outline-none focus:bg-white transition shadow-sm"
                                placeholder="••••••••">
                            <button type="button" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                                <i class="ph ph-eye text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 mb-8">
                        <label class="flex items-start cursor-pointer hover:bg-slate-50 transition p-1 -ml-1 rounded-lg">
                            <div class="relative flex items-start mt-0.5">
                                <input type="checkbox" name="remember" id="remember" class="peer sr-only">
                                <div class="w-4 h-4 rounded border-2 border-slate-300 bg-white flex items-center justify-center text-white transition-colors" style="background-color: var(--primary-color); border-color: var(--primary-color);">
                                    <i class="ph ph-check text-[10px] font-bold text-white"></i>
                                </div>
                            </div>
                            <span class="ml-2 text-[13px] font-medium text-slate-600 select-none">{{ __('remember_me') }}</span>
                        </label>
                    </div>

                    <button type="submit" class="w-full text-white font-bold text-[14px] py-3.5 rounded-xl shadow-lg transition transform hover:-translate-y-0.5 cursor-pointer" style="background-color: var(--primary-color);">
                        {{ __('login_button') }}
                    </button>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="bg-slate-50 border-t border-slate-100 p-4 text-center">
                <p class="text-[11px] font-medium text-slate-400">
                    {{ __('protected_by') }}<br>
                    {{ __('authorized_personnel_only') }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>
