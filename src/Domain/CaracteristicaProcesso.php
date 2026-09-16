<?php

namespace Domain;

use Domain\Enums\UnidadeMedida;
use Domain\Enums\VariavelControlada;

class CaracteristicaProcesso {
    public function __construct(
        public VariavelControlada $variavel,
        public int|float $valor,
        public UnidadeMedida $unidade
    ) {
        if (in_array($variavel, [VariavelControlada::HORAS_OPERACAO, VariavelControlada::OBSERVACAO_VISUAL, VariavelControlada::VAZAMENTO], true)) {
            throw new \InvalidArgumentException('A característica de processo exige uma variável numérica.');
        }
    }
}
