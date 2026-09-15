<?php

namespace Domain;

use Domain\Enums\StatusRegistro;

require_once __DIR__ . '/Tempo.php';
require_once __DIR__ . '/Enums/StatusRegistro.php';

class ExecucaoRegistro {
    public function __construct(
        public string $origem,
        public Tempo $tempo_execucao,
        public StatusRegistro $status
    ) {}
}
