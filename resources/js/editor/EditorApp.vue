<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import CanvasStage from './components/CanvasStage.vue';
import LeftLibrary from './components/LeftLibrary.vue';
import RightGuestList from './components/RightGuestList.vue';
import TableSettingsPanel from './components/TableSettingsPanel.vue';
import TopToolbar from './components/TopToolbar.vue';
import { useEditorState } from './composables/useEditorState';
import {
    assignSeat,
    createGuest,
    fetchEditorData,
    saveEditorLayout,
    unassignSeat,
    updateGuestType,
} from './services/editorApi';

const props = defineProps({
    config: {
        type: Object,
        required: true,
    },
});

const canvasStage = ref(null);
const libraryCollapsed = ref(false);
const guestsCollapsed = ref(false);
let errorTimer = null;
let messageTimer = null;
let autosaveTimer = null;
let activeSavePromise = null;
let saveRequestedDuringSave = false;

const {
    state,
    selectedElement,
    seatedCount,
    canUndo,
    canRedo,
    setLoadedData,
    applyEditorPayload,
    addTable,
    addElement,
    selectElement,
    updateElement,
    updateSelectedElementSettings,
    duplicateSelectedElement,
    deleteSelectedElement,
    zoomIn,
    zoomOut,
    resetZoom,
    togglePanMode,
    updateViewport,
    undo,
    redo,
    buildDesignJson,
} = useEditorState();

async function load() {
    try {
        const payload = await fetchEditorData(props.config.dataUrl);
        setLoadedData(payload);
    } catch (error) {
        state.error = error.message;
        state.loading = false;
    }
}

function showTransientError(message, timeout = 1800) {
    window.clearTimeout(errorTimer);
    state.error = message;

    errorTimer = window.setTimeout(() => {
        state.error = '';
    }, timeout);
}

function showTransientMessage(message, timeout = 2400) {
    window.clearTimeout(messageTimer);
    state.saveMessage = message;

    messageTimer = window.setTimeout(() => {
        state.saveMessage = '';
    }, timeout);
}

function designSnapshot() {
    return JSON.stringify(buildDesignJson());
}

function scheduleAutosave(delay = 900) {
    window.clearTimeout(autosaveTimer);

    if (!state.floorplan || state.loading || !state.dirty) {
        return;
    }

    autosaveTimer = window.setTimeout(() => {
        if (state.dirty) {
            save({ silent: true }).catch(() => {});
        }
    }, delay);
}

async function save(options = {}) {
    if (activeSavePromise) {
        saveRequestedDuringSave = true;

        return activeSavePromise;
    }

    const savedDesignSnapshot = designSnapshot();
    window.clearTimeout(autosaveTimer);
    state.saving = true;
    state.error = '';
    state.saveMessage = options.silent ? state.saveMessage : '';

    activeSavePromise = (async () => {
        const payload = await saveEditorLayout(
            props.config.saveUrl,
            props.config.csrfToken,
            buildDesignJson(),
        );

        state.lastSavedAt = payload.last_saved_at;

        if (designSnapshot() === savedDesignSnapshot) {
            state.dirty = false;
        } else {
            saveRequestedDuringSave = true;
        }

        if (!options.silent) {
            showTransientMessage(payload.message);
        }
    })();

    try {
        await activeSavePromise;
    } catch (error) {
        showTransientError(error.message);
        throw error;
    } finally {
        activeSavePromise = null;
        state.saving = false;

        if (saveRequestedDuringSave && state.dirty) {
            saveRequestedDuringSave = false;
            scheduleAutosave(300);
        } else {
            saveRequestedDuringSave = false;
        }
    }
}

async function assignGuestToSeat({ guestId, seatKey }) {
    state.assigning = true;
    state.error = '';
    state.saveMessage = '';

    try {
        if (state.dirty) {
            await save();
        }

        const payload = await assignSeat(
            props.config.assignSeatUrl,
            props.config.csrfToken,
            guestId,
            seatKey,
        );

        applyEditorPayload(payload);
        showTransientMessage(payload.message);
    } catch (error) {
        showTransientError(error.message);
    } finally {
        state.assigning = false;
    }
}

async function removeSeatAssignment(seatKey) {
    state.assigning = true;
    state.error = '';
    state.saveMessage = '';

    try {
        const payload = await unassignSeat(
            props.config.unassignSeatUrl,
            props.config.csrfToken,
            seatKey,
        );

        applyEditorPayload(payload);
        showTransientMessage(payload.message);
    } catch (error) {
        showTransientError(error.message);
    } finally {
        state.assigning = false;
    }
}

async function updateGuestTypeFromPanel({ guestId, guestTypeId }) {
    state.assigning = true;
    state.error = '';
    state.saveMessage = '';

    try {
        const url = props.config.updateGuestTypeUrl.replace('__GUEST__', String(guestId));
        const payload = await updateGuestType(
            url,
            props.config.csrfToken,
            guestTypeId,
        );

        applyEditorPayload(payload);
        showTransientMessage(payload.message);
    } catch (error) {
        showTransientError(error.message);
    } finally {
        state.assigning = false;
    }
}

async function createGuestFromPanel(guest) {
    state.assigning = true;
    state.error = '';
    state.saveMessage = '';

    try {
        const payload = await createGuest(
            props.config.createGuestUrl,
            props.config.csrfToken,
            guest,
        );

        applyEditorPayload(payload);
        showTransientMessage(payload.message);
    } catch (error) {
        showTransientError(error.message);
    } finally {
        state.assigning = false;
    }
}

