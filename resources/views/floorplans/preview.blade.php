@extends('layouts.app')

@section('title', 'معاينة المخطط')

@section('body')
    @php
        $design = $floorplan->design_json ?: [];
        $elements = collect($design['elements'] ?? []);
        $gridSize = max((int) ($floorplan->grid_size ?: 20), 5);
        $paperSizes = [
            'A2' => ['width' => 1684, 'height' => 1191],
            'A3' => ['width' => 1191, 'height' => 842],
            'A4' => ['width' => 842, 'height' => 595],
        ];
        $paperSize = $paperSizes[$floorplan->paper_size] ?? $paperSizes['A3'];
        $paperWidth = $floorplan->orientation === 'portrait' ? $paperSize['height'] : $paperSize['width'];
        $paperHeight = $floorplan->orientation === 'portrait' ? $paperSize['width'] : $paperSize['height'];
        $pixelsPerUnit = $floorplan->unit === 'foot' ? max($gridSize * 0.55, 8) : max($gridSize * 1.6, 24);
        $paperScale = $floorplan->paper_size === 'A2' ? 0.82 : ($floorplan->paper_size === 'A4' ? 1.06 : 0.94);
        $rawWidth = max(max((float) $floorplan->width, 1) * $pixelsPerUnit, $paperWidth * $paperScale);
        $rawHeight = max(max((float) $floorplan->height, 1) * $pixelsPerUnit, $paperHeight * $paperScale);
        $paperRatio = $paperWidth / $paperHeight;
        $canvasRatio = $rawWidth / $rawHeight;

        if ($canvasRatio > $paperRatio) {
            $rawHeight = $rawWidth / $paperRatio;
        } else {
            $rawWidth = $rawHeight * $paperRatio;
        }

        $fitScale = max(900 / $rawWidth, 620 / $rawHeight, 1);
        $canvasWidth = (int) (ceil(min(max($rawWidth * $fitScale, 900), 4200) / $gridSize) * $gridSize);
        $canvasHeight = (int) (ceil(min(max($rawHeight * $fitScale, 620), 3000) / $gridSize) * $gridSize);
        $widthMeters = $floorplan->unit === 'foot' ? ((float) $floorplan->width * 0.3048) : (float) $floorplan->width;
        $heightMeters = $floorplan->unit === 'foot' ? ((float) $floorplan->height * 0.3048) : (float) $floorplan->height;
        $formatMeters = fn (float $value): string => rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    @endphp

    <main class="floorplan-preview-page">
        <header class="floorplan-preview-header">
            <div>
                <p class="eyebrow">معاينة مخطط القاعة</p>
                <h1>{{ $floorplan->name }}</h1>
                <p>
                    {{ $event->name }} ·
                    {{ $floorplan->width }} × {{ $floorplan->height }}
                    {{ $floorplan->unit === 'meter' ? 'متر' : 'قدم' }}
                </p>
            </div>
            <img src="{{ asset('images/logo.png') }}" alt="Perfection">
        </header>

        <section class="floorplan-preview-shell" data-preview-shell>
            <div class="floorplan-preview-workspace">
                <aside class="floorplan-preview-legend" aria-label="توضيح ألوان المعاينة">
                    <h2>توضيح الألوان</h2>

                    <div class="floorplan-preview-legend-group">
                        <h3>أنواع الضيوف</h3>
                        @forelse ($guestTypes as $guestType)
                            <div class="floorplan-preview-legend-item">
                                <span class="legend-swatch" style="--legend-color: {{ $guestType->color ?: '#317C77' }}"></span>
                                <span>{{ $guestType->display_name_ar }}</span>
                            </div>
                        @empty
                            <div class="floorplan-preview-legend-item muted">
                                <span class="legend-swatch" style="--legend-color: #317C77"></span>
                                <span>المقاعد المحجوزة</span>
                            </div>
                        @endforelse
                    </div>

                    <div class="floorplan-preview-legend-group">
                        <h3>حالة الضيف</h3>
                        <div class="floorplan-preview-legend-item">
                            <span class="legend-icon attendance"></span>
                            <span>حضر</span>
                        </div>
                        <div class="floorplan-preview-legend-item">
                            <span class="legend-icon gift">
                                <svg viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                    <rect x="3.2" y="6.4" width="9.6" height="6.4" rx="1.2"></rect>
                                    <rect x="2.4" y="3.6" width="11.2" height="3.2" rx="1.1"></rect>
                                    <path d="M8 3.6v9.2M3.2 7.2h9.6"></path>
                                </svg>
                            </span>
                            <span>استلم الهدية</span>
                        </div>
                    </div>

                    <div class="floorplan-preview-legend-group">
                        <h3>عناصر المخطط</h3>
                        <div class="floorplan-preview-legend-item">
                            <span class="legend-line wall"></span>
                            <span>حائط</span>
                        </div>
                        <div class="floorplan-preview-legend-item">
                            <span class="legend-line door"></span>
                            <span>باب</span>
                        </div>
                        <div class="floorplan-preview-legend-item">
                            <span class="legend-line aisle"></span>
                            <span>ممر</span>
                        </div>
                    </div>
                </aside>

                <div class="floorplan-preview-canvas-frame">
                    <div class="floorplan-preview-dimension horizontal">
                        <span>{{ $formatMeters($widthMeters) }} متر عرض</span>
                    </div>
                    <div class="floorplan-preview-dimension vertical">
                        <span>{{ $formatMeters($heightMeters) }} متر طول</span>
                    </div>

                    <div
                        class="floorplan-preview-canvas"
                        style="width: {{ $canvasWidth }}px; height: {{ $canvasHeight }}px; --preview-grid-size: {{ $gridSize }}px;"
                    >
                        @if ($floorplan->background_image_path)
                            <img src="{{ $floorplan->backgroundImageUrl() }}" alt="" class="floorplan-preview-bg">
                        @endif

                        @forelse ($elements as $element)
                            @php
                                $type = $element['type'] ?? 'element';
                                $isTable = $type === 'table';
                                $isDoor = $type === 'door';
                                $shape = $element['tableShape'] ?? 'rectangle';
                                $left = (float) ($element['x'] ?? 0);
                                $top = (float) ($element['y'] ?? 0);
                                $width = max((float) ($element['width'] ?? 80), 12);
                                $height = max((float) ($element['height'] ?? 50), 12);
                                $rotation = (float) ($element['rotation'] ?? 0);
                            @endphp

                            <div
                                class="floorplan-preview-element {{ $isTable ? 'table' : 'generic' }} type-{{ $type }} {{ $shape === 'round' ? 'round' : '' }}"
                                style="left: {{ $left }}px; top: {{ $top }}px; width: {{ $width }}px; height: {{ $height }}px; transform: rotate({{ $rotation }}deg);"
                            >
                                @if ($isDoor)
                                    <svg class="floorplan-preview-door-svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                                        <line x1="8" y1="92" x2="92" y2="92"></line>
                                        <line x1="92" y1="8" x2="92" y2="92"></line>
                                        <path d="M 8 92 A 84 84 0 0 1 92 8"></path>
                                        <circle cx="8" cy="92" r="5"></circle>
                                    </svg>
                                    <span class="floorplan-preview-element-label door-label">{{ $element['label'] ?? 'باب' }}</span>
                                @else
                                    <span class="floorplan-preview-element-label">{{ $element['label'] ?? ($isTable ? 'طاولة' : 'عنصر') }}</span>
                                @endif

                                @if ($isTable)
                                    @foreach (($element['seats'] ?? []) as $seat)
                                        @php
                                            $assignment = $assignments->get($seat['key'] ?? '');
                                            $guest = $assignment?->guest;
                                            $guestType = $guest?->guestType;
                                            $seatColor = $guestType?->color ?: '#317C77';
                                            $seatLeft = (float) ($seat['x'] ?? 0);
                                            $seatTop = (float) ($seat['y'] ?? 0);
                                            $seatRotation = (float) ($seat['rotation'] ?? 0);
                                            $attended = $guest && $guest->status === 'attended';
                                            $giftUsed = $guest && $guest->gift_status === 'used';
                                        @endphp

                                        <div
                                            class="floorplan-preview-seat {{ $guest ? 'assigned' : '' }} {{ $attended ? 'attended' : '' }} {{ $giftUsed ? 'gift-used' : '' }}"
                                            style="left: {{ $seatLeft }}px; top: {{ $seatTop }}px; transform: translate(-50%, -50%) rotate({{ $seatRotation }}deg); --seat-color: {{ $seatColor }};"
                                        >
                                            @if ($guest)
                                                <strong class="floorplan-preview-guest-name">{{ $guest->name }}</strong>
                                                @if ($attended || $giftUsed)
                                                    <div class="floorplan-preview-seat-indicators" aria-label="حالة {{ $guest->name }}">
                                                        @if ($attended)
                                                            <span class="preview-seat-icon attendance active" title="حضر" aria-label="حضر"></span>
                                                        @endif
                                                        @if ($giftUsed)
                                                            <span class="preview-seat-icon gift active" title="استلم الهدية" aria-label="استلم الهدية">
                                                                <svg viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                                                                    <rect x="3.2" y="6.4" width="9.6" height="6.4" rx="1.2"></rect>
                                                                    <rect x="2.4" y="3.6" width="11.2" height="3.2" rx="1.1"></rect>
                                                                    <path d="M8 3.6v9.2M3.2 7.2h9.6"></path>
                                                                </svg>
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                            <span>{{ $seat['label'] ?? $seat['number'] ?? '' }}</span>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        @empty
                            <div class="floorplan-preview-empty">لم يتم تجهيز عناصر المخطط بعد</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        window.addEventListener('load', () => {
            const shell = document.querySelector('[data-preview-shell]');

            if (!shell) {
                return;
            }

            shell.scrollLeft = Math.max((shell.scrollWidth - shell.clientWidth) / 2, 0);
        });
    </script>
@endsection
