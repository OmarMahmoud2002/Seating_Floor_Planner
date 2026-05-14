<?php

namespace App\Support;

class ArabicPdfText
{
    /**
     * @var array<string, array{0: string, 1: string, 2?: string, 3?: string}>
     */
    private const FORMS = [
        'ء' => ['ﺀ', 'ﺀ'],
        'آ' => ['ﺁ', 'ﺂ'],
        'أ' => ['ﺃ', 'ﺄ'],
        'ؤ' => ['ﺅ', 'ﺆ'],
        'إ' => ['ﺇ', 'ﺈ'],
        'ئ' => ['ﺉ', 'ﺊ', 'ﺋ', 'ﺌ'],
        'ا' => ['ﺍ', 'ﺎ'],
        'ب' => ['ﺏ', 'ﺐ', 'ﺑ', 'ﺒ'],
        'ة' => ['ﺓ', 'ﺔ'],
        'ت' => ['ﺕ', 'ﺖ', 'ﺗ', 'ﺘ'],
        'ث' => ['ﺙ', 'ﺚ', 'ﺛ', 'ﺜ'],
        'ج' => ['ﺝ', 'ﺞ', 'ﺟ', 'ﺠ'],
        'ح' => ['ﺡ', 'ﺢ', 'ﺣ', 'ﺤ'],
        'خ' => ['ﺥ', 'ﺦ', 'ﺧ', 'ﺨ'],
        'د' => ['ﺩ', 'ﺪ'],
        'ذ' => ['ﺫ', 'ﺬ'],
        'ر' => ['ﺭ', 'ﺮ'],
        'ز' => ['ﺯ', 'ﺰ'],
        'س' => ['ﺱ', 'ﺲ', 'ﺳ', 'ﺴ'],
        'ش' => ['ﺵ', 'ﺶ', 'ﺷ', 'ﺸ'],
        'ص' => ['ﺹ', 'ﺺ', 'ﺻ', 'ﺼ'],
        'ض' => ['ﺽ', 'ﺾ', 'ﺿ', 'ﻀ'],
        'ط' => ['ﻁ', 'ﻂ', 'ﻃ', 'ﻄ'],
        'ظ' => ['ﻅ', 'ﻆ', 'ﻇ', 'ﻈ'],
        'ع' => ['ﻉ', 'ﻊ', 'ﻋ', 'ﻌ'],
        'غ' => ['ﻍ', 'ﻎ', 'ﻏ', 'ﻐ'],
        'ف' => ['ﻑ', 'ﻒ', 'ﻓ', 'ﻔ'],
        'ق' => ['ﻕ', 'ﻖ', 'ﻗ', 'ﻘ'],
        'ك' => ['ﻙ', 'ﻚ', 'ﻛ', 'ﻜ'],
        'ل' => ['ﻝ', 'ﻞ', 'ﻟ', 'ﻠ'],
        'م' => ['ﻡ', 'ﻢ', 'ﻣ', 'ﻤ'],
        'ن' => ['ﻥ', 'ﻦ', 'ﻧ', 'ﻨ'],
        'ه' => ['ﻩ', 'ﻪ', 'ﻫ', 'ﻬ'],
        'و' => ['ﻭ', 'ﻮ'],
        'ى' => ['ﻯ', 'ﻰ'],
        'ي' => ['ﻱ', 'ﻲ', 'ﻳ', 'ﻴ'],
    ];

    public static function make(string|int|float|null $value): string
    {
        $text = (string) ($value ?? '');

        if ($text === '') {
            return '';
        }

        return preg_replace_callback('/[\x{0600}-\x{06FF}\s،؛؟]+/u', function (array $matches): string {
            return self::reverse(self::shape($matches[0]));
        }, $text) ?? $text;
    }

    private static function shape(string $text): string
    {
        $chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $result = '';
        $count = count($chars);

        for ($index = 0; $index < $count; $index++) {
            $char = $chars[$index];

            if (! isset(self::FORMS[$char])) {
                $result .= $char;

                continue;
            }

            $previous = self::previousArabicLetter($chars, $index);
            $next = self::nextArabicLetter($chars, $index);
            $connectsPrevious = $previous !== null
                && self::canConnectPrevious($char)
                && self::canConnectNext($previous);
            $connectsNext = $next !== null
                && self::canConnectNext($char)
                && self::canConnectPrevious($next);

            $forms = self::FORMS[$char];

            $result .= match (true) {
                $connectsPrevious && $connectsNext && isset($forms[3]) => $forms[3],
                $connectsPrevious => $forms[1],
                $connectsNext && isset($forms[2]) => $forms[2],
                default => $forms[0],
            };
        }

        return $result;
    }

    /**
     * @param array<int, string> $chars
     */
    private static function previousArabicLetter(array $chars, int $index): ?string
    {
        for ($i = $index - 1; $i >= 0; $i--) {
            if ($chars[$i] === ' ') {
                return null;
            }

            if (isset(self::FORMS[$chars[$i]])) {
                return $chars[$i];
            }
        }

        return null;
    }

    /**
     * @param array<int, string> $chars
     */
    private static function nextArabicLetter(array $chars, int $index): ?string
    {
        $count = count($chars);

        for ($i = $index + 1; $i < $count; $i++) {
            if ($chars[$i] === ' ') {
                return null;
            }

            if (isset(self::FORMS[$chars[$i]])) {
                return $chars[$i];
            }
        }

        return null;
    }

    private static function canConnectPrevious(string $char): bool
    {
        return isset(self::FORMS[$char][1]);
    }

    private static function canConnectNext(string $char): bool
    {
        return isset(self::FORMS[$char][2]);
    }

    private static function reverse(string $text): string
    {
        return implode('', array_reverse(preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []));
    }
}
