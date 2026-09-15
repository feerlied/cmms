<?php

namespace Domain;

use Domain\Enums\OperadorLogico;

require_once __DIR__ . '/Enums/OperadorLogico.php';

class OperadorLogicoCollection {
    /** @var list<OperadorLogico> */
    private array $itens = [];

    public function __construct(OperadorLogico ...$itens) {
        $this->itens = $itens;
    }

    public function add(OperadorLogico $operador): void {
        $this->itens[] = $operador;
    }

    /** @return list<OperadorLogico> */
    public function all(): array {
        return $this->itens;
    }
}
