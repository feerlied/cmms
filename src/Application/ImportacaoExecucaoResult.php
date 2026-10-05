<?php

declare(strict_types=1);

namespace Application;

use Domain\OrdemServico;

final readonly class ImportacaoExecucaoResult {
    public function __construct(
        public ProcessingResult $processamento,
        public ?OrdemServico $ordem_servico,
    ) {}

    public function isSuccess(): bool {
        return $this->processamento->isSuccess();
    }
}
