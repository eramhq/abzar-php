---
title: "Related projects"
description: "Companion and upstream projects outside Abzar’s own API."
---

# Related projects

Abzar covers Persian utilities, not calendars or framework adapters. The links below lead to separate projects; their versions and requirements are independent.

- [`eram/daynum`](https://github.com/eramhq/daynum) is suggested in Abzar's `composer.json` for Jalali calendar utilities. It is not bundled or required.
- [`eramhq/persian-kit`](https://github.com/eramhq/persian-kit) is a separate WordPress integration project. This repository does not establish its current features or compatibility; check that project's documentation before adopting it.
- [persian-tools](https://github.com/persian-tools/persian-tools) supplies upstream fixture vectors and some MIT-licensed algorithms/data. Abzar is not a drop-in port: see [parity and differences](persian-tools-parity.md).
- [nikapps/iran-validator](https://github.com/nikapps/iran-validator) is another PHP validator project. Check its own documentation for supported versions and features.

For adapters owned by your application, use [framework integration](framework-integration.md) or the [WordPress recipe](recipes/wordpress.md). See [installation](installation.md) for Abzar's own requirements.
