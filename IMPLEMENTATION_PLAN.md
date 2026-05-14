# IMPLEMENTATION_PLAN.md

# خطة تنفيذ مشروع Arabic Event Floor Planner

## 1. Project Understanding Summary

المشروع هو تطبيق داخلي بسيط لشركة واحدة لإدارة فعاليات، إنشاء مخططات قاعات، ترتيب الطاولات والكراسي، تجليس الضيوف على المقاعد، واستيراد/تصدير البيانات. الهدف ليس بناء منصة SaaS كبيرة، بل نظام عملي، واضح، وسهل التشغيل على استضافة مشتركة.

الحالة الحالية للمستودع:

- يحتوي المستودع الحالي على ملفات التخطيط والتعليمات والهوية فقط.
- لا يوجد بعد هيكل Laravel فعلي مثل `composer.json` أو `routes/` أو `app/`.
- لذلك تبدأ خطة التنفيذ من إنشاء مشروع Laravel 10 داخل هذا المستودع، ثم إضافة الميزات على مراحل.

القيود الأساسية:

- Laravel 10 فقط.
- PHP 8.2 كحد أقصى.
- MySQL.
- Blade لكل صفحات النظام العادية.
- Vue 3 فقط في صفحة محرر المخطط.
- Konva.js للمحرر، السحب، التحريك، التكبير، التصغير، المقاعد، والتجليس.
- Laravel Excel لاستيراد وتصدير قوائم الضيوف.
- DomPDF لتصدير PDF، وليس Browsershot أو Puppeteer.
- واجهة عربية بالكامل مع دعم RTL.
- نشر مناسب للاستضافة المشتركة مع بناء Vite محليا ورفع `public/build`.

## 2. Main Features and Modules

### 2.1 Authentication

- تسجيل الدخول.
- تسجيل الخروج.
- لا يوجد تسجيل عام للمستخدمين.
- النظام يعمل بحساب Admin واحد فقط في النسخة الأولى.
- إنشاء حساب Admin عبر Seeder أو أمر Artisan أثناء التهيئة.
- إخفاء/تعطيل صفحة التسجيل إذا تم استخدام Laravel Breeze.
- إعادة تعيين كلمة المرور اختياري فقط إذا كان جاهزا ضمن scaffolding بدون تعقيد.
- حماية كل صفحات النظام بـ `auth`.

### 2.2 Dashboard

- عرض إجمالي الفعاليات.
- عرض الفعاليات القادمة.
- عرض إجمالي الضيوف.
- عرض أحدث المخططات.
- زر واضح: `إنشاء حدث جديد`.

### 2.3 Events

- إضافة حدث.
- تعديل حدث.
- حذف حدث مع تأكيد.
- البحث في الأحداث.
- صفحة تفاصيل الحدث.
- رابط معاينة read-only لكل حدث.
- الحقول:
  - `name`
  - `type`
  - `event_date`
  - `location`
  - `description`
  - `user_id`
  - `preview_token`
  - `preview_enabled`

### 2.4 Floorplans

- إنشاء مخطط لكل حدث.
- تعديل بيانات المخطط.
- تحديد:
  - اسم المخطط.
  - عرض القاعة.
  - ارتفاع القاعة.
  - الوحدة: متر أو قدم.
  - حجم الورق: A4 أو Letter.
  - الاتجاه: portrait أو landscape.
  - حجم الشبكة اختياري.
  - صورة خلفية اختيارية.
- فتح المحرر.
- حفظ التصميم كـ JSON.
- حفظ المقاعد في جدول مستقل للتقارير والتجليس.
- تصدير المخطط إلى PDF.

### 2.5 Floor Planner Editor

- محرر سطح مكتب مصقول.
- Top toolbar.
- Left element library.
- Center Konva canvas.
- Right guest list.
- شبكة خفيفة.
- صورة خلفية اختيارية مع تحكم بالشفافية لاحقا.
- إضافة عناصر:
  - طاولات.
  - كراسي.
  - مسرح.
  - حوائط.
  - أبواب.
  - ممرات.
  - مناطق VIP.
  - إضاءة وصوت.
  - عناصر أخرى.
- تحريك العناصر.
- تغيير الحجم.
- دوران العناصر.
- حذف/نسخ العناصر.
- تكبير وتصغير.
- Undo/Redo عند الإمكان.
- حفظ يدوي مع Autosave اختياري كل 30 ثانية في مرحلة لاحقة.

### 2.6 Tables and Seats

- اختيار شكل الطاولة:
  - دائرية.
  - مستطيلة.
  - مربعة.
  - Banquet طويلة.
  - صفوف مسرح.
- تحديد عدد المقاعد.
- توليد المقاعد تلقائيا حول الطاولة.
- ترقيم المقاعد.
- عرض اسم الطاولة ورقم/كود المقعد.
- دعم تغيير اللون أو الفئة.

### 2.7 Guests

- إضافة ضيف يدويا.
- تعديل ضيف.
- حذف ضيف.
- البحث والتصفية.
- عرض نوع الضيف.
- عرض الطاولة والمقعد عند التجليس.
- الحقول:
  - `event_id`
  - `guest_type_id`
  - `name`
  - `phone`
  - `email`
  - `notes`

