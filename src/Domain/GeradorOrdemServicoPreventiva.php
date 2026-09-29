<?php

declare(strict_types=1);

namespace Domain;

use Domain\Enums\TipoManutencao;

final class GeradorOrdemServicoPreventiva {
    public function generate(
        Manutencao $manutencao,
        \DateTimeImmutable $primeiro_vencimento,
        \DateTimeImmutable $agora,
    ): ?OrdemServico {
        if ($manutencao->tipo !== TipoManutencao::PREVENTIVA) {
            return null;
        }

        $minuto_atual = intdiv($agora->getTimestamp(), 60);
        $minuto_vencimento = intdiv($primeiro_vencimento->getTimestamp(), 60);

        if ($minuto_atual < $minuto_vencimento) {
            return null;
        }

        return new OrdemServico(
            equipamento_identificador: $manutencao->equipamento_identificador,
            manutencao_identificador: $manutencao->nome,
            tipo_manutencao: TipoManutencao::PREVENTIVA,
            chave_evento: $primeiro_vencimento->format('Y-m-d\\TH:i:s.uP'),
            data_referencia: $primeiro_vencimento,
            procedimento_identificador: $manutencao->procedimento_identificador,
            prioridade: $manutencao->prioridade,
            duracao: $manutencao->duracao,
            homem_hora: $manutencao->homem_hora,
            prazo: $manutencao->prazo,
        );
    }
}
