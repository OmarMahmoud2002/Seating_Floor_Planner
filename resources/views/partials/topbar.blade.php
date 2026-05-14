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
        <span class="admin-chip">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-secondary">تسجيل الخروج</button>
        </form>
    </div>
</header>
