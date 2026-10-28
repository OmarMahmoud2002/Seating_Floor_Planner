@extends('layouts.app')

@section('title', 'تسجيل الدخول')

@section('body')
    <main class="auth-page">
        <aside class="auth-brand-panel" aria-hidden="true">
            <div class="auth-brand-inner">
                <img src="{{ asset('images/tashrif-logo-white.png') }}" alt="" class="auth-brand-logo">
                <div class="auth-brand-copy">
                    <span class="auth-brand-divider"></span>
                    <p class="auth-brand-quote">كل ضيف في مقعده الصحيح، من لحظة الدعوة حتى الجلوس.</p>
                </div>
                <ul class="auth-brand-points">
                    <li>مخططات قاعات دقيقة بالمقاييس الحقيقية</li>
                    <li>تجليس الضيوف بالسحب والإفلات</li>
                    <li>تصدير المخطط وقوائم الضيوف للطباعة</li>
                </ul>
            </div>
        </aside>

        <section class="auth-panel" aria-labelledby="login-title">
            <div class="brand-lockup">
                <img src="{{ asset('images/tashrif-logo.png') }}" alt="تشريف" class="brand-logo">
            </div>

            <div class="auth-heading">
                <p class="eyebrow">نظام التجليس الإلكتروني</p>
                <h1 id="login-title">أهلاً بعودتك</h1>
                <p>سجّل الدخول لإدارة الأحداث ومخططات التجليس.</p>
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

                <button type="submit" class="btn btn-primary btn-lg full-width">دخول لوحة التحكم</button>
            </form>

            <p class="auth-footnote">نظام داخلي · <span class="wordmark-latin">Tashrif</span></p>
        </section>
    </main>
@endsection
