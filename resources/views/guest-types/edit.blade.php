@extends('layouts.dashboard')

@section('title', 'تعديل نوع ضيف')
@section('page_title', 'تعديل نوع ضيف')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">تصنيف الضيوف</p>
            <h2>{{ $guestType->display_name_ar }}</h2>
            <p>عدّل اسم النوع أو لونه ليظهر بشكل أوضح في القوائم والمحرر.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('guest-types.index') }}" class="btn btn-secondary">رجوع للأنواع</a>
        </div>
    </section>

    <section class="panel-card">
        <form method="POST" action="{{ route('guest-types.update', $guestType) }}" class="form-stack">
            @method('PUT')
            @include('guest-types._form')
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                <a href="{{ route('guest-types.index') }}" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </section>
@endsection
