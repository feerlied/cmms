<?php

declare(strict_types=1);

namespace Application;

use Domain\OrdemServico;

final readonly class ImportacaoLeituraResult {
    /** @param list<OrdemServico> $ordens_servico */
    public function __construct(
        public ProcessingResult $processamento,
        public array $ordens_servico,
    ) {}

    public function isSuccess(): bool {
        return $this->processamento->isSuccess();
    }
}