### 2.8 Guest Types

الأنواع الافتراضية:

- عادي
- VIP
- عائلة
- أصدقاء
- موظف
- إعلام
- راعي
- ذوي احتياجات خاصة

الحقول:

- `name_ar`
- `color`
- `icon`
- `sort_order`

ملاحظة قرار:

- أنواع الضيوف عامة على مستوى النظام وليست خاصة بكل حدث.

### 2.9 Seating Assignment

- سحب ضيف من اللوحة اليمنى إلى مقعد فارغ.
- منع تجليس ضيفين على نفس المقعد.
- منع تجليس نفس الضيف في أكثر من مقعد داخل نفس المخطط إلا عند إعادة التجليس.
- التحقق أن الضيف والمقعد ينتميان لنفس الحدث.
- عرض اسم الضيف على المقعد أو بجانبه.
- تحديث عداد التجليس.
- إزالة التجليس.

### 2.10 Imports and Exports

- استيراد الضيوف من Excel.
- تصدير قائمة الضيوف Excel.
- تصدير المخطط PDF عبر صورة Canvas مرسلة من Vue إلى Laravel.

## 3. Recommended Architecture

### 3.1 General Architecture

استخدام Laravel MVC بسيط:

- Controllers للتنسيق بين الطلب والاستجابة.
- Form Requests للتحقق.
- Models للعلاقات و casts.
- Policies أو scoped queries لحماية الملكية.
- Services فقط للمنطق المعقد.
- Blade للصفحات العادية.
- Vue 3 + Konva فقط داخل صفحة المحرر.

لا يتم استخدام:

- Repository pattern.
- Multi-tenancy.
- Billing.
- Subscriptions.
- WebSockets.
- Microservices.
- Separate frontend app.
- Puppeteer/Browsershot/server Chrome.

### 3.2 Suggested Folders

```text
app/
  Http/
    Controllers/
      DashboardController.php
      EventController.php
      FloorplanController.php
      FloorplanEditorController.php
      GuestController.php
      GuestTypeController.php
      GuestImportController.php
      ExportController.php
    Requests/
      StoreEventRequest.php
      UpdateEventRequest.php
      StoreFloorplanRequest.php
      UpdateFloorplanRequest.php
      SaveFloorplanLayoutRequest.php
      StoreGuestRequest.php
      UpdateGuestRequest.php
      StoreGuestTypeRequest.php
      ImportGuestsRequest.php
      AssignSeatRequest.php
  Models/
    Event.php
    Floorplan.php
    Guest.php
    GuestType.php
    Seat.php
  Policies/
    EventPolicy.php
    FloorplanPolicy.php
    GuestPolicy.php
  Services/
    FloorPlanner/
      FloorplanLayoutService.php
      SeatGenerationService.php
      SeatingAssignmentService.php
    Guests/
      GuestImportService.php
    Exports/
      FloorplanPdfExportService.php
      GuestListExportService.php
resources/
  views/
  js/
    editor/
routes/
  web.php
  api.php
```

### 3.3 Package Direction

Required packages after Laravel setup:

- `laravel/framework:^10`
- Laravel auth starter compatible with Laravel 10, preferably Breeze if approved.
- `maatwebsite/excel` compatible with PHP 8.2 and Laravel 10.
- `barryvdh/laravel-dompdf` compatible with PHP 8.2 and Laravel 10.
- `vue@3`
- `konva`
- `vue-konva` if it fits the Vite setup, otherwise direct Konva usage in Vue.

Composer config should include:

```json
"config": {
  "platform": {
    "php": "8.2.0"
  }
}
```

## 4. Database Design

### 4.1 Recommended Simple Approach

أفضل خيار بسيط:

- حفظ كامل تصميم المحرر داخل `floorplans.design_json`.
- حفظ المقاعد القابلة للتقرير داخل `seats`.
- ربط الضيف بالمقعد عبر `seats.guest_id`.

السبب:

- JSON يعطي حرية كاملة للمحرر والأشكال.
- جدول `seats` يجعل التقارير والتصدير والتجليس موثوقة.
- لا نحتاج جداول كثيرة مثل `floorplan_elements` في البداية.
- يمكن إضافة `floorplan_versions` لاحقا فقط عند الحاجة.

### 4.2 Tables

#### users

استخدام جدول Laravel الافتراضي بدون نظام أدوار في النسخة الأولى:

- `id`
- `name`
- `email`
- `password`
- timestamps

ملاحظة:

- يتم إنشاء حساب Admin واحد عبر Seeder أو أمر Artisan.
- لا توجد شاشة تسجيل عامة للمستخدمين.

#### events

- `id`
- `user_id` foreign key.
- `name`
- `type` nullable.
- `event_date` date nullable.
- `location` nullable.
- `description` text nullable.
- `preview_token` unique string.
- `preview_enabled` boolean default true.
- timestamps

Indexes:

- `user_id`
- `event_date`
- `name`
- `preview_token`

#### floorplans

- `id`
- `event_id` foreign key.
- `name`
- `width` decimal.
- `height` decimal.
- `unit` enum/string: `meter`, `foot`.
- `paper_size` enum/string: `A4`, `Letter`.
- `orientation` enum/string: `portrait`, `landscape`.
- `grid_size` unsigned integer default 20.
- `background_image_path` nullable.
- `design_json` json nullable.
- `last_saved_at` nullable.
- timestamps

