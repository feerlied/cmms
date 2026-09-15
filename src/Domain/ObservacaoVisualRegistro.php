<?php

namespace Domain;

use Domain\Enums\EstadoObservacao;

require_once __DIR__ . '/Enums/EstadoObservacao.php';

class ObservacaoVisualRegistro {
    public function __construct(
        public EstadoObservacao $estado
    ) {}
}
