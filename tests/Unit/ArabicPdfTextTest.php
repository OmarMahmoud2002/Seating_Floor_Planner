<?php

namespace Tests\Unit;

use App\Support\ArabicPdfText;
use PHPUnit\Framework\TestCase;

class ArabicPdfTextTest extends TestCase
{
    public function test_arabic_text_is_prepared_for_dompdf(): void
    {
        $prepared = ArabicPdfText::make('اليوم الوطني');

        $this->assertNotSame('اليوم الوطني', $prepared);
        $this->assertStringContainsString('ﻟ', $prepared);
    }
}
