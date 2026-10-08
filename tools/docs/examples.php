<?php

declare(strict_types=1);

$root = dirname(__DIR__, 2);
if (!is_file($root . '/vendor/autoload.php')) {
    fwrite(STDERR, "Run composer install before checking examples.\n");
    exit(1);
}
$files = [$root . '/README.md', $root . '/README.fa.md'];
foreach (['en', 'fa'] as $locale) {
    array_push($files, ...glob($root . '/docs/' . $locale . '/*.md'));
    array_push($files, ...glob($root . '/docs/' . $locale . '/recipes/*.md'));
}
$checked = 0;
$linted = 0;
$errors = [];
foreach ($files as $file) {
    $text = (string) file_get_contents($file);
    preg_match_all('/```php\R([\s\S]*?)```/', $text, $snippets, PREG_SET_ORDER);
    foreach ($snippets as $i => $snippet) {
        $code = trim($snippet[1]);
        if (preg_match('/^(?:public|protected|private) function /', $code)) {
            $code = "final class DocumentationSnippet {\n" . $code . "\n}";
        }
        if (!str_starts_with($code, '<?php')) {
            $code = "<?php\n" . $code;
        }
        $script = tempnam(sys_get_temp_dir(), 'abzar-lint-');
        if ($script === false) {
            throw new RuntimeException('Cannot create temporary snippet');
        }
        $output = $script . '.out';
        $errorOutput = $script . '.err';
        try {
            file_put_contents($script, $code);
            $process = proc_open([PHP_BINARY, '-l', $script], [1 => ['file', $output, 'w'], 2 => ['file', $errorOutput, 'w']], $pipes);
            if (!is_resource($process)) {
                throw new RuntimeException('Cannot start PHP lint');
            }
            if (proc_close($process) !== 0) {
                $errors[] = substr($file, strlen($root) + 1) . ' snippet ' . ($i + 1) . ': ' . file_get_contents($errorOutput);
            }
            $linted++;
        } finally {
            foreach ([$script, $output, $errorOutput] as $temporary) {
                if (is_file($temporary)) {
                    unlink($temporary);
                }
            }
        }
    }
    if (str_contains($file, '/recipes/')) {
        continue;
    }
    preg_match_all('/```php\R(<\?php\R(?:(?!```)[\s\S])*?)```\s*```text\R([\s\S]*?)```/', $text, $examples, PREG_SET_ORDER);
    foreach ($examples as $i => $example) {
        $label = substr($file, strlen($root) + 1) . ' example ' . ($i + 1);
        $script = tempnam(sys_get_temp_dir(), 'abzar-doc-');
        if ($script === false) {
            throw new RuntimeException('Cannot create temporary example');
        }
        $stdoutFile = $script . '.out';
        $stderrFile = $script . '.err';
        try {
            file_put_contents($script, $example[1]);
            $process = proc_open(
                [PHP_BINARY, '-d', 'display_errors=stderr', '-d', 'log_errors=0', '-d', 'error_reporting=-1',
                    '-d', 'max_execution_time=30', '-d', 'auto_prepend_file=' . __DIR__ . '/bootstrap.php', $script],
                [1 => ['file', $stdoutFile, 'w'], 2 => ['file', $stderrFile, 'w']],
                $pipes,
                $root,
            );
            if (!is_resource($process)) {
                throw new RuntimeException('Cannot start PHP');
            }
            $code = proc_close($process);
            $stdout = (string) file_get_contents($stdoutFile);
            $stderr = (string) file_get_contents($stderrFile);
            $checked++;
            if ($code !== 0 || $stderr !== '' || str_replace("\r\n", "\n", $stdout) !== $example[2]) {
                $errors[] = $label . "\nExpected: " . $example[2] . "Actual: " . $stdout . $stderr;
            }
        } finally {
            foreach ([$script, $stdoutFile, $stderrFile] as $temporary) {
                if (is_file($temporary)) {
                    unlink($temporary);
                }
            }
        }
    }
}
foreach ($errors as $error) {
    fwrite(STDERR, "ERROR: {$error}\n");
}
echo "Linted {$linted} PHP snippets.\n";
echo "Executed {$checked} standalone PHP/output examples; " . count($errors) . " error(s).\n";
echo "Not executed: framework recipes (host applications absent), partial reference snippets and random fixture demonstrations.\n";
exit($errors === [] && $checked > 0 ? 0 : 1);
