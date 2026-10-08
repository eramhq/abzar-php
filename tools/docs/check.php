<?php

declare(strict_types=1);

// Dependency-free repository check; an alternate root supports isolated fixture tests.
$root = realpath($argv[1] ?? dirname(__DIR__, 2));
if ($root === false) {
    fwrite(STDERR, "Repository root does not exist.\n");
    exit(1);
}
$errors = [];
$missing = [];
$fail = static function (string $message) use (&$errors): void {
    $errors[] = $message;
};
$validId = static fn (mixed $id): bool => is_string($id)
    && preg_match('~^[a-z0-9]+(?:-[a-z0-9]+)*(?:/[a-z0-9]+(?:-[a-z0-9]+)*)*$~D', $id) === 1;
$keys = static function (array $value, array $expected): bool {
    $actual = array_keys($value);
    sort($actual);
    sort($expected);
    return $actual === $expected;
};
$pages = [];
try {
    $path = $root . '/docs/navigation.json';
    if (!is_file($path)) {
        throw new RuntimeException('docs/navigation.json is missing');
    }
    $nav = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($nav) || !$keys($nav, ['schemaVersion', 'defaultLocale', 'locales', 'entry', 'sections'])) {
        throw new RuntimeException('navigation must have exactly the schemaVersion, defaultLocale, locales, entry and sections fields');
    }
    if ($nav['schemaVersion'] !== 1 || $nav['defaultLocale'] !== 'en' || $nav['locales'] !== ['en', 'fa']) {
        $fail('navigation requires schemaVersion 1, defaultLocale en and locales [en, fa]');
    }
    if (!is_array($nav['sections']) || !array_is_list($nav['sections']) || $nav['sections'] === []) {
        throw new RuntimeException('sections must be a non-empty list');
    }
    $sections = [];
    foreach ($nav['sections'] as $section) {
        if (!is_array($section) || !$keys($section, ['id', 'title', 'pages'])) {
            $fail('each section requires exactly id, title and pages');
            continue;
        }
        if (!$validId($section['id']) || str_contains($section['id'], '/') || isset($sections[$section['id']])) {
            $fail('invalid or duplicate section id');
        } else {
            $sections[$section['id']] = true;
        }
        if (!is_array($section['title']) || !$keys($section['title'], ['en', 'fa'])) {
            $fail('section title must contain en and fa');
        } else {
            foreach ($section['title'] as $title) {
                if (!is_string($title) || trim($title) === '') {
                    $fail('section titles must be non-empty strings');
                }
            }
        }
        if (!is_array($section['pages']) || !array_is_list($section['pages']) || $section['pages'] === []) {
            $fail('section pages must be a non-empty list');
            continue;
        }
        foreach ($section['pages'] as $id) {
            if (!$validId($id)) {
                $fail('page IDs must be lowercase English paths without extensions');
                continue;
            }
            if (isset($pages[$id])) {
                $fail("duplicate page ID: {$id}");
            }
            $pages[$id] = true;
            if (!is_file($root . "/docs/en/{$id}.md")) {
                $fail("missing English page: {$id}");
            }
            if (!is_file($root . "/docs/fa/{$id}.md")) {
                $missing[] = $id;
            }
        }
    }
    if (!$validId($nav['entry']) || !isset($pages[$nav['entry']])) {
        $fail('entry must be a listed page ID');
    }
} catch (Throwable $e) {
    $fail('navigation: ' . $e->getMessage());
}

