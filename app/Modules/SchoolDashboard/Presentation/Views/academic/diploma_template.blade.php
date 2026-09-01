@extends('SchoolDashboard::layouts.app')

@section('content')
<div class="space-y-6"
     x-data="{
        title: {{ \Illuminate\Support\Js::from($template->title) }},
        subtitle: {{ \Illuminate\Support\Js::from($template->subtitle) }},
        bodyText: {{ \Illuminate\Support\Js::from($template->body_text ?: '') }},
        orientation: {{ \Illuminate\Support\Js::from($template->orientation) }},
        borderStyle: {{ \Illuminate\Support\Js::from($template->border_style) }},
        layout: {{ \Illuminate\Support\Js::from($template->layout ?: 'classic') }},
        primaryColor: {{ \Illuminate\Support\Js::from($template->primary_color) }},
        backgroundColor: {{ \Illuminate\Support\Js::from($template->background_color) }},
        textColor: {{ \Illuminate\Support\Js::from($template->text_color) }},
        logoPreview: {{ $template->logo_path ? \Illuminate\Support\Js::from(asset('storage/' . $template->logo_path)) : 'null' }},
        sealPreview: {{ $template->seal_path ? \Illuminate\Support\Js::from(asset('storage/' . $template->seal_path)) : 'null' }},
        backgroundImagePreview: {{ $template->background_image_path ? \Illuminate\Support\Js::from(asset('storage/' . $template->background_image_path)) : 'null' }},
        signature1Name: {{ \Illuminate\Support\Js::from($template->signature_1_name) }},
        signature1Title: {{ \Illuminate\Support\Js::from($template->signature_1_title) }},
        signature2Name: {{ \Illuminate\Support\Js::from($template->signature_2_name) }},
        signature2Title: {{ \Illuminate\Support\Js::from($template->signature_2_title) }},
        previewData: {{ \Illuminate\Support\Js::from($previewData) }},
        fieldsLayout: {{ $template->fields_layout ? \Illuminate\Support\Js::from($template->fields_layout) : '{}' }},
        fieldValues: {{ \Illuminate\Support\Js::from($fieldValues) }},
        fieldLabels: {{ \Illuminate\Support\Js::from(\App\Modules\Academic\Domain\Models\DiplomaTemplate::AVAILABLE_FIELDS) }},
        draggingKey: null,
        fieldState(key, defaultX = 50, defaultY = 50) {
            if (!this.fieldsLayout[key]) {
                this.fieldsLayout[key] = { enabled: false, x: defaultX, y: defaultY };
            }
            return this.fieldsLayout[key];
        },
        startDrag(key, event) {
            this.draggingKey = key;
            event.preventDefault();
        },
        onDragMove(event) {
            if (!this.draggingKey) return;
            const rect = this.$refs.canvas.getBoundingClientRect();
            const clientX = event.touches ? event.touches[0].clientX : event.clientX;
            const clientY = event.touches ? event.touches[0].clientY : event.clientY;
            let x = ((clientX - rect.left) / rect.width) * 100;
            let y = ((clientY - rect.top) / rect.height) * 100;
            this.fieldState(this.draggingKey).x = Math.max(0, Math.min(100, x));
            this.fieldState(this.draggingKey).y = Math.max(0, Math.min(100, y));
        },
        stopDrag() {
            this.draggingKey = null;
        },
        toggleField(key, checked) {
            this.fieldState(key).enabled = checked;
            const token = '{' + key + '}';
            if (checked) {
                if (!this.bodyText.includes(token)) {
                    this.bodyText = this.bodyText ? (this.bodyText.replace(/\s+$/, '') + ' ' + token) : token;
                }
            } else {
                this.bodyText = this.bodyText.split(token).join('').replace(/[ \t]{2,}/g, ' ').replace(/\n{3,}/g, '\n\n').trim();
            }
        },
        renderedBody() {
            let out = this.bodyText || '';
            const aliases = {
                recipient: this.fieldValues.recipient_name,
                award: this.fieldValues.award_name,
                reason: this.fieldValues.reason,
                date: this.fieldValues.awarded_date,
                school: this.fieldValues.school_name,
            };
            const merged = Object.assign({}, aliases, this.fieldValues);
            for (const key in merged) {
                out = out.split('{' + key + '}').join(merged[key] || '');
            }
            return out;
        },
        containerClasses() {
            return 'shadow-xl shrink-0 relative flex flex-col p-10 ' + (this.layout === 'modern' ? 'items-start text-left' : 'items-center text-center');
        },
        containerStyle() {
            let s = 'background-color: ' + this.backgroundColor + '; color: ' + this.textColor + ';';
            if (this.backgroundImagePreview) {
                s += ' background-image: url(\'' + this.backgroundImagePreview + '\'); background-size: cover; background-position: center;';
            }
            s += ' width: ' + (this.orientation === 'landscape' ? '620px' : '440px') + '; min-height: ' + (this.orientation === 'landscape' ? '440px' : '620px') + ';';
            if (this.layout === 'modern') {
                s += ' border-left: 12px solid ' + this.primaryColor + ';';
            } else if (this.borderStyle === 'classic') {
                s += ' border: 10px double ' + this.primaryColor + ';';
            } else if (this.borderStyle === 'modern') {
                s += ' border: 3px solid ' + this.primaryColor + ';';
            }
            if (this.layout === 'elegant') {
                s += ' font-family: Georgia, \'Times New Roman\', serif;';
            }
            return s;
        },
        recipientStyle() {
            return this.layout === 'modern' ? 'font-weight: 800;' : 'font-family: \'Brush Script MT\', cursive; font-size: 30px;';
        }
     }"
     @mousemove.window="onDragMove($event)"
     @mouseup.window="stopDrag()"
     @touchmove.window="onDragMove($event)"
     @touchend.window="stopDrag()">

    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">Modèle de Diplôme</h2>
            @if($awardType)
                <p class="text-slate-600 text-[15px] font-medium mt-1">Pour la récompense <span class="font-bold text-[#031C5B]">{{ $awardType->name }}</span>, avec aperçu en direct.</p>
                @if(!$template->exists)
                    <span class="inline-flex items-center gap-1.5 mt-2 text-[11.5px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full bg-amber-50 text-amber-700"><i class="ph-bold ph-warning"></i> Pas encore configuré — cette récompense ne sera pas attribuable tant que vous n'enregistrez pas ce modèle</span>
                @endif
            @else
                <p class="text-slate-600 text-[15px] font-medium mt-1">Modèle par défaut, utilisé par les récompenses du catalogue standard. Aperçu en direct.</p>
            @endif
        </div>
        <a href="{{ route('school.academic.awards.models.index') }}" class="px-4 py-2 bg-white border border-slate-200 hover:border-slate-300 text-slate-600 font-bold text-[13px] rounded-xl hover:bg-slate-50 transition shrink-0">
            <i class="ph-bold ph-arrow-left"></i> Retour
        </a>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 text-sm text-red-800 rounded-xl bg-red-50" role="alert">
        <div class="flex items-center gap-2 mb-2">
            <i class="ph-fill ph-warning-circle text-lg"></i>
            <span class="font-bold">Il y a des erreurs dans le formulaire :</span>
        </div>
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Left: Builder controls -->
        <form method="POST" action="{{ route('school.academic.awards.template.update') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <input type="hidden" name="award_type_id" value="{{ $awardType->id ?? '' }}">
            <input type="hidden" name="fields_layout" :value="JSON.stringify(fieldsLayout)">

            <!-- Texte -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-bold ph-text-aa text-[#031C5B]"></i> Texte du Diplôme</h3>

                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Titre</label>
                    <input type="text" name="title" x-model="title" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Sous-titre</label>
                    <input type="text" name="subtitle" x-model="subtitle" required class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                </div>
                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Corps du texte</label>
                    <textarea name="body_text" x-model="bodyText" required rows="4" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]"></textarea>
                    <p class="text-[11px] text-slate-400 mt-1.5">
                        Variables disponibles : <code class="bg-slate-100 px-1 rounded">{recipient}</code> <code class="bg-slate-100 px-1 rounded">{award}</code> <code class="bg-slate-100 px-1 rounded">{reason}</code> <code class="bg-slate-100 px-1 rounded">{date}</code> <code class="bg-slate-100 px-1 rounded">{school}</code>
                        — ainsi que tous les <span class="font-bold">Champs Additionnels</span> ci-dessous, ex.
                        <template x-for="key in Object.keys(fieldLabels)" :key="'hint-' + key">
                            <code class="bg-slate-100 px-1 rounded" x-text="'{' + key + '}'"></code>
                        </template>
                    </p>
                </div>
            </div>

            <!-- Champs Additionnels -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-bold ph-list-checks text-[#031C5B]"></i> Champs Additionnels</h3>
                <p class="text-[11.5px] text-slate-400">Cochez les informations à insérer automatiquement dans le corps du texte ci-dessus.</p>
                <div class="grid grid-cols-2 gap-2">
                    <template x-for="key in Object.keys(fieldLabels)" :key="'chk-' + key">
                        <label class="flex items-center gap-2 px-2.5 py-2 rounded-lg bg-slate-50 cursor-pointer">
                            <input type="checkbox" :checked="fieldState(key).enabled" @change="toggleField(key, $event.target.checked)" class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                            <span class="text-[12.5px] font-medium text-slate-700" x-text="fieldLabels[key]"></span>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Format -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-bold ph-layout text-[#031C5B]"></i> Mise en Page</h3>
                <div>
                    <p class="text-[12px] font-bold text-slate-600 mb-1.5">Modèle</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(\App\Modules\Academic\Domain\Models\DiplomaTemplate::LAYOUTS as $value => $label)
                            <label class="flex items-center justify-center text-center border rounded-lg px-2 py-2.5 cursor-pointer transition" :class="layout === '{{ $value }}' ? 'border-[#031C5B] bg-blue-50/50' : 'border-slate-200'">
                                <input type="radio" name="layout" value="{{ $value }}" x-model="layout" class="hidden">
                                <span class="text-[12.5px] font-semibold text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-[12px] font-bold text-slate-600 mb-1.5">Orientation</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(\App\Modules\Academic\Domain\Models\DiplomaTemplate::ORIENTATIONS as $value => $label)
                            <label class="flex items-center justify-center gap-2 border rounded-lg px-3 py-2.5 cursor-pointer transition" :class="orientation === '{{ $value }}' ? 'border-[#031C5B] bg-blue-50/50' : 'border-slate-200'">
                                <input type="radio" name="orientation" value="{{ $value }}" x-model="orientation" class="text-[#031C5B] focus:ring-[#031C5B]">
                                <span class="text-[13px] font-semibold text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-[12px] font-bold text-slate-600 mb-1.5">Bordure</p>
                    <select name="border_style" x-model="borderStyle" :class="layout === 'modern' ? 'opacity-50' : ''" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                        @foreach(\App\Modules\Academic\Domain\Models\DiplomaTemplate::BORDER_STYLES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p x-show="layout === 'modern'" x-cloak class="text-[11px] text-slate-400 mt-1">Le modèle Moderne utilise une barre d'accent à la place de la bordure.</p>
                </div>
            </div>

            <!-- Identité Visuelle -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-bold ph-palette text-[#031C5B]"></i> Identité Visuelle</h3>

                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Logo de l'École</label>
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="logoPreview"><img :src="logoPreview" class="w-full h-full object-cover"></template>
                            <template x-if="!logoPreview"><i class="ph-bold ph-image text-slate-400 text-xl"></i></template>
                        </div>
                        <label class="px-3 py-2 border border-slate-200 rounded-lg text-[12.5px] font-bold text-slate-600 hover:bg-slate-50 cursor-pointer transition">
                            Choisir un Logo
                            <input type="file" name="logo" accept="image/jpeg,image/png,image/jpg" class="hidden"
                                @change="
                                    if ($event.target.files[0]) {
                                        let reader = new FileReader();
                                        reader.onload = (e) => { logoPreview = e.target.result; };
                                        reader.readAsDataURL($event.target.files[0]);
                                    }
                                ">
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5">Glissez le logo directement sur l'aperçu pour le positionner.</p>
                </div>

                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Sceau / Cachet <span class="text-slate-400 font-medium">(optionnel)</span></label>
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="sealPreview"><img :src="sealPreview" class="w-full h-full object-cover"></template>
                            <template x-if="!sealPreview"><i class="ph-bold ph-seal-check text-slate-400 text-xl"></i></template>
                        </div>
                        <label class="px-3 py-2 border border-slate-200 rounded-lg text-[12.5px] font-bold text-slate-600 hover:bg-slate-50 cursor-pointer transition">
                            Choisir un Sceau
                            <input type="file" name="seal" accept="image/jpeg,image/png,image/jpg" class="hidden"
                                @change="
                                    if ($event.target.files[0]) {
                                        let reader = new FileReader();
                                        reader.onload = (e) => { sealPreview = e.target.result; };
                                        reader.readAsDataURL($event.target.files[0]);
                                    }
                                ">
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1.5">Glissez le sceau directement sur l'aperçu pour le positionner.</p>
                </div>

                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Image de Fond <span class="text-slate-400 font-medium">(optionnel, recouvre la couleur de fond)</span></label>
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="backgroundImagePreview"><img :src="backgroundImagePreview" class="w-full h-full object-cover"></template>
                            <template x-if="!backgroundImagePreview"><i class="ph-bold ph-image-square text-slate-400 text-xl"></i></template>
                        </div>
                        <label class="px-3 py-2 border border-slate-200 rounded-lg text-[12.5px] font-bold text-slate-600 hover:bg-slate-50 cursor-pointer transition">
                            Choisir une Image
                            <input type="file" name="background_image" accept="image/jpeg,image/png,image/jpg" class="hidden"
                                @change="
                                    if ($event.target.files[0]) {
                                        let reader = new FileReader();
                                        reader.onload = (e) => { backgroundImagePreview = e.target.result; };
                                        reader.readAsDataURL($event.target.files[0]);
                                    }
                                ">
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Couleur Principale</label>
                        <input type="color" name="primary_color" x-model="primaryColor" class="w-full h-9 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Couleur de Fond</label>
                        <input type="color" name="background_color" x-model="backgroundColor" class="w-full h-9 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Couleur du Texte</label>
                        <input type="color" name="text_color" x-model="textColor" class="w-full h-9 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                    </div>
                </div>
            </div>

            <!-- Signatures -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2"><i class="ph-bold ph-signature text-[#031C5B]"></i> Signatures</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Nom (1)</label>
                        <input type="text" name="signature_1_name" x-model="signature1Name" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Fonction (1)</label>
                        <input type="text" name="signature_1_title" x-model="signature1Title" placeholder="Ex: Le Directeur" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Nom (2)</label>
                        <input type="text" name="signature_2_name" x-model="signature2Name" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    </div>
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Fonction (2)</label>
                        <input type="text" name="signature_2_title" x-model="signature2Title" placeholder="Ex: Le Fondateur" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full px-4 py-3.5 bg-[#031C5B] text-white rounded-xl text-[14px] font-bold hover:bg-[#031C5B]/90 transition">
                Enregistrer le Modèle
            </button>
        </form>

        <!-- Right: Live Preview -->
        <div class="lg:sticky lg:top-6 space-y-4">
            <div class="flex items-center justify-between gap-2 flex-wrap">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-full text-[12px] font-bold text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aperçu en Direct</span>
                @if($awards->isNotEmpty())
                <form method="GET">
                    @if($awardType)<input type="hidden" name="award_type_id" value="{{ $awardType->id }}">@endif
                    <select name="preview" onchange="this.form.submit()" class="bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-[12.5px] font-semibold text-slate-600 outline-none focus:border-[#031C5B]">
                        @foreach($awards as $award)
                            <option value="{{ $award->id }}" {{ $selectedAward && $selectedAward->id === $award->id ? 'selected' : '' }}>{{ $award->recipientName }} — {{ $award->type->name ?? '' }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
            </div>

            <div class="flex justify-center py-8 bg-slate-100 rounded-2xl overflow-x-auto">
                <div x-ref="canvas" :class="containerClasses()" :style="containerStyle()">

                    <template x-if="logoPreview">
                        <img :src="logoPreview"
                             class="absolute w-14 h-14 object-contain cursor-move z-10"
                             :style="'left: ' + fieldState('logo', 50, 12).x + '%; top: ' + fieldState('logo', 50, 12).y + '%; transform: translate(-50%,-50%);'"
                             @mousedown="startDrag('logo', $event)"
                             @touchstart="startDrag('logo', $event)">
                    </template>

                    <template x-if="sealPreview">
                        <img :src="sealPreview"
                             class="absolute w-16 h-16 object-contain opacity-90 cursor-move z-10"
                             :style="'left: ' + fieldState('seal', 50, 88).x + '%; top: ' + fieldState('seal', 50, 88).y + '%; transform: translate(-50%,-50%);'"
                             @mousedown="startDrag('seal', $event)"
                             @touchstart="startDrag('seal', $event)">
                    </template>

                    <template x-if="layout === 'elegant'">
                        <div>
                            <span class="absolute top-3 left-3 w-5 h-5 border-t-2 border-l-2 pointer-events-none" :style="'border-color: ' + primaryColor"></span>
                            <span class="absolute top-3 right-3 w-5 h-5 border-t-2 border-r-2 pointer-events-none" :style="'border-color: ' + primaryColor"></span>
                            <span class="absolute bottom-3 left-3 w-5 h-5 border-b-2 border-l-2 pointer-events-none" :style="'border-color: ' + primaryColor"></span>
                            <span class="absolute bottom-3 right-3 w-5 h-5 border-b-2 border-r-2 pointer-events-none" :style="'border-color: ' + primaryColor"></span>
                        </div>
                    </template>

                    <div class="w-full flex gap-3 mb-1" :class="layout === 'modern' ? 'items-center justify-start' : 'items-center justify-center flex-col gap-1'">
                        <p class="text-[11px] font-bold uppercase tracking-[3px] opacity-70" x-text="previewData.school"></p>
                    </div>

                    <p class="text-[30px] font-extrabold tracking-wide mt-4" :style="'color: ' + primaryColor" x-text="title"></p>
                    <p class="text-[14px] font-semibold uppercase tracking-wider mt-2 opacity-80" x-text="subtitle"></p>

                    <p class="mt-4" :style="'font-size: 26px; ' + recipientStyle()" x-text="previewData.recipient"></p>

                    <p class="text-[13px] leading-relaxed mt-4 max-w-md whitespace-pre-line" x-text="renderedBody()"></p>

                    <div class="flex items-end justify-between w-full mt-auto pt-10 gap-8">
                        <div class="flex-1 text-center">
                            <template x-if="signature1Name"><p class="text-[13px] font-bold border-t border-current pt-1.5 mt-6" x-text="signature1Name"></p></template>
                            <template x-if="!signature1Name"><div class="border-t border-current pt-1.5 mt-6 opacity-40 text-[11px]">&nbsp;</div></template>
                            <p class="text-[10.5px] opacity-70" x-text="signature1Title"></p>
                        </div>
                        <div class="flex-1 text-center">
                            <template x-if="signature2Name"><p class="text-[13px] font-bold border-t border-current pt-1.5 mt-6" x-text="signature2Name"></p></template>
                            <template x-if="!signature2Name"><div class="border-t border-current pt-1.5 mt-6 opacity-40 text-[11px]">&nbsp;</div></template>
                            <p class="text-[10.5px] opacity-70" x-text="signature2Title"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
