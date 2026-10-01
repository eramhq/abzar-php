<?php

declare(strict_types=1);

namespace Eram\Abzar\Tests\Unit\Fixtures;

use Eram\Abzar\Text\CharNormalizer;
use Eram\Abzar\Text\HalfSpaceFixer;
use Eram\Abzar\Text\Script;

/**
 * isPersian.spec.ts, isArabic.spec.ts, toPersianChars.spec.ts and halfSpace.spec.ts.
 *
 * | upstream          | abzar                                  |
 * |-------------------|----------------------------------------|
 * | isPersian(s)      | Script::isPersian($s)                  |
 * | hasPersian(s)     | Script::hasPersian($s)                 |
 * | isArabic(s)       | Script::isArabic($s)                   |
 * | toPersianChars(s) | (new CharNormalizer())->normalize($s)  |
 * | halfSpace(s)      | HalfSpaceFixer::fix($s)                |
 */
final class TextContractTest extends PersianToolsFixtureTestCase
{
    /**
     * @dataProvider scriptVectors
     */
    public function test_script_parity(string $upstreamFn, string $input, bool $upstream): void
    {
        $actual = match ($upstreamFn) {
            'isPersian'  => Script::isPersian($input),
            'hasPersian' => Script::hasPersian($input),
            'isArabic'   => Script::isArabic($input),
            default      => throw new \InvalidArgumentException("No abzar counterpart mapped for $upstreamFn"),
        };

        self::assertSame($upstream, $actual);
    }

    /** @return iterable<string, array{string, string, bool}> */
    public static function scriptVectors(): iterable
    {
        $persianQuestion = 'این یک متن فارسی است؟';
        $persianLong     = 'آیا سیستم میتواند گزینه های دیگری را به اشتباه به عنوان متن فارسی تشخیص دهد؟';
        $arabicQuestion  = 'هل هذا نص فارسي؟';
        $arabicLong      = 'أكد رئيس اللجنة العسكرية الممثلة لحكومة الوفاق الوطني في ليبيا أحمد علي أبو شحمة، أن اللجنة لا تستطيع تنفيذ خطتها لإخراج العناصر الأجنبية من أراضي البلاد.';

        yield 'isPersian.spec.ts:7'  => ['isPersian', $persianQuestion, true];
        yield 'isPersian.spec.ts:8'  => ['isPersian', $persianLong, true];
        yield 'isPersian.spec.ts:10' => ['isPersian', 'Lorem Ipsum Test', false];
        yield 'isPersian.spec.ts:11' => ['isPersian', 'これはペルシア語のテキストですか', false];
        yield 'isPersian.spec.ts:12' => ['isPersian', 'Это персидский текст?', false];
        yield 'isPersian.spec.ts:13' => ['isPersian', '这是波斯文字吗?', false];
        yield 'isPersian.spec.ts:14' => ['isPersian', $arabicQuestion, false];
        yield 'isPersian.spec.ts:15' => ['isPersian', $arabicLong, false];
        yield 'isPersian.spec.ts:20' => ['isPersian', '', false];

        yield 'isPersian.spec.ts:29' => ['hasPersian', $persianQuestion, true];
        yield 'isPersian.spec.ts:30' => ['hasPersian', $arabicQuestion, true];
        yield 'isPersian.spec.ts:31' => ['hasPersian', $persianLong, true];
        yield 'isPersian.spec.ts:32' => ['hasPersian', 'This text includes فارسی', true];
        yield 'isPersian.spec.ts:33' => ['hasPersian', 'Это персидский س текст?', true];
        yield 'isPersian.spec.ts:34' => ['hasPersian', 'أكد رئيس اللجنة العسكرية الممثلة لحكومة الوفاق أراضي البلاد.', true];
        yield 'isPersian.spec.ts:36' => ['hasPersian', 'Lorem Ipsum Test', false];
        yield 'isPersian.spec.ts:37' => ['hasPersian', 'これはペルシア語のテキストですか', false];
        yield 'isPersian.spec.ts:38' => ['hasPersian', 'Это персидский текст?', false];
        yield 'isPersian.spec.ts:39' => ['hasPersian', '这是波斯文字吗?', false];
        yield 'isPersian.spec.ts:40' => ['hasPersian', '', false];

        yield 'isArabic.spec.ts:5'  => ['isArabic', $persianQuestion, false];
        yield 'isArabic.spec.ts:6'  => ['isArabic', $persianLong, false];
        yield 'isArabic.spec.ts:7'  => ['isArabic', 'Lorem Ipsum Test', false];
        yield 'isArabic.spec.ts:8'  => ['isArabic', 'これはペルシア語のテキストですか', false];
        yield 'isArabic.spec.ts:9'  => ['isArabic', 'Это персидский текст?', false];
        yield 'isArabic.spec.ts:10' => ['isArabic', '这是波斯文字吗?', false];
        yield 'isArabic.spec.ts:11' => ['isArabic', $arabicQuestion, true];
        yield 'isArabic.spec.ts:12' => ['isArabic', $arabicLong, true];
        yield 'isArabic.spec.ts:17' => ['isArabic', '', false];
    }

