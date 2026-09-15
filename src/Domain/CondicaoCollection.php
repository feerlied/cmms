<?php

namespace Domain;

require_once __DIR__ . '/CondicaoNumerica.php';
require_once __DIR__ . '/CondicaoObservacao.php';
require_once __DIR__ . '/CondicaoVazamento.php';

class CondicaoCollection {
    /** @var list<CondicaoNumerica|CondicaoObservacao|CondicaoVazamento> */
    private array $itens = [];

    public function __construct(CondicaoNumerica|CondicaoObservacao|CondicaoVazamento ...$itens) {
        $this->itens = $itens;
    }

    public function add(CondicaoNumerica|CondicaoObservacao|CondicaoVazamento $condicao): void {
        $this->itens[] = $condicao;
    }

    /** @return list<CondicaoNumerica|CondicaoObservacao|CondicaoVazamento> */
    public function all(): array {
        return $this->itens;
    }
}