Indexes:

- `event_id`
- `name`

#### guest_types

- `id`
- `name_ar`
- `color`
- `icon` nullable.
- `sort_order` default 0.
- `is_default` boolean default false.
- timestamps

Indexes:

- `sort_order`

#### guests

- `id`
- `event_id` foreign key.
- `guest_type_id` nullable foreign key.
- `name`
- `phone` nullable.
- `email` nullable.
- `notes` text nullable.
- timestamps

Indexes:

- `event_id`
- `guest_type_id`
- composite optional index on `event_id`, `name`
- optional duplicate helper index on `event_id`, `phone`, `email`

#### seats

- `id`
- `floorplan_id` foreign key.
- `guest_id` nullable foreign key.
- `table_key`
- `table_name` nullable.
- `seat_key`
- `seat_number` nullable.
- `x` decimal.
- `y` decimal.
- `rotation` decimal default 0.
- `status` string default `available`.
- `metadata` json nullable.
- timestamps

Indexes:

- `floorplan_id`
- `guest_id`
- unique `floorplan_id + seat_key`

Rules:

- `guest_id` must be unique per floorplan logically. This can be enforced in service validation and optionally with a generated assignment table later if needed.

### 4.3 Optional Later Tables

Do not add initially unless needed:

- `floorplan_versions`
- `floorplan_exports`
- `activity_logs`
- `floorplan_elements`
- `seating_assignments`

If future history/audit is requested, use `seating_assignments` instead of only `seats.guest_id`.

## 5. Models and Relationships

### User

- `hasMany(Event::class)`

### Event

- `belongsTo(User::class)`
- `hasMany(Floorplan::class)`
- `hasMany(Guest::class)`
- generates/owns a tokenized read-only preview link.

### Floorplan

- `belongsTo(Event::class)`
- `hasMany(Seat::class)`
- casts:
  - `design_json` as array.
  - `last_saved_at` as datetime.

### GuestType

- `hasMany(Guest::class)`

### Guest

- `belongsTo(Event::class)`
- `belongsTo(GuestType::class)`
- `hasMany(Seat::class)` conceptually, but service should prevent more than one seat per floorplan.

### Seat

- `belongsTo(Floorplan::class)`
- `belongsTo(Guest::class)` nullable.
- casts:
  - `metadata` as array.

## 6. Routes and Controllers

All routes should be under `auth` middleware.

### 6.1 Web Routes

```php
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('events', EventController::class);
    Route::post('/events/{event}/preview-token', [EventController::class, 'refreshPreviewToken'])->name('events.preview-token.refresh');

    Route::get('/events/{event}/floorplans/create', [FloorplanController::class, 'create'])->name('events.floorplans.create');
    Route::post('/events/{event}/floorplans', [FloorplanController::class, 'store'])->name('events.floorplans.store');
    Route::get('/floorplans/{floorplan}/edit', [FloorplanController::class, 'edit'])->name('floorplans.edit');
    Route::put('/floorplans/{floorplan}', [FloorplanController::class, 'update'])->name('floorplans.update');
    Route::delete('/floorplans/{floorplan}', [FloorplanController::class, 'destroy'])->name('floorplans.destroy');

    Route::get('/floorplans/{floorplan}/editor', [FloorplanEditorController::class, 'show'])->name('floorplans.editor');

    Route::get('/events/{event}/guests', [GuestController::class, 'index'])->name('events.guests.index');
    Route::post('/events/{event}/guests', [GuestController::class, 'store'])->name('events.guests.store');
    Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');

    Route::get('/guest-types', [GuestTypeController::class, 'index'])->name('guest-types.index');
    Route::post('/guest-types', [GuestTypeController::class, 'store'])->name('guest-types.store');
    Route::put('/guest-types/{guestType}', [GuestTypeController::class, 'update'])->name('guest-types.update');
    Route::delete('/guest-types/{guestType}', [GuestTypeController::class, 'destroy'])->name('guest-types.destroy');

    Route::get('/events/{event}/guests/import', [GuestImportController::class, 'create'])->name('events.guests.import.create');
    Route::post('/events/{event}/guests/import/preview', [GuestImportController::class, 'preview'])->name('events.guests.import.preview');
    Route::post('/events/{event}/guests/import', [GuestImportController::class, 'store'])->name('events.guests.import.store');

    Route::get('/events/{event}/guests/export', [ExportController::class, 'guestList'])->name('events.guests.export');
});

Route::get('/preview/events/{event:preview_token}', [EventPreviewController::class, 'show'])->name('events.preview');
```

### 6.2 Editor JSON Endpoints

Can be in `routes/web.php` under auth for simpler CSRF handling, or in `routes/api.php` with `auth:sanctum` only if Sanctum is already used. Simpler recommendation: keep authenticated JSON routes in `web.php`.

