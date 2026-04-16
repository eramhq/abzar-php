<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

/**
 * Canonical Iranian bank / credit-institution identifier.
 *
 * The backing value is an English slug (URL / JSON safe). Persian display name
 * via {@see self::persianName()}. Surface forms vary slightly between card-BIN
 * and IBAN data tables — both resolve to the same case via {@see self::fromPersian()}.
 */
enum Bank: string
{
    use PersianLookup;

    case AYANDEH           = 'ayandeh';
    case EGHTESAD_NOVIN    = 'eghtesad-novin';
    case ANSAR             = 'ansar';
    case IRAN_ZAMIN        = 'iran-zamin';
    case PARSIAN           = 'parsian';
    case PASARGAD          = 'pasargad';
    case POST_BANK         = 'post-bank';
    case TEJARAT           = 'tejarat';
    case TOSEE_TAAVON      = 'tosee-taavon';
    case TOSEE_SADERAT     = 'tosee-saderat';
    case HEKMAT_IRANIAN    = 'hekmat-iranian';
    case KHAVARMIANEH      = 'khavarmianeh';
    case DEY               = 'dey';
    case RESALAT           = 'resalat';
    case REFAH             = 'refah';
    case SAMAN             = 'saman';
    case SEPAH             = 'sepah';
    case SARMAYEH          = 'sarmayeh';
    case SINA              = 'sina';
    case SHAHR             = 'shahr';
    case SADERAT           = 'saderat';
    case SANAT_MADAN       = 'sanat-madan';
    case GHARZ_MEHR        = 'gharz-mehr';
    case GHAVAMIN          = 'ghavamin';
    case KARAFARIN         = 'karafarin';
    case KESHAVARZI        = 'keshavarzi';
    case GARDESHGARI       = 'gardeshgari';
    case MARKAZI           = 'markazi';
    case MASKAN            = 'maskan';
    case MELLAT            = 'mellat';
    case MELLI             = 'melli';
    case MEHR_IRAN         = 'mehr-iran';
    case MEHR_EGHTESAD     = 'mehr-eghtesad';
    case KOSAR             = 'kosar';
    case MELAL             = 'melal';
    case TOSEE             = 'tosee';
    case NOOR              = 'noor';
    case IRAN_VENEZUELA    = 'iran-venezuela';

    public function persianName(): string
    {
        return match ($this) {
            self::AYANDEH           => 'بانک آینده',
            self::EGHTESAD_NOVIN    => 'بانک اقتصاد نوین',
            self::ANSAR             => 'بانک انصار',
            self::IRAN_ZAMIN        => 'بانک ایران زمین',
            self::PARSIAN           => 'بانک پارسیان',
            self::PASARGAD          => 'بانک پاسارگاد',
            self::POST_BANK         => 'پست بانک ایران',
            self::TEJARAT           => 'بانک تجارت',
            self::TOSEE_TAAVON      => 'بانک توسعه تعاون',
            self::TOSEE_SADERAT     => 'بانک توسعه صادرات',
            self::HEKMAT_IRANIAN    => 'بانک حکمت ایرانیان',
            self::KHAVARMIANEH      => 'بانک خاورمیانه',
            self::DEY               => 'بانک دی',
            self::RESALAT           => 'بانک رسالت',
            self::REFAH             => 'بانک رفاه کارگران',
            self::SAMAN             => 'بانک سامان',
            self::SEPAH             => 'بانک سپه',
            self::SARMAYEH          => 'بانک سرمایه',
            self::SINA              => 'بانک سینا',
            self::SHAHR             => 'بانک شهر',
            self::SADERAT           => 'بانک صادرات ایران',
            self::SANAT_MADAN       => 'بانک صنعت و معدن',
            self::GHARZ_MEHR        => 'بانک قرض الحسنه مهر',
            self::GHAVAMIN          => 'بانک قوامین',
            self::KARAFARIN         => 'بانک کارآفرین',
            self::KESHAVARZI        => 'بانک کشاورزی',
            self::GARDESHGARI       => 'بانک گردشگری',
            self::MARKAZI           => 'بانک مرکزی جمهوری اسلامی ایران',
            self::MASKAN            => 'بانک مسکن',
            self::MELLAT            => 'بانک ملت',
            self::MELLI             => 'بانک ملی ایران',
            self::MEHR_IRAN         => 'بانک مهر ایران',
            self::MEHR_EGHTESAD     => 'بانک مهر اقتصاد',
            self::KOSAR             => 'موسسه اعتباری کوثر',
            self::MELAL             => 'موسسه اعتباری ملل',
            self::TOSEE             => 'موسسه اعتباری توسعه',
            self::NOOR              => 'موسسه اعتباری نور',
            self::IRAN_VENEZUELA    => 'بانک ایران و ونزوئلا',
        };
    }

    /**
     * Banks dissolved or merged into Sepah (2020–2023). Historic BINs / IBAN
     * codes remain in circulation, but the institution no longer issues new accounts.
     */
    public function isDefunct(): bool
    {
        return match ($this) {
            self::ANSAR,
            self::HEKMAT_IRANIAN,
            self::GHAVAMIN,
            self::MEHR_EGHTESAD,
            self::NOOR,
            self::DEY,
            self::KOSAR => true,
            default     => false,
        };
    }

    /**
     * @return array<string, self>
     */
    protected static function persianAliases(): array
    {
        return [
            'بانک مرکزی ایران' => self::MARKAZI,
            'موسسه کوثر'        => self::KOSAR,
            'موسسه نور'         => self::NOOR,
        ];
    }
}
