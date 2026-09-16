<?php

namespace Domain;

use Domain\Enums\UnidadeTempo;


class Tempo {
    public function __construct(
        public int|float $valor,
        public UnidadeTempo $unidade
    ) {}
}