    /**
     * @dataProvider normalizedIsPersianVectors
     */
    public function test_normalized_text_is_persian(string $input): void
    {
        // isPersian.spec.ts:23-26 (Bug#208): isPersian(toPersianChars(s)).
        self::assertTrue(Script::isPersian((new CharNormalizer())->normalize($input)));
    }

    /** @return iterable<string, array{string}> */
    public static function normalizedIsPersianVectors(): iterable
    {
        yield 'isPersian.spec.ts:24' => ['مهدی'];
        yield 'isPersian.spec.ts:25' => ['شاه'];
    }

    /**
     * @dataProvider toPersianCharsVectors
     */
    public function test_to_persian_chars_parity(string $input, string $upstream): void
    {
        self::assertSame($upstream, (new CharNormalizer())->normalize($input));
    }

    /** @return iterable<string, array{string, string}> */
    public static function toPersianCharsVectors(): iterable
    {
        yield 'toPersianChars.spec.ts:5' => ['علي', 'علی'];
        yield 'toPersianChars.spec.ts:6' => ['تلفن همراه', 'تلفن همراه'];
        // Upstream asserts a falsy result.
        yield 'toPersianChars.spec.ts:7' => ['', ''];
    }

    /**
     * @dataProvider halfSpaceVectors
     */
    public function test_half_space_parity(string $input, string $upstream): void
    {
        self::assertSame($upstream, HalfSpaceFixer::fix($input));
    }

    /** @return iterable<string, array{string, string}> */
    public static function halfSpaceVectors(): iterable
    {
        yield 'halfSpace.spec.ts:7'   => ['', ''];
        yield 'halfSpace.spec.ts:11'  => ['سلام', 'سلام'];
        yield 'halfSpace.spec.ts:19'  => ['می رود', "می\u{200C}رود"];
        yield 'halfSpace.spec.ts:23'  => ['نمی دانم', "نمی\u{200C}دانم"];
        yield 'halfSpace.spec.ts:35'  => ['خانه ها', "خانه\u{200C}ها"];
        yield 'halfSpace.spec.ts:47'  => ['می رود و نمی خواهد', "می\u{200C}رود و نمی\u{200C}خواهد"];
        yield 'halfSpace.spec.ts:55'  => ['سلام دنیا', 'سلام دنیا'];
        yield 'halfSpace.spec.ts:79'  => ['می   رود', "می\u{200C}رود"];
        yield 'halfSpace.spec.ts:87'  => ['Hello World', 'Hello World'];
        yield 'halfSpace.spec.ts:91'  => ['خانه test', 'خانه test'];
        yield 'halfSpace.spec.ts:95'  => ['خانه ها خانه ها خانه ها', "خانه\u{200C}ها خانه\u{200C}ها خانه\u{200C}ها"];
        yield 'halfSpace.spec.ts:123' => ['هادی', 'هادی'];
        yield 'halfSpace.spec.ts:134' => ['میرود', 'میرود'];
        // :137-143 — idempotent on its own output.
        yield 'halfSpace.spec.ts:141' => ["می\u{200C}رود", "می\u{200C}رود"];
        yield 'halfSpace.spec.ts:146' => ['خانه ها، آپارتمان ها', "خانه\u{200C}ها، آپارتمان\u{200C}ها"];
        yield 'halfSpace.spec.ts:154' => ['درخت ها', "درخت\u{200C}ها"];
    }

    /**
     * @dataProvider divergences
     */
    public function test_divergence(string $api, string $input, mixed $upstream, mixed $abzar, string $reason): void
    {
        self::assertSame('halfSpace', $api);
        self::assertDivergence($upstream, $abzar, HalfSpaceFixer::fix($input), $reason);
    }

