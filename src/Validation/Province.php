<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

use Eram\Abzar\Text\CharNormalizer;

enum Province: string
{
    use PersianLookup;

    case AZARBAIJAN_SHARGHI = 'azarbaijan-sharghi';
    case AZARBAIJAN_GHARBI  = 'azarbaijan-gharbi';
    case ARDABIL            = 'ardabil';
    case ISFAHAN            = 'isfahan';
    case FOREIGN_AFFAIRS    = 'foreign-affairs';
    case ILAM               = 'ilam';
    case BUSHEHR            = 'bushehr';
    case TEHRAN             = 'tehran';
    case KHORASAN_JONUBI    = 'khorasan-jonubi';
    case KHORASAN_RAZAVI    = 'khorasan-razavi';
    case KHORASAN_SHOMALI   = 'khorasan-shomali';
    case KHUZESTAN          = 'khuzestan';
    case ZANJAN             = 'zanjan';
    case SEMNAN             = 'semnan';
    case SISTAN_BALUCHESTAN = 'sistan-baluchestan';
    case FARS               = 'fars';
    case QAZVIN             = 'qazvin';
    case QOM                = 'qom';
    case LORESTAN           = 'lorestan';
    case MAZANDARAN         = 'mazandaran';
    case MARKAZI            = 'markazi';
    case HORMOZGAN          = 'hormozgan';
    case HAMEDAN            = 'hamedan';
    case CHAHAR_MAHAL       = 'chahar-mahal';
    case KORDESTAN          = 'kordestan';
    case KERMAN             = 'kerman';
    case KERMANSHAH         = 'kermanshah';
    case KOHGILUYEH         = 'kohgiluyeh';
    case GOLESTAN           = 'golestan';
    case GILAN              = 'gilan';
    case YAZD               = 'yazd';

    public function persianName(): string
    {
        return match ($this) {
            self::AZARBAIJAN_SHARGHI => 'آذربایجان شرقی',
            self::AZARBAIJAN_GHARBI  => 'آذربایجان غربی',
            self::ARDABIL            => 'اردبیل',
            self::ISFAHAN            => 'اصفهان',
            self::FOREIGN_AFFAIRS    => 'امور خارجه',
            self::ILAM               => 'ایلام',
            self::BUSHEHR            => 'بوشهر',
            self::TEHRAN             => 'تهران',
            self::KHORASAN_JONUBI    => 'خراسان جنوبی',
            self::KHORASAN_RAZAVI    => 'خراسان رضوی',
            self::KHORASAN_SHOMALI   => 'خراسان شمالی',
            self::KHUZESTAN          => 'خوزستان',
            self::ZANJAN             => 'زنجان',
            self::SEMNAN             => 'سمنان',
            self::SISTAN_BALUCHESTAN => 'سیستان و بلوچستان',
            self::FARS               => 'فارس',
            self::QAZVIN             => 'قزوین',
            self::QOM                => 'قم',
            self::LORESTAN           => 'لرستان',
            self::MAZANDARAN         => 'مازندران',
            self::MARKAZI            => 'مرکزی',
            self::HORMOZGAN          => 'هرمزگان',
            self::HAMEDAN            => 'همدان',
            self::CHAHAR_MAHAL       => 'چهارمحال و بختیاری',
            self::KORDESTAN          => 'کردستان',
            self::KERMAN             => 'کرمان',
            self::KERMANSHAH         => 'کرمانشاه',
            self::KOHGILUYEH         => 'کهکیلویه و بویراحمد',
            self::GOLESTAN           => 'گلستان',
            self::GILAN              => 'گیلان',
            self::YAZD               => 'یزد',
        };
    }

    protected static function normalizeInput(string $name): string
    {
        static $normalizer = null;
        $normalizer ??= new CharNormalizer();

        return $normalizer->normalize($name);
    }
}
