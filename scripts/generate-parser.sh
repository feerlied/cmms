#!/usr/bin/env bash

set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"

mkdir -p "$ROOT/src/Generated"

java -jar "$ROOT/tools/antlr-4.13.2-complete.jar" \
    -Dlanguage=PHP \
    -visitor \
    -listener \
    -o "$ROOT/src/Generated" \
    "$ROOT/grammar/CMMSLexer.g4" \
    "$ROOT/grammar/CMMSParser.g4"