<?php

namespace Domain;

use Domain\Enums\EstadoObservacao;


class CondicaoObservacao {
    public function __construct(
        public EstadoObservacao $estado
    ) {}
}
