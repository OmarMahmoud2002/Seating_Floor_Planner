# دليل النشر والتشغيل

هذا المشروع Laravel 10 مخصص للاستخدام الداخلي، ومصمم ليعمل على استضافة مشتركة بدون Node.js أو Chrome على الخادم.

## المتطلبات

- PHP 8.2 كحد أقصى.
- MySQL.
- Composer محليًا أو على الخادم.
- Node.js محليًا فقط لبناء Vite.
- لا يحتاج الخادم إلى npm أو Puppeteer أو Browsershot أو WebSockets.

## تجهيز نسخة الإنتاج محليًا

نفذ الأوامر التالية قبل رفع الملفات:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan test
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

إذا كنت تعمل على Windows وتعطل `npm run build` بسبب PowerShell execution policy، استخدم:

```bash
npm.cmd run build
```

## ملفات يجب رفعها

- ملفات Laravel كلها.
- مجلد `vendor` إذا كان Composer غير متاح على الاستضافة.
- مجلد `public/build` الناتج من Vite.
- مجلد `storage/app/public` إذا كان يحتوي صور خلفيات فعلية.
- ملف `.env` خاص بالإنتاج يتم إنشاؤه يدويًا على الخادم.

## ملفات لا ترفعها

- `.env` المحلي.
- `node_modules`.
- ملفات الاختبار المؤقتة أو ملفات Excel الخاصة بالضيوف.
- أي نسخة قاعدة بيانات تحتوي بيانات ضيوف حقيقية إلا ضمن إجراء نقل مقصود وآمن.

## إعداد `.env` للإنتاج

استخدم قيمًا مناسبة للخادم:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

ثم أنشئ مفتاح التطبيق إذا كان المشروع جديدًا:

```bash
php artisan key:generate --force
```

## قاعدة البيانات

على الخادم أو بيئة staging:

```bash
php artisan migrate --force
php artisan db:seed --class=GuestTypeSeeder --force
php artisan db:seed --class=AdminUserSeeder --force
```

حدّث بيانات admin من `.env` قبل تشغيل `AdminUserSeeder`:

```env
ADMIN_NAME="مدير النظام"
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=strong-password-here
```

## التخزين والصور

أنشئ رابط التخزين:

```bash
php artisan storage:link
```

إذا كانت الاستضافة لا تدعم symlink، اربط `public/storage` يدويًا بمحتويات `storage/app/public` من لوحة التحكم، أو استخدم إعداد الاستضافة المتاح لنفس الغرض.

## إعدادات الكاش بعد الرفع

بعد تعديل `.env` أو المسارات أو الواجهات، نفذ:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## فحص نهائي

- افتح صفحة تسجيل الدخول.
- سجل الدخول بحساب المدير.
- أنشئ حدثًا ومخططًا.
- افتح المحرر وتأكد أن الشبكة تظهر.
- جرّب استيراد ضيوف Excel بملف صغير.
- جرّب تصدير قائمة الضيوف Excel.
- جرّب تصدير PDF من المحرر.

## ملاحظات أمان

- لا تفعّل `APP_DEBUG=true` في الإنتاج.
- لا تشارك ملفات Excel المستوردة أو بيانات الضيوف خارج النظام.
- تأكد أن مجلدات `storage` و`bootstrap/cache` قابلة للكتابة.
- لا يعتمد تصدير PDF على Chrome أو Node.js على الخادم؛ يتم عبر DomPDF فقط.
