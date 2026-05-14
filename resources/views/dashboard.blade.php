@extends('layouts.dashboard')

@section('title', 'لوحة التحكم')
@section('page_title', 'لوحة التحكم')

@section('content')
    <section class="dashboard-hero">
        <div class="dashboard-hero-content">
            <span class="hero-kicker">جاهز للتجليس</span>
            <h2>نظم الأحداث ومخططات القاعات من شاشة واحدة</h2>
            <p>
                أنشئ حدثك، أضف الضيوف، جهز مخطط القاعة، ثم افتح المحرر لتجليس كل ضيف على المقعد المناسب.
            </p>
            <div class="hero-actions">
                <a href="{{ route('events.create') }}" class="btn btn-primary">
                    إنشاء حدث جديد
                </a>
                <a href="{{ route('events.index') }}" class="btn btn-secondary">عرض الأحداث</a>
                @if ($isSuperAdmin)
                    <a href="{{ route('organizations.index') }}" class="btn btn-secondary">إدارة المنظمات</a>
                @endif
            </div>
        </div>

        <div class="hero-summary-card" aria-label="ملخص سريع">
            @if ($isSuperAdmin)
                <span>صلاحية كاملة</span>
                <strong>أنت ترى كل المنظمات والأحداث</strong>
                <p>استخدم صفحة المنظمات لمراجعة البيانات المتزامنة والمحلية من مكان واحد.</p>
            @else
                <span>العمل التالي</span>
                <strong>اختر حدثا أو أنشئ حدثا جديدا</strong>
                <p>كل حدث يمكن أن يحتوي على أكثر من مخطط قاعة وقائمة ضيوف مستقلة.</p>
            @endif
        </div>
    </section>

    <section class="stats-grid dashboard-stats" aria-label="ملخص النظام">
        @foreach ($stats as $stat)
            <article class="stat-card">
                <p>{{ $stat['label'] }}</p>
                <strong>{{ $stat['value'] }}</strong>
                <span>{{ $stat['hint'] }}</span>
            </article>
        @endforeach
    </section>

    <section class="dashboard-events-section">
        <article class="panel-card dashboard-events-panel">
            <div class="panel-heading">
                <div>
                    <h2>آخر الأحداث</h2>
                    <p class="section-hint">أقرب مكان للعودة إلى إدارة الحدث والمخططات.</p>
                </div>
                <a href="{{ route('events.index') }}" class="status-badge">عرض الكل</a>
            </div>

            @if ($recentEvents->isEmpty())
                <div class="empty-state">
                    <img src="{{ asset('images/icon2.jpeg') }}" alt="" aria-hidden="true">
                    <h3>لا توجد أحداث بعد</h3>
                    <p>ابدأ بإنشاء أول حدث، وبعدها ستتمكن من إضافة مخطط قاعة وفتح المحرر للتجليس.</p>
                    <a href="{{ route('events.create') }}" class="btn btn-primary">إنشاء حدث جديد</a>
                </div>
            @else
                <div class="event-list rich-event-list">
                    @foreach ($recentEvents as $event)
                        <a href="{{ route('events.show', $event) }}" class="event-list-item rich-event-item">
                            <div>
                                <strong>{{ $event->name }}</strong>
                                <span>
                                    {{ optional($event->event_date)->format('Y-m-d') ?: 'بدون تاريخ' }} · {{ $event->location ?: 'بدون موقع' }}
                                    @if ($isSuperAdmin && $event->organization)
                                        · {{ $event->organization->name }}
                                    @endif
                                </span>
                            </div>
                            <div class="event-mini-stats">
                                <span>{{ $event->floorplans_count }} مخطط</span>
                                <span>{{ $event->guests_count }} ضيف</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
