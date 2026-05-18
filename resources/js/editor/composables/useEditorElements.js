import { pixelsToCm } from '../utils/scale';

export const ELEMENT_LIBRARY = [
    {
        group: 'عناصر القاعة',
        items: [
            { type: 'stage', label: 'مسرح', description: 'منصة رئيسية', icon: 'shape-stage' },
            { type: 'wall', label: 'حائط', description: 'فاصل أو جدار', icon: 'shape-wall' },
            { type: 'door', label: 'باب', description: 'مدخل أو مخرج', icon: 'shape-door' },
            { type: 'aisle', label: 'ممر', description: 'مسار حركة', icon: 'shape-aisle' },
            { type: 'vip-zone', label: 'منطقة VIP', description: 'منطقة مميزة', icon: 'shape-vip-zone' },
        ],
    },
    {
        group: 'معدات',
        items: [
            { type: 'chair', label: 'كرسي منفرد', description: 'كرسي إضافي', icon: 'shape-chair' },
            { type: 'lighting', label: 'إضاءة', description: 'نقطة إضاءة', icon: 'shape-lighting' },
            { type: 'sound', label: 'صوت', description: 'سماعة أو جهاز صوت', icon: 'shape-sound' },
            { type: 'equipment', label: 'معدات', description: 'عنصر تجهيز عام', icon: 'shape-equipment' },
        ],
    },
];

const DEFAULTS = {
    stage: {
        label: 'مسرح',
        width: 260,
        height: 90,
        fill: '#EAF4FB',
        stroke: '#31719D',
    },
    wall: {
        label: 'حائط',
        width: 240,
        height: 16,
        fill: '#344054',
        stroke: '#1F2937',
    },
    door: {
        label: 'باب',
        width: 88,
        height: 88,
        fill: '#FFFFFF',
        stroke: '#317C77',
    },
    aisle: {
        label: 'ممر',
        width: 260,
        height: 58,
        fill: '#F8FAFC',
        stroke: '#A19F9E',
    },
    'vip-zone': {
        label: 'منطقة VIP',
        width: 220,
        height: 120,
        fill: '#FFF8D8',
        stroke: '#E7C539',
    },
    chair: {
        label: 'كرسي',
        width: 42,
        height: 42,
        fill: '#FFFFFF',
        stroke: '#4596CF',
    },
    lighting: {
        label: 'إضاءة',
        width: 48,
        height: 48,
        fill: '#FFF8D8',
        stroke: '#E7C539',
    },
    sound: {
        label: 'صوت',
        width: 54,
        height: 48,
        fill: '#EAF4FB',
        stroke: '#31719D',
    },
    equipment: {
        label: 'معدات',
        width: 72,
        height: 54,
        fill: '#F3F8FA',
        stroke: '#4D9B97',
    },
};

export function elementDefaults(type) {
    return DEFAULTS[type] || DEFAULTS.equipment;
}

export function elementTypeLabel(type) {
    return DEFAULTS[type]?.label || 'عنصر';
}

export function normalizeGenericElement(element) {
    const defaults = elementDefaults(element.type);
    const widthCm = Number(element.widthCm ?? pixelsToCm(element.width ?? defaults.width));
    const heightCm = Number(element.heightCm ?? pixelsToCm(element.height ?? defaults.height));
    const xCm = Number(element.xCm ?? pixelsToCm(element.x ?? 120));
    const yCm = Number(element.yCm ?? pixelsToCm(element.y ?? 120));

    return {
        ...element,
        label: element.label || defaults.label,
        xCm,
        yCm,
        widthCm: Math.max(widthCm, 10),
        heightCm: Math.max(heightCm, 10),
        x: xCm,
        y: yCm,
        width: Math.max(widthCm, 10),
        height: Math.max(heightCm, 10),
        rotation: Number(element.rotation || 0),
        fill: element.fill || defaults.fill,
        stroke: element.stroke || defaults.stroke,
        opacity: Math.min(Math.max(Number(element.opacity ?? 1), 0.1), 1),
    };
}
