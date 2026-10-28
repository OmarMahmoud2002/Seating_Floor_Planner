<header class="topbar">
    <button type="button" class="sidebar-menu-button" data-sidebar-toggle aria-controls="app-sidebar" aria-expanded="false" aria-label="فتح القائمة">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="topbar-heading">
        <h1>@yield('page_title', 'لوحة التحكم')</h1>
    </div>

    <div class="topbar-actions">
        <span class="admin-chip">
            <span class="admin-avatar" aria-hidden="true">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
            <span class="admin-name">{{ auth()->user()->name }}</span>
        </span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-ghost" title="تسجيل الخروج">
                <svg class="btn-svg" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" /><path d="M10 17l-5-5 5-5" /><path d="M5 12h11" /></svg>
                <span>تسجيل الخروج</span>
            </button>
        </form>
    </div>
</header>
