<?php

namespace Domain;

require_once __DIR__ . '/Tempo.php';

class HorasOperacaoRegistro {
    public function __construct(
        public Tempo $horas_operacao
    ) {}
}
