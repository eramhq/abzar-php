<?php

declare(strict_types=1);

namespace Eram\Abzar\Validation;

/**
 * Category of an Iranian license plate, derived from the Persian letter in the
 * middle slot. Unknown letters resolve to {@see self::OTHER} — many regional
 * variants exist and the table intentionally stays conservative.
 */
enum PlateType: string
{
    case PRIVATE        = 'private';
    case TAXI           = 'taxi';
    case PUBLIC         = 'public';
    case POLICE         = 'police';
    case GOVERNMENT     = 'government';
    case GOVERNMENT_CIV = 'government-civil';
    case AGRICULTURAL   = 'agricultural';
    case RENTAL         = 'rental';
    case DISABLED       = 'disabled';
    case MILITARY       = 'military';
    case DIPLOMATIC     = 'diplomatic';
    case OTHER          = 'other';
}
