<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

/**
 * Category of an Iranian license plate, derived from the Persian letter in the
 * middle slot (table in {@see \Eram\Abzar\Data\DataSources::plateLetters()}).
 * {@see self::MILITARY} covers the army (ش), IRGC (ث), defence ministry (ز) and
 * armed-forces HQ (ف) letters. Unknown letters resolve to {@see self::OTHER}.
 */
enum PlateType: string
{
    case PRIVATE        = 'private';
    case TAXI           = 'taxi';
    case PUBLIC         = 'public';
    case POLICE         = 'police';
    case GOVERNMENT     = 'government';
    /** @deprecated since 0.7 — no plate letter maps here; kept for BC. */
    case GOVERNMENT_CIV = 'government-civil';
    case AGRICULTURAL   = 'agricultural';
    /** @deprecated since 0.7 — no plate letter maps here; kept for BC. */
    case RENTAL         = 'rental';
    case DISABLED       = 'disabled';
    case MILITARY       = 'military';
    case DIPLOMATIC     = 'diplomatic';
    case TEMPORARY      = 'temporary';
    case OTHER          = 'other';
}
