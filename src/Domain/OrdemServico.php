<?php

declare(strict_types=1);

namespace Domain;

use Domain\Enums\Prioridade;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\TipoManutencao;

final readonly class OrdemServico {
    public string $identificador;

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
        public StatusOrdemServico $status = StatusOrdemServico::EM_ABERTO,
        public ?\DateTimeImmutable $data_cancelamento = null,
    ) {
        if (($status === StatusOrdemServico::CANCELADA) !== ($data_cancelamento !== null)) {
            throw new \InvalidArgumentException('A data de cancelamento deve existir somente em uma OS cancelada.');
        }

        $this->identificador = 'os_' . hash('sha256', json_encode([
            $equipamento_identificador,
            $manutencao_identificador,
            $tipo_manutencao->value,
            $chave_evento,
        ], JSON_THROW_ON_ERROR));
    }

    public function transitionTo(
        StatusOrdemServico $novo_status,
        ?\DateTimeImmutable $data_cancelamento = null,
    ): self {
        if ($novo_status === $this->status) {
            return $this;
        }

        $permitida = match ($this->status) {
            StatusOrdemServico::EM_ABERTO => in_array($novo_status, [
                StatusOrdemServico::EM_EXECUCAO,
                StatusOrdemServico::CONCLUIDA,
                StatusOrdemServico::CANCELADA,
            ], true),
            StatusOrdemServico::EM_EXECUCAO => in_array($novo_status, [
                StatusOrdemServico::CONCLUIDA,
                StatusOrdemServico::CANCELADA,
            ], true),
            StatusOrdemServico::CONCLUIDA, StatusOrdemServico::CANCELADA => false,
        };

        if (!$permitida) {
            throw new \InvalidArgumentException('Transição de estado da ordem de serviço não permitida.');
        }

        return new self(
            equipamento_identificador: $this->equipamento_identificador,
            manutencao_identificador: $this->manutencao_identificador,
            tipo_manutencao: $this->tipo_manutencao,
            chave_evento: $this->chave_evento,
            data_referencia: $this->data_referencia,
            procedimento_identificador: $this->procedimento_identificador,
            prioridade: $this->prioridade,
            duracao: $this->duracao,
            homem_hora: $this->homem_hora,
            prazo: $this->prazo,
            status: $novo_status,
            data_cancelamento: $data_cancelamento,
        );
    }
}
