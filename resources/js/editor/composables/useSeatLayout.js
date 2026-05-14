export const TABLE_SHAPES = [
    { value: 'round', label: 'دائرية' },
    { value: 'rectangle', label: 'مستطيلة' },
    { value: 'square', label: 'مربعة' },
    { value: 'banquet', label: 'وليمة طويلة' },
    { value: 'theater', label: 'صفوف مسرح' },
];

export function shapeLabel(shape) {
    return TABLE_SHAPES.find((item) => item.value === shape)?.label || 'مستطيلة';
}

export function defaultSizeForShape(shape) {
    const sizes = {
        round: { width: 102, height: 102 },
        rectangle: { width: 178, height: 104 },
        square: { width: 124, height: 124 },
        banquet: { width: 240, height: 84 },
        theater: { width: 280, height: 170 },
    };

    return sizes[shape] || sizes.rectangle;
}

export function defaultSeatCountForShape(shape) {
    const defaults = {
        round: 5,
        theater: 24,
    };

    return defaults[shape] || 8;
}

export function normalizeSeatCount(value, shape = 'rectangle') {
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

    if (shape === 'round') {
        return generateRoundSeats(element, seatCount);
    }

    return generatePerimeterSeats(element, seatCount);
}

function makeSeat(element, index, x, y, rotation = 0) {
    const number = index + 1;

    return {
        key: `${element.id}-seat-${number}`,
        number,
        label: String(number),
        x: Math.round(x),
        y: Math.round(y),
        rotation: Math.round(rotation),
    };
}

function generateRoundSeats(element, seatCount) {
    const centerX = element.width / 2;
    const centerY = element.height / 2;
    const radius = Math.max(element.width, element.height) / 2 + 28;

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
    const width = element.width;
    const height = element.height;
    const gap = 28;
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
    const xGap = 36;
    const yGap = 36;
    const startX = Math.max(28, (element.width - ((columns - 1) * xGap)) / 2);
    const startY = 34;

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
