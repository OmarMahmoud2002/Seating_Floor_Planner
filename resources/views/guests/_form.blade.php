@csrf

<div class="form-grid">
    <div class="field">
        <label for="name">اسم الضيف <span class="required">*</span></label>
        <input
            id="name"
            name="name"
            type="text"
            class="form-control @error('name') is-invalid @enderror"
            value="{{ old('name', $guest->name) }}"
            required
        >
        @error('name')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="guest_type_id">نوع الضيف</label>
        <select id="guest_type_id" name="guest_type_id" class="form-control @error('guest_type_id') is-invalid @enderror">
            <option value="">بدون نوع</option>
            @foreach ($guestTypes as $guestType)
                <option
                    value="{{ $guestType->id }}"
                    @selected((string) old('guest_type_id', $guest->guest_type_id) === (string) $guestType->id)
                >
                    {{ $guestType->display_name_ar }}
                </option>
            @endforeach
        </select>
        @error('guest_type_id')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="phone">رقم الهاتف</label>
        <input
            id="phone"
            name="phone"
            type="text"
            class="form-control @error('phone') is-invalid @enderror"
            value="{{ old('phone', $guest->phone) }}"
        >
        @error('phone')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="email">البريد الإلكتروني</label>
        <input
            id="email"
            name="email"
            type="email"
            class="form-control @error('email') is-invalid @enderror"
            value="{{ old('email', $guest->email) }}"
        >
        @error('email')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field full-row">
        <label for="notes">ملاحظات</label>
        <textarea id="notes" name="notes" class="form-control textarea @error('notes') is-invalid @enderror">{{ old('notes', $guest->notes) }}</textarea>
        @error('notes')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>
