import { computed, reactive } from 'vue';
import { elementDefaults, normalizeGenericElement } from './useEditorElements';
import {
    defaultSeatCountForShape,
    defaultSizeForShape,
    generateSeats,
    normalizeSeatCount,
    normalizeTheaterRows,
    shapeLabel,
} from './useSeatLayout';

function clone(value) {
    return JSON.parse(JSON.stringify(value));
}

export function useEditorState() {
    const state = reactive({
        loading: true,
        saving: false,
        assigning: false,
        exporting: false,
        error: '',
        saveMessage: '',
        floorplan: null,
        event: null,
        guests: [],
        guestTypes: [],
        assignments: {},
        elements: [],
        viewport: {
            scale: 1,
            x: 0,
            y: 0,
        },
        panMode: false,
        selectedElementId: null,
        lastSavedAt: null,
        dirty: false,
        historyPast: [],
        historyFuture: [],
    });

    const selectedElement = computed(() => {
        return state.elements.find((element) => element.id === state.selectedElementId) || null;
    });

    const seatedCount = computed(() => Object.keys(state.assignments || {}).length);
    const canUndo = computed(() => state.historyPast.length > 0);
    const canRedo = computed(() => state.historyFuture.length > 0);

    function snapshot() {
        return {
            elements: clone(state.elements),
            viewport: clone(state.viewport),
            selectedElementId: state.selectedElementId,
        };
    }

    function restore(snap) {
        state.elements = clone(snap.elements);
        state.viewport = clone(snap.viewport);
        state.selectedElementId = snap.selectedElementId;
        state.dirty = true;
    }

    function remember() {
        state.historyPast.push(snapshot());

        if (state.historyPast.length > 40) {
            state.historyPast.shift();
        }

        state.historyFuture = [];
    }

    function setLoadedData(payload) {
        const designJson = payload.floorplan.design_json || {};

        state.floorplan = payload.floorplan;
        state.event = payload.event;
        state.guests = Array.isArray(payload.guests) ? payload.guests : [];
        state.guestTypes = Array.isArray(payload.guest_types) ? payload.guest_types : [];
        state.assignments = payload.assignments || {};
        state.elements = Array.isArray(designJson.elements)
            ? designJson.elements.map((element) => normalizeElement(element))
            : [];
        state.viewport = normalizeViewport(designJson.viewport);
        state.lastSavedAt = payload.floorplan.last_saved_at;
        state.loading = false;
        state.dirty = false;
        state.historyPast = [];
        state.historyFuture = [];
    }

    function applyEditorPayload(payload) {
        state.floorplan = payload.floorplan || state.floorplan;
        state.event = payload.event || state.event;
        state.guests = Array.isArray(payload.guests) ? payload.guests : state.guests;
        state.guestTypes = Array.isArray(payload.guest_types) ? payload.guest_types : state.guestTypes;
        state.assignments = payload.assignments || {};
        state.lastSavedAt = payload.floorplan?.last_saved_at || state.lastSavedAt;
    }

    function addTable(shape = 'rectangle') {
        remember();

        const index = state.elements.filter((element) => element.type === 'table').length + 1;
        const size = defaultSizeForShape(shape);
        const seatCount = normalizeSeatCount(defaultSeatCountForShape(shape), shape);
        const element = normalizeElement({
            id: `table-${Date.now()}`,
            type: 'table',
            label: `طاولة ${index}`,
            tableShape: shape,
            seatCount,
            theaterRows: shape === 'theater' ? 4 : null,
            x: 140 + (index * 24),
            y: 120 + (index * 18),
            width: size.width,
            height: size.height,
            rotation: 0,
            fill: '#F3F8FA',
            stroke: '#4596CF',
        });

        state.elements.push(element);
        state.selectedElementId = element.id;
        state.dirty = true;
    }

    function addElement(type) {
        remember();

        const defaults = elementDefaults(type);
        const index = state.elements.filter((element) => element.type === type).length + 1;
        const element = normalizeElement({
            id: `${type}-${Date.now()}`,
            type,
            ...defaults,
            label: `${defaults.label} ${index}`,
            x: 150 + (index * 22),
            y: 135 + (index * 18),
        });

        state.elements.push(element);
        state.selectedElementId = element.id;
        state.dirty = true;
    }

    function selectElement(id) {
        state.selectedElementId = id;
    }

    function updateElement(id, patch, options = {}) {
        const element = state.elements.find((item) => item.id === id);

        if (!element) {
            return;
        }

        if (!options.skipHistory) {
            remember();
        }

        Object.assign(element, patch);

        if (element.type === 'table') {
            normalizeTableElement(element);
            element.seats = generateSeats(element);
        } else {
            Object.assign(element, normalizeGenericElement(element));
        }

        state.dirty = true;
    }

    function updateSelectedElementSettings(patch) {
        const element = selectedElement.value;

        if (!element) {
            return;
        }

        updateElement(element.id, patch);
    }

    function duplicateSelectedElement() {
        const element = selectedElement.value;

        if (!element) {
            return;
        }

        remember();

        const copy = normalizeElement({
            ...clone(element),
            id: `${element.type}-${Date.now()}`,
            label: `${element.label || 'عنصر'} نسخة`,
            x: Number(element.x || 0) + 28,
            y: Number(element.y || 0) + 28,
        });

        state.elements.push(copy);
        state.selectedElementId = copy.id;
        state.dirty = true;
    }

    function deleteSelectedElement() {
        if (!state.selectedElementId) {
            return;
        }

        remember();
        state.elements = state.elements.filter((element) => element.id !== state.selectedElementId);
        state.selectedElementId = null;
        state.dirty = true;
    }

    function setZoom(scale) {
        remember();

        state.viewport.scale = Math.min(Math.max(Number(scale) || 1, 0.4), 2.5);
        state.dirty = true;
    }

    function togglePanMode() {
        state.panMode = !state.panMode;
    }

    function updateViewport(patch) {
        remember();

        state.viewport = normalizeViewport({
            ...state.viewport,
            ...patch,
        });
        state.dirty = true;
    }

    function zoomIn() {
        setZoom(state.viewport.scale + 0.1);
    }

    function zoomOut() {
        setZoom(state.viewport.scale - 0.1);
    }

    function resetZoom() {
        setZoom(1);
    }

    function undo() {
        if (!canUndo.value) {
            return;
        }

        const current = snapshot();
        const previous = state.historyPast.pop();
        state.historyFuture.push(current);
        restore(previous);
    }

    function redo() {
        if (!canRedo.value) {
            return;
        }

        const current = snapshot();
        const next = state.historyFuture.pop();
        state.historyPast.push(current);
        restore(next);
    }

    function normalizeElement(element) {
        if (element.type !== 'table') {
            return normalizeGenericElement(element);
        }

        return normalizeTableElement({ ...element });
    }

    function normalizeTableElement(element) {
        const shape = element.tableShape || 'rectangle';
        const size = defaultSizeForShape(shape);

        element.tableShape = shape;
        element.label = element.label || shapeLabel(shape);
        element.seatCount = normalizeSeatCount(
            element.seatCount ?? element.seats?.length ?? defaultSeatCountForShape(shape),
            shape,
        );
        element.theaterRows = shape === 'theater'
            ? normalizeTheaterRows(element.theaterRows || 4, element.seatCount || 8)
            : null;
        element.x = Number(element.x || 120);
        element.y = Number(element.y || 120);
        element.width = size.width;
        element.height = size.height;
        element.rotation = 0;
        element.opacity = 1;
        element.fill = element.fill || '#F3F8FA';
        element.stroke = element.stroke || '#4596CF';
        element.seats = generateSeats(element);

        return element;
    }

    function normalizeViewport(viewport = {}) {
        return {
            scale: Math.min(Math.max(Number(viewport?.scale || 1), 0.4), 2.5),
            x: Number(viewport?.x || 0),
            y: Number(viewport?.y || 0),
        };
    }

    function buildDesignJson() {
        return {
            version: 1,
            elements: state.elements.map((element) => clone(element)),
            viewport: clone(state.viewport),
        };
    }

    return {
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
    };
}
