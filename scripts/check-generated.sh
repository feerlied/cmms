#!/usr/bin/env bash

set -euo pipefail

raiz="$(cd "$(dirname "$0")/.." && pwd)"
diretorio_temporario="$(mktemp -d)"

trap 'rm -rf -- "$diretorio_temporario"' EXIT

java -jar "$raiz/tools/antlr-4.13.2-complete.jar" \
    -Dlanguage=PHP \
    -visitor \
    -listener \
    -o "$diretorio_temporario" \
    "$raiz/grammar/CMMSLexer.g4" \
    "$raiz/grammar/CMMSParser.g4"

if ! diff -qr "$raiz/src/Generated" "$diretorio_temporario"; then
    echo "Os arquivos em src/Generated estao dessincronizados com a grammar." >&2
    echo "Execute: ./scripts/generate-parser.sh" >&2
    exit 1
fi
