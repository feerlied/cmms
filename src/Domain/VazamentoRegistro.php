<?php

namespace Domain;

use Domain\Enums\EstadoVazamento;


class VazamentoRegistro {
    public function __construct(
        public EstadoVazamento $estado
    ) {}
}
