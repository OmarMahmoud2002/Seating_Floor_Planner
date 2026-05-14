@extends('layouts.dashboard')

@section('title', 'تعديل ضيف')
@section('page_title', 'تعديل ضيف')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">تعديل بيانات الضيف</p>
            <h2>{{ $guest->name }}</h2>
            <p>حدث: {{ $event->name }}</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('events.guests.index', $event) }}" class="btn btn-secondary">رجوع للضيوف</a>
        </div>
    </section>

    <section class="panel-card">
        <form method="POST" action="{{ route('guests.update', $guest) }}" class="form-stack">
            @method('PUT')
            @include('guests._form')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                <a href="{{ route('events.guests.index', $event) }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </section>
@endsection
