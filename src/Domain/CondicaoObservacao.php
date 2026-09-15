<?php

namespace Domain;

use Domain\Enums\EstadoObservacao;

require_once __DIR__ . '/Enums/EstadoObservacao.php';

class CondicaoObservacao {
    public function __construct(
        public EstadoObservacao $estado
    ) {}
}
