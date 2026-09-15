<?php

namespace Domain;

use Domain\Enums\EstadoVazamento;

require_once __DIR__ . '/Enums/EstadoVazamento.php';

class CondicaoVazamento {
    public function __construct(
        public EstadoVazamento $estado
    ) {}
}
