@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@section('body')
    <main class="auth-page">
        <section class="auth-panel" aria-labelledby="login-title">
            <div class="brand-lockup">
                <img src="{{ asset('images/logo.png') }}" alt="Perfection" class="brand-logo">
            </div>

            <div class="auth-heading">
                <p class="eyebrow">نظام داخلي</p>
                <h1 id="login-title">تسجيل الدخول</h1>
                <p>ادخل إلى لوحة إدارة الفعاليات ومخططات التجليس.</p>
            </div>

            @include('partials.flash')

            <form method="POST" action="{{ route('login.store') }}" class="form-stack" novalidate>
                @csrf

                <div class="field">
                    <label for="email">البريد الإلكتروني</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        class="form-control @error('email') is-invalid @enderror"
                        autocomplete="email"
                        autofocus
                        required
                    >
                    @error('email')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="password">كلمة المرور</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-control @error('password') is-invalid @enderror"
                        autocomplete="current-password"
                        required
                    >
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <label class="checkbox-line">
                    <input type="checkbox" name="remember" value="1">
                    <span>تذكرني</span>
                </label>

                <button type="submit" class="btn btn-primary full-width">دخول لوحة التحكم</button>
            </form>
        </section>
    </main>
@endsection
