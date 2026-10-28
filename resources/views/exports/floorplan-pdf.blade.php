<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 22px 24px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1E1826;
            font-size: 12px;
            line-height: 1.65;
            background: #FFFFFF;
        }

        .header {
            text-align: right;
            border: 1px solid #E3DCEA;
            border-right: 5px solid #6D4299;
            padding: 12px 14px;
            margin-bottom: 12px;
            background: #FAF8FC;
        }

        h1,
        h2,
        p {
            margin: 0;
        }

        h1 {
            color: #4B2A6E;
            font-size: 21px;
        }

        h2 {
            margin-top: 4px;
            color: #4B2A6E;
            font-size: 15px;
        }

        .header p {
            color: #645C6E;
        }

        .meta {
            width: 100%;
            margin: 12px 0;
            border-collapse: collapse;
        }

        .meta td {
            border: 1px solid #E3DCEA;
            padding: 6px 9px;
            vertical-align: top;
            text-align: right;
        }

        .meta .label {
            width: 22%;
            background: #F8F6FA;
            color: #645C6E;
            font-weight: bold;
        }

        .canvas-card {
            border: 1px solid #E3DCEA;
            padding: 8px;
            margin-top: 10px;
            background: #FAF8FC;
            text-align: center;
            page-break-inside: avoid;
        }

        .canvas-title {
            margin-bottom: 7px;
            color: #4B2A6E;
            font-weight: bold;
            text-align: right;
        }

        .canvas-image {
            display: block;
            border: 1px solid #CFC4DB;
            background: #FFFFFF;
            margin: 0 auto;
        }

        .summary {
            margin-top: 12px;
            border: 1px solid #E3DCEA;
            padding: 9px 10px;
            background: #FFFFFF;
        }

        .summary strong {
            color: #4B2A6E;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $pdfText($event->name) }}</h1>
        <h2>{{ $pdfText($floorplan->name) }}</h2>
        <p>{{ $pdfText('تم إنشاء التقرير في') }} {{ $generatedAt->format('Y-m-d H:i') }}</p>
    </div>

    <table class="meta">
        <tr>
            <td class="label">{{ $pdfText('تاريخ الحدث') }}</td>
            <td>{{ optional($event->event_date)->format('Y-m-d') ?: $pdfText('غير محدد') }}</td>
            <td class="label">{{ $pdfText('الموقع') }}</td>
            <td>{{ $pdfText($event->location ?: 'غير محدد') }}</td>
        </tr>
        <tr>
            <td class="label">{{ $pdfText('مقاس القاعة') }}</td>
            <td>{{ $floorplan->width }} × {{ $floorplan->height }} {{ $pdfText($floorplan->unit === 'meter' ? 'متر' : 'قدم') }}</td>
            <td class="label">{{ $pdfText('الورق') }}</td>
            <td>{{ $floorplan->paper_size }} - {{ $pdfText($floorplan->orientation === 'landscape' ? 'عرضي' : 'عمودي') }}</td>
        </tr>
        <tr>
            <td class="label">{{ $pdfText('عدد المقاعد') }}</td>
            <td>{{ $seatCount }}</td>
            <td class="label">{{ $pdfText('تم تجليسهم') }}</td>
            <td>{{ $assignedCount }}</td>
        </tr>
    </table>

    <div class="canvas-card">
        <p class="canvas-title">{{ $pdfText('صورة المخطط') }}</p>
        <img
            src="{{ $imageData }}"
            alt=""
            class="canvas-image"
            width="{{ $imageBox['width'] }}"
            height="{{ $imageBox['height'] }}"
        >
    </div>

    <div class="summary">
        <strong>{{ $pdfText('ملخص أنواع الضيوف المسكنين') }}</strong>
        @if ($typeSummary->isEmpty())
            <p>{{ $pdfText('لا توجد تجليسات حتى الآن.') }}</p>
        @else
            @foreach ($typeSummary as $typeName => $count)
                <p>{{ $pdfText($typeName) }}: {{ $count }}</p>
            @endforeach
        @endif
    </div>
</body>
</html>