// Only single-line scalar frontmatter is needed by these documentation pages.
$body = static function (string $text): string {
    return preg_replace('/\A---\R[\s\S]*?\R---(?:\R|$)/', '', $text) ?? $text;
};
$withoutFences = static function (string $text): string {
    $out = [];
    $fence = null;
    foreach (explode("\n", $text) as $line) {
        if ($fence !== null) {
            if (preg_match('/^ {0,3}' . preg_quote($fence[0], '/') . '{' . strlen($fence) . ',}\s*$/', $line)) {
                $fence = null;
            }
            continue;
        }
        if (preg_match('/^ {0,3}(`{3,}|~{3,})/', $line, $match)) {
            $fence = $match[1];
        } else {
            $out[] = $line;
        }
    }
    return implode("\n", $out);
};
$anchors = static function (string $text) use ($body, $withoutFences): array {
    $text = $withoutFences($body($text));
    preg_match_all('/^ {0,3}#{1,6}\s+(.+?)\s*#*\s*$/m', $text, $headings);
    $found = [];
    foreach ($headings[1] as $heading) {
        $heading = preg_replace('/\[([^\]]+)\]\([^)]*\)/', '$1', strip_tags($heading)) ?? $heading;
        $slug = mb_strtolower(html_entity_decode($heading, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        $slug = preg_replace('/[^\p{L}\p{M}\p{N}\p{Pc}\p{Pd}\s]/u', '', $slug) ?? '';
        $slug = str_replace(' ', '-', $slug);
        $candidate = $slug;
        $n = 0;
        while (isset($found[$candidate])) {
            $candidate = $slug . '-' . ++$n;
        }
        $found[$candidate] = true;
    }
    return $found;
};
$files = [];
foreach (['en', 'fa'] as $locale) {
    $dir = $root . '/docs/' . $locale;
    if (!is_dir($dir)) {
        continue;
    }
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->isFile() && $file->getExtension() === 'md') {
            $files[$file->getPathname()] = true;
        }
    }
}
foreach ([...glob($root . '/*.md'), $root . '/docs/README.md'] as $file) {
    if (is_file($file)) {
        $files[$file] = false;
    }
}
foreach ($files as $file => $published) {
    $label = substr($file, strlen($root) + 1);
    $text = (string) file_get_contents($file);
    if ($published) {
        if (!preg_match('/\A---\R([\s\S]*?)\R---(?:\R|$)/', $text, $frontmatter)) {
            $fail("{$label}: missing frontmatter");
        } else {
            $metadata = [];
            foreach (explode("\n", $frontmatter[1]) as $line) {
                if (!preg_match('/^(title|description):\s*(.+)$/', $line, $field) || isset($metadata[$field[1]])) {
                    $fail("{$label}: unsupported or duplicate frontmatter field");
                    continue;
                }
                $value = trim($field[2]);
                if (str_starts_with($value, '"')) {
                    try {
                        $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException $e) {
                        $value = null;
                    }
                } elseif (preg_match('/^[\[\]{}&*!|>\x27%@`]|: |\s#|^(?:null|true|false|~)$/i', $value)) {
                    $value = null;
                }
                if (!is_string($value) || trim($value) === '') {
                    $fail("{$label}: {$field[1]} must be a non-empty single-line string");
                }
                $metadata[$field[1]] = $value;
            }
            if (!isset($metadata['title'], $metadata['description'])) {
                $fail("{$label}: title and description are required");
            }
        }
        if (preg_match_all('/^#\s+\S.*$/m', $withoutFences($body($text))) !== 1) {
            $fail("{$label}: exactly one body H1 is required");
        }
    }
    $prose = $withoutFences($body($text));
    $prose = preg_replace('/(`+)(?!`)[\s\S]*?\1(?!`)/', '', $prose) ?? $prose;
    $prose = preg_replace('/<!--[\s\S]*?-->/', '', $prose) ?? $prose;
    $targets = [];
    $definitions = [];
    $refKey = static fn (string $s): string => mb_strtolower(preg_replace('/\s+/u', ' ', trim($s)) ?? $s);
    // Inline destinations allow one nested parenthesis level, optional titles, and <angle paths>.
    $destination = '(?:<[^>\r\n]+>|[^\s()]+(?:\([^\s()]*\)[^\s()]*)*)';
    preg_match_all('~!?\[[^\]\n]*\]\(\s*(' . $destination . ')(?:\s+["\x27][^\r\n]*?["\x27])?\s*\)~u', $prose, $inline);
    $targets = $inline[1];
    preg_match_all('~^ {0,3}\[([^\]]+)\]:\s*(' . $destination . ')(?:\s+.*)?$~mu', $prose, $refs, PREG_SET_ORDER);
    foreach ($refs as $ref) {
        $definitions[$refKey($ref[1])] = $ref[2];
        $targets[] = $ref[2];
    }
    preg_match_all('/!?\[([^\]\n]+)\]\[([^\]\n]*)\]/u', $prose, $uses, PREG_SET_ORDER);
    foreach ($uses as $use) {
        $key = $refKey($use[2] === '' ? $use[1] : $use[2]);
        if (!isset($definitions[$key])) {
            $fail("{$label}: undefined link reference [{$key}]");
        }
    }
    preg_match_all('/\b(?:src|href)\s*=\s*["\x27]([^"\x27]+)["\x27]/i', $prose, $html);
    array_push($targets, ...$html[1]);
    foreach (array_unique($targets) as $target) {
        $target = html_entity_decode(trim($target, '<>'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (preg_match('~^(?:[a-z][a-z0-9+.-]*:|//)~i', $target)) {
            continue;
        }
        $parts = explode('#', $target, 2);
        $relative = rawurldecode(explode('?', $parts[0], 2)[0]);
        $candidate = $relative === '' ? $file : dirname($file) . '/' . $relative;
        $resolved = realpath($candidate);
        $translationFallback = false;
        if ($resolved === false) {
            $segments = [];
            foreach (explode('/', $candidate) as $segment) {
                if ($segment === '..') {
                    array_pop($segments);
                } elseif ($segment !== '' && $segment !== '.') {
                    $segments[] = $segment;
                }
            }
            $normalized = '/' . implode('/', $segments);
            foreach ($missing as $id) {
                if ($normalized === $root . '/docs/fa/' . $id . '.md') {
                    $resolved = realpath($root . '/docs/en/' . $id . '.md');
                    $translationFallback = $resolved !== false;
                    break;
                }
            }
        }
        if ($resolved === false || ($resolved !== $root && !str_starts_with($resolved, $root . '/'))) {
            $fail("{$label}: broken local target {$target}");
            continue;
        }
        if (!$translationFallback && isset($parts[1]) && $parts[1] !== '' && is_file($resolved) && pathinfo($resolved, PATHINFO_EXTENSION) === 'md') {
            $fragment = rawurldecode($parts[1]);
            if (!isset($anchors((string) file_get_contents($resolved))[$fragment])) {
                $fail("{$label}: broken heading fragment {$target}");
            }
        }
    }
}
$missing = array_values(array_unique($missing));
echo 'Missing Persian translations: ' . ($missing === [] ? 'none' : implode(', ', $missing)) . "\n";
foreach ($errors as $error) {
    fwrite(STDERR, "ERROR: {$error}\n");
}
echo sprintf("Checked %d page IDs and %d Markdown files; %d error(s).\n", count($pages), count($files), count($errors));
exit($errors === [] ? 0 : 1);
