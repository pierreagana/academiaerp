<div x-show="createOpen" x-cloak class="fixed inset-0 bg-slate-900/50 z-[9999] flex items-center justify-center p-4 overflow-y-auto" style="display: none;">
    <div @click.outside="createOpen = false" class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 my-8" x-data="{ hasAssistant: false }">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-[17px] font-bold text-[#031C5B]">Ajouter un Chauffeur</h3>
            <button @click="createOpen = false" class="text-slate-400 hover:text-slate-600"><i class="ph-bold ph-x"></i></button>
        </div>
        <form method="POST" action="{{ route('school.transport.drivers.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Nom</label>
                    <input type="text" name="last_name" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Prénoms</label>
                    <input type="text" name="first_name" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Téléphone</label>
                    <input type="tel" name="phone" placeholder="Ex: 0102030405" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    <p class="text-[11px] text-slate-400 mt-1">Sert d'identifiant de connexion pour l'app chauffeur.</p>
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Mot de Passe</label>
                    <input type="password" name="password" minlength="6" placeholder="Optionnel" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                @include('SchoolDashboard::transport._document_upload_field', ['name' => 'id_card_front', 'label' => "Pièce d'Identité — Recto"])
                @include('SchoolDashboard::transport._document_upload_field', ['name' => 'id_card_back', 'label' => "Pièce d'Identité — Verso"])
            </div>
            <div class="grid grid-cols-2 gap-3">
                @include('SchoolDashboard::transport._document_upload_field', ['name' => 'license_front', 'label' => 'Permis de Conduire — Recto'])
                @include('SchoolDashboard::transport._document_upload_field', ['name' => 'license_back', 'label' => 'Permis de Conduire — Verso'])
            </div>

            <label class="flex items-center gap-2.5 cursor-pointer">
                <input type="checkbox" name="has_assistant" value="1" x-model="hasAssistant" class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                <span class="text-[13px] font-bold text-slate-700">Ce conducteur a un assistant</span>
            </label>

            <div x-show="hasAssistant" x-cloak class="space-y-4 border-t border-slate-100 pt-4">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Nom et Prénoms de l'Assistant</label>
                        <input type="text" name="assistant_name" :required="hasAssistant" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Téléphone de l'Assistant</label>
                        <input type="tel" name="assistant_phone" placeholder="Ex: 0102030405" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    @include('SchoolDashboard::transport._document_upload_field', ['name' => 'assistant_id_card_front', 'label' => "Pièce d'Identité — Recto"])
                    @include('SchoolDashboard::transport._document_upload_field', ['name' => 'assistant_id_card_back', 'label' => "Pièce d'Identité — Verso"])
                </div>
            </div>

            <button type="submit" class="w-full mt-2 px-4 py-2.5 bg-[#031C5B] text-white rounded-xl text-[13px] font-bold hover:bg-[#031C5B]/90 transition">
                Ajouter le Chauffeur
            </button>
        </form>
    </div>
</div>
