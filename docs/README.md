# Documentation maintenance and importer contract

This is a repository maintenance note, not a public navigation page. Public documentation lives in [en](en/overview.md) and [fa](fa/overview.md); the authoritative ordered manifest is [navigation.json](navigation.json).

## Authoring

- Keep one main home for each topic: `overview.md` for orientation, `validation.md` for validator usage, `error-handling.md` for handling failures, `error-codes.md` for exact messages, and `api-stability.md` for compatibility policy. Link to the relevant guide instead of duplicating its tutorial. Root READMEs keep the requested short installation and quick-start examples.
- Preserve existing lowercase English page paths. Both locales use the same relative paths. The legacy `en/README.md` and `fa/README.md` indexes remain as GitHub compatibility links, outside navigation.
- Each public page has YAML frontmatter with non-empty `title` and `description`, plus exactly one body H1. Use double-quoted single-line strings (JSON string syntax is valid YAML) for predictable escaping. The checker also accepts simple unquoted scalars; no multiline YAML or custom tags.
- Use plain Markdown and relative links. Store shared images in `docs/assets/`; link from a top-level locale page with `../assets/name.png` and from `recipes/` with `../../assets/name.png`. There are no images yet; `.gitkeep` reserves the shared folder.
- Write natural Persian, use familiar developer terms when clearer, preserve half-spaces, and avoid diacritics or visible ezafe in authored prose. Code, identifiers and literal library/upstream output must remain exact, even when they contain those marks.
- Update both translations when behavior documentation changes. Do not copy English prose into a Persian page as a placeholder. Missing Persian pages are reported by the checker, never fabricated.
- A complete runnable example starts with `<?php`, loads `vendor/autoload.php`, and is immediately followed by a `text` fence containing exact stdout. Keep these examples deterministic. Reference snippets use comments for return values. Framework recipes explicitly state their host requirements.

## Checks

```bash
composer docs:check
composer docs:examples
composer test
```

`docs:check` uses only PHP and scans all Markdown under both locale folders, these maintenance notes, and the root Markdown files. It validates the manifest, metadata, one H1, local file/image targets, reference-style links and local Markdown heading fragments. Remote URLs are not fetched. A link to a listed but missing Persian translation is reported through the translation report and resolves to its existing English counterpart for checking; its translated fragment is not checked against English headings. Other missing local targets fail. Plain Markdown destinations with optional titles, angle destinations and reference definitions are supported; use simple heading links rather than custom HTML anchors. Raw HTML `src`/`href` targets are also checked. Fenced examples and inline code are ignored when checking links.

`docs:examples` syntax-checks every PHP fence (wrapping class-method fragments in a dummy class), then executes complete standalone PHP/output pairs from both languages and the root READMEs in isolated PHP subprocesses. It requires Composer autoloading and checks the exact printed output. Its bootstrap suppresses deprecations only while loading existing Composer development dependencies, then restores full error reporting before running the unchanged example. Subprocess output goes to temporary files to avoid pipe-buffer deadlocks. It does not execute framework recipes, partial reference snippets, random fixture demonstrations or arbitrary code fences. The PHPUnit documentation tests additionally check the checker failure modes and error-code table completeness.

## Release source policy

The public Eram website must import only **published releases**, using the **exact commit resolved from the release tag** (peel annotated tags). A branch head, an unpublished tag, or a draft release is not a documentation source. Beta prereleases remain labeled beta; importing one does not imply stable API guarantees.

Before a future release, include both locale trees, `docs/navigation.json` and all referenced `docs/assets/` files in the commit being tagged. Run the checks on that commit, not just a later branch. Neither `.gitattributes` nor Composer archive exclusions exclude `docs/`; keep it that way. The repository's existing tag workflow runs the documentation checks as part of its gate. This task adds no cross-repository publishing automation.

## Importer assumptions

1. Read schema version 1; `defaultLocale` is `en`, locales are `en` and `fa`, and `entry` is a listed page ID. Reject unsupported schemas. Section titles are localized; page titles come from frontmatter.
2. IDs are unique paths relative to each locale folder without `.md`. Navigation order and groups are authoritative. Do not auto-publish maintenance notes, compatibility README indexes, plans or files merely discovered in the repository.
3. Load English for every listed ID. If a Persian file is missing, load that English page and show exactly: «این صفحه هنوز به فارسی ترجمه نشده است. متن انگلیسی را می‌خوانید.» The repository checker reports missing translations without failing. Existing translations must have valid metadata and links. A stale translation still requires human review; presence is not evidence of freshness.
4. Keep the body H1 for GitHub readability and avoid duplicating it with the frontmatter title on the website. Render Persian pages RTL, with code and exact outputs displayed appropriately.
5. Resolve relative links from the original file path. Convert links to listed documentation into routes for the selected release. During fallback, resolve the English body's links against its English source, then apply locale fallback per target. A fragment from a missing translation may have no English counterpart; route to the fallback page top in that case. Assets come from the same release commit.
6. Links outside published pages (for example `src/`, fixtures, root `UPGRADE.md` or `CHANGELOG.md`) must resolve to that release commit on GitHub, not nonexistent website routes or the default branch. Preserve fragments and distinguish directory links from page IDs.

Do not publish from the working tree. No release, tag, commit or push is performed by the documentation checks.
