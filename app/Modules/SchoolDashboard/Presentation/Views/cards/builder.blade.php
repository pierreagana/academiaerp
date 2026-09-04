@extends('SchoolDashboard::layouts.app')

@php
    $frontEnabledMap = [];
    $backEnabledMap = [];
    foreach (array_keys($fieldCatalog) as $key) {
        $frontEnabledMap[$key] = in_array($key, $template->front_fields ?? []);
        $backEnabledMap[$key] = in_array($key, $template->back_fields ?? []);
    }
    $frontOrder = array_values(array_unique(array_merge($template->front_fields ?? [], array_keys($fieldCatalog))));
    $backOrder = array_values(array_unique(array_merge($template->back_fields ?? [], array_keys($fieldCatalog))));
@endphp

@section('content')
<div class="space-y-6"
     x-data="{
        editingFace: 'front',
        primaryColor: {{ \Illuminate\Support\Js::from($template->primary_color) }},
        backgroundColor: {{ \Illuminate\Support\Js::from($template->background_color) }},
        textColor: {{ \Illuminate\Support\Js::from($template->text_color) }},
        orientation: {{ \Illuminate\Support\Js::from($template->orientation) }},
        photoPosition: {{ \Illuminate\Support\Js::from($template->photo_position) }},
        logoPreview: {{ $template->logo_path ? \Illuminate\Support\Js::from(asset('storage/' . $template->logo_path)) : 'null' }},
        watermarkPreview: {{ $template->watermark_path ? \Illuminate\Support\Js::from(asset('storage/' . $template->watermark_path)) : 'null' }},
        frontOrder: {{ \Illuminate\Support\Js::from($frontOrder) }},
        backOrder: {{ \Illuminate\Support\Js::from($backOrder) }},
        frontEnabled: {{ \Illuminate\Support\Js::from($frontEnabledMap) }},
        backEnabled: {{ \Illuminate\Support\Js::from($backEnabledMap) }},
        fieldLabels: {{ \Illuminate\Support\Js::from($fieldCatalog) }},
        previewData: {{ \Illuminate\Support\Js::from($previewData) }},
        moveField(face, key, direction) {
            const arr = face === 'front' ? this.frontOrder : this.backOrder;
            const idx = arr.indexOf(key);
            const swapWith = direction === 'up' ? idx - 1 : idx + 1;
            if (swapWith < 0 || swapWith >= arr.length) return;
            [arr[idx], arr[swapWith]] = [arr[swapWith], arr[idx]];
        },
        visibleFields() {
            const order = this.editingFace === 'front' ? this.frontOrder : this.backOrder;
            const enabled = this.editingFace === 'front' ? this.frontEnabled : this.backEnabled;
            return order.filter(k => enabled[k]);
        }
     }">

    <div>
        <h2 class="text-[32px] font-bold text-[#031C5B] tracking-tight">{{ \App\Modules\Cards\Domain\Models\CardTemplate::TYPES[$type] }}</h2>
        <p class="text-slate-600 text-[15px] font-medium mt-1">Configurez la mise en page, l'identité visuelle et les champs de données de la carte.</p>
    </div>

    @if(session('success'))
    <div class="p-4 text-sm text-emerald-800 rounded-xl bg-emerald-50 flex items-center gap-2 border border-emerald-100" role="alert">
        <i class="ph-fill ph-check-circle text-lg"></i>
        <span class="font-bold">{{ session('success') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <!-- Left: Builder controls -->
        <form method="POST" action="{{ route('school.academic.cards.store', $type) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Editing Face -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3">Face en Édition</p>
                <div class="flex items-center gap-5">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="editingFace" value="front" class="text-[#031C5B] focus:ring-[#031C5B]">
                        <span class="text-[13px] font-semibold text-slate-700">Recto</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" x-model="editingFace" value="back" class="text-[#031C5B] focus:ring-[#031C5B]">
                        <span class="text-[13px] font-semibold text-slate-700">Verso</span>
                    </label>
                </div>
            </div>

            <!-- Format -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3">Orientation de la Carte</p>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(\App\Modules\Cards\Domain\Models\CardTemplate::ORIENTATIONS as $value => $label)
                        <label class="flex items-center justify-center gap-2 border rounded-lg px-3 py-2.5 cursor-pointer transition" :class="orientation === '{{ $value }}' ? 'border-[#031C5B] bg-blue-50/50' : 'border-slate-200'">
                            <input type="radio" name="orientation" value="{{ $value }}" x-model="orientation" class="text-[#031C5B] focus:ring-[#031C5B]">
                            <span class="text-[13px] font-semibold text-slate-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Branding & Layout -->
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
                </div>

                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Filigrane <span class="text-slate-400 font-medium">(image en fond, semi-transparente)</span></label>
                    <div class="flex items-center gap-3">
                        <div class="w-14 h-14 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="watermarkPreview"><img :src="watermarkPreview" class="w-full h-full object-cover opacity-50"></template>
                            <template x-if="!watermarkPreview"><i class="ph-bold ph-drop text-slate-400 text-xl"></i></template>
                        </div>
                        <label class="px-3 py-2 border border-slate-200 rounded-lg text-[12.5px] font-bold text-slate-600 hover:bg-slate-50 cursor-pointer transition">
                            Choisir un Filigrane
                            <input type="file" name="watermark" accept="image/jpeg,image/png,image/jpg" class="hidden"
                                @change="
                                    if ($event.target.files[0]) {
                                        let reader = new FileReader();
                                        reader.onload = (e) => { watermarkPreview = e.target.result; };
                                        reader.readAsDataURL($event.target.files[0]);
                                    }
                                ">
                        </label>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Couleur Principale</label>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            @foreach(\App\Modules\Cards\Domain\Models\CardTemplate::COLOR_SWATCHES as $swatch)
                            <button type="button" @click="primaryColor = '{{ $swatch }}'" class="w-6 h-6 rounded-full border-2 transition" :class="primaryColor === '{{ $swatch }}' ? 'border-slate-800' : 'border-transparent'" style="background-color: {{ $swatch }}"></button>
                            @endforeach
                            <input type="color" name="primary_color" x-model="primaryColor" class="w-6 h-6 rounded-full border border-slate-200 cursor-pointer p-0">
                        </div>
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

                <div>
                    <label class="block text-[12px] font-bold text-slate-600 mb-1.5">Position de la Photo</label>
                    <select name="photo_position" x-model="photoPosition" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2.5 text-[13px] outline-none focus:border-[#031C5B]">
                        @foreach(\App\Modules\Cards\Domain\Models\CardTemplate::PHOTO_POSITIONS as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Displayed Data -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-[15px] font-bold text-slate-900 flex items-center gap-2 mb-4"><i class="ph-bold ph-list-checks text-[#031C5B]"></i> Données Affichées <span x-text="editingFace === 'front' ? '(Recto)' : '(Verso)'"></span></h3>

                <div x-show="editingFace === 'front'" class="space-y-1.5">
                    <template x-for="key in frontOrder" :key="key">
                        <div class="flex items-center gap-2 py-1.5">
                            <div class="flex flex-col shrink-0">
                                <button type="button" @click="moveField('front', key, 'up')" class="w-5 h-4 flex items-center justify-center text-slate-400 hover:text-[#031C5B]"><i class="ph-bold ph-caret-up text-[11px]"></i></button>
                                <button type="button" @click="moveField('front', key, 'down')" class="w-5 h-4 flex items-center justify-center text-slate-400 hover:text-[#031C5B]"><i class="ph-bold ph-caret-down text-[11px]"></i></button>
                            </div>
                            <span class="flex-1 text-[13px] font-medium text-slate-700" x-text="fieldLabels[key]"></span>
                            <input type="checkbox" x-model="frontEnabled[key]" class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                        </div>
                    </template>
                </div>

                <div x-show="editingFace === 'back'" x-cloak class="space-y-1.5">
                    <template x-for="key in backOrder" :key="key">
                        <div class="flex items-center gap-2 py-1.5">
                            <div class="flex flex-col shrink-0">
                                <button type="button" @click="moveField('back', key, 'up')" class="w-5 h-4 flex items-center justify-center text-slate-400 hover:text-[#031C5B]"><i class="ph-bold ph-caret-up text-[11px]"></i></button>
                                <button type="button" @click="moveField('back', key, 'down')" class="w-5 h-4 flex items-center justify-center text-slate-400 hover:text-[#031C5B]"><i class="ph-bold ph-caret-down text-[11px]"></i></button>
                            </div>
                            <span class="flex-1 text-[13px] font-medium text-slate-700" x-text="fieldLabels[key]"></span>
                            <input type="checkbox" x-model="backEnabled[key]" class="rounded border-slate-300 text-[#031C5B] focus:ring-[#031C5B]">
                        </div>
                    </template>
                </div>
            </div>

            <!-- hidden inputs reflecting final enabled+ordered field arrays -->
            <template x-for="key in frontOrder.filter(k => frontEnabled[k])" :key="'fh-' + key">
                <input type="hidden" name="front_fields[]" :value="key">
            </template>
            <template x-for="key in backOrder.filter(k => backEnabled[k])" :key="'bh-' + key">
                <input type="hidden" name="back_fields[]" :value="key">
            </template>

            <button type="submit" class="w-full px-4 py-3.5 bg-[#031C5B] text-white rounded-xl text-[14px] font-bold hover:bg-[#031C5B]/90 transition">
                Enregistrer le Modèle
            </button>
        </form>

        <!-- Right: Live Preview -->
        <div class="lg:sticky lg:top-6 space-y-4">
            <div class="flex items-center justify-between">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 rounded-full text-[12px] font-bold text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aperçu en Direct</span>
                @if($records->isNotEmpty())
                <form method="GET">
                    <select name="preview" onchange="this.form.submit()" class="bg-white border border-slate-200 rounded-lg px-3 py-1.5 text-[12.5px] font-semibold text-slate-600 outline-none focus:border-[#031C5B]">
                        @foreach($records as $record)
                            <option value="{{ $record->preview_key }}" {{ $selected && $selected->preview_key === $record->preview_key ? 'selected' : '' }}>{{ $record->first_name }} {{ $record->last_name }}</option>
                        @endforeach
                    </select>
                </form>
                @endif
            </div>

            <div class="flex justify-center py-8 bg-slate-100 rounded-2xl overflow-x-auto">
                <div class="rounded-2xl shadow-xl overflow-hidden shrink-0" :style="'width: ' + (orientation === 'landscape' ? '460px' : '320px')">
                    <div class="p-5 flex items-center gap-3" :style="'background-color: ' + primaryColor">
                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center overflow-hidden shrink-0">
                            <template x-if="logoPreview"><img :src="logoPreview" class="w-full h-full object-cover"></template>
                            <template x-if="!logoPreview"><i class="ph-fill ph-graduation-cap text-lg" :style="'color: ' + primaryColor"></i></template>
                        </div>
                        <div class="text-white min-w-0">
                            <p class="font-extrabold text-[15px] leading-tight truncate">{{ $school->name ?? 'École' }}</p>
                            <p class="text-[11px] opacity-80 truncate">{{ $school->city ?? $school->address ?? '' }}</p>
                        </div>
                    </div>
                    <div class="relative p-5 overflow-hidden" :style="'background-color: ' + backgroundColor">
                        <template x-if="watermarkPreview">
                            <img :src="watermarkPreview" class="absolute inset-0 w-full h-full object-contain opacity-10 pointer-events-none" style="padding: 24px;">
                        </template>
                        @if($records->isEmpty())
                            <p class="relative text-center text-slate-400 text-[13px] py-10">{{ $type === 'student' ? 'Aucun élève enregistré pour prévisualiser la carte.' : 'Aucun employé enregistré pour prévisualiser la carte.' }}</p>
                        @else
                            <div class="relative" :class="orientation === 'landscape' ? 'flex gap-4 items-start' : ''">
                                <div class="mb-4 shrink-0" :class="orientation === 'landscape' ? '' : {'flex justify-center': photoPosition === 'center', 'flex justify-end': photoPosition === 'right'}">
                                    <div class="rounded-xl bg-slate-100 overflow-hidden flex items-center justify-center shrink-0" :class="orientation === 'landscape' ? 'w-20 h-20' : 'w-24 h-24'">
                                        @if($previewPhoto)
                                            <img src="{{ $previewPhoto }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="ph-bold ph-user text-3xl text-slate-300"></i>
                                        @endif
                                    </div>
                                </div>
                                <div :class="orientation === 'landscape' ? 'grid grid-cols-2 gap-x-4 gap-y-3 flex-1' : ''">
                                    <template x-for="key in visibleFields()" :key="key">
                                        <div :class="orientation === 'portrait' ? 'mb-3' : ''">
                                            <p class="text-[10px] font-bold uppercase tracking-wider opacity-60" :style="'color: ' + textColor" x-text="fieldLabels[key]"></p>
                                            <p class="text-[15px] font-bold" :style="'color: ' + textColor" x-text="previewData[key] || '-'"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        @endif
                        @if($qrData)
                            <div class="absolute bottom-4 right-4 bg-white p-1 rounded-md shadow-sm" id="card-qrcode" title="{{ $qrData }}"></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@if($qrData)
@push('scripts')
<script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var container = document.getElementById('card-qrcode');
        if (container && window.QRCode) {
            new QRCode(container, {
                text: {{ \Illuminate\Support\Js::from($qrData) }},
                width: 56,
                height: 56,
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    });
</script>
@endpush
@endif
