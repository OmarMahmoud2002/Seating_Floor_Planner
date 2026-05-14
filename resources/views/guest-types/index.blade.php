@extends('layouts.dashboard')

@section('title', 'أنواع الضيوف')
@section('page_title', 'أنواع الضيوف')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">تصنيف الضيوف</p>
            <h2>أنواع الضيوف</h2>
            <p>هذه الأنواع عامة على النظام وتظهر في كل الأحداث وفي محرر المخطط.</p>
        </div>
    </section>

    <section class="content-grid">
        <article class="panel-card">
            <div class="panel-heading">
                <h2>إضافة نوع جديد</h2>
            </div>
            <form method="POST" action="{{ route('guest-types.store') }}" class="form-stack">
                @include('guest-types._form')
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">إضافة نوع</button>
                </div>
            </form>
        </article>

        <article class="panel-card">
            <div class="panel-heading">
                <h2>الأنواع الحالية</h2>
                <span class="status-badge">{{ $guestTypes->count() }} نوع</span>
            </div>

            @if ($guestTypes->isEmpty())
                <div class="empty-state compact">
                    <h3>لا توجد أنواع ضيوف بعد</h3>
                    <p>أضف نوعا واحدا على الأقل لتصنيف الضيوف.</p>
                </div>
            @else
                <div class="guest-type-list">
                    @foreach ($guestTypes as $type)
                        <article class="guest-type-row">
                            <div>
                                <span class="guest-type-badge" style="--badge-color: {{ $type->color }}">
                                    {{ $type->display_name_ar }}
                                </span>
                                <p>{{ $type->guests_count }} ضيف مرتبط</p>
                            </div>
                            <div class="table-actions">
                                <a href="{{ route('guest-types.edit', $type) }}" class="btn btn-secondary btn-sm">تعديل</a>
                                <form method="POST" action="{{ route('guest-types.destroy', $type) }}" data-confirm-message="هل تريد حذف هذا النوع؟ لا يمكن حذف النوع إذا كان مرتبطًا بضيوف.">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </article>
    </section>
@endsection
