@csrf

<div class="form-grid">
    <div class="field">
        <label for="name_ar">اسم النوع <span class="required">*</span></label>
        <input
            id="name_ar"
            name="name_ar"
            type="text"
            class="form-control @error('name_ar') is-invalid @enderror"
            value="{{ old('name_ar', $guestType->name_ar) }}"
            required
        >
        @error('name_ar')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="color">اللون <span class="required">*</span></label>
        <input
            id="color"
            name="color"
            type="color"
            class="form-control color-control @error('color') is-invalid @enderror"
            value="{{ old('color', $guestType->color ?: '#4D9B97') }}"
            required
        >
        @error('color')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="icon">الأيقونة</label>
        <input
            id="icon"
            name="icon"
            type="text"
            class="form-control @error('icon') is-invalid @enderror"
            value="{{ old('icon', $guestType->icon) }}"
            placeholder="user, star, camera"
        >
        @error('icon')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="sort_order">ترتيب العرض</label>
        <input
            id="sort_order"
            name="sort_order"
            type="number"
            min="0"
            max="999"
            class="form-control @error('sort_order') is-invalid @enderror"
            value="{{ old('sort_order', $guestType->sort_order) }}"
        >
        @error('sort_order')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>
