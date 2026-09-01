@extends('SuperAdmin::layouts.app')

@section('content')
    <!-- Page Header -->
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-extrabold text-[#111827]">Broadcast Hub</h2>
            <p class="text-[15px] text-slate-500 mt-1">Gérez les communications globales et notifications réseau (Base SQL).</p>
        </div>
    </div>

    <!-- Toast Alert -->
    @if(session('success'))
    <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-check-circle text-emerald-600 text-xl font-bold"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-lg font-bold">✕</button>
    </div>
    @endif

    <!-- Toast JS Notification -->
    <div id="broadcastToast" class="hidden mb-6 bg-purple-50 border border-purple-200 text-purple-800 px-5 py-3.5 rounded-xl flex items-center justify-between text-sm font-semibold shadow-xs">
        <div class="flex items-center gap-2.5">
            <i class="ph ph-sparkle text-purple-600 text-xl font-bold"></i>
            <span id="broadcastToastMsg">Message réécrit par l'IA avec succès.</span>
        </div>
        <button onclick="document.getElementById('broadcastToast').classList.add('hidden')" class="text-purple-500 hover:text-purple-800 text-lg font-bold">✕</button>
    </div>

    <!-- Main Layout -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
        
        <!-- Left: New Broadcast Form -->
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
            <div class="flex items-center gap-3 mb-6 border-b border-slate-100 pb-4">
                <i class="ph ph-pencil-simple text-[22px] text-[#031C5B] font-bold"></i>
                <h3 class="text-[20px] font-extrabold text-[#111827]">Nouveau Broadcast d'Annonce</h3>
            </div>
            <form action="{{ route('superadmin.broadcast.store') }}" method="POST">
            @csrf
            <input type="hidden" name="target_audience" id="targetAudienceInput" value="Tous les établissements">

            <!-- Audience Targeting -->
            <div class="mb-6">
                <label class="block text-[13px] font-bold text-slate-600 mb-3">Ciblage de l'Audience *</label>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" onclick="selectAudience(this, 'Tous les établissements')" class="audience-btn px-5 py-2 rounded-full bg-[#031C5B] text-white text-[13px] font-bold border border-[#031C5B] shadow-xs cursor-pointer">
                        Tous les établissements
                    </button>
                    <button type="button" onclick="selectAudience(this, 'Administrateurs & Directeurs')" class="audience-btn px-5 py-2 rounded-full bg-white text-slate-600 text-[13px] font-bold border border-slate-300 hover:bg-slate-50 transition shadow-xs cursor-pointer">
                        Administrateurs
                    </button>
                    <button type="button" onclick="selectAudience(this, 'Corps Enseignant')" class="audience-btn px-5 py-2 rounded-full bg-white text-slate-600 text-[13px] font-bold border border-slate-300 hover:bg-slate-50 transition shadow-xs cursor-pointer">
                        Corps Enseignant
                    </button>
                    <button type="button" onclick="selectAudience(this, 'Segment Spécial IA (Écoles Premium)')" class="audience-btn px-5 py-2 rounded-full bg-white text-slate-600 text-[13px] font-bold border border-slate-300 hover:bg-slate-50 transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <i class="ph ph-sparkle text-purple-600 font-bold"></i> Segment Spécial IA
                    </button>
                </div>
            </div>

            <!-- Message Title -->
            <div class="mb-6">
                <label class="block text-[13px] font-bold text-slate-600 mb-2">Titre du Message *</label>
                <input type="text" name="title" id="broadcastTitle" required placeholder="Ex: Maintenance système programmée & mise à jour IA" class="w-full bg-white border border-slate-300 text-slate-800 text-[15px] font-medium rounded-xl px-4 py-3 outline-none focus:border-[#031C5B] focus:ring-1 focus:ring-[#031C5B] transition shadow-xs">
            </div>

            <!-- Content Editor -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-[13px] font-bold text-slate-600">Contenu de l'Annonce *</label>
                    <button type="button" onclick="aiRewriteMessage()" class="flex items-center gap-1.5 text-[#7C3AED] text-[12px] font-bold hover:text-purple-800 transition cursor-pointer">
                        <i class="ph ph-sparkle-fill text-sm"></i> IA Assistant Rédaction
                    </button>
                </div>
                <div class="border border-slate-300 rounded-xl overflow-hidden shadow-xs focus-within:border-[#031C5B] focus-within:ring-1 focus-within:ring-[#031C5B] transition">
                    <!-- Toolbar -->
                    <div class="bg-[#F8FAFC] border-b border-slate-200 px-4 py-2 flex items-center justify-between">
                        <div class="flex items-center gap-1 text-slate-600">
                            <button type="button" onclick="formatText('bold')" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white transition cursor-pointer"><i class="ph ph-text-b font-bold"></i></button>
                            <button type="button" onclick="formatText('italic')" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white transition cursor-pointer"><i class="ph ph-text-italic font-bold"></i></button>
                            <button type="button" onclick="formatText('link')" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-white transition cursor-pointer"><i class="ph ph-link font-bold"></i></button>
                        </div>
                        <span class="text-[11px] font-bold text-slate-400">Éditeur HTML & Markdown</span>
                    </div>
                    <!-- Textarea -->
                    <textarea name="message" id="broadcastMessage" rows="6" required placeholder="Rédigez votre message ici..." class="w-full p-4 text-[14px] font-medium text-slate-700 outline-none resize-none"></textarea>
                </div>
            </div>

            <!-- Delivery Channels (Interactive Toggle Cards) -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <label class="block text-[13px] font-bold text-slate-600">Canaux de Diffusion Multi-canaux *</label>
                    <span class="text-[11px] font-medium text-slate-400">Cliquez sur une carte pour activer/désactiver</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <!-- Email Card -->
                    <div onclick="toggleChannelCard(this)" class="channel-card active relative flex items-center justify-center gap-2.5 p-3.5 rounded-xl border-2 border-[#031C5B] bg-[#031C5B] text-white cursor-pointer shadow-xs transition select-none">
                        <input type="checkbox" name="channels[]" value="email" checked class="hidden channel-checkbox">
                        <i class="ph ph-envelope-simple text-xl font-bold"></i>
                        <span class="text-[13px] font-bold">Email</span>
                        <i class="ph ph-check-circle text-base font-bold check-icon"></i>
                    </div>

                    <!-- SMS Card -->
                    <div onclick="toggleChannelCard(this)" class="channel-card active relative flex items-center justify-center gap-2.5 p-3.5 rounded-xl border-2 border-[#031C5B] bg-[#031C5B] text-white cursor-pointer shadow-xs transition select-none">
                        <input type="checkbox" name="channels[]" value="sms" checked class="hidden channel-checkbox">
                        <i class="ph ph-chat-centered-text text-xl font-bold"></i>
                        <span class="text-[13px] font-bold">SMS</span>
                        <i class="ph ph-check-circle text-base font-bold check-icon"></i>
                    </div>

                    <!-- Push App Card -->
                    <div onclick="toggleChannelCard(this)" class="channel-card active relative flex items-center justify-center gap-2.5 p-3.5 rounded-xl border-2 border-[#031C5B] bg-[#031C5B] text-white cursor-pointer shadow-xs transition select-none">
                        <input type="checkbox" name="channels[]" value="push" checked class="hidden channel-checkbox">
                        <i class="ph ph-bell text-xl font-bold"></i>
                        <span class="text-[13px] font-bold">Push App</span>
                        <i class="ph ph-check-circle text-base font-bold check-icon"></i>
                    </div>

                    <!-- Bandeau Web Card -->
                    <div onclick="toggleChannelCard(this)" class="channel-card relative flex items-center justify-center gap-2.5 p-3.5 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 cursor-pointer shadow-xs transition select-none">
                        <input type="checkbox" name="channels[]" value="banner" class="hidden channel-checkbox">
                        <i class="ph ph-browser text-xl font-bold text-slate-400"></i>
                        <span class="text-[13px] font-bold">Bandeau Web</span>
                        <i class="ph ph-check-circle text-base font-bold check-icon hidden"></i>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-4">
                <button type="submit" name="priority" value="normal" class="px-6 py-2.5 rounded-xl border border-slate-300 text-slate-700 text-[14px] font-bold hover:bg-slate-50 transition shadow-xs cursor-pointer">
                    Enregistrer Brouillon
                </button>
                <button type="submit" name="priority" value="high" class="flex items-center gap-2 px-6 py-2.5 rounded-xl bg-[#031C5B] text-white text-[14px] font-bold hover:bg-blue-900 transition shadow-sm cursor-pointer">
                    <i class="ph ph-paper-plane-right text-lg font-bold"></i> Envoyer le Broadcast
                </button>
            </div>
            </form>
        </div>

        <!-- Right: Insights & Timeline -->
        <div class="flex flex-col gap-6">
            
            <!-- Performance Insight -->
            <div class="bg-[#FDF4FF] border border-[#F5D0FE] rounded-2xl shadow-sm p-6 relative overflow-hidden">
                <div class="flex items-center gap-3 mb-6 relative z-10">
                    <div class="w-8 h-8 rounded-xl bg-white border border-[#F0ABFC] text-[#7C3AED] flex items-center justify-center font-bold">
                        <i class="ph ph-chart-bar text-lg"></i>
                    </div>
                    <h3 class="text-[18px] font-extrabold text-[#111827]">Performance Insight IA</h3>
                </div>

                <div class="mb-6 relative z-10">
                    <p class="text-[11px] font-bold text-slate-500 mb-1">Taux d'Ouverture Global Réseau</p>
                    <div class="flex items-baseline gap-3">
                        <h2 class="text-[42px] font-extrabold text-[#031C5B] leading-none">94.8%</h2>
                        <span class="inline-flex items-center gap-1 bg-[#059669] text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                            <i class="ph ph-trend-up"></i> +6.2%
                        </span>
                    </div>
                    <p class="text-[13px] font-medium text-slate-500 mt-1">Dernière campagne d'annonces</p>
                </div>

                <div class="grid grid-cols-2 gap-4 border-t border-[#F5D0FE] pt-4 relative z-10">
                    <div>
                        <h4 class="text-[20px] font-extrabold text-[#111827] leading-none mb-1">38.4%</h4>
                        <p class="text-[12px] font-medium text-slate-500">Taux de Clics</p>
                    </div>
                    <div>
                        <h4 class="text-[20px] font-extrabold text-[#111827] leading-none mb-1">4.2k</h4>
                        <p class="text-[12px] font-medium text-slate-500">Comptes Touchés</p>
                    </div>
                </div>
            </div>

            <!-- Timeline (DB records) -->
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 flex-1 flex flex-col">
                <div class="flex items-center justify-between mb-6 border-b border-slate-100 pb-4">
                    <h3 class="text-[18px] font-extrabold text-[#111827]">Historique Broadcasts (BD SQL)</h3>
                </div>

                <div class="relative pl-4 space-y-6 before:absolute before:inset-0 before:left-[4px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-100">
                    @if(isset($messages) && count($messages) > 0)
                        @foreach($messages as $msg)
                        @php
                            $st = $msg->status ?? 'sent';
                            $statusClass = $st === 'sent' ? 'bg-[#D1FAE5] text-[#059669]' : 'bg-purple-100 text-purple-700';
                            $statusText = $st === 'sent' ? 'Diffusé' : 'Actif';
                            $dotColor = $st === 'sent' ? 'bg-[#059669]' : 'bg-[#7C3AED]';
                            
                            $dateStr = '08/08/2026';
                            if (!empty($msg->created_at)) {
                                $dateStr = is_string($msg->created_at) ? date('d/m/Y H:i', strtotime($msg->created_at)) : $msg->created_at->format('d/m/Y H:i');
                            }
                            
                            $targetsStr = 'Tous les établissements';
                            if (is_array($msg->target_roles) && count($msg->target_roles) > 0) {
                                $targetsStr = implode(', ', $msg->target_roles);
                            } elseif (!empty($msg->target_audience)) {
                                $targetsStr = $msg->target_audience;
                            }
                        @endphp
                        <div class="relative mb-6 last:mb-0">
                            <div class="absolute -left-[20px] top-1.5 w-2.5 h-2.5 rounded-full {{ $dotColor }} ring-4 ring-white"></div>
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="inline-flex {{ $statusClass }} text-[10px] font-bold px-2 py-0.5 rounded-full">{{ $statusText }}</span>
                                <span class="text-[11px] font-medium text-slate-400">{{ $dateStr }}</span>
                            </div>
                            <h4 class="text-[14px] font-bold text-slate-800 mb-0.5">{{ $msg->title }}</h4>
                            <p class="text-[12px] font-medium text-slate-500">Cible & Canaux : <span class="font-bold text-slate-700">{{ $targetsStr }}</span></p>
                        </div>
                        @endforeach
                    @else
                        <p class="text-xs text-slate-400 text-center py-6">Aucun broadcast enregistré.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
        function selectAudience(btn, audienceText) {
            document.querySelectorAll('.audience-btn').forEach(b => {
                b.className = 'audience-btn px-5 py-2 rounded-full bg-white text-slate-600 text-[13px] font-bold border border-slate-300 hover:bg-slate-50 transition shadow-xs cursor-pointer';
            });
            btn.className = 'audience-btn px-5 py-2 rounded-full bg-[#031C5B] text-white text-[13px] font-bold border border-[#031C5B] shadow-xs cursor-pointer';
            const hiddenInput = document.getElementById('targetAudienceInput');
            if (hiddenInput) hiddenInput.value = audienceText;
        }

        function toggleChannelCard(card) {
            const checkbox = card.querySelector('.channel-checkbox');
            const checkIcon = card.querySelector('.check-icon');
            const icon = card.querySelector('.ph');
            
            if (!checkbox) return;
            
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                card.className = 'channel-card active relative flex items-center justify-center gap-2.5 p-3.5 rounded-xl border-2 border-[#031C5B] bg-[#031C5B] text-white cursor-pointer shadow-xs transition select-none';
                if (checkIcon) checkIcon.classList.remove('hidden');
                if (icon) icon.classList.remove('text-slate-400');
            } else {
                card.className = 'channel-card relative flex items-center justify-center gap-2.5 p-3.5 rounded-xl border border-slate-300 bg-white text-slate-600 hover:bg-slate-50 cursor-pointer shadow-xs transition select-none';
                if (checkIcon) checkIcon.classList.add('hidden');
                if (icon) icon.classList.add('text-slate-400');
            }
        }

        function aiRewriteMessage() {
            const titleInput = document.getElementById('broadcastTitle');
            const msgInput = document.getElementById('broadcastMessage');
            const toast = document.getElementById('broadcastToast');
            const toastMsg = document.getElementById('broadcastToastMsg');

            fetch('{{ route("superadmin.broadcast.ai-rewrite") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    title: titleInput ? titleInput.value : '',
                    message: msgInput ? msgInput.value : ''
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    toastMsg.innerText = data.error || "Échec de la génération IA.";
                    toast.classList.remove('hidden', 'bg-purple-50', 'border-purple-200', 'text-purple-800');
                    toast.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-800');
                    return;
                }
                if (titleInput && data.title) titleInput.value = data.title;
                if (msgInput && data.message) msgInput.value = data.message;

                toastMsg.innerText = "Message réécrit par l'IA avec succès.";
                toast.classList.remove('hidden', 'bg-rose-50', 'border-rose-200', 'text-rose-800');
                toast.classList.add('bg-purple-50', 'border-purple-200', 'text-purple-800');
            })
            .catch(() => {
                toastMsg.innerText = "Erreur de communication avec le serveur.";
                toast.classList.remove('hidden', 'bg-purple-50', 'border-purple-200', 'text-purple-800');
                toast.classList.add('bg-rose-50', 'border-rose-200', 'text-rose-800');
            });
        }

        function formatText(type) {
            const msgInput = document.getElementById('broadcastMessage');
            if (!msgInput) return;
            if (type === 'bold') msgInput.value += ' **Texte en gras** ';
            if (type === 'italic') msgInput.value += ' *Texte en italique* ';
            if (type === 'link') msgInput.value += ' [Lien](https://academiaerp.com) ';
        }
    </script>
@endsection
