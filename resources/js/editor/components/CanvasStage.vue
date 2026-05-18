<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Konva from 'konva';
import { isRoundTableShape, shapeLabel } from '../composables/useSeatLayout';
import {
    ENGINEERING_SCALE,
    cmRectToPixels,
    cmToPixels,
    floorPixelSize,
    metersToPixels,
    pixelsToCm,
    pixelsToSnappedCm,
} from '../utils/scale';

const props = defineProps({
    floorplan: {
        type: Object,
        required: true,
    },
    elements: {
        type: Array,
        required: true,
    },
    assignments: {
        type: Object,
        default: () => ({}),
    },
    viewport: {
        type: Object,
        default: () => ({ scale: 1, x: 0, y: 0 }),
    },
    panMode: {
        type: Boolean,
        default: false,
    },
    snapToGrid: {
        type: Boolean,
        default: true,
    },
    selectedElementId: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['select', 'update-element', 'update-viewport', 'assign-seat', 'drop-error']);

const container = ref(null);
const hoverSeatKey = ref(null);
let stage;
let gridLayer;
let backgroundLayer;
let elementLayer;
let transformer;

function clamp(value, min, max) {
    return Math.min(Math.max(value, min), max);
}

function canvasDimensions() {
    return floorPixelSize(props.floorplan);
}

function formatMeters(value) {
    return new Intl.NumberFormat('ar-EG', {
        maximumFractionDigits: 2,
        minimumFractionDigits: 0,
    }).format(value);
}

function dimensionLabel(axis) {
    const value = axis === 'height'
        ? Number(props.floorplan.height || 1)
        : Number(props.floorplan.width || 1);
    const suffix = axis === 'height' ? 'متر طول' : 'متر عرض';

    return `${formatMeters(value)} ${suffix}`;
}

function stageSize() {
    return canvasDimensions();
}

function viewportScale() {
    return Math.min(Math.max(Number(props.viewport?.scale || 1), 0.4), 2.5);
}

function logicalStageSize() {
    return stageSize();
}

function applyViewport() {
    if (!stage) {
        return;
    }

    const scale = viewportScale();
    stage.scale({ x: scale, y: scale });
    stage.position({
        x: Number(props.viewport?.x || 0),
        y: Number(props.viewport?.y || 0),
    });
    stage.draggable(props.panMode);
    stage.batchDraw();
}

function drawGrid() {
    gridLayer.destroyChildren();

    const size = logicalStageSize();
    const smallGridSize = cmToPixels(ENGINEERING_SCALE.SMALL_GRID_CM);
    const meterGridSize = metersToPixels(1);

    for (let x = 0; x <= size.width + 0.1; x += smallGridSize) {
        const isMeterLine = Math.round(x) % Math.round(meterGridSize) === 0;
        gridLayer.add(new Konva.Line({
            points: [x, 0, x, size.height],
            stroke: isMeterLine ? '#B6C2D2' : '#E8EDF3',
            strokeWidth: isMeterLine ? 1.1 : 0.45,
        }));
    }

    for (let y = 0; y <= size.height + 0.1; y += smallGridSize) {
        const isMeterLine = Math.round(y) % Math.round(meterGridSize) === 0;
        gridLayer.add(new Konva.Line({
            points: [0, y, size.width, y],
            stroke: isMeterLine ? '#B6C2D2' : '#E8EDF3',
            strokeWidth: isMeterLine ? 1.1 : 0.45,
        }));
    }

    for (let meter = 0; meter <= Number(props.floorplan.width || 1); meter += 1) {
        const x = metersToPixels(meter);

        gridLayer.add(new Konva.Text({
            x: x + 3,
            y: 3,
            text: `${meter}m`,
            fill: '#475467',
            fontFamily: 'Cairo, Arial, sans-serif',
            fontSize: 9,
            fontStyle: 'bold',
            listening: false,
        }));
    }

    for (let meter = 0; meter <= Number(props.floorplan.height || 1); meter += 1) {
        const y = metersToPixels(meter);

        gridLayer.add(new Konva.Text({
            x: 3,
            y: y + 3,
            text: `${meter}m`,
            fill: '#475467',
            fontFamily: 'Cairo, Arial, sans-serif',
            fontSize: 9,
            fontStyle: 'bold',
            listening: false,
        }));
    }

    gridLayer.add(new Konva.Rect({
        x: 2,
        y: 2,
        width: size.width - 4,
        height: size.height - 4,
        stroke: '#CBD5E1',
        strokeWidth: 1.25,
        opacity: 0.7,
        listening: false,
    }));

    gridLayer.add(new Konva.Rect({
        x: 9,
        y: 9,
        width: size.width - 18,
        height: size.height - 18,
        stroke: '#E7C539',
        strokeWidth: 1,
        dash: [10, 8],
        opacity: 0.38,
        listening: false,
    }));

    gridLayer.batchDraw();
}

function drawBackground() {
    backgroundLayer.destroyChildren();

    if (!props.floorplan.background_image_url) {
        return;
    }

    Konva.Image.fromURL(props.floorplan.background_image_url, (imageNode) => {
        const size = logicalStageSize();
        const imageRatio = imageNode.image().width / imageNode.image().height;
        const stageRatio = size.width / size.height;
        let width = size.width;
        let height = size.height;

        if (imageRatio > stageRatio) {
            height = width / imageRatio;
        } else {
            width = height * imageRatio;
        }

        imageNode.setAttrs({
            x: (size.width - width) / 2,
            y: (size.height - height) / 2,
            width,
            height,
            opacity: 0.32,
            listening: false,
        });

        backgroundLayer.add(imageNode);
        backgroundLayer.batchDraw();
    });
}

function addTableShape(group, element, isSelected) {
    const shape = element.tableShape || 'rectangle';
    const rect = cmRectToPixels(element);
    const baseAttrs = {
        fill: element.fill || '#F3F8FA',
        stroke: isSelected ? '#31719D' : element.stroke || '#4596CF',
        strokeWidth: isSelected ? 3 : 2,
        shadowColor: 'rgba(15, 23, 42, 0.15)',
        shadowBlur: isSelected ? 12 : 4,
        shadowOpacity: 0.18,
        opacity: Number(element.opacity ?? 1),
    };

    if (isRoundTableShape(shape)) {
        group.add(new Konva.Circle({
            ...baseAttrs,
            x: rect.width / 2,
            y: rect.height / 2,
            radius: Math.min(rect.width, rect.height) / 2,
        }));

        return;
    }

    group.add(new Konva.Rect({
        ...baseAttrs,
        width: rect.width,
        height: rect.height,
        cornerRadius: shape === 'theater' ? 8 : 12,
        dash: shape === 'theater' ? [8, 6] : [],
    }));
}

function addTableLabel(group, element) {
    const shape = element.tableShape || 'rectangle';
    const rect = cmRectToPixels(element);
    const label = element.label || shapeLabel(shape);

    if (isRoundTableShape(shape)) {
        group.add(new Konva.Text({
            text: label,
            width: rect.width,
            height: rect.height,
            x: 0,
            y: 0,
            align: 'center',
            verticalAlign: 'middle',
            fill: '#1F2937',
            fontFamily: 'Cairo, Arial, sans-serif',
            fontSize: 12,
            fontStyle: 'bold',
            ellipsis: true,
            listening: false,
        }));

        return;
    }

    group.add(new Konva.Text({
        text: label,
        width: rect.width,
        height: shape === 'theater' ? 26 : rect.height,
        y: shape === 'theater' ? 4 : 0,
        align: 'center',
        verticalAlign: shape === 'theater' ? 'top' : 'middle',
        fill: '#1F2937',
        fontFamily: 'Cairo, Arial, sans-serif',
        fontSize: 15,
        fontStyle: 'bold',
        listening: false,
    }));
}

function compactGuestLabelPosition(seat, isHovered, element) {
    const x = cmToPixels(seat.xCm ?? seat.x ?? 0);
    const y = cmToPixels(seat.yCm ?? seat.y ?? 0);
    const labelWidth = isHovered ? 102 : 88;
    const labelHeight = isHovered ? 22 : 20;
    const shape = element?.tableShape || '';
    const rotation = Math.abs(Number(seat.rotation || 0) % 180);
    const isHorizontalSeat = rotation === 0;
    const alternatesVertically = ['rectangle', 'square'].includes(shape) && isHorizontalSeat;
    const isEvenSeat = Number(seat.number || 0) % 2 === 0;

    return {
        x: x - (labelWidth / 2),
        y: alternatesVertically && isEvenSeat
            ? y + 18
            : y - labelHeight - 18,
        width: labelWidth,
        height: labelHeight,
    };
}

function hexToRgba(color, alpha = 1) {
    const normalized = String(color || '').replace('#', '').trim();

    if (!/^[0-9a-f]{6}$/i.test(normalized)) {
        return `rgba(49, 124, 119, ${alpha})`;
    }

    const r = Number.parseInt(normalized.slice(0, 2), 16);
    const g = Number.parseInt(normalized.slice(2, 4), 16);
    const b = Number.parseInt(normalized.slice(4, 6), 16);

    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function guestTypeColor(assignment) {
    return assignment?.guest?.type?.color || '#317C77';
}

function seatBadges(assignment) {
    const badges = assignment?.seat_badges || assignment?.guest?.seat_badges || [];

    return Array.isArray(badges) ? badges.slice(0, 2) : [];
}

function seatGuestDisplayName(name) {
    const parts = String(name || '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (parts.length <= 2) {
        return parts.join(' ');
    }

    return `${parts[0]} ${parts[parts.length - 1]}`;
}

function makeGuestLabel(guestName, position, isHovered, fontSize = 10, guestColor = '#317C77') {
    const labelNode = new Konva.Label({
        x: position.x,
        y: position.y,
        listening: false,
    });

    labelNode.add(new Konva.Tag({
        fill: isHovered ? '#FFFDF1' : '#FFFFFF',
        stroke: isHovered ? guestColor : hexToRgba(guestColor, 0.45),
        strokeWidth: 1,
        cornerRadius: 8,
        shadowColor: 'rgba(15, 23, 42, 0.16)',
        shadowBlur: isHovered ? 8 : 5,
        shadowOpacity: isHovered ? 0.18 : 0.12,
    }));

    labelNode.add(new Konva.Text({
        text: guestName,
        width: position.width,
        height: position.height,
        padding: 3,
        align: 'center',
        verticalAlign: 'middle',
        ellipsis: true,
        fill: '#1F2937',
        fontFamily: 'Cairo, Arial, sans-serif',
        fontSize,
        fontStyle: 'bold',
    }));

    return labelNode;
}

function badgePosition(index, total) {
    if (total <= 1) {
        return { x: 10, y: -10 };
    }

    return index === 0
        ? { x: 9, y: -9 }
        : { x: 9, y: 7 };
}

function addBadgeIcon(group, badge, index, total) {
    const position = badgePosition(index, total);
    const color = badge.color || (badge.type === 'gift' ? '#E7C539' : '#00B894');
    const isGift = badge.type === 'gift';
    const radius = isGift ? 8 : 6.2;

    group.add(new Konva.Circle({
        x: position.x,
        y: position.y,
        radius,
        fill: color,
        stroke: '#FFFFFF',
        strokeWidth: isGift ? 1.8 : 1.2,
        shadowColor: isGift ? 'rgba(249, 115, 22, 0.45)' : 'rgba(15, 23, 42, 0.16)',
        shadowBlur: isGift ? 6 : 3,
        shadowOpacity: isGift ? 0.28 : 0.14,
        listening: false,
    }));

    if (isGift) {
        group.add(new Konva.Rect({
            x: position.x - 4.4,
            y: position.y - 1.2,
            width: 8.8,
            height: 5.8,
            cornerRadius: 1,
            stroke: '#FFFFFF',
            strokeWidth: 1.25,
            listening: false,
        }));
        group.add(new Konva.Rect({
            x: position.x - 5,
            y: position.y - 4.5,
            width: 10,
            height: 3.6,
            cornerRadius: 1,
            stroke: '#FFFFFF',
            strokeWidth: 1.25,
            listening: false,
        }));
        group.add(new Konva.Line({
            points: [
                position.x,
                position.y - 4.5,
                position.x,
                position.y + 4.6,
                position.x - 5,
                position.y - 0.8,
                position.x + 5,
                position.y - 0.8,
            ],
            stroke: '#FFFFFF',
            strokeWidth: 1.25,
            lineCap: 'round',
            lineJoin: 'round',
            listening: false,
        }));

        return;
    }

    group.add(new Konva.Line({
        points: [
            position.x - 3,
            position.y,
            position.x - 1,
            position.y + 2.3,
            position.x + 3.2,
            position.y - 2.4,
        ],
        stroke: '#FFFFFF',
        strokeWidth: 1.4,
        lineCap: 'round',
        lineJoin: 'round',
        listening: false,
    }));
}

function tableLocalBounds(element) {
    const rect = cmRectToPixels(element);
    const seatRadius = cmToPixels(ENGINEERING_SCALE.CHAIR_DIAMETER_CM / 2);
    const bounds = {
        minX: 0,
        minY: 0,
        maxX: rect.width,
        maxY: rect.height,
    };

    (element.seats || []).forEach((seat) => {
        const seatX = cmToPixels(seat.xCm ?? seat.x ?? 0);
        const seatY = cmToPixels(seat.yCm ?? seat.y ?? 0);

        bounds.minX = Math.min(bounds.minX, seatX - seatRadius);
        bounds.minY = Math.min(bounds.minY, seatY - seatRadius);
        bounds.maxX = Math.max(bounds.maxX, seatX + seatRadius);
        bounds.maxY = Math.max(bounds.maxY, seatY + seatRadius);

        if (props.assignments?.[seat.key]) {
            const labelPosition = compactGuestLabelPosition(seat, false, element);

            bounds.minX = Math.min(bounds.minX, labelPosition.x);
            bounds.minY = Math.min(bounds.minY, labelPosition.y);
            bounds.maxX = Math.max(bounds.maxX, labelPosition.x + labelPosition.width);
            bounds.maxY = Math.max(bounds.maxY, labelPosition.y + labelPosition.height);
        }
    });

    return bounds;
}

function clampTablePosition(element, x, y) {
    const size = logicalStageSize();
    const bounds = tableLocalBounds(element);
    const minX = -bounds.minX;
    const minY = -bounds.minY;
    const maxX = size.width - bounds.maxX;
    const maxY = size.height - bounds.maxY;

    return {
        x: Math.round(clamp(x, Math.min(minX, maxX), Math.max(minX, maxX))),
        y: Math.round(clamp(y, Math.min(minY, maxY), Math.max(minY, maxY))),
    };
}

function nudgeNodeInsidePaper(node) {
    const size = logicalStageSize();
    const box = node.getClientRect({ relativeTo: stage, skipShadow: true });
    let dx = 0;
    let dy = 0;

    if (box.x < 0) {
        dx = -box.x;
    } else if (box.x + box.width > size.width) {
        dx = size.width - (box.x + box.width);
    }

    if (box.y < 0) {
        dy = -box.y;
    } else if (box.y + box.height > size.height) {
        dy = size.height - (box.y + box.height);
    }

    if (dx !== 0 || dy !== 0) {
        node.position({
            x: Math.round(node.x() + dx),
            y: Math.round(node.y() + dy),
        });
    }
}

function keepNodeInsidePaper(node, element) {
    if (element?.type === 'table') {
        node.position(clampTablePosition(element, node.x(), node.y()));
    }

    nudgeNodeInsidePaper(node);
}

function setHoverSeat(seatKey) {
    if (hoverSeatKey.value === seatKey) {
        return;
    }

    hoverSeatKey.value = seatKey;
    drawElements();
}

function addSeatNodes(group, element, isSelected) {
    const guestLabels = [];
    const hoveredGuestLabels = [];

    (element.seats || []).forEach((seat) => {
        const assignment = props.assignments?.[seat.key];
        const guestName = seatGuestDisplayName(assignment?.guest?.name);
        const isHovered = hoverSeatKey.value === seat.key;
        const assignedColor = guestTypeColor(assignment);
        const badges = seatBadges(assignment);
        const seatGroup = new Konva.Group({
            x: cmToPixels(seat.xCm ?? seat.x ?? 0),
            y: cmToPixels(seat.yCm ?? seat.y ?? 0),
            rotation: seat.rotation || 0,
        });

        seatGroup.on('mouseenter touchstart', () => {
            stage.container().style.cursor = 'pointer';
            setHoverSeat(seat.key);
        });

        seatGroup.on('mouseleave', () => {
            stage.container().style.cursor = 'default';
            clearHoverSeat();
        });

        seatGroup.add(new Konva.Circle({
            radius: isHovered ? cmToPixels(ENGINEERING_SCALE.CHAIR_DIAMETER_CM / 2) + 2 : cmToPixels(ENGINEERING_SCALE.CHAIR_DIAMETER_CM / 2),
            fill: isHovered
                ? hexToRgba(assignedColor, assignment ? 0.22 : 0.12)
                : assignment ? hexToRgba(assignedColor, 0.16) : '#FFFFFF',
            stroke: isHovered ? assignedColor : assignment ? assignedColor : isSelected ? '#317C77' : '#CBD5E1',
            strokeWidth: isHovered ? 3 : assignment || isSelected ? 2 : 1.5,
        }));

        seatGroup.add(new Konva.Text({
            text: seat.label || String(seat.number),
            x: -13,
            y: -8,
            width: 26,
            height: 16,
            align: 'center',
            verticalAlign: 'middle',
            fill: assignment ? assignedColor : '#31719D',
            fontFamily: 'Cairo, Arial, sans-serif',
            fontSize: 10,
            fontStyle: 'bold',
            listening: false,
        }));

        badges.forEach((badge, index) => {
            addBadgeIcon(seatGroup, badge, index, badges.length);
        });

        group.add(seatGroup);

        if (guestName) {
            const labelNode = makeGuestLabel(
                guestName,
                compactGuestLabelPosition(seat, isHovered, element),
                isHovered,
                isHovered ? 10 : 9,
                assignedColor,
            );

            if (isHovered) {
                hoveredGuestLabels.push(labelNode);
            } else {
                guestLabels.push(labelNode);
            }
        }
    });

    guestLabels.forEach((label) => {
        group.add(label);
    });

    hoveredGuestLabels.forEach((label) => {
        group.add(label);
    });
}

function makeTableNode(element) {
    const isSelected = props.selectedElementId === element.id;
    const rect = cmRectToPixels(element);
    const position = clampTablePosition(element, rect.x, rect.y);
    const group = new Konva.Group({
        id: element.id,
        x: position.x,
        y: position.y,
        width: rect.width,
        height: rect.height,
        draggable: !props.panMode,
        rotation: element.rotation || 0,
    });

    addTableShape(group, element, isSelected);
    addTableLabel(group, element);
    addSeatNodes(group, element, isSelected);
    attachNodeEvents(group, element);
    keepNodeInsidePaper(group, element);

    return group;
}

function addGenericElementShape(group, element, isSelected) {
    const rect = cmRectToPixels(element);
    const baseAttrs = {
        width: rect.width,
        height: rect.height,
        fill: element.fill || '#F3F8FA',
        stroke: isSelected ? '#31719D' : element.stroke || '#4D9B97',
        strokeWidth: isSelected ? 3 : 2,
        opacity: Number(element.opacity ?? 1),
        shadowColor: 'rgba(15, 23, 42, 0.12)',
        shadowBlur: isSelected ? 10 : 3,
        shadowOpacity: isSelected ? 0.16 : 0.08,
    };

    if (element.type === 'wall') {
        group.add(new Konva.Rect({
            ...baseAttrs,
            cornerRadius: 3,
        }));

        return;
    }

    if (element.type === 'door') {
        const stroke = isSelected ? '#31719D' : element.stroke || '#0F766E';
        const padding = Math.max(5, Math.min(rect.width, rect.height) * 0.08);
        const hingeX = padding;
        const baseY = rect.height - padding;
        const radius = Math.max(16, Math.min(rect.width, rect.height) - (padding * 2));
        const jambX = hingeX + radius;
        const topY = baseY - radius;
        const strokeWidth = isSelected ? 3 : 2.6;

        group.add(new Konva.Rect({
            width: rect.width,
            height: rect.height,
            fill: 'rgba(255,255,255,0.01)',
            stroke: isSelected ? '#7CB6D8' : 'transparent',
            strokeWidth: isSelected ? 1.2 : 0,
            dash: [5, 5],
            opacity: 0.75,
        }));
        group.add(new Konva.Line({
            points: [hingeX, baseY, jambX, baseY],
            stroke,
            strokeWidth,
            lineCap: 'round',
            listening: false,
        }));
        group.add(new Konva.Line({
            points: [jambX, topY, jambX, baseY],
            stroke,
            strokeWidth,
            lineCap: 'round',
            listening: false,
        }));
        group.add(new Konva.Path({
            data: `M ${hingeX} ${baseY} A ${radius} ${radius} 0 0 1 ${jambX} ${topY}`,
            stroke,
            strokeWidth,
            lineCap: 'round',
            lineJoin: 'round',
            listening: false,
        }));
        group.add(new Konva.Circle({
            x: hingeX,
            y: baseY,
            radius: Math.max(3.5, strokeWidth + 1.4),
            fill: '#F59E0B',
            stroke: '#F8FAFC',
            strokeWidth: 1.2,
            listening: false,
        }));

        return;
    }

    if (element.type === 'chair') {
        const radius = Math.min(rect.width, rect.height) / 2;
        group.add(new Konva.Circle({
            ...baseAttrs,
            x: rect.width / 2,
            y: rect.height / 2,
            radius,
        }));

        return;
    }

    if (element.type === 'lighting') {
        group.add(new Konva.Star({
            x: rect.width / 2,
            y: rect.height / 2,
            numPoints: 8,
            innerRadius: Math.max(Math.min(rect.width, rect.height) * 0.22, 6),
            outerRadius: Math.max(Math.min(rect.width, rect.height) * 0.45, 12),
            fill: baseAttrs.fill,
            stroke: baseAttrs.stroke,
            strokeWidth: baseAttrs.strokeWidth,
            opacity: baseAttrs.opacity,
            shadowColor: baseAttrs.shadowColor,
            shadowBlur: baseAttrs.shadowBlur,
            shadowOpacity: baseAttrs.shadowOpacity,
        }));

        return;
    }

    group.add(new Konva.Rect({
        ...baseAttrs,
        cornerRadius: element.type === 'aisle' ? 6 : 10,
        dash: element.type === 'aisle' ? [10, 8] : [],
    }));
}

function addGenericElementLabel(group, element) {
    if (element.type === 'wall') {
        return;
    }

    const rect = cmRectToPixels(element);

    group.add(new Konva.Text({
        text: element.label || '',
        width: rect.width,
        height: rect.height,
        align: 'center',
        verticalAlign: 'middle',
        fill: '#1F2937',
        fontFamily: 'Cairo, Arial, sans-serif',
        fontSize: Math.max(Math.min(rect.height / 3, 15), 10),
        fontStyle: 'bold',
        listening: false,
    }));
}

function makeGenericNode(element) {
    const isSelected = props.selectedElementId === element.id;
    const rect = cmRectToPixels(element);
    const group = new Konva.Group({
        id: element.id,
        x: rect.x,
        y: rect.y,
        width: rect.width,
        height: rect.height,
        draggable: !props.panMode,
        rotation: element.rotation || 0,
    });

    addGenericElementShape(group, element, isSelected);
    addGenericElementLabel(group, element);
    attachNodeEvents(group, element);
    keepNodeInsidePaper(group, element);

    return group;
}

function attachNodeEvents(group, element) {
    group.on('click tap', () => {
        emit('select', element.id);
    });

    group.on('dragend', () => {
        keepNodeInsidePaper(group, element);
        const xCm = pixelsToSnappedCm(group.x(), props.snapToGrid);
        const yCm = pixelsToSnappedCm(group.y(), props.snapToGrid);

        emit('update-element', element.id, {
            xCm,
            yCm,
            x: xCm,
            y: yCm,
        });
    });

    group.on('dragmove', () => {
        keepNodeInsidePaper(group, element);
    });

    group.on('transformend', () => {
        if (element.type === 'table') {
            group.scale({ x: 1, y: 1 });
            const xCm = pixelsToSnappedCm(group.x(), props.snapToGrid);
            const yCm = pixelsToSnappedCm(group.y(), props.snapToGrid);

            emit('update-element', element.id, {
                xCm,
                yCm,
                x: xCm,
                y: yCm,
            });

            return;
        }

        const scaleX = group.scaleX();
        const scaleY = group.scaleY();

        group.scale({ x: 1, y: 1 });
        const xCm = pixelsToSnappedCm(group.x(), props.snapToGrid);
        const yCm = pixelsToSnappedCm(group.y(), props.snapToGrid);
        const widthCm = pixelsToCm(Math.min(
            Math.max(Math.round((group.width() || cmToPixels(element.widthCm || element.width || 40)) * scaleX), cmToPixels(10)),
            logicalStageSize().width,
        ));
        const heightCm = pixelsToCm(Math.min(
            Math.max(Math.round((group.height() || cmToPixels(element.heightCm || element.height || 40)) * scaleY), cmToPixels(10)),
            logicalStageSize().height,
        ));

        emit('update-element', element.id, {
            xCm,
            yCm,
            widthCm,
            heightCm,
            x: xCm,
            y: yCm,
            width: widthCm,
            height: heightCm,
            rotation: Math.round(group.rotation()),
        });
    });
}

function drawElements() {
    elementLayer.destroyChildren();

    props.elements.forEach((element) => {
        if (element.type === 'table') {
            elementLayer.add(makeTableNode(element));
        } else {
            elementLayer.add(makeGenericNode(element));
        }
    });

    const selectedElement = props.elements.find((element) => element.id === props.selectedElementId) || null;
    const selectedIsTable = selectedElement?.type === 'table';

    transformer = new Konva.Transformer({
        rotateEnabled: !selectedIsTable,
        enabledAnchors: selectedIsTable ? [] : [
            'top-left',
            'top-center',
            'top-right',
            'middle-left',
            'middle-right',
            'bottom-left',
            'bottom-center',
            'bottom-right',
        ],
        borderStroke: '#31719D',
        anchorStroke: '#31719D',
        anchorFill: '#FFFFFF',
        anchorSize: 10,
        boundBoxFunc: (oldBox, newBox) => {
            if (Math.abs(newBox.width) < 12 || Math.abs(newBox.height) < 12) {
                return oldBox;
            }

            const size = logicalStageSize();

            if (
                newBox.x < 0
                || newBox.y < 0
                || newBox.x + newBox.width > size.width
                || newBox.y + newBox.height > size.height
            ) {
                return oldBox;
            }

            return newBox;
        },
    });
    elementLayer.add(transformer);

    const selectedNode = props.selectedElementId ? stage.findOne(`#${props.selectedElementId}`) : null;
    transformer.nodes(selectedNode ? [selectedNode] : []);

    elementLayer.batchDraw();
}

function seatAbsolutePosition(element, seat) {
    const rotation = ((element.rotation || 0) * Math.PI) / 180;
    const cos = Math.cos(rotation);
    const sin = Math.sin(rotation);

    return {
        x: cmToPixels(element.xCm ?? element.x ?? 0)
            + (cmToPixels(seat.xCm ?? seat.x ?? 0) * cos)
            - (cmToPixels(seat.yCm ?? seat.y ?? 0) * sin),
        y: cmToPixels(element.yCm ?? element.y ?? 0)
            + (cmToPixels(seat.xCm ?? seat.x ?? 0) * sin)
            + (cmToPixels(seat.yCm ?? seat.y ?? 0) * cos),
    };
}

function nearestSeat(point) {
    let closest = null;
    const hitRadius = cmToPixels(ENGINEERING_SCALE.CHAIR_DIAMETER_CM);

    props.elements.forEach((element) => {
        if (element.type !== 'table') {
            return;
        }

        (element.seats || []).forEach((seat) => {
            const absolute = seatAbsolutePosition(element, seat);
            const distance = Math.hypot(point.x - absolute.x, point.y - absolute.y);
            const assignment = props.assignments?.[seat.key] || null;

            if (distance <= hitRadius && (!closest || distance < closest.distance)) {
                closest = {
                    distance,
                    seatKey: seat.key,
                    guestId: assignment?.guest?.id || null,
                    guestName: assignment?.guest?.name || '',
                };
            }
        });
    });

    return closest;
}

function stagePointFromDragEvent(event) {
    const rect = stage.container().getBoundingClientRect();
    const transform = stage.getAbsoluteTransform().copy().invert();

    return transform.point({
        x: event.clientX - rect.left,
        y: event.clientY - rect.top,
    });
}

function handleDragOver(event) {
    event.preventDefault();

    if (!stage) {
        return;
    }

    const seat = nearestSeat(stagePointFromDragEvent(event));
    const nextHoverSeatKey = seat?.seatKey || null;

    if (hoverSeatKey.value !== nextHoverSeatKey) {
        hoverSeatKey.value = nextHoverSeatKey;
        drawElements();
    }
}

function clearHoverSeat() {
    if (!hoverSeatKey.value) {
        return;
    }

    hoverSeatKey.value = null;
    drawElements();
}

function handleDrop(event) {
    event.preventDefault();

    const guestId = Number(event.dataTransfer?.getData('application/x-guest-id'));

    if (!guestId || !stage) {
        emit('drop-error', 'اسحب بطاقة ضيف صحيحة إلى مقعد.');

        return;
    }

    const point = stagePointFromDragEvent(event);
    const seat = nearestSeat(point);
    clearHoverSeat();

    if (!seat) {
        emit('drop-error', 'ضع الضيف فوق مقعد فارغ داخل المخطط.');

        return;
    }

    if (seat.guestId && seat.guestId !== guestId) {
        emit('drop-error', `هذا المقعد محجوز بالفعل لـ ${seat.guestName}.`);

        return;
    }

    emit('assign-seat', {
        guestId,
        seatKey: seat.seatKey,
    });
}

onMounted(async () => {
    await nextTick();

    const size = stageSize();

    stage = new Konva.Stage({
        container: container.value,
        width: size.width,
        height: size.height,
    });

    gridLayer = new Konva.Layer({ listening: false });
    backgroundLayer = new Konva.Layer({ listening: false });
    elementLayer = new Konva.Layer();

    stage.add(gridLayer);
    stage.add(backgroundLayer);
    stage.add(elementLayer);
    applyViewport();

    stage.on('click tap', (event) => {
        if (event.target === stage) {
            emit('select', null);
        }
    });

    stage.on('dragend', () => {
        if (!props.panMode) {
            return;
        }

        emit('update-viewport', {
            x: Math.round(stage.x()),
            y: Math.round(stage.y()),
        });
    });

    drawGrid();
    drawBackground();
    drawElements();
});

watch(() => props.elements, drawElements, { deep: true });
watch(() => props.assignments, drawElements, { deep: true });
watch(() => props.selectedElementId, drawElements);
watch(() => props.panMode, () => {
    applyViewport();
    drawElements();
});
watch(() => props.viewport, () => {
    applyViewport();
    drawGrid();
    drawBackground();
    drawElements();
}, { deep: true });

onBeforeUnmount(() => {
    stage?.destroy();
});

function exportImage() {
    if (!stage) {
        return null;
    }

    const previousScale = stage.scale();
    const previousPosition = stage.position();

    transformer?.visible(false);
    stage.scale({ x: 1, y: 1 });
    stage.position({ x: 0, y: 0 });
    gridLayer?.batchDraw();
    backgroundLayer?.batchDraw();
    elementLayer?.batchDraw();

    const dataUrl = stage.toDataURL({
        mimeType: 'image/png',
        pixelRatio: 1.6,
    });

    stage.scale(previousScale);
    stage.position(previousPosition);
    transformer?.visible(true);
    gridLayer?.batchDraw();
    backgroundLayer?.batchDraw();
    elementLayer?.batchDraw();

    return dataUrl;
}

defineExpose({
    exportImage,
});
</script>

<template>
    <div
        class="canvas-shell"
        @dragover="handleDragOver"
        @dragleave="clearHoverSeat"
        @drop="handleDrop"
    >
        <div class="canvas-measure-frame">
            <div class="editor-dimension horizontal">
                <span>{{ dimensionLabel('width') }}</span>
            </div>
            <div class="editor-dimension vertical">
                <span>{{ dimensionLabel('height') }}</span>
            </div>
            <div
                ref="container"
                class="konva-container"
                :style="{
                    width: `${canvasDimensions().width}px`,
                    height: `${canvasDimensions().height}px`,
                }"
            ></div>
        </div>
    </div>
</template>
