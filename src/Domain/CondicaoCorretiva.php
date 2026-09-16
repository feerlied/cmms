<?php

namespace Domain;

use Domain\Enums\OperadorLogico;


class CondicaoCorretiva {
    public CondicaoCollection $condicoes;
    public OperadorLogicoCollection $operadores;

    public function __construct(CondicaoNumerica|CondicaoObservacao|CondicaoVazamento $primeira) {
        $this->condicoes = new CondicaoCollection($primeira);
        $this->operadores = new OperadorLogicoCollection();
    }

    public function add(OperadorLogico $operador, CondicaoNumerica|CondicaoObservacao|CondicaoVazamento $condicao): void {
        $this->operadores->add($operador);
        $this->condicoes->add($condicao);
    }

    /** @return list<CondicaoNumerica|CondicaoObservacao|CondicaoVazamento> */
    public function getConditions(): array {
        return $this->condicoes->all();
    }

    /** @return list<OperadorLogico> */
    public function getOperators(): array {
        return $this->operadores->all();
    }
}
