@extends('layouts.dashboard')

@section('title', 'إنشاء مخطط قاعة')
@section('page_title', 'إنشاء مخطط قاعة')

@section('content')
    @include('partials.setup-steps', ['currentStep' => 2])

    <section class="page-header-card">
        <div>
            <p class="eyebrow">مخطط جديد</p>
            <h2>{{ $event->name }}</h2>
            <p>حدد مقاس القاعة وإعدادات الورق. بعد حفظ المخطط سيتم فتح المحرر مباشرة.</p>
        </div>
        <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">العودة للحدث</a>
    </section>

    <section class="panel-card">
        <form method="POST" action="{{ route('events.floorplans.store', $event) }}" enctype="multipart/form-data" class="form-stack">
            @include('floorplans._form', [
                'submitLabel' => 'التالي: فتح المحرر',
                'cancelUrl' => route('events.show', $event),
            ])
        </form>
    </section>
@endsection
