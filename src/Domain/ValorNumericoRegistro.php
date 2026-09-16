<?php

namespace Domain;

use Domain\Enums\UnidadeMedida;
use Domain\Enums\VariavelControlada;


class ValorNumericoRegistro {
    public function __construct(
        public VariavelControlada $variavel,
        public int|float $valor,
        public UnidadeMedida $unidade
    ) {
        if (in_array($variavel, [VariavelControlada::HORAS_OPERACAO, VariavelControlada::OBSERVACAO_VISUAL, VariavelControlada::VAZAMENTO], true)) {
            throw new \InvalidArgumentException('O valor numérico exige uma variável numérica.');
        }
    }
}
