@extends('layouts.dashboard')

@section('title', 'معاينة استيراد الضيوف')
@section('page_title', 'معاينة الاستيراد')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">مراجعة قبل الحفظ</p>
            <h2>{{ $event->name }}</h2>
            <p>الملف: {{ $originalName }}. سيتم حفظ الصفوف الصالحة فقط عند تأكيد الاستيراد.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('events.guests.import.create', $event) }}" class="btn btn-secondary">رفع ملف آخر</a>
            <a href="{{ route('events.guests.index', $event) }}" class="btn btn-secondary">رجوع للضيوف</a>
        </div>
    </section>

    <section class="import-summary-grid">
        <article class="stat-card">
            <p>إجمالي الصفوف</p>
            <strong>{{ $preview['summary']['total_rows'] }}</strong>
            <span>كل الصفوف المقروءة من أول Sheet.</span>
        </article>
        <article class="stat-card">
            <p>جاهزة للاستيراد</p>
            <strong>{{ $preview['summary']['valid_rows'] }}</strong>
            <span>سيتم حفظها عند التأكيد.</span>
        </article>
        <article class="stat-card">
            <p>مكررة</p>
            <strong>{{ $preview['summary']['duplicate_rows'] }}</strong>
            <span>سيتم تخطيها بدون تعديل البيانات الحالية.</span>
        </article>
        <article class="stat-card">
            <p>غير صالحة</p>
            <strong>{{ $preview['summary']['invalid_rows'] }}</strong>
            <span>تحتاج تصحيحًا في الملف.</span>
        </article>
    </section>

    @if (! empty($preview['summary']['new_type_names']))
        <section class="panel-card import-note">
            <strong>أنواع ضيوف جديدة</strong>
            <p>سيتم إنشاء الأنواع التالية عند التأكيد: {{ implode('، ', $preview['summary']['new_type_names']) }}</p>
        </section>
    @endif

    <section class="panel-card">
        <div class="panel-heading">
            <h2>معاينة الصفوف</h2>
            <form method="POST" action="{{ route('events.guests.import.store', $event) }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <button type="submit" class="btn btn-primary" @disabled($preview['summary']['valid_rows'] === 0)>تأكيد الاستيراد</button>
            </form>
        </div>

        @if ($preview['summary']['total_rows'] === 0)
            <div class="empty-state compact">
                <h3>لا توجد صفوف قابلة للقراءة</h3>
                <p>تأكد من أن الملف يحتوي على صف عناوين وصفوف ضيوف تحته.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>الصف</th>
                            <th>الحالة</th>
                            <th>الضيف</th>
                            <th>النوع</th>
                            <th>التواصل</th>
                            <th>ملاحظات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($preview['rows'] as $row)
                            <tr>
                                <td>{{ $row['row_number'] }}</td>
                                <td>
                                    @if ($row['status'] === 'valid')
                                        <span class="import-status valid">صالح</span>
                                    @elseif ($row['status'] === 'duplicate')
                                        <span class="import-status duplicate">مكرر</span>
                                    @else
                                        <span class="import-status invalid">غير صالح</span>
                                    @endif

                                    @if ($row['errors'])
                                        <small class="muted-line">{{ implode(' ', $row['errors']) }}</small>
                                    @endif
                                </td>
                                <td><strong>{{ $row['name'] ?: 'بدون اسم' }}</strong></td>
                                <td>
                                    @if ($row['type'])
                                        <span class="guest-type-badge">{{ $row['type'] }}</span>
                                        @if ($row['is_new_type'])
                                            <small class="muted-line">نوع جديد</small>
                                        @endif
                                    @else
                                        <span class="status-badge muted">بدون نوع</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="stacked-meta">
                                        <span>{{ $row['phone'] ?: 'لا يوجد هاتف' }}</span>
                                        <span>{{ $row['email'] ?: 'لا يوجد بريد' }}</span>
                                    </span>
                                </td>
                                <td>{{ $row['notes'] ?: 'لا توجد ملاحظات' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
