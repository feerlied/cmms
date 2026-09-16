<?php

namespace Domain;

use Domain\Enums\StatusRegistro;


class ExecucaoRegistro {
    public function __construct(
        public string $origem,
        public Tempo $tempo_execucao,
        public StatusRegistro $status
    ) {}
}