function showCanvasDropError(message) {
    showTransientError(message);
}

async function exportPdf() {
    state.exporting = true;
    state.error = '';
    state.saveMessage = '';

    try {
        if (state.dirty) {
            await save();
        }

        const imageData = canvasStage.value?.exportImage();

        if (!imageData) {
            throw new Error('تعذر إنشاء صورة المخطط للتصدير.');
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = props.config.pdfExportUrl;
        form.style.display = 'none';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = props.config.csrfToken;

        const imageInput = document.createElement('input');
        imageInput.type = 'hidden';
        imageInput.name = 'image_data';
        imageInput.value = imageData;

        form.appendChild(csrfInput);
        form.appendChild(imageInput);
        document.body.appendChild(form);
        form.submit();
        form.remove();

        showTransientMessage('جاري تجهيز ملف PDF...');
    } catch (error) {
        showTransientError(error.message);
    } finally {
        state.exporting = false;
    }
}

onMounted(load);

watch(() => state.dirty, (dirty) => {
    if (dirty) {
        scheduleAutosave();
    }
}, { flush: 'post' });

watch(() => [state.elements, state.viewport], () => {
    if (state.dirty) {
        scheduleAutosave();
    }
}, { deep: true, flush: 'post' });

onBeforeUnmount(() => {
    window.clearTimeout(errorTimer);
    window.clearTimeout(messageTimer);
    window.clearTimeout(autosaveTimer);
});
</script>

<template>
    <div class="editor-app" dir="rtl">
        <TopToolbar
            :floorplan="state.floorplan"
            :event="state.event"
            :saving="state.saving || state.assigning || state.exporting"
            :home-url="config.homeUrl"
            :logo-url="config.logoUrl"
            :back-url="config.backUrl"
            :guest-export-url="config.guestExportUrl"
            :preview-url="config.previewUrl || ''"
            :seated-count="seatedCount"
            :guest-count="state.guests.length"
            :zoom="state.viewport.scale"
            :can-undo="canUndo"
            :can-redo="canRedo"
            :has-selection="Boolean(selectedElement)"
            :pan-mode="state.panMode"
            @zoom-in="zoomIn"
            @zoom-out="zoomOut"
            @reset-zoom="resetZoom"
            @toggle-pan="togglePanMode"
            @undo="undo"
            @redo="redo"
            @duplicate-selected="duplicateSelectedElement"
            @delete-selected="deleteSelectedElement"
            @save="save"
            @export-pdf="exportPdf"
        />

        <div v-if="state.loading" class="editor-loading">جار تحميل المخطط...</div>

        <div v-else-if="state.error && !state.floorplan" class="editor-loading error">
            {{ state.error }}
        </div>

        <template v-else>
            <div v-if="state.error" class="editor-toast error">{{ state.error }}</div>
            <div v-if="state.saveMessage" class="editor-toast">{{ state.saveMessage }}</div>

            <main
                class="editor-workspace"
                :class="{
                    'library-collapsed': libraryCollapsed,
                    'guests-collapsed': guestsCollapsed,
                    'settings-hidden': !selectedElement,
                }"
            >
                <LeftLibrary
                    v-if="!libraryCollapsed"
                    @add-table="addTable"
                    @add-element="addElement"
                    @collapse="libraryCollapsed = true"
                />
                <button
                    v-else
                    type="button"
                    class="side-panel-rail library-rail"
                    aria-label="فتح عناصر الإضافة"
                    @click="libraryCollapsed = false"
                >
                    <strong aria-hidden="true">›</strong>
                    <span>فتح العناصر</span>
                </button>

                <CanvasStage
                    ref="canvasStage"
                    :floorplan="state.floorplan"
                    :elements="state.elements"
                    :assignments="state.assignments"
                    :viewport="state.viewport"
                    :pan-mode="state.panMode"
                    :selected-element-id="state.selectedElementId"
                    @select="selectElement"
                    @update-element="updateElement"
                    @update-viewport="updateViewport"
                    @assign-seat="assignGuestToSeat"
                    @drop-error="showCanvasDropError"
                />

                <TableSettingsPanel
                    v-if="selectedElement"
                    :element="selectedElement"
                    @update="updateSelectedElementSettings"
                    @duplicate="duplicateSelectedElement"
                    @delete="deleteSelectedElement"
                />

                <RightGuestList
                    v-if="!guestsCollapsed"
                    :guests="state.guests"
                    :guest-types="state.guestTypes"
                    :elements="state.elements"
                    :assignments="state.assignments"
                    :assigning="state.assigning"
                    @create-guest="createGuestFromPanel"
                    @assign="assignGuestToSeat"
                    @update-type="updateGuestTypeFromPanel"
                    @unassign="removeSeatAssignment"
                    @collapse="guestsCollapsed = true"
                />
                <button
                    v-else
                    type="button"
                    class="side-panel-rail guests-rail"
                    aria-label="فتح قائمة الضيوف"
                    @click="guestsCollapsed = false"
                >
                    <strong aria-hidden="true">‹</strong>
                    <span>فتح الضيوف</span>
                </button>
            </main>
        </template>
    </div>
</template>
