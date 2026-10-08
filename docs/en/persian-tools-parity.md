---
title: "Compatibility with persian-tools"
description: "Fixture coverage and deliberate differences from the JavaScript library."
---

# persian-tools parity

abzar covers much of the same ground as the JS library [persian-tools](https://github.com/persian-tools/persian-tools). Its contract tests replay upstream's own test vectors against abzar. This page says which specs are covered, which are not, and every place where abzar deliberately gives a different answer.

The upstream specs are vendored under [`tests/fixtures/persian-tools/`](../../tests/fixtures/persian-tools/), pinned at SHA [`25a2dc9f`](https://github.com/persian-tools/persian-tools/tree/25a2dc9f22444b78bf16f6c48bda6727688e8552). Each vector in the tests cites its `spec.ts:line`.

## Coverage

| Spec | abzar API | Test class |
|---|---|---|
| `verifyIranianNationalId.spec.ts` | `NationalId::validate()` | `PersianToolsContractTest` |
| `verifyCardNumber.spec.ts` | `CardNumber::validate()` | `PersianToolsContractTest` |
| `sheba.spec.ts` | `Iban::validate()`, `Iban::bank()` | `PersianToolsContractTest` |
| `phoneNumber.spec.ts` | `PhoneNumber::validate()`, `operatorEnum()` | `PersianToolsContractTest` |
| `findCapitalByProvince.spec.ts` | `Province::fromPersian()` | `PersianToolsContractTest` |
| `bill.spec.ts` | `BillId::validatePair()` | `PersianToolsContractTest` |
| `verifyIranianLegalId.spec.ts` | `LegalId::validate()` | `LegalIdContractTest` |
| `NumberToWords.spec.ts` | `NumberToWords::convert()`, `OrdinalNumber::toWord()` | `NumberWordsContractTest` |
| `addOrdinalSuffix.spec.ts` | `OrdinalNumber::addSuffix()` | `NumberWordsContractTest` |
| `wordsToNumber.spec.ts` | `WordsToNumber::parse()` | `NumberWordsContractTest` |
| `addCommas.spec.ts` | `NumberFormatter::withSeparators()` | `FormattingContractTest` |
| `removeCommas.spec.ts` | `NumberFormatter::withSeparators($s, '')` | `FormattingContractTest` |
| `digits.spec.ts` | `DigitConverter::toPersian()` / `toEnglish()` / `toArabic()` | `FormattingContractTest` |
| `isPersian.spec.ts` | `Script::isPersian()`, `Script::hasPersian()` | `TextContractTest` |
| `isArabic.spec.ts` | `Script::isArabic()` | `TextContractTest` |
| `toPersianChars.spec.ts` | `CharNormalizer::normalize()` | `TextContractTest` |
| `halfSpace.spec.ts` | `HalfSpaceFixer::fix()` | `TextContractTest` |

Upstream also has tests that throw `TypeError` when a function gets the wrong argument type. abzar's signatures are typed, so those tests have no counterpart.

### Out of scope

- `slugify.spec.ts`: upstream itself skips the suite (`describe.skip`).
- `timeAgo.spec.ts`: its inputs are Jalali date strings. Calendar handling belongs to [`eram/daynum`](https://github.com/eramhq/daynum).
- `wordsToNumber-fuzzy.spec.ts` and `moneyWordsToNumber.spec.ts`: abzar has no fuzzy parser.
- `wordsToNumber.spec.ts` output options (`digits`, `addCommas`) and the `autoConvert*` block: pass the result through `DigitConverter` / `NumberFormatter`, or the input through `CharNormalizer` first.
- Specs not yet lifted: `extractCardNumber`, `getBankNameFromCardNumber`, `getPlaceByIranNationalId`, `numberplate`, `findProvinceFromCoordinate`, `remainingTime`, `removeOrdinalSuffix`, `textAnalyzer`, `URLfix`.

## Divergences

Every deliberate difference is recorded in the test class's `divergences()` registry as `[api, input, upstream result, abzar result, reason]`. Each entry asserts abzar's real output, and also asserts that it still differs from upstream's. A behaviour change on either side therefore fails the suite instead of passing silently.

### Validators

Both of these are pinned in the validator unit tests, not in the registry:

| Input | persian-tools | abzar | Why |
|---|---|---|---|
| `CardNumber` Luhn-valid with an unknown BIN, e.g. `1234567890123452` | invalid | valid with `CARD_NUMBER.UNKNOWN_BIN` warning, `bank: null` | New and co-branded BINs appear faster than any table is updated; rejecting them blocks real cards. |
| `PhoneNumber` `09802002580` (mobile prefix not in the operator table) | invalid | valid with `PHONE_NUMBER.UNKNOWN_OPERATOR` warning, `operator: null` | Same reasoning, for MVNO prefixes. |

The plate city-code table follows upstream's numberplate dataset, with the differences listed in the header of [`src/Data/PlateCodes.php`](../../src/Data/PlateCodes.php).

### Number words

`NumberWordsContractTest::divergences()`.

| Upstream call | persian-tools | abzar | Why |
|---|---|---|---|
| `numberToWords(500443)` | `پانصد هزار و چهار صد و چهل و سه` | `پانصد هزار و چهارصد و چهل و سه` | abzar joins the hundreds (`چهارصد`, `نهصد`), writes `یکصد` for a bare hundred and spells 10¹⁵ `کوادریلیون`. |
| `numberToWords(987654321)` | `نه صد و هشتاد و هفت میلیون و شش صد …` | `نهصد و هشتاد و هفت میلیون و ششصد …` | Same. |
| `numberToWords(9006199254740992)` | `نه کوآدریلیون و شش تریلیون و صد و نود و نه میلیارد …` | `نه کوادریلیون و شش تریلیون و یکصد و نود و نه میلیارد …` | Same. |
| `numberToWords(500443, {ordinal: true})` | `… چهار صد و چهل و سوم` | `… چهارصد و چهل و سوم` | Same. |
| `numberToWords(-30, {ordinal: true})` | `منفی سی اُم` | throws `ORDINAL_NUMBER.NON_POSITIVE` | An ordinal names a position, so `OrdinalNumber::toWord()` rejects n < 1. |
| `numberToWords(-123, {ordinal: true})` | `منفی صد و بیست و سوم` | throws `ORDINAL_NUMBER.NON_POSITIVE` | Same. |
| `addOrdinalSuffix('سی')` | `سی اُم` | `سی‌ام` | Words ending in `ی` take `ام` joined with a ZWNJ, per standard orthography. |

abzar reads both spellings of number words. `WordsToNumber::parse()` accepts every cardinal string upstream produces: split hundreds (`چهار صد`), a bare `صد`, and `کوآدریلیون`. Upstream's alternate table entries `شیش` (6), `چارصد` (400) and `بیلیون` (10⁹) are accepted too.

`wordsToNumber()` is lenient: it ignores non-number words, strips ordinal suffixes, reads digits and returns `0` for text with no number in it. `WordsToNumber::parse()` returns `null` instead of guessing:

| Input | persian-tools | abzar |
|---|---|---|
| `منفی ۳ هزار`, `منفی 3 هزار و 200`, `منفی چهارصد 200` (digits mixed with words) | `-3000`, `-3200`, `-600` | `null` |
| `0`, `-999` (digits only) | `0`, `-999` | `null`; use `(int) DigitConverter::toEnglish($s)` |
| `منفی سه هزارمین`, `منفی سه هزارم`, `منفی سی اُم`, `دهم هزار` (ordinal words) | `-3000`, `-3000`, `-30`, `10000` | `null` |
| `سلام دنیا`, `منفی سلام دنیا` (no number) | `0` | `null` |
| `''` | `''` | `null` |

### Formatting

`FormattingContractTest::divergences()`.

| Upstream call | persian-tools | abzar | Why |
|---|---|---|---|
| `removeCommas('30,000,000')` | `30000000` (number) | `'30000000'` (string) | There is no `removeCommas`. `withSeparators($s, '')` strips the grouping and returns a string, so decimals and values past 2⁵³ stay exact. Cast it if you need a number. |
| `removeCommas('300')` | `300` | `'300'` | Same. |
| `digitsArToFa('۸۹123۴۵')` | `۸۹123۴۵` | `۸۹۱۲۳۴۵` | `DigitConverter` is keyed by target script and folds every other digit set into it. Upstream converts one source script per function. |
| `digitsArToEn('0123۴۵۶789')` | `0123۴۵۶789` | `0123456789` | Same. |
| `digitsEnToFa('٤٥٦')` | `٤٥٦` | `۴۵۶` | Same. |
| `digitsFaToAr('٤٤٤444۴۴۴')` | `٤٤٤444٤٤٤` | `٤٤٤٤٤٤٤٤٤` | Same. |

### Text

`TextContractTest::divergences()`. `Script` and `CharNormalizer` match every vector. All the divergences are in `halfSpace`, where `HalfSpaceFixer` takes a narrower, rule-based approach:

| Input | persian-tools | abzar | Why |
|---|---|---|---|
| `بزرگ تر`, `بزرگ ترین`, `(آبی تر)` | `بزرگتر`, `بزرگترین`, `(آبیتر)` | `بزرگ‌تر`, `بزرگ‌ترین`, `(آبی‌تر)` | `تر` / `ترین` are joined with a ZWNJ, as the Academy of Persian Language recommends. |
| `بی دلیل`, `هم زمان` | `بی‌دلیل`, `هم‌زمان` | unchanged | Only `می` / `نمی` are bound as prefixes. `بی` and `هم` are also free-standing words (`من هم رفتم`), and telling them apart needs a lexicon. |
| `به هر حال`, `به وجود آمد`, `هم چنین گفت`, `این جا است`, `آن که می رود`, `چند سال بعد` | ZWNJ inside each compound | unchanged (apart from `می‌رود`) | abzar applies rules, not a list of fixed compounds, and these are commonly written with a space too. |
| `سلام   دنیا`, `خانه ها `, `خانه ها ، بزرگ تر هستند.` | spaces collapsed, trimmed, space before `،` removed | spaces kept as given | `HalfSpaceFixer` only replaces the space it binds. Whitespace and punctuation spacing are left to the caller. |

The longer sentences in the spec (lines 71, 75, 116 and 127) combine these rules and are in the registry too.

## Refreshing the fixtures

Run `composer fixtures:pull` to re-sync the vendored specs from the SHA pinned in `tools/fixtures/SHA`. Bumping that pin should happen in its own PR: re-run the suite, then update the registries and this page for any change in upstream behaviour. See [`tests/fixtures/persian-tools/README.md`](../../tests/fixtures/persian-tools/README.md).
