@csrf

@if ($event->exists)
    @method('PUT')
@endif

<div class="form-grid">
    <div class="field">
        <label for="name">اسم الحدث <span class="required">*</span></label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $event->name) }}"
            class="form-control @error('name') is-invalid @enderror"
            required
        >
        @error('name')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="event_date">تاريخ الحدث</label>
        <input
            id="event_date"
            name="event_date"
            type="date"
            value="{{ old('event_date', optional($event->event_date)->format('Y-m-d')) }}"
            class="form-control @error('event_date') is-invalid @enderror"
        >
        @error('event_date')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="location">الموقع</label>
        <input
            id="location"
            name="location"
            type="text"
            value="{{ old('location', $event->location) }}"
            class="form-control @error('location') is-invalid @enderror"
            placeholder="اسم القاعة أو العنوان"
        >
        @error('location')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="field">
    <label for="description">الوصف</label>
    <textarea
        id="description"
        name="description"
        rows="5"
        class="form-control textarea @error('description') is-invalid @enderror"
        placeholder="ملاحظات داخلية مختصرة عن الحدث"
    >{{ old('description', $event->description) }}</textarea>
    @error('description')
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>

<section class="registration-options-panel">
    <div class="registration-options-heading">
        <span class="registration-options-icon" aria-hidden="true">★</span>
        <div>
            <h3>خيارات التسجيل</h3>
            <p>إعدادات إضافية للروابط وأنواع الحضور</p>
        </div>
    </div>

    <div class="registration-options-grid">
        @foreach ($guestTypeLinkTypes as $guestTypeLinkType)
            @php($field = "{$guestTypeLinkType['key']}_registration_enabled")
            <label class="registration-option">
                <input
                    type="checkbox"
                    name="{{ $field }}"
                    value="1"
                    @checked(old($field, $event->{$field}))
                >
                <span>تفعيل رابط {{ $guestTypeLinkType['label'] }}</span>
            </label>
        @endforeach
    </div>
</section>

<div class="form-actions">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? ($event->exists ? 'حفظ التعديلات' : 'إنشاء الحدث') }}</button>
    <a href="{{ $cancelUrl ?? ($event->exists ? route('events.show', $event) : route('events.index')) }}" class="btn btn-secondary">إلغاء</a>
</div>
