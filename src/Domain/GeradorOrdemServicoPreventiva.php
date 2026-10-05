<?php

declare(strict_types=1);

namespace Domain;

use DateTimeZone;
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

        $vencimento_utc = $primeiro_vencimento->setTimezone(new DateTimeZone('UTC'));
        $vencimento_utc = $vencimento_utc->setTime(
            (int) $vencimento_utc->format('H'),
            (int) $vencimento_utc->format('i'),
            (int) $vencimento_utc->format('s'),
        );

        $minuto_atual = intdiv($agora->getTimestamp(), 60);
        $minuto_vencimento = intdiv($vencimento_utc->getTimestamp(), 60);

        if ($minuto_atual < $minuto_vencimento) {
            return null;
        }

        return new OrdemServico(
            equipamento_identificador: $manutencao->equipamento_identificador,
            manutencao_identificador: $manutencao->nome,
            tipo_manutencao: TipoManutencao::PREVENTIVA,
            chave_evento: $vencimento_utc->format('Y-m-d\\TH:i:s'),
            data_referencia: $vencimento_utc,
            procedimento_identificador: $manutencao->procedimento_identificador,
            prioridade: $manutencao->prioridade,
            duracao: $manutencao->duracao,
            homem_hora: $manutencao->homem_hora,
            prazo: $manutencao->prazo,
        );
    }
}
