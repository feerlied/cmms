<?php

namespace Domain;

require_once __DIR__ . '/CaracteristicaProcesso.php';

class CaracteristicaProcessoCollection {
    /** @var list<CaracteristicaProcesso> */
    private array $itens = [];

    public function __construct(CaracteristicaProcesso ...$itens) {
        $this->itens = $itens;
    }

    public function add(CaracteristicaProcesso $item): void {
        $this->itens[] = $item;
    }

    /** @return list<CaracteristicaProcesso> */
    public function all(): array {
        return $this->itens;
    }
}
