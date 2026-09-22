<?php

declare(strict_types=1);

namespace Diagnostic;

final readonly class DiagnosticPosition {
    public function __construct(
        public int $linha,
        public int $coluna,
    ) {
        if ($linha < 0) {
            throw new \InvalidArgumentException('A linha da posição deve ser maior ou igual a zero.');
        }

        if ($coluna < 0) {
            throw new \InvalidArgumentException('A coluna da posição deve ser maior ou igual a zero.');
        }
    }
}
