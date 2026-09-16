<?php

namespace Domain;

use Domain\Enums\EstadoObservacao;


class ObservacaoVisualRegistro {
    public function __construct(
        public EstadoObservacao $estado
    ) {}
}
