<?php

declare(strict_types=1);

namespace Application;

use Diagnostic\Diagnostic;

final readonly class ProcessingResult {
    /**
     * @param list<\Domain\Equipamento|\Domain\Manutencao|\Domain\Registro> $objetos
     * @param list<Diagnostic> $diagnosticos
     */
    public function __construct(
        public ProcessingStatus $status,
        public array $objetos,
        public array $diagnosticos,
    ) {}

    public function isSuccess(): bool {
        return $this->status === ProcessingStatus::SUCCESS;
    }
}
