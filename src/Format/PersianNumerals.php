<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

/**
 * Shared Persian numeral-word tables consumed by both {@see NumberToWords}
 * and {@see WordsToNumber}. Value-only — no behaviour beyond the constants.
 */
final class PersianNumerals
{
    public const ONES     = ['', 'یک', 'دو', 'سه', 'چهار', 'پنج', 'شش', 'هفت', 'هشت', 'نه'];
    public const TEENS    = ['ده', 'یازده', 'دوازده', 'سیزده', 'چهارده', 'پانزده', 'شانزده', 'هفده', 'هجده', 'نوزده'];
    public const TENS     = ['', '', 'بیست', 'سی', 'چهل', 'پنجاه', 'شصت', 'هفتاد', 'هشتاد', 'نود'];
    public const HUNDREDS = ['', 'یکصد', 'دویست', 'سیصد', 'چهارصد', 'پانصد', 'ششصد', 'هفتصد', 'هشتصد', 'نهصد'];
    public const SCALES   = ['', 'هزار', 'میلیون', 'میلیارد', 'تریلیون', 'کوادریلیون'];

    private function __construct()
    {
    }
}
