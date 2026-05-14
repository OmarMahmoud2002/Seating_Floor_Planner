@extends('layouts.dashboard')

@section('title', 'إنشاء حدث')
@section('page_title', 'إنشاء حدث')

@section('content')
    @include('partials.setup-steps', ['currentStep' => 1])

    <section class="page-header-card">
        <div>
            <p class="eyebrow">حدث جديد</p>
            <h2>إضافة حدث</h2>
            <p>أدخل بيانات الحدث الأساسية، ثم انتقل مباشرة لإعداد أول مخطط.</p>
        </div>
        <a href="{{ route('events.index') }}" class="btn btn-secondary">العودة للأحداث</a>
    </section>

    <section class="panel-card">
        <form method="POST" action="{{ route('events.store') }}" class="form-stack">
            @include('events._form', [
                'submitLabel' => 'التالي: إعداد المخطط',
                'cancelUrl' => route('events.index'),
            ])
        </form>
    </section>
@endsection
