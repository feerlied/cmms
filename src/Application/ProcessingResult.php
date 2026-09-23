<?php

declare(strict_types=1);

namespace Application;

use Diagnostic\Diagnostic;
use Diagnostic\DiagnosticSeverity;

final readonly class ProcessingResult {
    /**
     * @param list<\Domain\Equipamento|\Domain\Manutencao|\Domain\Registro> $objetos
     * @param list<Diagnostic> $diagnosticos
     */
    public function __construct(
        public array $objetos,
        public array $diagnosticos,
    ) {}

    public function isSuccess(): bool {
        foreach ($this->diagnosticos as $diagnostico) {
            if ($diagnostico->severidade === DiagnosticSeverity::ERROR) {
                return false;
            }
        }

        return true;
    }
}
