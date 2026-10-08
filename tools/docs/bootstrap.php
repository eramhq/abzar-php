<?php

declare(strict_types=1);

// Old development-only autoload files emit PHP 8.4+ deprecations before an
// example starts. Suppress only those during Composer bootstrap, then restore
// full reporting for the unchanged documentation example and library calls.
$docsErrorLevel = error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
try {
    require dirname(__DIR__, 2) . '/vendor/autoload.php';
} finally {
    error_reporting($docsErrorLevel);
    unset($docsErrorLevel);
}