```php
Route::middleware(['auth'])->prefix('editor')->group(function () {
    Route::get('/floorplans/{floorplan}/data', [FloorplanEditorController::class, 'data'])->name('editor.floorplans.data');
    Route::post('/floorplans/{floorplan}/save', [FloorplanEditorController::class, 'save'])->name('editor.floorplans.save');
    Route::post('/floorplans/{floorplan}/seats/assign', [FloorplanEditorController::class, 'assignSeat'])->name('editor.seats.assign');
    Route::post('/floorplans/{floorplan}/seats/unassign', [FloorplanEditorController::class, 'unassignSeat'])->name('editor.seats.unassign');
    Route::post('/floorplans/{floorplan}/export-pdf', [ExportController::class, 'floorplanPdf'])->name('editor.floorplans.export-pdf');
});
```

### 6.3 Controller Responsibilities

#### DashboardController

- Fetch summary counts scoped to current user.
- Render dashboard.

#### EventController

- CRUD for events.
- Scope by authenticated user.
- Use Form Requests.
- Generate and refresh event preview tokens.
- Toggle preview availability if needed.

#### EventPreviewController

- Render a read-only event preview by `preview_token`.
- Do not expose edit actions.
- Do not expose private guest contact fields by default.
- Return 404 if `preview_enabled` is false.

#### FloorplanController

- CRUD for floorplan metadata.
- Validate dimensions and uploads.
- Store background images in `storage/app/public`.

#### FloorplanEditorController

- Render Blade page that mounts Vue.
- Provide floorplan JSON data.
- Save `design_json`.
- Sync generated seats.
- Assign/unassign guests.

#### GuestController

- CRUD guests.
- Search/filter for event page and editor panel.

#### GuestTypeController

- CRUD guest types.
- Manage global guest types.
- Seed default global guest types.

#### GuestImportController

- Upload validation.
- Preview parsing.
- Confirm import.
- Return Arabic summary.

#### ExportController

- Floorplan PDF.
- Guest list Excel.

## 7. Blade Pages

### 7.1 Layout Foundation

Files:

```text
resources/views/layouts/app.blade.php
resources/views/partials/sidebar.blade.php
resources/views/partials/topbar.blade.php
resources/views/partials/flash.blade.php
```

Rules:

- `<html lang="ar" dir="rtl">`.
- Use Cairo font.
- Use brand CSS variables.
- Dashboard sidebar on the right.
- Clean white cards, soft borders, subtle shadows.
- Arabic validation messages and buttons.

### 7.2 Auth Pages

```text
resources/views/auth/login.blade.php
resources/views/auth/register.blade.php
resources/views/auth/forgot-password.blade.php
```

Style:

- Logo visible.
- Simple centered card.
- Brand gradient primary button.
- Arabic labels.

### 7.3 Dashboard

```text
resources/views/dashboard.blade.php
```

Sections:

- Summary cards.
- Upcoming events.
- Recent floorplans.
- CTA to create event.

### 7.4 Events

```text
resources/views/events/index.blade.php
resources/views/events/create.blade.php
resources/views/events/edit.blade.php
resources/views/events/show.blade.php
resources/views/events/preview.blade.php
```

Event details page should show:

- Event information.
- Public/read-only preview link.
- Button to copy preview link.
- Button to refresh preview token when needed.
- Floorplans.
- Guests.
- Guest import/export actions.

### 7.5 Floorplans

```text
resources/views/floorplans/create.blade.php
resources/views/floorplans/edit.blade.php
resources/views/floorplans/editor.blade.php
```

`editor.blade.php` only mounts Vue and passes initial IDs/routes/config.

### 7.6 Guests and Guest Types

```text
resources/views/guests/index.blade.php
resources/views/guests/partials/form.blade.php
resources/views/guest-types/index.blade.php
resources/views/imports/guests.blade.php
resources/views/imports/guests-preview.blade.php
```

## 8. Vue 3 + Konva Editor Structure

### 8.1 File Structure

```text
resources/js/editor/
  EditorApp.vue
  components/
    TopToolbar.vue
    LeftLibrary.vue
    CanvasStage.vue
    RightGuestList.vue
    TableSettingsPanel.vue
    ElementSettingsPanel.vue
    GuestCard.vue
    SaveStatus.vue
  composables/
    useEditorState.js
    useSeatLayout.js
    useHistory.js
    useZoomPan.js
    useGuestAssignment.js
  services/
    editorApi.js
  utils/
    ids.js
    geometry.js
    canvasExport.js
```

### 8.2 EditorApp.vue

Responsibilities:

- Load floorplan data.
- Hold global editor state.
- Render toolbar, library, canvas, guest list.
- Track selected element.
- Track dirty/saved state.
- Coordinate save/export.

### 8.3 TopToolbar.vue

Controls:

- اسم المخطط.
- Zoom in/out.
- Zoom percentage.
- Undo/Redo.
- Paper size selector.
- عداد التجليس.
- حفظ.
- مخططاتي.
- تصدير قائمة الضيوف.
- تصدير PDF.

### 8.4 LeftLibrary.vue

Sections:

- طاولات.
- كراسي.
- مسرح.
- حوائط.
- أبواب.
- ممرات.
- مناطق VIP.
- إضاءة وصوت.
- عناصر أخرى.

Behavior:

- Click or drag item to canvas.
- Open table settings for table elements.

### 8.5 CanvasStage.vue

Responsibilities:

