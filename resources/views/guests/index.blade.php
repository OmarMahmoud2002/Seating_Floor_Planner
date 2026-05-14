@extends('layouts.dashboard')

@section('title', 'ضيوف '.$event->name)
@section('page_title', 'إدارة الضيوف')

@section('content')
    <section class="page-header-card guests-header-card">
        <div>
            <p class="eyebrow">ضيوف الحدث</p>
            <h2>{{ $event->name }}</h2>
            <p>تابع قائمة الضيوف، ابحث بسرعة، ثم أضف أو استورد الضيوف قبل تجليسهم داخل محرر المخطط.</p>
        </div>
        <div class="header-actions guests-actions">
            <a href="{{ route('events.show', $event) }}" class="btn btn-secondary action-back">
                رجوع للحدث
            </a>
            <a href="{{ route('events.guests.export', $event) }}" class="btn btn-secondary action-export">
                تصدير Excel
            </a>
            <a href="{{ route('events.guests.import.create', $event) }}" class="btn btn-secondary action-import">
                استيراد Excel
            </a>
            <a href="{{ route('guest-types.index') }}" class="btn btn-secondary action-types">
                أنواع الضيوف
            </a>
            <button type="button" class="btn btn-primary" data-modal-open="guest-create-modal">
                إضافة ضيف
            </button>
        </div>
    </section>

    <section class="panel-card guests-list-panel">
        <div class="panel-heading guests-list-heading">
            <div>
                <h2>قائمة الضيوف</h2>
                <p class="section-hint">{{ $guests->total() }} ضيف مسجل في هذا الحدث.</p>
            </div>
            <span class="status-badge">{{ $guests->total() }} ضيف</span>
        </div>

        <form method="GET" action="{{ route('events.guests.index', $event) }}" class="toolbar-form guest-filter-bar">
            <div class="field search-field">
                <label for="search">بحث</label>
                <input id="search" name="search" type="search" class="form-control" value="{{ $search }}" placeholder="اسم، هاتف، أو بريد">
            </div>
            <div class="field compact-filter-field">
                <label for="type">النوع</label>
                <select id="type" name="type" class="form-control">
                    <option value="">كل الأنواع</option>
                    @foreach ($guestTypes as $guestType)
                        <option value="{{ $guestType->id }}" @selected((string) $typeId === (string) $guestType->id)>
                            {{ $guestType->display_name_ar }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-secondary">تطبيق</button>
            @if ($search !== '' || $typeId)
                <a href="{{ route('events.guests.index', $event) }}" class="btn btn-secondary">إعادة ضبط</a>
            @endif
        </form>

        @if ($guests->isEmpty())
            <div class="empty-state compact">
                <h3>لم يتم إضافة ضيوف لهذا الحدث</h3>
                <p>استخدم زر إضافة ضيف أو استيراد Excel لبدء القائمة.</p>
                <button type="button" class="btn btn-primary" data-modal-open="guest-create-modal">إضافة ضيف</button>
            </div>
        @else
            <div class="table-wrap">
                <table class="data-table modern-table">
                    <thead>
                        <tr>
                            <th>الضيف</th>
                            <th>النوع</th>
                            <th>التواصل</th>
                            <th>التجليس</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guests as $guestItem)
                            @php($seat = $guestItem->seats->first())
                            <tr>
                                <td>
                                    <strong>{{ $guestItem->name }}</strong>
                                    @if ($guestItem->notes)
                                        <small class="muted-line">{{ $guestItem->notes }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($guestItem->guestType)
                                        <span class="guest-type-badge" style="--badge-color: {{ $guestItem->guestType->color }}">
                                            {{ $guestItem->guestType->display_name_ar }}
                                        </span>
                                    @else
                                        <span class="status-badge muted">بدون نوع</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="stacked-meta">
                                        <span>{{ $guestItem->phone ?: 'لا يوجد هاتف' }}</span>
                                        <span>{{ $guestItem->email ?: 'لا يوجد بريد' }}</span>
                                    </span>
                                </td>
                                <td>
                                    @if ($seat)
                                        {{ $seat->table_name ?: $seat->table_key }} - مقعد {{ $seat->seat_number }}
                                    @else
                                        <span class="status-badge muted">غير مسكن</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions action-buttons">
                                        <a href="{{ route('guests.edit', $guestItem) }}" class="btn btn-secondary btn-sm">تعديل</a>
                                        <form method="POST" action="{{ route('guests.destroy', $guestItem) }}" data-confirm-message="هل تريد حذف هذا الضيف؟ سيتم إزالة بياناته من قائمة الضيوف.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                {{ $guests->links() }}
            </div>
        @endif
    </section>

    <dialog id="guest-create-modal" class="modal-dialog" @if ($errors->any()) data-open-on-load="true" @endif>
        <div class="modal-panel">
            <div class="modal-heading">
                <div>
                    <h2>إضافة ضيف</h2>
                    <p>أضف بيانات الضيف، ويمكنك تعديلها لاحقا من القائمة.</p>
                </div>
                <button type="button" class="modal-close-button" data-modal-close aria-label="إغلاق">×</button>
            </div>

            <form method="POST" action="{{ route('events.guests.store', $event) }}" class="form-stack">
                @include('guests._form')
                <div class="form-actions modal-actions">
                    <button type="button" class="btn btn-secondary" data-modal-close>إلغاء</button>
                    <button type="submit" class="btn btn-primary">إضافة ضيف</button>
                </div>
            </form>
        </div>
    </dialog>
@endsection
