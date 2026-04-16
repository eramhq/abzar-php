<?php

declare(strict_types=1);

namespace Eram\Abzar\Format;

use Eram\Abzar\AbzarFormatException;
use Eram\Abzar\Digits\DigitConverter;
use Eram\Abzar\Validation\ErrorCode;

final class TimeAgo
{
    private function __construct()
    {
    }

    private const MINUTE = 60;
    private const HOUR = 3600;
    private const DAY = 86400;
    private const WEEK = 604800;
    private const MONTH = 2592000;
    private const YEAR = 31536000;

    /**
     * @param (callable(int): string)|null $jalaliMonthResolver
     *        Optional hook invoked for ≥ 1 year deltas to append a Jalali
     *        month + year tag (e.g. {@code اردیبهشت ۱۴۰۲}). Receives the
     *        resolved UTC timestamp; the caller is responsible for
     *        timezone handling (typically {@code Asia/Tehran}).
     */
    public static function format(
        int|string|\DateTimeInterface $timestamp,
        ?int $now = null,
        bool $persianDigits = true,
        ?callable $jalaliMonthResolver = null,
    ): string {
        $time = self::resolveTimestamp($timestamp);
        $now ??= time();

        $diff = $now - $time;
        $suffix = 'پیش';

        if ($diff < 0) {
            $diff = abs($diff);
            $suffix = 'بعد';
        }

        $inYearBucket = $diff >= self::YEAR;

        $text = match (true) {
            $diff < 10          => 'همین الان',
            $diff < self::MINUTE => "چند ثانیه {$suffix}",
            $diff < self::HOUR   => self::unit(intdiv($diff, self::MINUTE), 'دقیقه', $suffix),
            $diff < self::DAY    => self::unit(intdiv($diff, self::HOUR), 'ساعت', $suffix),
            $diff < self::WEEK   => self::unit(intdiv($diff, self::DAY), 'روز', $suffix, true),
            $diff < self::MONTH  => self::unit(intdiv($diff, self::WEEK), 'هفته', $suffix, true),
            $diff < self::YEAR   => self::unit(intdiv($diff, self::MONTH), 'ماه', $suffix, true),
            default              => self::unit(intdiv($diff, self::YEAR), 'سال', $suffix, true),
        };

        if ($inYearBucket && $jalaliMonthResolver !== null) {
            $text .= ' — ' . $jalaliMonthResolver($time);
        }

        if ($persianDigits) {
            $text = DigitConverter::toPersian($text);
        }

        return $text;
    }

    private static function unit(int $value, string $unit, string $suffix, bool $approximate = false): string
    {
        $prefix = $approximate ? 'حدود ' : '';
        return "{$prefix}{$value} {$unit} {$suffix}";
    }

    private static function resolveTimestamp(int|string|\DateTimeInterface $timestamp): int
    {
        if ($timestamp instanceof \DateTimeInterface) {
            return $timestamp->getTimestamp();
        }

        if (is_int($timestamp)) {
            return $timestamp;
        }

        $parsed = strtotime($timestamp);

        if ($parsed === false) {
            throw AbzarFormatException::forInput(ErrorCode::TIME_AGO_INVALID_TIMESTAMP, $timestamp);
        }

        return $parsed;
    }
}
