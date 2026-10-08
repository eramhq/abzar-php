<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Docs;

use PHPUnit\Framework\TestCase;

final class DocumentationCheckTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        $this->root = sys_get_temp_dir() . '/abzar-doc-check-' . bin2hex(random_bytes(8));
        mkdir($this->root . '/docs/en', 0o777, true);
        mkdir($this->root . '/docs/fa', 0o777, true);
        $this->navigation(['overview']);
        file_put_contents($this->root . '/docs/en/overview.md', "---\ntitle: Overview\ndescription: A guide\n---\n\n# Overview\n");
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        rmdir($this->root);
    }

    public function test_missing_translation_is_reported_without_failure(): void
    {
        $this->append("\n[فارسی](../fa/overview.md)\n");
        [$status, $output] = $this->check();
        self::assertSame(0, $status, $output);
        self::assertStringContainsString('Missing Persian translations: overview', $output);
    }

    public function test_duplicate_ids_fail_across_sections(): void
    {
        $nav = json_decode((string) file_get_contents($this->root . '/docs/navigation.json'), true, 512, JSON_THROW_ON_ERROR);
        $nav['sections'][] = ['id' => 'more', 'title' => ['en' => 'More', 'fa' => 'بیشتر'], 'pages' => ['overview']];
        file_put_contents($this->root . '/docs/navigation.json', json_encode($nav, JSON_THROW_ON_ERROR));
        [$status, $output] = $this->check();
        self::assertSame(1, $status);
        self::assertStringContainsString('duplicate page ID: overview', $output);
    }

    public function test_invalid_navigation_and_missing_english_fail(): void
    {
        $this->navigation(['missing']);
        [$status, $output] = $this->check();
        self::assertSame(1, $status);
        self::assertStringContainsString('missing English page: missing', $output);
        self::assertStringContainsString('entry must be a listed page ID', $output);
        file_put_contents($this->root . '/docs/navigation.json', '{broken');
        self::assertSame(1, $this->check()[0]);
    }

    public function test_wrong_schema_and_unsafe_ids_fail(): void
    {
        $this->navigation(['../escape', 'README', 'overview.md']);
        self::assertSame(1, $this->check()[0]);
        $this->navigation(['overview']);
        $path = $this->root . '/docs/navigation.json';
        $nav = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        $nav['schemaVersion'] = 2;
        file_put_contents($path, json_encode($nav, JSON_THROW_ON_ERROR));
        self::assertSame(1, $this->check()[0]);
    }

    public function test_existing_translation_requires_metadata_and_one_h1(): void
    {
        file_put_contents($this->root . '/docs/fa/overview.md', "# معرفی\n# دوم\n");
        [$status, $output] = $this->check();
        self::assertSame(1, $status);
        self::assertStringContainsString('missing frontmatter', $output);
        self::assertStringContainsString('exactly one body H1', $output);
        file_put_contents($this->root . '/docs/fa/overview.md', "---\ntitle: \"\"\ndescription: []\n---\n# معرفی\n");
        self::assertSame(1, $this->check()[0]);
    }

    public function test_broken_links_images_references_and_fragments_fail(): void
    {
        $this->append("\n[Missing](absent.md)\n![Image](../assets/missing.png)\n[Heading](#absent)\n[Reference][unknown]\n<img src=\"../assets/other.png\">\n");
        [$status, $output] = $this->check();
        self::assertSame(1, $status);
        foreach (['absent.md', 'missing.png', 'broken heading fragment', 'undefined link reference', 'other.png'] as $message) {
            self::assertStringContainsString($message, $output);
        }
    }

    public function test_relative_assets_reference_links_and_duplicate_unicode_headings_resolve(): void
    {
        mkdir($this->root . '/docs/assets');
        file_put_contents($this->root . '/docs/assets/image (1).png', 'fixture');
        $this->append("\n## راهنما\n## راهنما\n[Second](#راهنما-1)\n![Image](<../assets/image%20(1).png> \"Image\")\n[Page][home]\n[home]: overview.md#overview\n");
        [$status, $output] = $this->check();
        self::assertSame(0, $status, $output);
    }

    public function test_example_links_are_ignored_and_missing_reference_targets_fail(): void
    {
        $this->append("\n```text\n[Ignored](missing.md)\n```\n`[Ignored](other.md)`\n");
        self::assertSame(0, $this->check()[0]);
        $this->append("\n[reference]: missing.md\n");
        self::assertSame(1, $this->check()[0]);
    }

    /** @param list<string> $pages */
    private function navigation(array $pages): void
    {
        file_put_contents($this->root . '/docs/navigation.json', json_encode([
            'schemaVersion' => 1,
            'defaultLocale' => 'en',
            'locales' => ['en', 'fa'],
            'entry' => 'overview',
            'sections' => [['id' => 'start', 'title' => ['en' => 'Start', 'fa' => 'شروع'], 'pages' => $pages]],
        ], JSON_THROW_ON_ERROR));
    }

    private function append(string $text): void
    {
        file_put_contents($this->root . '/docs/en/overview.md', $text, FILE_APPEND);
    }

    /** @return array{int, string} */
    private function check(): array
    {
        $stdout = $this->root . '/check.out';
        $stderr = $this->root . '/check.err';
        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/../../../tools/docs/check.php', $this->root],
            [1 => ['file', $stdout, 'w'], 2 => ['file', $stderr, 'w']],
            $pipes,
        );
        self::assertIsResource($process);
        $status = proc_close($process);
        return [$status, (string) file_get_contents($stdout) . (string) file_get_contents($stderr)];
    }
}
