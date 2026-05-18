import { ENGINEERING_SCALE } from '../utils/scale';

export const TABLE_SHAPES = [
    { value: 'round-100', label: 'Round 100 cm', chairs: 4 },
    { value: 'round-120', label: 'Round 120 cm', chairs: 5 },
    { value: 'rectangle', label: 'Rectangle' },
    { value: 'square', label: 'Square' },
    { value: 'banquet', label: 'Banquet' },
    { value: 'theater', label: 'Theater rows' },
];

export function shapeLabel(shape) {
    return TABLE_SHAPES.find((item) => item.value === shape)?.label || 'Rectangle';
}

export function isRoundTableShape(shape) {
    return ['round', 'round-100', 'round-120'].includes(shape);
}

export function defaultSizeForShape(shape) {
    const sizes = {
        round: { widthCm: 100, heightCm: 100, diameterCm: 100 },
        'round-100': { widthCm: 100, heightCm: 100, diameterCm: 100 },
        'round-120': { widthCm: 120, heightCm: 120, diameterCm: 120 },
        rectangle: { widthCm: 220, heightCm: 120 },
        square: { widthCm: 140, heightCm: 140 },
        banquet: { widthCm: 300, heightCm: 100 },
        theater: { widthCm: 360, heightCm: 220 },
    };

    return sizes[shape] || sizes.rectangle;
}

export function tableDiameterCm(elementOrShape) {
    const shape = typeof elementOrShape === 'string'
        ? elementOrShape
        : elementOrShape?.tableShape || 'rectangle';

    return Number(defaultSizeForShape(shape).diameterCm || 0);
}

export function defaultSeatCountForShape(shape) {
    const defaults = {
        round: 4,
        'round-100': 4,
        'round-120': 5,
        theater: 24,
    };

    return defaults[shape] || 8;
}

export function normalizeSeatCount(value, shape = 'rectangle') {
    if (isRoundTableShape(shape)) {
        return defaultSeatCountForShape(shape);
    }

    const max = shape === 'theater' ? 160 : 80;
    const parsed = Number.parseInt(value, 10);

    if (Number.isNaN(parsed)) {
        return defaultSeatCountForShape(shape);
    }

    return Math.min(Math.max(parsed, 1), max);
}

export function normalizeTheaterRows(value, seatCount) {
    const parsed = Number.parseInt(value, 10);
    const rows = Number.isNaN(parsed) ? 4 : parsed;

    return Math.min(Math.max(rows, 1), Math.max(seatCount, 1));
}

export function generateSeats(element) {
    const shape = element.tableShape || 'rectangle';
    const seatCount = normalizeSeatCount(element.seatCount ?? defaultSeatCountForShape(shape), shape);

    if (shape === 'theater') {
        return generateTheaterSeats(element, seatCount);
    }

    if (isRoundTableShape(shape)) {
        return generateRoundSeats(element, seatCount);
    }

    return generatePerimeterSeats(element, seatCount);
}

function makeSeat(element, index, xCm, yCm, rotation = 0) {
    const number = index + 1;
    const roundedXCm = Math.round(xCm * 10) / 10;
    const roundedYCm = Math.round(yCm * 10) / 10;

    return {
        key: `${element.id}-seat-${number}`,
        number,
        label: String(number),
        xCm: roundedXCm,
        yCm: roundedYCm,
        x: roundedXCm,
        y: roundedYCm,
        rotation: Math.round(rotation),
    };
}

function elementWidthCm(element) {
    return Number(element.widthCm ?? defaultSizeForShape(element.tableShape).widthCm);
}

function elementHeightCm(element) {
    return Number(element.heightCm ?? defaultSizeForShape(element.tableShape).heightCm);
}

function generateRoundSeats(element, seatCount) {
    const centerX = elementWidthCm(element) / 2;
    const centerY = elementHeightCm(element) / 2;
    const radius = Math.max(elementWidthCm(element), elementHeightCm(element)) / 2
        + (ENGINEERING_SCALE.CHAIR_DIAMETER_CM / 2)
        + ENGINEERING_SCALE.CHAIR_GAP_CM;

    return Array.from({ length: seatCount }, (_, index) => {
        const angle = ((index / seatCount) * Math.PI * 2) - (Math.PI / 2);

        return makeSeat(
            element,
            index,
            centerX + (Math.cos(angle) * radius),
            centerY + (Math.sin(angle) * radius),
            (angle * 180 / Math.PI) + 90,
        );
    });
}

function generatePerimeterSeats(element, seatCount) {
    const width = elementWidthCm(element);
    const height = elementHeightCm(element);
    const gap = (ENGINEERING_SCALE.CHAIR_DIAMETER_CM / 2) + ENGINEERING_SCALE.CHAIR_GAP_CM;
    const sides = allocateSeatsBySide(width, height, seatCount, element.tableShape || 'rectangle');
    const seats = [];

    appendEdgeSeats(seats, element, sides.top, 0, width, -gap, -gap, 0);
    appendEdgeSeats(seats, element, sides.right, width + gap, width + gap, 0, height, 90);
    appendEdgeSeats(seats, element, sides.bottom, width, 0, height + gap, height + gap, 180);
    appendEdgeSeats(seats, element, sides.left, -gap, -gap, height, 0, 270);

    return seats;
}

function allocateSeatsBySide(width, height, seatCount, shape) {
    if (seatCount <= 0) {
        return { top: 0, right: 0, bottom: 0, left: 0 };
    }

    const longSidesBias = shape === 'banquet' ? 1.55 : 1;
    const weights = [
        { key: 'top', value: width * longSidesBias },
        { key: 'right', value: height },
        { key: 'bottom', value: width * longSidesBias },
        { key: 'left', value: height },
    ];
    const totalWeight = weights.reduce((total, side) => total + side.value, 0);
    const allocation = { top: 0, right: 0, bottom: 0, left: 0 };
    const fractions = [];

    weights.forEach((side) => {
        const exact = (seatCount * side.value) / totalWeight;
        allocation[side.key] = Math.floor(exact);
        fractions.push({ key: side.key, value: exact - allocation[side.key] });
    });

    let used = Object.values(allocation).reduce((total, count) => total + count, 0);

    fractions
        .sort((a, b) => b.value - a.value)
        .forEach((side) => {
            if (used >= seatCount) {
                return;
            }

            allocation[side.key] += 1;
            used += 1;
        });

    return allocation;
}

function appendEdgeSeats(seats, element, count, startX, endX, startY, endY, rotation) {
    for (let offset = 0; offset < count; offset += 1) {
        const ratio = (offset + 1) / (count + 1);
        const x = startX + ((endX - startX) * ratio);
        const y = startY + ((endY - startY) * ratio);

        seats.push(makeSeat(element, seats.length, x, y, rotation));
    }
}

function generateTheaterSeats(element, seatCount) {
    const rows = normalizeTheaterRows(element.theaterRows || 4, seatCount);
    const columns = Math.ceil(seatCount / rows);
    const xGap = 55;
    const yGap = 55;
    const startX = Math.max(35, (elementWidthCm(element) - ((columns - 1) * xGap)) / 2);
    const startY = 40;

    return Array.from({ length: seatCount }, (_, index) => {
        const row = Math.floor(index / columns);
        const column = index % columns;

        return makeSeat(
            element,
            index,
            startX + (column * xGap),
            startY + (row * yGap),
            0,
        );
    });
}