- Render Konva Stage and Layers.
- Draw grid.
- Render background image.
- Render elements and seats.
- Handle drag/drop.
- Handle selection.
- Handle resizing/rotation via Konva Transformer.
- Emit updates to state.

### 8.6 RightGuestList.vue

Features:

- Search by guest name/type/table.
- Filter by seated/unseated.
- Guest cards.
- Drag guests to seats.
- Remove assignment.
- Add guest button.

### 8.7 TableSettingsPanel.vue

Fields:

- اسم/رقم الطاولة.
- الشكل.
- عدد المقاعد.
- الحجم.
- اللون.
- نمط الترقيم.

On apply:

- Create table element.
- Generate seat records client-side for immediate rendering.
- Send normalized seats when saving.

### 8.8 Composables

#### useEditorState.js

- Elements.
- Seats.
- Guests.
- Selected element.
- Dirty state.
- Save status.

#### useSeatLayout.js

- Round table seat placement.
- Rectangle table seat placement.
- Square table seat placement.
- Banquet table placement.
- Theater row placement.

#### useHistory.js

- Undo stack.
- Redo stack.
- Snapshot after meaningful changes, not every pixel.

#### useZoomPan.js

- Zoom controls.
- Pan behavior.
- Fit to paper/canvas.

#### useGuestAssignment.js

- Client-side assignment state.
- Calls API.
- Handles conflict messages in Arabic.

### 8.9 Data Contract Between Laravel and Vue

GET editor data returns:

```json
{
  "floorplan": {
    "id": 1,
    "name": "مخطط القاعة الرئيسية",
    "width": 30,
    "height": 20,
    "unit": "meter",
    "paper_size": "A4",
    "orientation": "landscape",
    "grid_size": 20,
    "background_image_url": null,
    "design_json": {}
  },
  "seats": [],
  "guests": [],
  "guest_types": []
}
```

POST save payload:

```json
{
  "design_json": {
    "version": 1,
    "elements": [],
    "viewport": {}
  },
  "seats": []
}
```

## 9. Guest Import/Export Flow

### 9.1 Excel Import

Accepted columns:

- `name`
- `phone`
- `email`
- `type`
- `notes`

قرار النسخة الأولى:

- لا يلزم دعم أسماء أعمدة عربية في Excel.
- الأعمدة الإنجليزية السابقة كافية.

Flow:

1. User opens import page from event details.
2. Upload Excel file.
3. Validate extension and size.
4. Parse first sheet.
5. Show preview:
   - valid rows.
   - rows with missing name.
   - likely duplicates.
   - new guest types to create.
6. User confirms import.
7. System imports valid rows inside a transaction where practical.
8. Show Arabic summary:
   - تم استيراد X ضيف.
   - تم تخطي Y مكرر.
   - يوجد Z صف يحتاج مراجعة.

Duplicate rule:

- Same event.
- Same name with same phone or email.
- If no phone/email, same normalized name can be treated as warning, not hard duplicate.

Unknown type rule:

- Simpler default: create new guest type automatically with a safe color.
- Alternative if stricter: map unknown types in preview.

Recommended first version:

- Auto-create unknown types and report them.

### 9.2 Guest List Export

Excel columns:

- اسم الضيف.
- نوع الضيف.
- الهاتف.
- البريد.
- الطاولة.
- المقعد.
- ملاحظات.

Export should be scoped to event and authenticated user.

قرار النسخة الأولى:

- تصدير قائمة الضيوف يكون Excel فقط.
- لا يتم بناء PDF لقائمة الضيوف إلا إذا طُلب لاحقا.

## 10. PDF Export Flow

### 10.1 Floor Plan PDF

Because DomPDF cannot render a live canvas:

1. Vue calls Konva `stage.toDataURL()`.
2. Vue sends image data URL to Laravel endpoint.
3. Laravel validates image payload size and floorplan ownership.
4. Laravel renders a Blade PDF view through DomPDF.
5. PDF includes:
   - اسم الحدث.
   - اسم المخطط.
   - تاريخ الحدث.
   - مقاس القاعة.
   - عدد المقاعد.
   - عدد الضيوف المسكنين.
   - صورة المخطط.
   - ملخص اختياري للأنواع.

### 10.2 PDF Blade View

```text
resources/views/exports/floorplan-pdf.blade.php
```

Rules:

- Use Arabic-compatible font if available.
- Keep layout simple for DomPDF.
- Avoid complex CSS grid in PDF view.
- Embed canvas image.

### 10.3 Guest List PDF

Optional after Excel export:

```text
resources/views/exports/guest-list-pdf.blade.php
```

Keep table simple, printable, and Arabic RTL.

## 11. UI/UX Design Rules Based on Logo, Icon, Colors, and Reference Image

### 11.1 Brand Observations

From `logo.png`, `logo2.png`, and `icon2.png`:

- The brand mark uses angular geometric ribbons.
- Dominant identity colors are teal, blue, and gold.
- Logo text uses calm gray.
- The visual identity feels professional and architectural, which fits floor planning.

From `image.png`:

- The editor reference uses a very clear three-panel workflow.
- Canvas grid is central and dominant.
- Toolbar actions are compact and task-focused.
- Guest list cards are simple and scannable.
- Purple appears in the screenshot, but project brand colors should take priority.

### 11.2 Color Rules

Use:

