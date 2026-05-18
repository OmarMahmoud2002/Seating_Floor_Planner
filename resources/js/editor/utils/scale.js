export const ENGINEERING_SCALE = Object.freeze({
    CM_PER_METER: 100,
    PIXELS_PER_METER: 80,
    SMALL_GRID_CM: 10,
    CHAIR_DIAMETER_CM: 45,
    CHAIR_GAP_CM: 10,
});

export function metersToPixels(meters) {
    return Math.max(Number(meters || 0), 0) * ENGINEERING_SCALE.PIXELS_PER_METER;
}

export function cmToPixels(cm) {
    return (Number(cm || 0) / ENGINEERING_SCALE.CM_PER_METER)
        * ENGINEERING_SCALE.PIXELS_PER_METER;
}

export function pixelsToCm(px) {
    return (Number(px || 0) / ENGINEERING_SCALE.PIXELS_PER_METER)
        * ENGINEERING_SCALE.CM_PER_METER;
}

export function floorPixelSize(floorplan) {
    return {
        width: Math.max(Math.round(metersToPixels(floorplan?.width || 1)), 1),
        height: Math.max(Math.round(metersToPixels(floorplan?.height || 1)), 1),
    };
}

export function floorCmSize(floorplan) {
    return {
        width: Math.max(Number(floorplan?.width || 1), 1) * ENGINEERING_SCALE.CM_PER_METER,
        height: Math.max(Number(floorplan?.height || 1), 1) * ENGINEERING_SCALE.CM_PER_METER,
    };
}

export function snapCm(value, enabled = true) {
    const numeric = Number(value || 0);

    if (!enabled) {
        return numeric;
    }

    return Math.round(numeric / ENGINEERING_SCALE.SMALL_GRID_CM) * ENGINEERING_SCALE.SMALL_GRID_CM;
}

export function pixelsToSnappedCm(px, enabled = true) {
    return snapCm(pixelsToCm(px), enabled);
}

export function cmRectToPixels(element) {
    return {
        x: cmToPixels(element.xCm ?? pixelsToCm(element.x || 0)),
        y: cmToPixels(element.yCm ?? pixelsToCm(element.y || 0)),
        width: Math.max(cmToPixels(element.widthCm ?? pixelsToCm(element.width || 0)), 1),
        height: Math.max(cmToPixels(element.heightCm ?? pixelsToCm(element.height || 0)), 1),
    };
}

export function formatMeters(value, locale = 'ar-EG') {
    return new Intl.NumberFormat(locale, {
        maximumFractionDigits: 2,
        minimumFractionDigits: 0,
    }).format(Number(value || 0));
}