    /** @return iterable<string, array{string, string, string, string, string}> */
    public static function divergences(): iterable
    {
        $tar = 'تر / ترین are joined with a ZWNJ (بزرگ‌تر), as the Academy of Persian Language recommends; '
            . 'upstream drops the space entirely (بزرگتر).';
        $prefixes = 'Only می / نمی are bound as prefixes. بی and هم are also free-standing words '
            . '(«من هم رفتم»), and telling them apart needs a lexicon.';
        $compounds = 'abzar applies rules, not a list of fixed compounds (به‌هر، به‌وجود، این‌جا، آن‌که، چند‌سال); '
            . 'those are commonly written with a space too.';
        $whitespace = 'HalfSpaceFixer only replaces the space it binds; collapsing runs of spaces, trimming and '
            . 'punctuation spacing are left to the caller.';

        yield 'halfSpace.spec.ts:15'  => ['halfSpace', 'سلام   دنیا', 'سلام دنیا', 'سلام   دنیا', $whitespace];
        yield 'halfSpace.spec.ts:27'  => ['halfSpace', 'بی دلیل', "بی\u{200C}دلیل", 'بی دلیل', $prefixes];
        yield 'halfSpace.spec.ts:31'  => ['halfSpace', 'هم زمان', "هم\u{200C}زمان", 'هم زمان', $prefixes];
        yield 'halfSpace.spec.ts:39'  => ['halfSpace', 'بزرگ تر', 'بزرگتر', "بزرگ\u{200C}تر", $tar];
        yield 'halfSpace.spec.ts:43'  => ['halfSpace', 'بزرگ ترین', 'بزرگترین', "بزرگ\u{200C}ترین", $tar];
        yield 'halfSpace.spec.ts:51'  => [
            'halfSpace', 'خانه ها بزرگ تر شدند',
            "خانه\u{200C}ها بزرگتر شدند",
            "خانه\u{200C}ها بزرگ\u{200C}تر شدند",
            $tar,
        ];
        yield 'halfSpace.spec.ts:59'  => ['halfSpace', 'به هر حال', "به\u{200C}هر حال", 'به هر حال', $compounds];
        yield 'halfSpace.spec.ts:63'  => ['halfSpace', 'به وجود آمد', "به\u{200C}وجود آمد", 'به وجود آمد', $compounds];
        yield 'halfSpace.spec.ts:67'  => ['halfSpace', 'هم چنین گفت', "هم\u{200C}چنین گفت", 'هم چنین گفت', $compounds];
        yield 'halfSpace.spec.ts:71'  => [
            'halfSpace', 'به هر حال خانه ها بزرگ تر شدند',
            "به\u{200C}هر حال خانه\u{200C}ها بزرگتر شدند",
            "به هر حال خانه\u{200C}ها بزرگ\u{200C}تر شدند",
            $compounds . ' ' . $tar,
        ];
        yield 'halfSpace.spec.ts:75'  => [
            'halfSpace', 'می تواند به هر حال کوچک تر بماند',
            "می\u{200C}تواند به\u{200C}هر حال کوچکتر بماند",
            "می\u{200C}تواند به هر حال کوچک\u{200C}تر بماند",
            $compounds . ' ' . $tar,
        ];
        yield 'halfSpace.spec.ts:83'  => [
            'halfSpace', 'خانه ها ، بزرگ تر هستند.',
            "خانه\u{200C}ها، بزرگتر هستند.",
            "خانه\u{200C}ها ، بزرگ\u{200C}تر هستند.",
            $whitespace . ' ' . $tar,
        ];
        yield 'halfSpace.spec.ts:99'  => ['halfSpace', 'خانه ها ', "خانه\u{200C}ها", "خانه\u{200C}ها ", $whitespace];
        yield 'halfSpace.spec.ts:103' => ['halfSpace', 'این جا است', "این\u{200C}جا است", 'این جا است', $compounds];
        yield 'halfSpace.spec.ts:107' => ['halfSpace', 'آن که می رود', "آن\u{200C}که می\u{200C}رود", "آن که می\u{200C}رود", $compounds];
        yield 'halfSpace.spec.ts:111' => ['halfSpace', 'چند سال بعد', "چند\u{200C}سال بعد", 'چند سال بعد', $compounds];
        yield 'halfSpace.spec.ts:116' => [
            'halfSpace', 'نمی دانم به هر حال بزرگ تر خواهد شد',
            "نمی\u{200C}دانم به\u{200C}هر حال بزرگتر خواهد شد",
            "نمی\u{200C}دانم به هر حال بزرگ\u{200C}تر خواهد شد",
            $compounds . ' ' . $tar,
        ];
        yield 'halfSpace.spec.ts:127' => [
            'halfSpace', 'می رود و نمی خواهد خانه ها را به هر شکل بزرگ تر از این جا کند',
            "می\u{200C}رود و نمی\u{200C}خواهد خانه\u{200C}ها را به\u{200C}هر شکل بزرگتر از این\u{200C}جا کند",
            "می\u{200C}رود و نمی\u{200C}خواهد خانه\u{200C}ها را به هر شکل بزرگ\u{200C}تر از این جا کند",
            $compounds . ' ' . $tar,
        ];
        yield 'halfSpace.spec.ts:150' => ['halfSpace', '(آبی تر)', '(آبیتر)', "(آبی\u{200C}تر)", $tar];
    }
}
