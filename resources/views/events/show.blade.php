@extends('layouts.dashboard')

@section('title', $event->name)
@section('page_title', 'تفاصيل الحدث')

@section('content')
    <section class="event-hero-card">
        <div class="event-hero-main">
            <p class="eyebrow">تفاصيل الحدث</p>
            <h2>{{ $event->name }}</h2>
            <p>{{ $event->description ?: 'لا يوجد وصف لهذا الحدث.' }}</p>
        </div>

        <div class="header-actions event-hero-actions">
            <a href="{{ route('events.guests.index', $event) }}" class="btn btn-primary">
                <span class="btn-icon" aria-hidden="true">+</span>
                إدارة الضيوف
            </a>
            <a href="{{ route('events.edit', $event) }}" class="btn btn-secondary">تعديل</a>
            <form method="POST" action="{{ route('events.destroy', $event) }}" class="inline-form" data-confirm-message="هل تريد حذف هذا الحدث؟ سيتم حذف المخططات والضيوف المرتبطين به.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <span class="btn-icon" aria-hidden="true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 6h18"></path>
                            <path d="M8 6V4h8v2"></path>
                            <path d="M6 6l1 15h10l1-15"></path>
                            <path d="M10 11v6"></path>
                            <path d="M14 11v6"></path>
                        </svg>
                    </span>
                    حذف
                </button>
            </form>
            <a href="{{ route('events.index') }}" class="btn btn-secondary">كل الأحداث</a>
        </div>
    </section>

    <section class="event-summary-strip" aria-label="ملخص الحدث">
        <article>
            <span>تاريخ الحدث</span>
            <strong>{{ optional($event->event_date)->format('Y-m-d') ?: 'غير محدد' }}</strong>
        </article>
        <article>
            <span>الموقع</span>
            <strong>{{ $event->location ?: 'غير محدد' }}</strong>
        </article>
        <article>
            <span>المخططات</span>
            <strong>{{ $event->floorplans_count }} مخطط</strong>
        </article>
        <article>
            <span>الضيوف</span>
            <strong>{{ $event->guests_count }} ضيف</strong>
        </article>
    </section>

    @if ($guestTypeLinks !== [])
        <section class="panel-card event-type-links">
            <div class="panel-heading">
                <div>
                    <h2>روابط الأنواع</h2>
                    <p class="section-hint">انسخ رابط النوع المطلوب لاستخدامه مع تسجيل الضيوف.</p>
                </div>
            </div>

            <div class="event-type-link-grid">
                @foreach ($guestTypeLinks as $guestTypeLink)
                    <article class="event-type-link-card" style="--badge-color: {{ $guestTypeLink['color'] }}">
                        <div>
                            <span class="guest-type-badge">{{ $guestTypeLink['label'] }}</span>
                            <code>{{ $guestTypeLink['key'] }}</code>
                        </div>
                        <button
                            type="button"
                            class="btn btn-secondary btn-sm"
                            data-copy-text="{{ $guestTypeLink['url'] }}"
                            data-copy-label="نسخ رابط {{ $guestTypeLink['label'] }}"
                        >
                            نسخ رابط {{ $guestTypeLink['label'] }}
                        </button>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="panel-card floorplans-section">
        <div class="panel-heading">
            <div>
                <h2>مخططات القاعة</h2>
                <p class="section-hint">افتح المحرر أو عدل إعدادات أي مخطط من هنا.</p>
            </div>
            <a href="{{ route('events.floorplans.create', $event) }}" class="btn btn-primary btn-sm">
                <span class="btn-icon" aria-hidden="true">+</span>
                إضافة مخطط
            </a>
        </div>

        @if ($event->floorplans->isEmpty())
            <div class="empty-state compact">
                <h3>لا توجد مخططات بعد</h3>
                <p>أضف أول مخطط للقاعة وحدد المقاسات وصورة الخلفية إن وجدت.</p>
                <a href="{{ route('events.floorplans.create', $event) }}" class="btn btn-primary">إضافة مخطط</a>
            </div>
        @else
            <div class="floorplan-card-grid">
                @foreach ($event->floorplans as $floorplan)
                    @php($floorplanPreviewUrl = route('events.floorplans.preview', [$event->preview_token, $floorplan]))
                    <article class="floorplan-tile">
                        @if ($floorplan->background_image_path)
                            <img src="{{ $floorplan->backgroundImageUrl() }}" alt="خلفية {{ $floorplan->name }}">
                        @else
                            <div class="floorplan-placeholder">بدون صورة</div>
                        @endif

                        <div class="floorplan-tile-body">
                            <div class="floorplan-title-row">
                                <h3>{{ $floorplan->name }}</h3>
                                <span class="status-badge">{{ $floorplan->paper_size }} · {{ $floorplan->orientation === 'landscape' ? 'عرضي' : 'عمودي' }}</span>
                            </div>
                            <p>
                                {{ $floorplan->width }} × {{ $floorplan->height }}
                                {{ $floorplan->unit === 'meter' ? 'متر' : 'قدم' }}
                                · {{ $floorplan->seats_count }} مقعد
                            </p>

                            <div class="table-actions action-buttons">
                                <a href="{{ route('floorplans.editor', $floorplan) }}" class="btn btn-primary btn-sm">فتح المحرر</a>
                                @if ($event->preview_enabled)
                                    <a href="{{ $floorplanPreviewUrl }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">معاينة</a>
                                    <button type="button" class="btn btn-secondary btn-sm" data-copy-text="{{ $floorplanPreviewUrl }}" data-copy-label="نسخ رابط">
                                        نسخ رابط
                                    </button>
                                @endif
                                <a href="{{ route('floorplans.edit', $floorplan) }}" class="btn btn-secondary btn-sm">تعديل</a>
                                <form method="POST" action="{{ route('floorplans.destroy', $floorplan) }}" data-confirm-message="هل تريد حذف هذا المخطط؟ لن يظهر مرة أخرى داخل الحدث.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

@endsection
