@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6">
    @include('SchoolDashboard::library._tabs')

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Paramètres & Configuration</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Gérez les paramètres essentiels de votre bibliothèque.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Circulation Rules -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-[16px] font-bold text-[#031C5B] flex items-center gap-2"><i class="ph-bold ph-sliders-horizontal"></i> Règles de Circulation</h3>
                <span class="px-2.5 py-1 bg-purple-50 text-purple-600 rounded-full text-[10.5px] font-bold uppercase tracking-wider">Optimisé par IA</span>
            </div>
            <form action="{{ route('school.library.settings.rules.update') }}" method="POST" class="p-5 space-y-5">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Max livres par élève</label>
                        <input type="number" name="max_books_per_student" min="1" value="{{ old('max_books_per_student', $settings->max_books_per_student) }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                        <p class="text-[11px] text-slate-400">Augmente l'engagement selon les données historiques.</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-[13px] font-bold text-slate-700">Durée du prêt (jours)</label>
                        <input type="number" name="loan_duration_days" min="1" value="{{ old('loan_duration_days', $settings->loan_duration_days) }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                </div>
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="space-y-1.5 flex-1">
                        <label class="block text-[13px] font-bold text-slate-700">Pénalité de retard / jour (FCFA)</label>
                        <input type="number" step="0.01" name="late_fee_per_day" min="0" value="{{ old('late_fee_per_day', $settings->late_fee_per_day) }}" required class="w-full bg-slate-50 border border-slate-200 text-slate-900 text-[14px] rounded-lg px-4 py-2.5 outline-none focus:border-[#031C5B]">
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer mt-1 md:mt-6">
                        <input type="checkbox" name="enforce_fees" value="1" {{ old('enforce_fees', $settings->enforce_fees) ? 'checked' : '' }} class="w-5 h-5 rounded text-[#031C5B] focus:ring-[#031C5B]">
                        <span class="text-[13px] font-bold text-slate-700">Appliquer les pénalités</span>
                    </label>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="submit" class="px-6 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Enregistrer les Règles</button>
                </div>
            </form>
        </div>

        <!-- System Status -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-[16px] font-bold text-slate-900">État du Système</h3>
                <i class="ph-fill ph-check-circle text-emerald-500 text-xl"></i>
            </div>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-[13px] text-slate-500 font-semibold">Membres Actifs</span>
                    <span class="text-[18px] font-extrabold text-[#031C5B]">{{ number_format($systemStatus['active_members'], 0, ',', ' ') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[13px] text-slate-500 font-semibold">Livres en Circulation</span>
                    <span class="text-[18px] font-extrabold text-purple-600">{{ number_format($systemStatus['books_out'], 0, ',', ' ') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-[13px] text-slate-500 font-semibold">Emprunts en Retard</span>
                    <span class="text-[18px] font-extrabold text-red-600">{{ number_format($systemStatus['overdue_returns'], 0, ',', ' ') }}</span>
                </div>
            </div>
            <div class="mt-5 bg-blue-50/60 border border-blue-100 rounded-xl p-3.5 flex items-start gap-2.5">
                <i class="ph-fill ph-trend-up text-blue-500 mt-0.5"></i>
                <p class="text-[12px] text-slate-600">L'IA prédit une hausse de 15% des emprunts si les pénalités sont réduites pour les petites classes.</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Book Categories -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5" x-data="{ addingCategory: false }">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[16px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-bold ph-tag"></i> Catégories de Livres</h3>
                <button type="button" @click="addingCategory = !addingCategory" class="w-8 h-8 rounded-full bg-[#031C5B] text-white flex items-center justify-center hover:bg-[#031C5B]/90 transition">
                    <i class="ph-bold ph-plus"></i>
                </button>
            </div>

            <div x-show="addingCategory" x-cloak class="mb-4 p-3 bg-slate-50 rounded-xl border border-slate-100">
                <form action="{{ route('school.library.settings.categories.store') }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="name" required placeholder="Nom de la catégorie" class="flex-1 bg-white border border-slate-200 text-slate-900 text-[13px] rounded-lg px-3 py-2 outline-none focus:border-[#031C5B]">
                    <button type="submit" class="px-3 py-2 bg-[#031C5B] text-white rounded-lg text-[12.5px] font-bold hover:bg-[#031C5B]/90 transition">Ajouter</button>
                </form>
            </div>

            <div class="space-y-2">
                @forelse($categories as $category)
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl border border-slate-100">
                        <div class="flex items-center gap-2.5">
                            <span class="w-1.5 h-5 rounded-full bg-[#1E3A8A]"></span>
                            <span class="text-[13.5px] font-semibold text-slate-700">{{ $category->name }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded-md text-[11px] font-bold">{{ $category->books_count }} Livres</span>
                            <form action="{{ route('school.library.settings.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 transition"><i class="ph-bold ph-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-[13px] text-center py-6">Aucune catégorie enregistrée.</p>
                @endforelse
            </div>
        </div>

        <!-- Member Cards & Access -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <h3 class="text-[16px] font-bold text-slate-900 flex items-center gap-2 mb-4"><i class="ph-bold ph-identification-card"></i> Cartes de Membre & Accès</h3>
            <form action="{{ route('school.library.settings.access.update') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Format de carte</p>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex flex-col gap-1 p-3.5 rounded-xl border-2 cursor-pointer transition {{ $settings->card_format === 'digital_qr' ? 'border-[#031C5B] bg-blue-50/50' : 'border-slate-200' }}">
                            <div class="flex items-center justify-between">
                                <i class="ph-bold ph-qr-code text-xl text-[#031C5B]"></i>
                                <input type="radio" name="card_format" value="digital_qr" {{ $settings->card_format === 'digital_qr' ? 'checked' : '' }} class="text-[#031C5B]">
                            </div>
                            <span class="text-[13px] font-bold text-slate-800">QR Digital</span>
                            <span class="text-[11px] text-slate-500">Scan via application mobile.</span>
                        </label>
                        <label class="flex flex-col gap-1 p-3.5 rounded-xl border-2 cursor-pointer transition {{ $settings->card_format === 'physical_barcode' ? 'border-[#031C5B] bg-blue-50/50' : 'border-slate-200' }}">
                            <div class="flex items-center justify-between">
                                <i class="ph-bold ph-credit-card text-xl text-slate-500"></i>
                                <input type="radio" name="card_format" value="physical_barcode" {{ $settings->card_format === 'physical_barcode' ? 'checked' : '' }} class="text-[#031C5B]">
                            </div>
                            <span class="text-[13px] font-bold text-slate-800">Code-barres Physique</span>
                            <span class="text-[11px] text-slate-500">Étiquettes imprimables.</span>
                        </label>
                    </div>
                </div>
                <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl">
                    <div>
                        <p class="text-[13px] font-bold text-slate-700">Auto-Renouvellement Personnel</p>
                        <p class="text-[11.5px] text-slate-500">Renouvelle automatiquement les cartes du personnel chaque année.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer shrink-0 ml-3">
                        <input type="checkbox" name="auto_renew_staff_cards" value="1" {{ $settings->auto_renew_staff_cards ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:bg-[#031C5B] transition-colors"></div>
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-transform peer-checked:translate-x-5"></div>
                    </label>
                </div>
                <div class="flex justify-end pt-1">
                    <button type="submit" class="px-6 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
