<script setup>
import { computed } from 'vue';
import { isRoundTableShape, TABLE_SHAPES } from '../composables/useSeatLayout';

const props = defineProps({
    element: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['update', 'duplicate', 'delete']);

const isTable = computed(() => props.element?.type === 'table');
const isFixedRoundTable = computed(() => isRoundTableShape(props.element?.tableShape));

function updateValue(key, value) {
    emit('update', { [key]: value });
}
</script>

<template>
    <section class="table-settings-panel">
        <template v-if="element">
            <div class="settings-heading">
                <h3>{{ isTable ? 'إعدادات الطاولة' : 'إعدادات العنصر' }}</h3>
                <span v-if="isTable">{{ element.seats?.length || 0 }} مقعد</span>
                <span v-else>{{ element.type }}</span>
            </div>

            <label class="field compact-field">
                <span>الاسم</span>
                <input
                    class="form-control"
                    type="text"
                    :value="element.label"
                    @input="updateValue('label', $event.target.value)"
                >
            </label>

            <label v-if="isTable && !isFixedRoundTable" class="field compact-field">
                <span>شكل الطاولة</span>
                <select
                    class="form-control"
                    :value="element.tableShape"
                    @change="updateValue('tableShape', $event.target.value)"
                >
                    <option
                        v-for="shape in TABLE_SHAPES"
                        :key="shape.value"
                        :value="shape.value"
                    >
                        {{ shape.label }}
                    </option>
                </select>
            </label>

            <label v-if="isTable" class="field compact-field">
                <span>عدد المقاعد</span>
                <input
                    class="form-control"
                    type="number"
                    min="1"
                    :max="element.tableShape === 'theater' ? 160 : 80"
                    :value="element.seatCount"
                    @input="updateValue('seatCount', $event.target.value)"
                >
            </label>

            <label v-if="isTable && element.tableShape === 'theater'" class="field compact-field">
                <span>عدد الصفوف</span>
                <input
                    class="form-control"
                    type="number"
                    min="1"
                    :max="element.seatCount"
                    :value="element.theaterRows"
                    @input="updateValue('theaterRows', $event.target.value)"
                >
            </label>

            <div v-if="!isTable" class="settings-grid">
                <label class="field compact-field">
                    <span>العرض</span>
                    <input
                        class="form-control"
                        type="number"
                        min="12"
                        :value="Math.round(element.width)"
                        @input="updateValue('width', $event.target.value)"
                    >
                </label>

                <label class="field compact-field">
                    <span>الارتفاع</span>
                    <input
                        class="form-control"
                        type="number"
                        min="12"
                        :value="Math.round(element.height)"
                        @input="updateValue('height', $event.target.value)"
                    >
                </label>

                <label class="field compact-field">
                    <span>الدوران</span>
                    <input
                        class="form-control"
                        type="number"
                        min="-360"
                        max="360"
                        :value="Math.round(element.rotation || 0)"
                        @input="updateValue('rotation', $event.target.value)"
                    >
                </label>

                <label class="field compact-field">
                    <span>الشفافية</span>
                    <input
                        class="form-control"
                        type="number"
                        min="0.1"
                        max="1"
                        step="0.1"
                        :value="element.opacity ?? 1"
                        @input="updateValue('opacity', $event.target.value)"
                    >
                </label>
            </div>

            <label class="field compact-field">
                <span>لون التعبئة</span>
                <input
                    class="form-control color-control"
                    type="color"
                    :value="element.fill || '#F3F8FA'"
                    @input="updateValue('fill', $event.target.value)"
                >
            </label>

            <label class="field compact-field">
                <span>لون الإطار</span>
                <input
                    class="form-control color-control"
                    type="color"
                    :value="element.stroke || '#4596CF'"
                    @input="updateValue('stroke', $event.target.value)"
                >
            </label>

            <div class="form-actions compact-actions">
                <button type="button" class="btn btn-secondary btn-sm" @click="$emit('duplicate')">نسخ</button>
                <button type="button" class="btn btn-danger btn-sm" @click="$emit('delete')">حذف</button>
            </div>
        </template>

        <div v-else class="empty-editor-panel flat">
            <strong>اختر عنصرًا لتعديله</strong>
            <span>يمكنك تعديل الاسم، الحجم، الدوران، اللون، وعدد المقاعد للطاولات بعد تحديد أي عنصر على المخطط.</span>
        </div>
    </section>
</template>
