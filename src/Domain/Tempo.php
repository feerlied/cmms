<?php

namespace Domain;

use Domain\Enums\UnidadeTempo;

require_once __DIR__ . '/Enums/UnidadeTempo.php';

class Tempo {
    public function __construct(
        public int|float $valor,
        public UnidadeTempo $unidade
    ) {}
}
