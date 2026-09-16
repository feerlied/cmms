<?php

namespace Domain;

use Domain\Enums\EstadoVazamento;


class CondicaoVazamento {
    public function __construct(
        public EstadoVazamento $estado
    ) {}
}
