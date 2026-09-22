<?php

declare(strict_types=1);

namespace Diagnostic;

final readonly class DiagnosticRange {
    public function __construct(
        public DiagnosticPosition $inicio,
        public DiagnosticPosition $fim,
    ) {
        if ($this->isAfter($inicio, $fim)) {
            throw new \InvalidArgumentException('O fim do range não pode anteceder o início.');
        }
    }

    private function isAfter(DiagnosticPosition $primeira_posicao, DiagnosticPosition $segunda_posicao): bool {
        return $primeira_posicao->linha > $segunda_posicao->linha
            || ($primeira_posicao->linha === $segunda_posicao->linha
                && $primeira_posicao->coluna > $segunda_posicao->coluna);
    }
}
