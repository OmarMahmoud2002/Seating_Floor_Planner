@extends('layouts.dashboard')

@section('title', 'تعديل مخطط قاعة')
@section('page_title', 'تعديل مخطط قاعة')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">تعديل المخطط</p>
            <h2>{{ $floorplan->name }}</h2>
            <p>حدّث مقاس القاعة أو إعدادات التصدير أو صورة الخلفية.</p>
        </div>
        <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">العودة للحدث</a>
    </section>

    <section class="panel-card">
        <form method="POST" action="{{ route('floorplans.update', $floorplan) }}" enctype="multipart/form-data" class="form-stack">
            @include('floorplans._form')
        </form>
    </section>
@endsection
