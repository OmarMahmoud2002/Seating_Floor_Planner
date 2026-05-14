@extends('layouts.dashboard')

@section('title', 'تعديل حدث')
@section('page_title', 'تعديل حدث')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">تعديل البيانات</p>
            <h2>{{ $event->name }}</h2>
            <p>حدّث بيانات الحدث.</p>
        </div>
        <a href="{{ route('events.show', $event) }}" class="btn btn-secondary">عرض الحدث</a>
    </section>

    <section class="panel-card">
        <form method="POST" action="{{ route('events.update', $event) }}" class="form-stack">
            @include('events._form')
        </form>
    </section>
@endsection