- Primary teal: `#4D9B97`.
- Primary blue: `#4596CF`.
- Primary gradient: `linear-gradient(135deg, #4D9B97 0%, #4596CF 100%)`.
- Deep blue: `#31719D`.
- Deep teal: `#317C77`.
- Gold: `#E7C539`.
- Gray: `#A19F9E`.

Avoid:

- Turning the whole app purple just because the screenshot has purple.
- Heavy gradients on every card.
- Random guest type colors outside the palette unless needed.

### 11.3 Typography

Use:

```css
font-family: "Cairo", "Tajawal", "IBM Plex Sans Arabic", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
```

Rules:

- UI text in Arabic.
- Developer-facing names in English.
- Body: 14-16px.
- Titles: 24-32px.
- Sidebar/editor labels: clear and readable, not tiny.

### 11.4 Dashboard UI

- Arabic RTL.
- Sidebar on the right.
- White cards.
- Soft borders.
- Subtle shadows.
- Brand gradient only for primary CTAs.
- Badges for statuses and guest types.
- Clear empty states:
  - `لا توجد أحداث بعد`
  - `لم يتم إضافة ضيوف لهذا الحدث`
  - `لا توجد مخططات محفوظة`

### 11.5 Editor UI

Layout:

- Top toolbar fixed at the top.
- Left library panel for elements.
- Center canvas.
- Right guest panel.

Although the app is RTL, keeping library left and guests right is acceptable because it matches the reference editor workflow. Text alignment inside panels remains RTL.

Editor styling:

- Light canvas background.
- Fine grid with low contrast.
- Selected objects use brand blue/teal stroke.
- VIP seats/guests can use gold.
- Seated state uses deep teal.
- Empty seat uses neutral border.
- Conflict/error uses red.

### 11.6 Controls

- Buttons:
  - `حفظ المخطط`
  - `تصدير PDF`
  - `استيراد الضيوف`
  - `إضافة ضيف`
- Save status:
  - `تم الحفظ`
  - `توجد تغييرات غير محفوظة`
  - `جار الحفظ...`
- Toasts:
  - `تم حفظ المخطط بنجاح`
  - `هذا المقعد محجوز بالفعل`
  - `تم تجليس الضيف`
  - `تم إزالة التجليس`

## 12. Development Phases

## Phase 0: Laravel Project Initialization

Goal:

- Create Laravel 10 project structure in the current repository.
- Preserve existing planning/reference files.

Tasks:

- Install Laravel 10.
- Configure PHP platform as 8.2.
- Configure `.env.example`.
- Configure MySQL.
- Install auth scaffolding if approved.
- Install frontend baseline with Vite.
- Add Arabic/RTL base layout and brand tokens.

Checks:

- `composer validate`
- `php artisan --version`
- `php artisan test` if tests exist
- `npm run build`

Review checklist:

- Laravel version is 10.
- PHP platform is 8.2.
- Existing reference files preserved.
- No PHP 8.3+ dependency.

## Phase 1: Auth, Layout, Dashboard

Tasks:

- Build login/logout flow.
- Disable public registration.
- Seed one Admin account.
- Create Arabic RTL layout.
- Add logo and icon assets.
- Add dashboard route/controller/view.
- Show basic summary cards.

Checks:

- Login works.
- Dashboard protected by auth.
- Arabic layout is RTL.
- Brand colors visible but not overused.

Testing:

- Unauthenticated user redirects to login.
- Authenticated user sees dashboard.
- `npm run build`.
- `php artisan test` if tests exist.

## Phase 2: Events CRUD and Event Details

Tasks:

- Create `events` migration/model/controller/requests/policy.
- Build event list, create, edit, show.
- Add search.
- Add delete confirmation.
- Scope events to current user.
- Generate preview link for every event.
- Add read-only event preview page by token.
- Add copy/refresh preview link actions.

Checks:

- User cannot access another user's event.
- Preview link opens read-only view when enabled.
- Preview link does not expose edit actions or private contact details.
- Validation errors are Arabic.
- Event details page shows floorplans and guests placeholders.
- `php artisan route:list`.
- `php artisan test`.

## Phase 3: Floorplan CRUD and Background Upload

Tasks:

- Create `floorplans` migration/model/controller/requests/policy.
- Build create/edit forms.
- Validate dimensions, unit, paper size, orientation.
- Upload optional background image.
- Apply conservative image compression/optimization without visibly damaging quality.
- Link floorplans to events.
- Add `فتح المحرر` action.

Checks:

- Invalid image rejected.
- Oversized image rejected.
- Optimized image remains visually clear.
- Floorplans scoped through event ownership.
- Storage link documented.

Testing:

- Create floorplan.
- Edit floorplan.
- Delete floorplan.
- Upload background image.
- `php artisan test`.
- `php artisan route:list`.

## Phase 4: Basic Vue + Konva Editor

Tasks:

- Add Vue 3 editor entry.
- Add Konva canvas.
- Build Blade editor mount page.
- Add editor data endpoint.
- Add save endpoint.
- Render grid.
- Add simple table rectangle.
- Drag table.
- Save/load `design_json`.

Checks:

- Vue only used in editor page.
- Editor opens from floorplan.
- Canvas grid visible.
- Save/load preserves element position.
- `npm run build`.

