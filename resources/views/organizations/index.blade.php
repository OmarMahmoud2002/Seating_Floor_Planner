@extends('layouts.dashboard')

@section('title', 'المنظمات')
@section('page_title', 'المنظمات')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">إدارة المنظمات</p>
            <h2>كل المنظمات</h2>
            <p>راجع المنظمات المرتبطة بمحرك التجليس، وعدد المستخدمين والأحداث والضيوف داخل كل منظمة.</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">لوحة التحكم</a>
    </section>

    <section class="panel-card">
        <form method="GET" action="{{ route('organizations.index') }}" class="toolbar-form compact-toolbar">
            <div class="field search-field">
                <label for="search">بحث</label>
                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="ابحث باسم المنظمة، البريد، الهاتف، أو external ID"
                >
            </div>
            <button type="submit" class="btn btn-secondary">بحث</button>
            @if ($search !== '')
                <a href="{{ route('organizations.index') }}" class="btn btn-secondary">إعادة ضبط</a>
            @endif
        </form>

        @if ($organizations->isEmpty())
            <div class="empty-state compact">
                <h3>لا توجد منظمات بعد</h3>
                <p>ستظهر المنظمات هنا بعد إنشاء البيانات المحلية القديمة أو استقبال أول مزامنة من eventos_25.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table modern-table">
                    <thead>
                        <tr>
                            <th>المنظمة</th>
                            <th>External ID</th>
                            <th>التواصل</th>
                            <th>المستخدمون</th>
                            <th>الأحداث</th>
                            <th>الضيوف</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($organizations as $organization)
                            <tr>
                                <td>
                                    <strong>{{ $organization->name }}</strong>
                                    @if ($organization->external_user_id === 0)
                                        <span class="muted-line">منظمة البيانات القديمة</span>
                                    @endif
                                </td>
                                <td>{{ $organization->external_user_id }}</td>
                                <td>
                                    <span class="stacked-meta">
                                        <span>{{ $organization->email ?: 'بدون بريد' }}</span>
                                        <span>{{ $organization->phone ?: 'بدون هاتف' }}</span>
                                    </span>
                                </td>
                                <td><span class="status-badge">{{ $organization->users_count }} مستخدم</span></td>
                                <td><span class="status-badge">{{ $organization->events_count }} حدث</span></td>
                                <td><span class="status-badge">{{ $organization->guests_count }} ضيف</span></td>
                                <td>
                                    <a href="{{ route('organizations.show', $organization) }}" class="btn btn-secondary btn-sm">عرض</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                {{ $organizations->links() }}
            </div>
        @endif
    </section>
@endsection
