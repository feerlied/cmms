<?php

namespace Domain;

use Domain\Enums\Prioridade;
use Domain\Enums\TipoManutencao;


class Manutencao {
    public function __construct(
        public string $nome,
        public TipoManutencao $tipo,
        public string $equipamento_identificador,
        public Tempo|CondicaoCorretiva $gatilho,
        public string $procedimento_identificador,
        public Prioridade $prioridade,
        public Tempo $duracao,
        public int|float $homem_hora,
        public ?Tempo $prazo = null
    ) {
        if ($tipo === TipoManutencao::PREVENTIVA && (!($gatilho instanceof Tempo) || $prazo !== null)) {
            throw new \InvalidArgumentException('Manutenção preventiva exige gatilho de calendário e não possui prazo.');
        }

        if ($tipo === TipoManutencao::CORRETIVA && (!($gatilho instanceof CondicaoCorretiva) || $prazo === null)) {
            throw new \InvalidArgumentException('Manutenção corretiva exige condição e prazo.');
        }
    }
}
