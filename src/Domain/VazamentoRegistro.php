<?php

namespace Domain;

use Domain\Enums\EstadoVazamento;

require_once __DIR__ . '/Enums/EstadoVazamento.php';

class VazamentoRegistro {
    public function __construct(
        public EstadoVazamento $estado
    ) {}
}
