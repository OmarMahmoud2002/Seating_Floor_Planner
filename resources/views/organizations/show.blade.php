@extends('layouts.dashboard')

@section('title', $organization->name)
@section('page_title', 'تفاصيل المنظمة')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">تفاصيل المنظمة</p>
            <h2>{{ $organization->name }}</h2>
            <p>
                External ID: {{ $organization->external_user_id }}
                @if ($organization->email)
                    · {{ $organization->email }}
                @endif
                @if ($organization->phone)
                    · {{ $organization->phone }}
                @endif
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('organizations.index') }}" class="btn btn-secondary">كل المنظمات</a>
            <a href="{{ route('events.index') }}" class="btn btn-secondary">كل الأحداث</a>
        </div>
    </section>

    <section class="stats-grid" aria-label="ملخص المنظمة">
        <article class="stat-card">
            <p>المستخدمون</p>
            <strong>{{ $organization->users_count }}</strong>
            <span>كل الحسابات المرتبطة بهذه المنظمة.</span>
        </article>
        <article class="stat-card">
            <p>الأحداث</p>
            <strong>{{ $organization->events_count }}</strong>
            <span>الأحداث المحلية أو المتزامنة داخل المنظمة.</span>
        </article>
        <article class="stat-card">
            <p>الضيوف</p>
            <strong>{{ $organization->guests_count }}</strong>
            <span>إجمالي الضيوف المرتبطين بأحداث المنظمة.</span>
        </article>
        <article class="stat-card">
            <p>نوع الربط</p>
            <strong>{{ $organization->external_user_id === 0 ? 'محلي' : 'متزامن' }}</strong>
            <span>{{ $organization->external_user_id === 0 ? 'بيانات قديمة داخل التجليس.' : 'مرتبط بمعرف user من eventos_25.' }}</span>
        </article>
    </section>

    <section class="content-grid">
        <article class="panel-card">
            <div class="panel-heading">
                <div>
                    <h2>المستخدمون</h2>
                    <p class="section-hint">الحسابات التي يمكنها الوصول لبيانات هذه المنظمة.</p>
                </div>
                <span class="status-badge">{{ $users->count() }} مستخدم</span>
            </div>

            @if ($users->isEmpty())
                <div class="empty-state compact">
                    <h3>لا يوجد مستخدمون</h3>
                    <p>ستظهر الحسابات المرتبطة بالمنظمة هنا.</p>
                </div>
            @else
                <div class="event-list">
                    @foreach ($users as $user)
                        <article class="event-list-item">
                            <strong>{{ $user->name }}</strong>
                            <span>{{ $user->email }} · {{ $user->role }}</span>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>

        <article class="panel-card">
            <div class="panel-heading">
                <div>
                    <h2>آخر الأحداث</h2>
                    <p class="section-hint">أحدث الأحداث داخل هذه المنظمة.</p>
                </div>
                <span class="status-badge">{{ $events->count() }} حدث</span>
            </div>

            @if ($events->isEmpty())
                <div class="empty-state compact">
                    <h3>لا توجد أحداث</h3>
                    <p>لم يتم إنشاء أو مزامنة أحداث لهذه المنظمة بعد.</p>
                </div>
            @else
                <div class="event-list">
                    @foreach ($events as $event)
                        <a href="{{ route('events.show', $event) }}" class="event-list-item">
                            <strong>{{ $event->name }}</strong>
                            <span>
                                {{ optional($event->event_date)->format('Y-m-d') ?: 'بدون تاريخ' }}
                                · {{ $event->floorplans_count }} مخطط
                                · {{ $event->guests_count }} ضيف
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
