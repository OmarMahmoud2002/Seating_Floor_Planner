@extends('layouts.app')

@section('body')
    <div class="app-shell">
        @include('partials.sidebar')
        <div class="sidebar-backdrop" data-sidebar-close></div>

        <div class="app-main">
            @include('partials.topbar')

            <main class="page-content">
                @include('partials.flash')
                @yield('content')
            </main>
        </div>

        <dialog id="confirm-action-modal" class="modal-dialog confirm-dialog" aria-labelledby="confirm-action-title">
            <div class="modal-panel confirm-panel">
                <div class="confirm-icon" aria-hidden="true">!</div>
                <div class="confirm-content">
                    <p class="eyebrow">تأكيد الإجراء</p>
                    <h2 id="confirm-action-title">تأكيد الحذف</h2>
                    <p id="confirm-action-message">هل تريد تنفيذ هذا الإجراء؟</p>
                </div>
                <div class="modal-actions confirm-actions">
                    <button type="button" class="btn btn-danger" data-confirm-accept>حذف</button>
                    <button type="button" class="btn btn-secondary" data-modal-close>إلغاء</button>
                </div>
            </div>
        </dialog>
    </div>
@endsection
