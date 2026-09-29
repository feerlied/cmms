<?php

declare(strict_types=1);

namespace Domain;

use Domain\Enums\TipoManutencao;

final class GeradorOrdemServicoCorretiva {
    public function generate(Manutencao $manutencao, Registro $registro): ?OrdemServico {
        if ($manutencao->tipo !== TipoManutencao::CORRETIVA
            || !$manutencao->gatilho instanceof CondicaoCorretiva
            || $registro->equipamento_identificador !== $manutencao->equipamento_identificador
            || $registro->execucao !== null
            || !(new CondicaoCorretivaEvaluator())->evaluate($manutencao->gatilho, $registro)) {
            return null;
        }

        return new OrdemServico(
            equipamento_identificador: $manutencao->equipamento_identificador,
            manutencao_identificador: $manutencao->nome,
            tipo_manutencao: TipoManutencao::CORRETIVA,
            chave_evento: $registro->data->format('Y-m-d'),
            data_referencia: $registro->data,
            procedimento_identificador: $manutencao->procedimento_identificador,
            prioridade: $manutencao->prioridade,
            duracao: $manutencao->duracao,
            homem_hora: $manutencao->homem_hora,
            prazo: $manutencao->prazo,
        );
    }
}
