@extends('layouts.dashboard')

@section('title', 'المخططات')
@section('page_title', 'المخططات')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">مخططات القاعات</p>
            <h2>كل المخططات</h2>
            <p>تابع مخططات كل الأحداث وافتح المحرر مباشرة للتعديل أو تجليس الضيوف.</p>
        </div>
        <a href="{{ route('events.index') }}" class="btn btn-primary">اختيار حدث لإضافة مخطط</a>
    </section>

    <section class="panel-card">
        <form method="GET" action="{{ route('floorplans.index') }}" class="toolbar-form compact-toolbar">
            <div class="field search-field">
                <label for="search">بحث</label>
                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="ابحث باسم المخطط، الحدث، أو الموقع"
                >
            </div>
            <button type="submit" class="btn btn-secondary">بحث</button>
            @if ($search !== '')
                <a href="{{ route('floorplans.index') }}" class="btn btn-secondary">إعادة ضبط</a>
            @endif
        </form>

        @if ($floorplans->isEmpty())
            <div class="empty-state">
                <img src="{{ asset('images/icon.png') }}" alt="" aria-hidden="true">
                <h3>لا توجد مخططات بعد</h3>
                <p>أنشئ حدثا أولا، ثم أضف مخطط القاعة من صفحة تفاصيل الحدث.</p>
                <a href="{{ route('events.index') }}" class="btn btn-primary">عرض الأحداث</a>
            </div>
        @else
            <div class="floorplan-list floorplans-index-list">
                @foreach ($floorplans as $floorplan)
                    <article class="floorplan-card floorplan-index-card">
                        @if ($floorplan->background_image_path)
                            <img src="{{ $floorplan->backgroundImageUrl() }}" alt="خلفية {{ $floorplan->name }}">
                        @else
                            <div class="floorplan-placeholder">بدون صورة</div>
                        @endif

                        <div class="floorplan-index-content">
                            <div class="floorplan-body">
                                <div>
                                    <h3>{{ $floorplan->name }}</h3>
                                    <p>
                                        {{ $floorplan->event->name }} ·
                                        {{ $floorplan->width }} × {{ $floorplan->height }}
                                        {{ $floorplan->unit === 'meter' ? 'متر' : 'قدم' }}
                                    </p>
                                </div>
                                <span class="status-badge">
                                    {{ $floorplan->paper_size }} · {{ $floorplan->orientation === 'landscape' ? 'عرضي' : 'عمودي' }}
                                </span>
                            </div>

                            <div class="table-actions">
                                <a href="{{ route('floorplans.editor', $floorplan) }}" class="btn btn-primary btn-sm">فتح المحرر</a>
                                @if ($floorplan->event->preview_enabled)
                                    <a href="{{ route('events.floorplans.preview', [$floorplan->event->preview_token, $floorplan]) }}" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">معاينة</a>
                                @endif
                                <a href="{{ route('floorplans.edit', $floorplan) }}" class="btn btn-secondary btn-sm">تعديل</a>
                                <a href="{{ route('events.show', $floorplan->event) }}" class="btn btn-secondary btn-sm">تفاصيل الحدث</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="pagination-wrap">
                {{ $floorplans->links() }}
            </div>
        @endif
    </section>
@endsection
