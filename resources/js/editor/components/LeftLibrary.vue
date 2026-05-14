<script setup>
import { ELEMENT_LIBRARY } from '../composables/useEditorElements';
import { TABLE_SHAPES } from '../composables/useSeatLayout';

defineEmits(['add-table', 'add-element', 'collapse']);
</script>

<template>
    <aside class="editor-side-panel library-panel">
        <button
            type="button"
            class="side-collapse-btn library-collapse-btn"
            aria-label="إغلاق عناصر الإضافة"
            title="إغلاق عناصر الإضافة"
            @click="$emit('collapse')"
        >
            ‹
        </button>

        <div class="side-panel-scroll">
            <div class="panel-heading compact-heading">
                <h2>إضافة عناصر</h2>
            </div>
            <p>أضف الطاولات وعناصر القاعة الأساسية، ثم حرّكها داخل مساحة المخطط.</p>

            <div class="library-section">
                <h3>طاولات ومقاعد</h3>
                <div class="library-group">
                    <button
                        v-for="shape in TABLE_SHAPES"
                        :key="shape.value"
                        type="button"
                        class="library-item"
                        @click="$emit('add-table', shape.value)"
                    >
                        <span class="library-icon" :class="`shape-${shape.value}`"></span>
                        <span>
                            <strong>{{ shape.label }}</strong>
                            <small>توليد مقاعد تلقائي بتسميات ثابتة</small>
                        </span>
                    </button>
                </div>
            </div>

            <div
                v-for="group in ELEMENT_LIBRARY"
                :key="group.group"
                class="library-section"
            >
                <h3>{{ group.group }}</h3>
                <div class="library-group">
                    <button
                        v-for="item in group.items"
                        :key="item.type"
                        type="button"
                        class="library-item"
                        @click="$emit('add-element', item.type)"
                    >
                        <span class="library-icon generic-icon" :class="item.icon"></span>
                        <span>
                            <strong>{{ item.label }}</strong>
                            <small>{{ item.description }}</small>
                        </span>
                    </button>
                </div>
            </div>

            <div class="editor-help">
                <strong>ملاحظات</strong>
                <span>اسحب أي عنصر لتحريكه داخل مساحة المخطط.</span>
                <span>استخدم مقابض التحديد لتغيير الحجم، والمقبض العلوي للدوران.</span>
            </div>
        </div>
    </aside>
</template>