Manual testing:

- Open editor.
- Add table.
- Move table.
- Save.
- Refresh.
- Confirm table remains.

## Phase 5: Table Builder and Seat Generation

Tasks:

- Add table settings panel.
- Implement `useSeatLayout.js`.
- Generate seats for round/rectangle/square/banquet/theater.
- Store seats in `seats` table on save.
- Display seat labels.
- Display table labels.

Checks:

- Seat count matches selected value.
- Seat keys are stable.
- Seats persist after refresh.
- Large seat counts remain usable.

Testing:

- Round table 8 seats.
- Rectangle table 10 seats.
- Theater rows.
- Save/load.
- `php artisan test` for seat generation service if backend generation exists.
- `npm run build`.

## Phase 6: Guests and Guest Types

Tasks:

- Create `guest_types`, `guests`, `seats` migrations if not already created.
- Seed default guest types.
- Build guest CRUD.
- Build global guest type CRUD.
- Add guest list to event page.
- Add guest list to editor right panel.
- Add search/filter.

Checks:

- Defaults appear in Arabic.
- Guest type badges use brand-aware colors.
- Guest CRUD is scoped by event, while guest types are global.
- Validation Arabic.

Testing:

- Create guest.
- Edit guest.
- Delete guest.
- Create guest type.
- Check guest appears in editor.
- `php artisan test`.
- `npm run build`.

## Phase 7: Drag Guest to Seat

Tasks:

- Enable dragging guest cards.
- Drop guest onto empty seat.
- Add assignment endpoint.
- Add unassignment endpoint.
- Update `seats.guest_id`.
- Show guest name near/on seat.
- Update seated counter.
- Prevent conflicts.

Checks:

- Same seat cannot hold two guests.
- Same guest cannot be seated twice in same floorplan.
- Guest from another event rejected.
- UI shows Arabic conflict messages.

Testing:

- Assign guest.
- Reassign guest.
- Unassign guest.
- Try assigning to occupied seat.
- Refresh editor and verify assignment remains.
- `php artisan test`.
- `npm run build`.

## Phase 8: Excel Import

Tasks:

- Install/configure Laravel Excel.
- Build import form.
- Validate file type and size.
- Parse rows.
- Show preview.
- Import valid rows.
- Create unknown guest types or report them.
- Skip duplicates.

Checks:

- Invalid files rejected.
- Missing name handled.
- Duplicates skipped or reported.
- Arabic summary shown.
- No private guest data logged.

Testing:

- Import valid file.
- Import file with missing names.
- Import duplicates.
- Import unknown guest type.
- `php artisan test`.

## Phase 9: Exports

Tasks:

- Add guest list Excel export.
- Add floorplan PDF export endpoint.
- Export Konva canvas image from Vue.
- Generate DomPDF view.

Checks:

- Export does not require Chrome/npm/server Node.
- Floorplan PDF includes canvas image.
- Arabic PDF content readable.
- Guest export includes table/seat.

Testing:

- Export guest list.
- Export floorplan PDF.
- Print preview PDF.
- `php artisan test`.
- `npm run build`.

## Phase 10: Polish, Validation, Testing, Deployment

Tasks:

- UI polish.
- Arabic language consistency.
- RTL review.
- Error and empty states.
- Responsive dashboard checks.
- Editor desktop width checks.
- Security review.
- Deployment guide.

Checks:

