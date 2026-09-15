<?php

namespace Domain;

use Domain\Enums\Comparador;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\VariavelControlada;

require_once __DIR__ . '/Enums/Comparador.php';
require_once __DIR__ . '/Enums/UnidadeMedida.php';
require_once __DIR__ . '/Enums/VariavelControlada.php';

class CondicaoNumerica {
    public function __construct(
        public VariavelControlada $variavel,
        public Comparador $comparador,
        public int|float $valor,
        public UnidadeMedida $unidade
    ) {
        if (in_array($variavel, [VariavelControlada::HORAS_OPERACAO, VariavelControlada::OBSERVACAO_VISUAL, VariavelControlada::VAZAMENTO], true)) {
            throw new \InvalidArgumentException('A condição numérica exige uma variável numérica.');
        }
    }
}
