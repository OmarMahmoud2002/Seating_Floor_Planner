@csrf

@if ($floorplan->exists)
    @method('PUT')
@endif

<div class="form-grid">
    <div class="field">
        <label for="name">اسم المخطط <span class="required">*</span></label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $floorplan->name) }}"
            placeholder="مثال: مخطط القاعة الرئيسية"
            class="form-control @error('name') is-invalid @enderror"
            required
        >
        @error('name')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="unit_display">وحدة القياس</label>
        <input type="hidden" name="unit" value="meter">
        <input
            id="unit_display"
            type="text"
            value="متر"
            class="form-control"
            readonly
            aria-describedby="unit_help"
        >
        <p id="unit_help" class="helper-text">كل أبعاد القاعة يتم إدخالها بالمتر فقط.</p>
        @error('unit')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="width">عرض القاعة <span class="required">*</span></label>
        <input
            id="width"
            name="width"
            type="number"
            min="1"
            step="0.01"
            value="{{ old('width', $floorplan->width) }}"
            placeholder="مثال: 30"
            class="form-control @error('width') is-invalid @enderror"
            aria-describedby="width_help"
            required
        >
        <p id="width_help" class="helper-text">اكتب عرض القاعة بالمتر.</p>
        @error('width')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="height">ارتفاع القاعة <span class="required">*</span></label>
        <input
            id="height"
            name="height"
            type="number"
            min="1"
            step="0.01"
            value="{{ old('height', $floorplan->height) }}"
            placeholder="مثال: 20"
            class="form-control @error('height') is-invalid @enderror"
            aria-describedby="height_help"
            required
        >
        <p id="height_help" class="helper-text">اكتب طول القاعة بالمتر.</p>
        @error('height')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="paper_size">حجم الورق</label>
        <select id="paper_size" name="paper_size" class="form-control @error('paper_size') is-invalid @enderror">
            @php($paperSize = old('paper_size', in_array($floorplan->paper_size, ['A2', 'A3', 'A4'], true) ? $floorplan->paper_size : 'A3'))
            <option value="A2" @selected($paperSize === 'A2')>A2</option>
            <option value="A3" @selected($paperSize === 'A3')>A3</option>
            <option value="A4" @selected($paperSize === 'A4')>A4</option>
        </select>
        @error('paper_size')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="orientation">اتجاه الورق</label>
        <select id="orientation" name="orientation" class="form-control @error('orientation') is-invalid @enderror">
            <option value="landscape" @selected(old('orientation', $floorplan->orientation) === 'landscape')>عرضي</option>
            <option value="portrait" @selected(old('orientation', $floorplan->orientation) === 'portrait')>عمودي</option>
        </select>
        @error('orientation')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="grid_size">حجم الشبكة</label>
        <input
            id="grid_size"
            name="grid_size"
            type="number"
            min="5"
            max="200"
            value="{{ old('grid_size', $floorplan->grid_size ?: 20) }}"
            placeholder="مثال: 20"
            class="form-control @error('grid_size') is-invalid @enderror"
            aria-describedby="grid_size_help"
        >
        <p id="grid_size_help" class="helper-text">حجم مربعات الشبكة داخل المحرر بالبكسل.</p>
        @error('grid_size')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <div class="field">
        <label for="background_image">صورة خلفية اختيارية</label>
        <input
            id="background_image"
            name="background_image"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="form-control file-control @error('background_image') is-invalid @enderror"
        >
        <p class="helper-text">JPG أو PNG أو WebP بحد أقصى 8MB. سيتم ضغط الصورة بدون تغيير أبعادها.</p>
        @error('background_image')
            <p class="field-error">{{ $message }}</p>
        @enderror
    </div>
</div>

@if ($floorplan->background_image_path)
    <div class="background-preview">
        <img src="{{ $floorplan->backgroundImageUrl() }}" alt="صورة خلفية المخطط">
        <label class="checkbox-line">
            <input type="checkbox" name="remove_background_image" value="1">
            <span>إزالة صورة الخلفية الحالية</span>
        </label>
    </div>
@endif

<div class="form-actions">
    <button type="submit" class="btn btn-primary">{{ $submitLabel ?? ($floorplan->exists ? 'حفظ التعديلات' : 'إنشاء المخطط') }}</button>
    <a href="{{ $cancelUrl ?? route('events.show', $event) }}" class="btn btn-secondary">إلغاء</a>
</div>
