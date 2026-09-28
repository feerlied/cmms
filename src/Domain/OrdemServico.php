<?php

declare(strict_types=1);

namespace Domain;

use Domain\Enums\Prioridade;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\TipoManutencao;

final readonly class OrdemServico {
    public StatusOrdemServico $status;

    public function __construct(
        public string $equipamento_identificador,
        public string $manutencao_identificador,
        public TipoManutencao $tipo_manutencao,
        public string $chave_evento,
        public \DateTimeImmutable $data_referencia,
        public string $procedimento_identificador,
        public Prioridade $prioridade,
        public Tempo $duracao,
        public int|float $homem_hora,
        public ?Tempo $prazo = null,
    ) {
        $this->status = StatusOrdemServico::EM_ABERTO;
    }
}
