# Benchmarks

Informational benchmarks for the abzar-php hot paths. Runner: [phpbench](https://phpbench.readthedocs.io).

```
composer bench
```

Baselines are **not** enforced as CI hard-fails in 0.3 — runner noise on shared CI is too high (±15%) for a tight regression gate. Publish JSON artifacts per run and compare side-by-side when reviewing data-layout / algorithm changes:

```
vendor/bin/phpbench run --progress=none --report=default --output=json
```

Suggested threshold when triaging a diff: flag any bench that moves by more than 10% on warm runs after repeating the comparison twice. Smaller moves fall within GitHub Actions jitter.

## Scope

- `NationalIdBench` — validator throughput (valid / invalid checksum / wrong length).
- `CharNormalizerBench` — `normalize()` on a ~10KB string, `normalizeContent()` on a ~150KB HTML blob.
- `TimeAgoBench` — short / day / year brackets.
- `DigitConverterBench` — `toPersian` / `toEnglish` on a ~380KB payload.
