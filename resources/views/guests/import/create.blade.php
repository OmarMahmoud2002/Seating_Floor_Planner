@extends('layouts.dashboard')

@section('title', 'استيراد الضيوف')
@section('page_title', 'استيراد الضيوف')

@section('content')
    <section class="page-header-card">
        <div>
            <p class="eyebrow">استيراد Excel</p>
            <h2>{{ $event->name }}</h2>
            <p>ارفع ملف الضيوف بصيغة xlsx أو xls أو csv، ثم راجع المعاينة قبل اعتماد الاستيراد.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('events.guests.index', $event) }}" class="btn btn-secondary">رجوع للضيوف</a>
        </div>
    </section>

    <section class="content-grid">
        <article class="panel-card">
            <div class="panel-heading">
                <h2>رفع ملف الضيوف</h2>
            </div>

            <form method="POST" action="{{ route('events.guests.import.preview', $event) }}" enctype="multipart/form-data" class="form-stack">
                @csrf

                <div class="field full-row">
                    <label for="file">ملف Excel <span class="required">*</span></label>
                    <input id="file" name="file" type="file" class="form-control file-control @error('file') is-invalid @enderror" accept=".xlsx,.xls,.csv" required>
                    <p class="helper-text">الحجم الأقصى 2 ميجابايت. يجب أن يحتوي الملف على صف عناوين باللغة الإنجليزية.</p>
                    @error('file')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">معاينة الملف</button>
                    <a href="{{ route('events.guests.index', $event) }}" class="btn btn-secondary">إلغاء</a>
                </div>
            </form>
        </article>

        <article class="panel-card">
            <div class="panel-heading">
                <h2>تنسيق الملف</h2>
            </div>

            <p class="muted-text">استخدم الأعمدة التالية بالإنجليزية في الصف الأول:</p>
            <div class="table-wrap import-format-table">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>العمود</th>
                            <th>الوصف</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>name</code></td>
                            <td>اسم الضيف، وهو حقل مطلوب.</td>
                        </tr>
                        <tr>
                            <td><code>phone</code></td>
                            <td>رقم الهاتف اختياري.</td>
                        </tr>
                        <tr>
                            <td><code>email</code></td>
                            <td>البريد الإلكتروني اختياري ويستخدم لاكتشاف التكرار.</td>
                        </tr>
                        <tr>
                            <td><code>type</code></td>
                            <td>نوع الضيف. سيتم إنشاء النوع إذا لم يكن موجودًا.</td>
                        </tr>
                        <tr>
                            <td><code>notes</code></td>
                            <td>ملاحظات داخلية اختيارية.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>
    </section>
@endsection
