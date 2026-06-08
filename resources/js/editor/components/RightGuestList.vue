<script setup>
import { computed, onBeforeUnmount, reactive, ref, watch } from 'vue';

const props = defineProps({
    guests: {
        type: Array,
        default: () => [],
    },
    guestTypes: {
        type: Array,
        default: () => [],
    },
    elements: {
        type: Array,
        default: () => [],
    },
    assignments: {
        type: Object,
        default: () => ({}),
    },
    assigning: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['create-guest', 'assign', 'update-type', 'unassign', 'collapse']);

const selectedGuestId = ref(null);
const searchTerm = ref('');
const showCreateForm = ref(false);
const activePointerDrag = ref(null);
const suppressNextClick = ref(false);
const selection = reactive({
    tableId: '',
    seatKey: '',
    guestTypeId: '',
});
const newGuest = reactive({
    name: '',
    guest_type_id: '',
    phone: '',
});
const formError = ref('');

const tableOptions = computed(() => {
    return props.elements
        .filter((element) => element.type === 'table')
        .map((table) => ({
            id: table.id,
            label: table.label || 'طاولة',
            seats: (table.seats || []).map((seat) => {
                const assignment = props.assignments?.[seat.key] || null;

                return {
                    key: seat.key,
                    number: seat.number,
                    label: seat.label || String(seat.number),
                    occupiedByGuestId: assignment?.guest?.id || null,
                    occupiedByName: assignment?.guest?.name || '',
                };
            }),
        }));
});

const selectedGuest = computed(() => {
    return props.guests.find((guest) => guest.id === selectedGuestId.value) || null;
});

const normalizedSearchTerm = computed(() => normalizeSearchText(searchTerm.value));

const filteredGuests = computed(() => {
    if (!normalizedSearchTerm.value) {
        return props.guests;
    }

    return props.guests.filter((guest) => {
        const searchableText = [
            guest.name,
            guest.type?.name_ar,
            guest.assigned_seat?.table_name,
            guest.assigned_seat?.seat_number ? `مقعد ${guest.assigned_seat.seat_number}` : '',
        ].join(' ');

        return normalizeSearchText(searchableText).includes(normalizedSearchTerm.value);
    });
});

const selectedTable = computed(() => {
    return tableOptions.value.find((table) => table.id === selection.tableId) || null;
});

const selectableSeats = computed(() => {
    if (!selectedTable.value || !selectedGuest.value) {
        return [];
    }

    return selectedTable.value.seats.map((seat) => ({
        ...seat,
        disabled: seat.occupiedByGuestId && seat.occupiedByGuestId !== selectedGuest.value.id,
    }));
});

function dragGuest(event, guest) {
    if (!event.dataTransfer || props.assigning) {
        return;
    }

    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('application/x-guest-id', String(guest.id));
}

function dispatchGuestPointerDrag(phase, drag, event) {
    window.dispatchEvent(new CustomEvent('editor:guest-pointer-drag', {
        detail: {
            phase,
            guestId: drag.guestId,
            clientX: event.clientX,
            clientY: event.clientY,
        },
    }));
}

function cleanupPointerDrag() {
    window.removeEventListener('pointermove', handleGuestPointerMove);
    window.removeEventListener('pointerup', handleGuestPointerEnd);
    window.removeEventListener('pointercancel', handleGuestPointerCancel);
    activePointerDrag.value = null;
}

function startGuestPointerDrag(event, guest) {
    if (props.assigning || event.pointerType === 'mouse' || (event.button !== undefined && event.button !== 0)) {
        return;
    }

    activePointerDrag.value = {
        pointerId: event.pointerId,
        guestId: guest.id,
        startX: event.clientX,
        startY: event.clientY,
        dragging: false,
    };

    event.currentTarget?.setPointerCapture?.(event.pointerId);
    window.addEventListener('pointermove', handleGuestPointerMove, { passive: false });
    window.addEventListener('pointerup', handleGuestPointerEnd, { passive: false });
    window.addEventListener('pointercancel', handleGuestPointerCancel, { passive: false });
}

function handleGuestPointerMove(event) {
    const drag = activePointerDrag.value;

    if (!drag || event.pointerId !== drag.pointerId) {
        return;
    }

    const distance = Math.hypot(event.clientX - drag.startX, event.clientY - drag.startY);

    if (!drag.dragging && distance < 8) {
        return;
    }

    event.preventDefault();

    if (!drag.dragging) {
        drag.dragging = true;
        dispatchGuestPointerDrag('start', drag, event);
    }

    dispatchGuestPointerDrag('move', drag, event);
}

function handleGuestPointerEnd(event) {
    const drag = activePointerDrag.value;

    if (!drag || event.pointerId !== drag.pointerId) {
        return;
    }

    if (drag.dragging) {
        event.preventDefault();
        suppressNextClick.value = true;
        dispatchGuestPointerDrag('end', drag, event);

        window.setTimeout(() => {
            suppressNextClick.value = false;
        }, 250);
    }

    cleanupPointerDrag();
}

function handleGuestPointerCancel(event) {
    const drag = activePointerDrag.value;

    if (drag && event.pointerId === drag.pointerId && drag.dragging) {
        dispatchGuestPointerDrag('cancel', drag, event);
    }

    cleanupPointerDrag();
}

function handleGuestCardClick(event, guest) {
    if (suppressNextClick.value) {
        event.preventDefault();
        event.stopPropagation();

        return;
    }

    openSeatPicker(guest);
}

function normalizeSearchText(value) {
    return String(value || '')
        .trim()
        .toLowerCase()
        .replace(/[أإآ]/g, 'ا')
        .replace(/ى/g, 'ي')
        .replace(/ة/g, 'ه')
        .replace(/\s+/g, ' ');
}

function openSeatPicker(guest) {
    selectedGuestId.value = selectedGuestId.value === guest.id ? null : guest.id;

    if (!selectedGuestId.value) {
        return;
    }

    const assignedSeatKey = guest.assigned_seat?.seat_key || '';
    const assignedTable = assignedSeatKey
        ? tableOptions.value.find((table) => table.seats.some((seat) => seat.key === assignedSeatKey))
        : null;
    const firstTableWithSeats = tableOptions.value.find((table) => table.seats.length > 0);

    selection.tableId = assignedTable?.id || firstTableWithSeats?.id || '';
    selection.seatKey = assignedSeatKey || firstAvailableSeatKey(selection.tableId);
    selection.guestTypeId = guest.type?.id ? String(guest.type.id) : '';
}

function firstAvailableSeatKey(tableId) {
    const table = tableOptions.value.find((item) => item.id === tableId);

    if (!table || !selectedGuest.value) {
        return '';
    }

    const seat = table.seats.find((item) => !item.occupiedByGuestId || item.occupiedByGuestId === selectedGuest.value.id);

    return seat?.key || '';
}

function changeTable(tableId) {
    selection.tableId = tableId;
    selection.seatKey = firstAvailableSeatKey(tableId);
}

function assignSelectedSeat() {
    if (!selectedGuest.value || !selection.seatKey) {
        return;
    }

    emit('assign', {
        guestId: selectedGuest.value.id,
        seatKey: selection.seatKey,
    });
}

function updateSelectedGuestType(guestTypeId) {
    selection.guestTypeId = guestTypeId;

    if (!selectedGuest.value) {
        return;
    }

    emit('update-type', {
        guestId: selectedGuest.value.id,
        guestTypeId: guestTypeId || null,
    });
}

function submitGuest() {
    formError.value = '';

    if (!newGuest.name.trim()) {
        formError.value = 'اسم الضيف مطلوب.';

        return;
    }

    emit('create-guest', {
        name: newGuest.name.trim(),
        guest_type_id: newGuest.guest_type_id || null,
        phone: newGuest.phone.trim() || null,
    });

    newGuest.name = '';
    newGuest.guest_type_id = '';
    newGuest.phone = '';
    showCreateForm.value = false;
}

watch(tableOptions, () => {
    if (!selectedGuest.value || !selection.tableId) {
        return;
    }

    const currentSeat = selectableSeats.value.find((seat) => seat.key === selection.seatKey);

    if (!currentSeat || currentSeat.disabled) {
        selection.seatKey = firstAvailableSeatKey(selection.tableId);
    }
}, { deep: true });

watch(filteredGuests, () => {
    if (!selectedGuestId.value) {
        return;
    }

    const stillVisible = filteredGuests.value.some((guest) => guest.id === selectedGuestId.value);

    if (!stillVisible) {
        selectedGuestId.value = null;
    }
});

onBeforeUnmount(() => {
    cleanupPointerDrag();
});
</script>

<template>
    <aside class="editor-side-panel guest-panel">
        <button
            type="button"
            class="side-collapse-btn guest-collapse-btn"
            aria-label="إغلاق قائمة الضيوف"
            title="إغلاق قائمة الضيوف"
            @click="$emit('collapse')"
        >
            ›
        </button>

        <div class="side-panel-scroll">
            <div class="panel-heading compact-heading">
                <h2>قائمة الضيوف</h2>
                <div class="panel-heading-actions">
                    <span class="status-badge">{{ guests.length }}</span>
                </div>
            </div>
            <p>اسحب الضيف إلى المقعد، أو اضغط على البطاقة واختر الطاولة والمقعد مباشرة.</p>

            <div class="guest-create-panel">
                <button
                    type="button"
                    class="btn btn-secondary btn-sm full-width"
                    @click="showCreateForm = !showCreateForm"
                >
                    <span aria-hidden="true">{{ showCreateForm ? '−' : '+' }}</span>
                    {{ showCreateForm ? 'إغلاق إضافة ضيف' : 'إضافة ضيف' }}
                </button>

            <form
                v-if="showCreateForm"
                class="guest-create-form"
                @submit.prevent="submitGuest"
            >
                <label class="field compact-field">
                    <span>اسم الضيف</span>
                    <input
                        v-model="newGuest.name"
                        class="form-control"
                        type="text"
                        placeholder="اكتب اسم الضيف"
                        :disabled="assigning"
                    >
                </label>

                <div class="seat-picker-grid">
                    <label class="field compact-field">
                        <span>نوع الضيف</span>
                        <select
                            v-model="newGuest.guest_type_id"
                            class="form-control"
                            :disabled="assigning"
                        >
                            <option value="">بدون نوع</option>
                            <option
                                v-for="guestType in guestTypes"
                                :key="guestType.id"
                                :value="guestType.id"
                            >
                                {{ guestType.name_ar }}
                            </option>
                        </select>
                    </label>

                    <label class="field compact-field">
                        <span>رقم الهاتف</span>
                        <input
                            v-model="newGuest.phone"
                            class="form-control"
                            type="text"
                            placeholder="اختياري"
                            :disabled="assigning"
                        >
                    </label>
                </div>

                <p v-if="formError" class="field-error">{{ formError }}</p>

                <button
                    type="submit"
                    class="btn btn-primary btn-sm"
                    :disabled="assigning"
                >
                    حفظ الضيف
                </button>
            </form>
            </div>

            <label class="guest-search">
                <span>بحث عن ضيف</span>
                <input
                    v-model="searchTerm"
                    class="form-control"
                    type="search"
                    placeholder="اكتب اسم الضيف أو الطاولة أو رقم المقعد"
                >
                <small v-if="searchTerm">
                    {{ filteredGuests.length }} نتيجة من {{ guests.length }}
                </small>
            </label>

            <div v-if="guests.length === 0" class="empty-editor-panel">
                <strong>لا توجد بيانات ضيوف بعد</strong>
                <span>أضف الضيوف من صفحة الحدث ليظهروا هنا داخل المحرر.</span>
            </div>

            <div v-else-if="filteredGuests.length === 0" class="empty-editor-panel">
                <strong>لا توجد نتائج مطابقة</strong>
                <span>جرّب البحث باسم آخر أو رقم مقعد مختلف.</span>
            </div>

            <div v-else class="editor-guest-list">
            <article
                v-for="guest in filteredGuests"
                :key="guest.id"
                class="editor-guest-card"
                :class="{ seated: guest.assigned_seat }"
                draggable="true"
                @dragstart="dragGuest($event, guest)"
                @pointerdown="startGuestPointerDrag($event, guest)"
                @click="handleGuestCardClick($event, guest)"
            >
                <div class="guest-card-main">
                    <span class="guest-card-icon" aria-hidden="true">{{ guest.assigned_seat ? '✓' : '•' }}</span>
                    <strong>{{ guest.name }}</strong>
                    <span
                        v-if="guest.type"
                        class="editor-guest-type"
                        :style="{ '--guest-type-color': guest.type.color }"
                    >
                        {{ guest.type.name_ar }}
                    </span>
                </div>
                <small v-if="guest.assigned_seat">
                    {{ guest.assigned_seat.table_name || 'طاولة' }} - مقعد {{ guest.assigned_seat.seat_number }}
                </small>
                <small v-else>غير مسكن</small>

                <div
                    v-if="selectedGuestId === guest.id"
                    class="seat-picker"
                    @click.stop
                >
                    <label class="field compact-field">
                        <span>نوع الضيف</span>
                        <select
                            class="form-control"
                            :value="selection.guestTypeId"
                            :disabled="assigning"
                            @change="updateSelectedGuestType($event.target.value)"
                        >
                            <option value="">بدون نوع</option>
                            <option
                                v-for="guestType in guestTypes"
                                :key="guestType.id"
                                :value="String(guestType.id)"
                            >
                                {{ guestType.name_ar }}
                            </option>
                        </select>
                    </label>

                    <div class="seat-picker-grid">
                        <label class="field compact-field">
                        <span>الطاولة</span>
                        <select
                            class="form-control"
                            :value="selection.tableId"
                            :disabled="assigning || tableOptions.length === 0"
                            @change="changeTable($event.target.value)"
                        >
                            <option value="" disabled>اختر الطاولة</option>
                            <option
                                v-for="table in tableOptions"
                                :key="table.id"
                                :value="table.id"
                                :disabled="table.seats.length === 0"
                            >
                                {{ table.label }} - {{ table.seats.length }} مقعد
                            </option>
                        </select>
                        </label>

                        <label class="field compact-field">
                            <span>المقعد</span>
                            <select
                                class="form-control"
                                v-model="selection.seatKey"
                                :disabled="assigning || selectableSeats.length === 0"
                            >
                                <option value="" disabled>اختر المقعد</option>
                                <option
                                    v-for="seat in selectableSeats"
                                    :key="seat.key"
                                    :value="seat.key"
                                    :disabled="seat.disabled"
                                >
                                    مقعد {{ seat.label }}{{ seat.disabled ? ` - محجوز: ${seat.occupiedByName}` : '' }}
                                </option>
                            </select>
                        </label>
                    </div>

                    <button
                        type="button"
                        class="btn btn-primary btn-sm seat-assign-btn icon-only-check"
                        aria-label="تجليس الضيف"
                        title="تجليس الضيف"
                        :disabled="assigning || !selection.seatKey"
                        @click.stop="assignSelectedSeat"
                    >
                        ✓
                    </button>
                </div>

                <button
                    v-if="guest.assigned_seat"
                    type="button"
                    class="editor-link-button"
                    :disabled="assigning"
                    @click.stop="emit('unassign', guest.assigned_seat.seat_key)"
                >
                    ازاله التجليس
                </button>
            </article>
            </div>
        </div>
    </aside>
</template>
