#!/usr/bin/env bash
set -euo pipefail

SHA="$(cat "$(dirname "$0")/SHA")"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

git -C "$TMP" init -q
git -C "$TMP" remote add origin https://github.com/persian-tools/persian-tools
git -C "$TMP" fetch --depth 1 -q origin "$SHA"
git -C "$TMP" checkout -q FETCH_HEAD

DEST="$(cd "$(dirname "$0")/../.." && pwd)/tests/fixtures/persian-tools"
mkdir -p "$DEST"
find "$DEST" -mindepth 1 ! -name README.md -exec rm -rf {} +
cp -R "$TMP"/test/. "$DEST"/
cp "$TMP"/LICENSE "$DEST"/LICENSE
echo "$SHA" > "$DEST"/SHA
echo "Pulled persian-tools fixtures at $SHA into $DEST"