- `composer validate`
- `composer check-platform-reqs`
- `php artisan test`
- `php artisan route:list`
- `npm run build`
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`

## 13. Step-by-Step Implementation Roadmap

1. Confirm plan approval.
2. Create Laravel 10 scaffold in the current root while preserving existing files.
3. Configure PHP 8.2 platform and environment examples.
4. Add auth with Admin-only access and no public registration.
5. Add Arabic RTL base layout.
6. Add brand tokens, logo assets, and dashboard shell.
7. Implement Events CRUD with preview links, policies, and tests.
8. Implement Floorplans CRUD and conservative image optimization.
9. Build basic editor mount page with Vue/Konva.
10. Add save/load JSON endpoints.
11. Implement table creation and seat generation.
12. Add global guest types and event guests.
13. Connect guest panel to editor.
14. Implement seat assignment and conflict validation.
15. Add Excel import preview and confirmation using English columns.
16. Add guest list Excel export.
17. Add floorplan PDF export using canvas image + DomPDF.
18. Run final UI/RTL/security/performance review.
19. Prepare shared hosting deployment notes.

## 14. Testing and Review Checklist for Each Phase

### Functional Checks

- Feature route exists.
- Main user flow works.
- Empty state works.
- Error state works.
- Create/edit/delete actions behave correctly.

### UI Checks

- Arabic labels.
- RTL layout.
- Brand colors.
- Clear primary and secondary actions.
- No broken text alignment.
- Dashboard responsive enough for tablet.
- Editor usable at common desktop widths.

### Data Validation Checks

- Required fields validated.
- File types validated.
- File sizes limited.
- Numeric dimensions validated.
- Guest assignment conflicts rejected.

### Security Checks

- Routes protected by `auth`.
- Ownership enforced.
- CSRF handled.
- Uploaded files stored safely.
- `.env` secrets not committed.
- Guest data not logged unnecessarily.

### Editor Checks

- Canvas loads.
- Grid visible.
- Elements can be added and moved.
- Seats generated correctly.
- Guest assignment persists.
- Save/load works.
- Zoom works.
- Export image is generated.

### Export Checks

- DomPDF works without browser dependencies.
- PDF includes event and floorplan details.
- PDF image is clear enough.
- Guest list export includes assigned table/seat.

## 15. Deployment Notes for Shared Hosting

### Local Build

Run locally before upload:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Production Upload

Upload:

- Laravel application files.
- `vendor/` if Composer is unavailable on hosting.
- `public/build`.
- `storage/app/public` uploads if migrating existing data.
- `.env` configured manually on server.

Do not upload:

- `.env` from local machine.
- `node_modules`.
- test/export temporary files.

### Shared Hosting Setup

- Point web root to `public/` if hosting supports it.
- If not, use hosting-specific public folder mapping carefully.
- Configure MySQL credentials in `.env`.
- Set:

```env
APP_ENV=production
APP_DEBUG=false
FILESYSTEM_DISK=public
```

- Run migrations through SSH if available:

```bash
php artisan migrate --force
```

- If SSH is unavailable, prepare database import carefully from local/staging.
- Ensure `storage` and `bootstrap/cache` are writable.
- Ensure `public/storage` symlink exists or use hosting file manager workaround.

### Production Constraints

- Do not rely on npm on server.
- Do not rely on Chrome, Puppeteer, or Browsershot.
- Do not require long-running workers.
- Use DomPDF only for PDFs.

## 16. Risks, Open Questions, and Assumptions

### Risks

- DomPDF Arabic font rendering may need font configuration.
- Very large canvas images can create heavy PDF payloads.
- Large guest lists can slow editor if loaded all at once.
- Shared hosting file permissions may affect uploads and storage symlink.
- Browser drag/drop from HTML panel to Konva needs careful implementation.
- Seat geometry for all shapes needs incremental testing.

### Simple Mitigations

- Keep PDF layout simple and embed canvas image.
- Limit exported canvas image size or compress before upload.
- Add guest search/filter and consider pagination for very large events.
- Document storage permissions and symlink setup.
- Implement assignment logic in backend service with transactions.
- Start with core table shapes, then add advanced shapes.

### Open Questions

تم حسم الأسئلة المفتوحة كالتالي:

- لا يوجد تسجيل عام للمستخدمين.
- النظام يستخدم Admin فقط في النسخة الأولى.
- أنواع الضيوف عامة على مستوى النظام وليست لكل حدث.
- تصدير قائمة الضيوف يكون Excel فقط.
- استيراد Excel يحتاج الأعمدة الإنجليزية فقط: `name`, `phone`, `email`, `type`, `notes`.
- صور الخلفية يتم تحسينها/ضغطها بحذر بدون إتلاف الجودة أو تشويه الصورة.
- كل حدث يجب أن يمتلك رابط معاينة read-only.

### Assumptions

- النظام لشركة واحدة فقط.
- Admin واحد يدير النظام في النسخة الأولى.
- رابط المعاينة tokenized وغير قابل للتخمين، ويعرض بيانات قراءة فقط.
- رابط المعاينة لا يعرض أرقام الهواتف أو البريد الإلكتروني للضيوف افتراضيا.
- أول نسخة تعتمد على حفظ يدوي للمخطط، مع Autosave اختياري لاحقا.
- `seats.guest_id` كاف في البداية ولا نحتاج جدول `seating_assignments`.
- Vue لن يستخدم خارج صفحة المحرر.
- لا توجد حاجة لتعاون لحظي أو WebSockets.

## 17. Suggestions and Improvements

### Recommended First-Version Choices

- استخدام `seats.guest_id` بدلا من جدول تجليس منفصل.
- حفظ كل عناصر المحرر داخل `floorplans.design_json`.
- استخدام Auth بسيط وليس منظومة صلاحيات كبيرة.
- إنشاء أنواع الضيوف الافتراضية تلقائيا لكل حدث أو كقيم عامة.
- تصدير PDF من صورة Canvas وليس محاولة رسم المخطط داخل DomPDF.
- البدء بالأشكال الأساسية للطاولات ثم إضافة التفاصيل المتقدمة لاحقا.

### UX Improvements Worth Adding

- مؤشر حفظ واضح: محفوظ/غير محفوظ/جار الحفظ.
- تحذير قبل مغادرة المحرر عند وجود تغييرات غير محفوظة.
- زر `ملاءمة المخطط للشاشة`.
- تحكم شفافية صورة الخلفية.
- فلتر في لوحة الضيوف: الكل، غير مسكن، تم تجليسه.
- لون ذهبي واضح لضيوف VIP.
- زر إزالة التجليس من بطاقة الضيف ومن المقعد.

### Future Enhancements Only If Needed

- نسخ متعددة للمخطط.
- سجل نشاط بسيط.
- قوالب طاولات محفوظة.
- تصدير عالي الدقة.
- صلاحيات أوسع للموظفين.
- لوحة إحصاءات متقدمة.

لا يوصى بإضافة هذه التحسينات في البداية حتى يبقى المشروع بسيطا وسهل التسليم.
