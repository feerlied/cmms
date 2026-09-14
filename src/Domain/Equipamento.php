<?php

namespace Domain;

class Equipamento
{
    public function __construct(
        public string $nome,
        public string $tipo,
        public string $servico,
        public string $produto,
        public array  $caracteristicas_processo = [],
        public array  $variaveis_controladas = []
    )
    {
    }
}
