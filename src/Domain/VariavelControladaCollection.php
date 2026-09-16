<?php

namespace Domain;

use Domain\Enums\VariavelControlada;

class VariavelControladaCollection {
    /** @var list<VariavelControlada> */
    private array $itens = [];

    public function __construct(VariavelControlada ...$itens) {
        $this->itens = $itens;
    }

    public function add(VariavelControlada $item): void {
        $this->itens[] = $item;
    }

    /** @return list<VariavelControlada> */
    public function all(): array {
        return $this->itens;
    }
}
