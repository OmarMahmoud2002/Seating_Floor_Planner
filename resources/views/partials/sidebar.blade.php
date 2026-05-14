<aside class="app-sidebar" id="app-sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard') }}" class="sidebar-brand" aria-label="لوحة التحكم">
            <img src="{{ asset('images/logo.png') }}" alt="بيرفكشن" class="sidebar-logo sidebar-logo-full">
            <img src="{{ asset('images/icon.png') }}" alt="بيرفكشن" class="sidebar-logo sidebar-logo-mark">
        </a>
    </div>

    <nav class="sidebar-nav" aria-label="التنقل الرئيسي">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="لوحة التحكم">
            <span class="nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M4 5.5A1.5 1.5 0 0 1 5.5 4h4A1.5 1.5 0 0 1 11 5.5v4A1.5 1.5 0 0 1 9.5 11h-4A1.5 1.5 0 0 1 4 9.5v-4Z" />
                    <path d="M13 5.5A1.5 1.5 0 0 1 14.5 4h4A1.5 1.5 0 0 1 20 5.5v4a1.5 1.5 0 0 1-1.5 1.5h-4A1.5 1.5 0 0 1 13 9.5v-4Z" />
                    <path d="M4 14.5A1.5 1.5 0 0 1 5.5 13h4a1.5 1.5 0 0 1 1.5 1.5v4A1.5 1.5 0 0 1 9.5 20h-4A1.5 1.5 0 0 1 4 18.5v-4Z" />
                    <path d="M13 14.5a1.5 1.5 0 0 1 1.5-1.5h4a1.5 1.5 0 0 1 1.5 1.5v4a1.5 1.5 0 0 1-1.5 1.5h-4a1.5 1.5 0 0 1-1.5-1.5v-4Z" />
                </svg>
            </span>
            <span class="nav-label">لوحة التحكم</span>
        </a>
        @if (auth()->user()->isSuperAdmin())
            <a href="{{ route('organizations.index') }}" class="nav-link {{ request()->routeIs('organizations.*') ? 'active' : '' }}" title="المنظمات">
                <span class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 20h16" />
                        <path d="M6 20V6.5A1.5 1.5 0 0 1 7.5 5h6A1.5 1.5 0 0 1 15 6.5V20" />
                        <path d="M15 10h1.5A1.5 1.5 0 0 1 18 11.5V20" />
                        <path d="M9 9h3" />
                        <path d="M9 13h3" />
                        <path d="M9 17h3" />
                    </svg>
                </span>
                <span class="nav-label">المنظمات</span>
            </a>
        @endif
        <a href="{{ route('events.index') }}" class="nav-link {{ request()->routeIs('events.*') || request()->routeIs('guests.*') ? 'active' : '' }}" title="الأحداث">
            <span class="nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M7 3v3" />
                    <path d="M17 3v3" />
                    <path d="M4.5 9h15" />
                    <path d="M6 5h12a2 2 0 0 1 2 2v11.5a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z" />
                    <path d="M8 13h2" />
                    <path d="M14 13h2" />
                    <path d="M8 17h2" />
                </svg>
            </span>
            <span class="nav-label">الأحداث</span>
        </a>
        <a href="{{ route('guest-types.index') }}" class="nav-link {{ request()->routeIs('guest-types.*') ? 'active' : '' }}" title="أنواع الضيوف">
            <span class="nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20" />
                    <path d="M10 11a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
                    <path d="M20 20v-1.2a3 3 0 0 0-2.3-2.9" />
                    <path d="M16.5 4.3a3.5 3.5 0 0 1 0 6.4" />
                </svg>
            </span>
            <span class="nav-label">أنواع الضيوف</span>
        </a>
        <a href="{{ route('floorplans.index') }}" class="nav-link {{ request()->routeIs('floorplans.*') || request()->routeIs('editor.floorplans.*') ? 'active' : '' }}" title="المخططات">
            <span class="nav-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M4 6.5 9 4l6 2.5L20 4v13.5L15 20l-6-2.5L4 20V6.5Z" />
                    <path d="M9 4v13.5" />
                    <path d="M15 6.5V20" />
                </svg>
            </span>
            <span class="nav-label">المخططات</span>
        </a>
    </nav>
</aside>
