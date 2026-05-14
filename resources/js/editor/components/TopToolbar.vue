<script setup>
defineProps({
    floorplan: {
        type: Object,
        default: null,
    },
    event: {
        type: Object,
        default: null,
    },
    saving: {
        type: Boolean,
        default: false,
    },
    homeUrl: {
        type: String,
        required: true,
    },
    logoUrl: {
        type: String,
        required: true,
    },
    backUrl: {
        type: String,
        required: true,
    },
    guestExportUrl: {
        type: String,
        required: true,
    },
    previewUrl: {
        type: String,
        default: '',
    },
    seatedCount: {
        type: Number,
        default: 0,
    },
    guestCount: {
        type: Number,
        default: 0,
    },
    zoom: {
        type: Number,
        default: 1,
    },
    canUndo: {
        type: Boolean,
        default: false,
    },
    canRedo: {
        type: Boolean,
        default: false,
    },
    hasSelection: {
        type: Boolean,
        default: false,
    },
    panMode: {
        type: Boolean,
        default: false,
    },
});

defineEmits([
    'zoom-in',
    'zoom-out',
    'reset-zoom',
    'toggle-pan',
    'undo',
    'redo',
    'duplicate-selected',
    'delete-selected',
    'save',
    'export-pdf',
]);
</script>

<template>
    <header class="editor-toolbar">
        <div class="editor-heading">
            <a :href="homeUrl" class="editor-brand" aria-label="الصفحة الرئيسية">
                <img :src="logoUrl" alt="بيرفكشن" class="editor-brand-logo">
            </a>

            <div class="editor-title">
                <span>{{ event?.name || 'الحدث' }}</span>
                <strong>{{ floorplan?.name || 'المخطط' }}</strong>
            </div>
        </div>

        <div class="editor-toolbar-actions">
            <span class="editor-counter">{{ seatedCount }} / {{ guestCount }} تم تجليسهم</span>
            <div class="editor-tool-group" aria-label="أدوات التحرير">
                <button type="button" class="editor-icon-btn" :disabled="!canUndo" title="تراجع" @click="$emit('undo')">↶</button>
                <button type="button" class="editor-icon-btn" :disabled="!canRedo" title="إعادة" @click="$emit('redo')">↷</button>
                <button type="button" class="editor-icon-btn editor-copy-btn" :disabled="!hasSelection" title="نسخ العنصر" @click="$emit('duplicate-selected')">
                    <svg class="editor-copy-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.05" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect class="editor-copy-icon-back" width="10" height="10" x="5" y="5" rx="2.5"></rect>
                        <rect class="editor-copy-icon-front" width="10" height="10" x="9" y="9" rx="2.5"></rect>
                        <path d="M12 14h4"></path>
                    </svg>
                </button>
                <button type="button" class="editor-icon-btn danger" :disabled="!hasSelection" title="حذف العنصر" @click="$emit('delete-selected')">×</button>
            </div>
            <div class="editor-tool-group" aria-label="تكبير المخطط">
                <button type="button" class="editor-icon-btn" title="تصغير" @click="$emit('zoom-out')">−</button>
                <button type="button" class="editor-zoom-value" title="إعادة التكبير" @click="$emit('reset-zoom')">{{ Math.round(zoom * 100) }}%</button>
                <button type="button" class="editor-icon-btn" title="تكبير" @click="$emit('zoom-in')">+</button>
                <button
                    type="button"
                    class="editor-icon-btn"
                    :class="{ active: panMode }"
                    title="تحريك مساحة المخطط"
                    @click="$emit('toggle-pan')"
                >
                    ⇅
                </button>
            </div>
            <button type="button" class="editor-btn primary" :disabled="saving" @click="$emit('save')">
                حفظ المخطط
            </button>
            <a v-if="previewUrl" :href="previewUrl" target="_blank" rel="noopener" class="editor-btn secondary">معاينة</a>
            <a :href="guestExportUrl" class="editor-btn secondary">تصدير الضيوف Excel</a>
            <button type="button" class="editor-btn secondary" :disabled="saving" @click="$emit('export-pdf')">تصدير PDF</button>
            <a :href="backUrl" class="editor-btn secondary">رجوع للحدث</a>
        </div>
    </header>
</template>
