<?php

namespace Domain;

class CaracteristicaProcesso {
    public function __construct(
        public string $variavel,
        public int|float $valor,
        public string $unidade
    ) {}
}
