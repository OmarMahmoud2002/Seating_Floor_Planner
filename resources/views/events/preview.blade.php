@extends('layouts.app')

@section('title', 'معاينة الحدث')

@section('body')
    <main class="preview-page">
        <section class="preview-card">
            <div class="preview-brand">
                <img src="{{ asset('images/logo.png') }}" alt="Perfection">
            </div>

            <div class="preview-heading">
                <p class="eyebrow">معاينة الحدث</p>
                <h1>{{ $event->name }}</h1>
                <p>{{ $event->description ?: 'لا يوجد وصف منشور لهذا الحدث.' }}</p>
            </div>

            <dl class="meta-list preview-meta">
                <div>
                    <dt>تاريخ الحدث</dt>
                    <dd>{{ optional($event->event_date)->format('Y-m-d') ?: 'غير محدد' }}</dd>
                </div>
                <div>
                    <dt>الموقع</dt>
                    <dd>{{ $event->location ?: 'غير محدد' }}</dd>
                </div>
            </dl>

            <div class="preview-note">
                هذه صفحة معاينة فقط. لا تحتوي على أزرار تعديل أو بيانات خاصة بالضيوف.
            </div>
        </section>
    </main>
@endsection
