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
        fill: '#F1EBF7',
        stroke: '#4B2A6E',
    },
    wall: {
        label: 'حائط',
        width: 240,
        height: 16,
        fill: '#3A3145',
        stroke: '#1E1826',
    },
    door: {
        label: 'باب',
        width: 88,
        height: 88,
        fill: '#FFFFFF',
        stroke: '#4B2A6E',
    },
    aisle: {
        label: 'ممر',
        width: 260,
        height: 58,
        fill: '#FAF8FC',
        stroke: '#A19F9E',
    },
    'vip-zone': {
        label: 'منطقة VIP',
        width: 220,
        height: 120,
        fill: '#F5F0EB',
        stroke: '#C1AC98',
    },
    chair: {
        label: 'كرسي',
        width: 42,
        height: 42,
        fill: '#FFFFFF',
        stroke: '#6D4299',
    },
    lighting: {
        label: 'إضاءة',
        width: 48,
        height: 48,
        fill: '#F5F0EB',
        stroke: '#C1AC98',
    },
    sound: {
        label: 'صوت',
        width: 54,
        height: 48,
        fill: '#F1EBF7',
        stroke: '#4B2A6E',
    },
    equipment: {
        label: 'معدات',
        width: 72,
        height: 54,
        fill: '#F8F6FA',
        stroke: '#6D4299',
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
