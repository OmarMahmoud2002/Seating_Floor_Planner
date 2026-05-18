@extends('layouts.app')

@section('title', 'معاينة المخطط')

@section('body')
    @php
        use App\Support\FloorPlannerScale as Scale;

        $design = $floorplan->design_json ?: [];
        $elements = collect($design['elements'] ?? []);
        $canvasWidth = (int) round(Scale::metersToPixels($floorplan->width));
        $canvasHeight = (int) round(Scale::metersToPixels($floorplan->height));
        $smallGridSize = Scale::cmToPixels(Scale::SMALL_GRID_CM);
        $meterGridSize = Scale::metersToPixels(1);
        $widthMeters = (float) $floorplan->width;
        $heightMeters = (float) $floorplan->height;
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
            <div class="floorplan-preview-stage">
                <aside class="floorplan-preview-legend" aria-label="توضيح ألوان المعاينة">
                    <div class="floorplan-preview-legend-actions">
                        <button type="button" class="floorplan-preview-refresh" data-preview-refresh>
                            تحديث
                        </button>
                    </div>

                    <div class="floorplan-preview-legend-content">
                        <h2>توضيح الألوان</h2>

                        <div class="floorplan-preview-legend-groups">
                            <div class="floorplan-preview-legend-group">
                                <h3>أنواع الضيوف</h3>
                                <div class="floorplan-preview-legend-items">
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
                            </div>

                            <div class="floorplan-preview-legend-group">
                                <h3>حالة الضيف</h3>
                                <div class="floorplan-preview-legend-items">
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
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="floorplan-preview-workspace">
                    <div class="floorplan-preview-canvas-frame">
                        <div class="floorplan-preview-dimension horizontal">
                            <span>{{ $formatMeters($widthMeters) }} متر عرض</span>
                        </div>
                        <div class="floorplan-preview-dimension vertical">
                            <span>{{ $formatMeters($heightMeters) }} متر طول</span>
                        </div>

                        <div
                            class="floorplan-preview-canvas"
                            style="width: {{ $canvasWidth }}px; height: {{ $canvasHeight }}px; --preview-small-grid-size: {{ $smallGridSize }}px; --preview-meter-grid-size: {{ $meterGridSize }}px;"
                        >
                            @if ($floorplan->background_image_path)
                                <img src="{{ $floorplan->backgroundImageUrl() }}" alt="" class="floorplan-preview-bg">
                            @endif

                            @for ($meter = 0; $meter <= floor($widthMeters); $meter++)
                                <span class="floorplan-preview-ruler top" style="left: {{ Scale::metersToPixels($meter) + 3 }}px;">{{ $meter }}m</span>
                            @endfor
                            @for ($meter = 0; $meter <= floor($heightMeters); $meter++)
                                <span class="floorplan-preview-ruler left" style="top: {{ Scale::metersToPixels($meter) + 3 }}px;">{{ $meter }}m</span>
                            @endfor

                            @forelse ($elements as $element)
                                @php
                                    $type = $element['type'] ?? 'element';
                                    $isTable = $type === 'table';
                                    $isDoor = $type === 'door';
                                    $shape = $element['tableShape'] ?? 'rectangle';
                                    $isRound = Scale::isRoundTable($shape);
                                    $rect = Scale::elementRect($element);
                                    $left = $rect['x'];
                                    $top = $rect['y'];
                                    $width = $rect['width'];
                                    $height = $rect['height'];
                                    $rotation = (float) ($element['rotation'] ?? 0);
                                @endphp

                                <div
                                    class="floorplan-preview-element {{ $isTable ? 'table' : 'generic' }} type-{{ $type }} {{ $isRound ? 'round' : '' }}"
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
                                    @elseif ($isRound)
                                        <span class="floorplan-preview-element-label">{{ $element['label'] ?? 'Table' }}</span>
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
                                                $seatPosition = Scale::seatPosition($seat);
                                                $seatLeft = $seatPosition['x'];
                                                $seatTop = $seatPosition['y'];
                                                $seatSize = Scale::cmToPixels(Scale::CHAIR_DIAMETER_CM);
                                                $seatRotation = (float) ($seat['rotation'] ?? 0);
                                                $labelPosition = $guest ? Scale::guestLabelPosition($seat, $shape) : null;
                                                $attended = $guest && $guest->status === 'attended';
                                                $giftUsed = $guest && $guest->gift_status === 'used';
                                            @endphp

                                            <div
                                                class="floorplan-preview-seat {{ $guest ? 'assigned' : '' }} {{ $attended ? 'attended' : '' }} {{ $giftUsed ? 'gift-used' : '' }}"
                                                style="left: {{ $seatLeft }}px; top: {{ $seatTop }}px; width: {{ $seatSize }}px; height: {{ $seatSize }}px; transform: translate(-50%, -50%) rotate({{ $seatRotation }}deg); --seat-color: {{ $seatColor }};"
                                            >
                                                @if ($guest)
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
                                            @if ($guest && $labelPosition)
                                                <strong
                                                    class="floorplan-preview-guest-name"
                                                    style="left: {{ $labelPosition['x'] }}px; top: {{ $labelPosition['y'] }}px; width: {{ $labelPosition['width'] }}px; height: {{ $labelPosition['height'] }}px; --seat-color: {{ $seatColor }};"
                                                >
                                                    {{ $guest->name }}
                                                </strong>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            @empty
                                <div class="floorplan-preview-empty">لم يتم تجهيز عناصر المخطط بعد</div>
                            @endforelse
                        </div>
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

        document.querySelector('[data-preview-refresh]')?.addEventListener('click', () => {
            window.location.reload();
        });
    </script>
@endsection
