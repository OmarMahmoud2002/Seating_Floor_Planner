@extends('layouts.dashboard')

@section('title', 'الأحداث')
@section('page_title', 'الأحداث')

@section('content')
    <section class="page-header-card events-header-card">
        <div>
            <p class="eyebrow">إدارة الأحداث</p>
            <h2>كل الأحداث</h2>
            <p>تابع أحداثك، عدد مخططاتها، وانتقل مباشرة لإنشاء مخطط جديد لكل حدث.</p>
        </div>
        <a href="{{ route('events.create') }}" class="btn btn-primary">
            <span class="btn-icon" aria-hidden="true">+</span>
            إنشاء حدث
        </a>
    </section>

    <section class="panel-card">
        <form method="GET" action="{{ route('events.index') }}" class="toolbar-form compact-toolbar">
            <div class="field search-field">
                <label for="search">بحث</label>
                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="ابحث باسم الحدث أو الموقع"
                >
            </div>
            <button type="submit" class="btn btn-secondary">بحث</button>
            @if ($search !== '')
                <a href="{{ route('events.index') }}" class="btn btn-secondary">إعادة ضبط</a>
            @endif
        </form>

        @if ($events->isEmpty())
            <div class="empty-state">
                <img src="{{ asset('images/icon.png') }}" alt="" aria-hidden="true">
                <h3>لا توجد أحداث بعد</h3>
                <p>ابدأ بإنشاء أول حدث، وبعدها ستتمكن من إضافة المخططات والضيوف.</p>
                <a href="{{ route('events.create') }}" class="btn btn-primary">إنشاء حدث جديد</a>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table modern-table">
                    <thead>
                        <tr>
                            <th>اسم الحدث</th>
                            <th>التاريخ</th>
                            <th>الموقع</th>
                            <th>عدد المخططات</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($events as $event)
                            <tr>
                                <td>
                                    <strong>{{ $event->name }}</strong>
                                </td>
                                <td>{{ optional($event->event_date)->format('Y-m-d') ?: 'غير محدد' }}</td>
                                <td>{{ $event->location ?: 'غير محدد' }}</td>
                                <td>
                                    <span class="status-badge">{{ $event->floorplans_count }} مخطط</span>
                                </td>
                                <td>
                                    <div class="table-actions action-buttons">
                                        <a href="{{ route('events.show', $event) }}" class="btn btn-secondary btn-sm">عرض</a>
                                        <a href="{{ route('events.floorplans.create', $event) }}" class="btn btn-primary btn-sm">
                                            <span class="btn-icon" aria-hidden="true">+</span>
                                            إنشاء مخطط
                                        </a>
                                        <a href="{{ route('events.edit', $event) }}" class="btn btn-secondary btn-sm">تعديل</a>
                                        <form method="POST" action="{{ route('events.destroy', $event) }}" data-confirm-message="هل تريد حذف هذا الحدث؟ سيتم حذف المخططات والضيوف المرتبطين به.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                {{ $events->links() }}
            </div>
        @endif
    </section>
@endsection
