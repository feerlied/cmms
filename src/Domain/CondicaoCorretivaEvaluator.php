<?php

namespace Domain;

use Domain\Enums\Comparador;
use Domain\Enums\OperadorLogico;


class CondicaoCorretivaEvaluator {
    public function evaluate(CondicaoCorretiva $condicao, Registro $registro): bool {
        if ($registro->execucao !== null) {
            return false;
        }

        $resultados = [];

        foreach ($condicao->getConditions() as $condicao_simples) {
            $resultado = $this->evaluateSimple($condicao_simples, $registro);

            // A ausência de qualquer variável impede o disparo, inclusive em ramos de "ou".
            if ($resultado === null) {
                return false;
            }

            $resultados[] = $resultado;
        }

        $resultado = $resultados[0];
        $operadores = $condicao->getOperators();

        for ($indice = 1; $indice < count($resultados); $indice++) {
            $resultado = $operadores[$indice - 1] === OperadorLogico::E
                ? $resultado && $resultados[$indice]
                : $resultado || $resultados[$indice];
        }

        return $resultado;
    }

    private function evaluateSimple(
        CondicaoNumerica|CondicaoObservacao|CondicaoVazamento $condicao,
        Registro $registro
    ): ?bool {
        foreach ($registro->valores->all() as $valor) {
            if ($condicao instanceof CondicaoNumerica
                && $valor instanceof ValorNumericoRegistro
                && $valor->variavel === $condicao->variavel) {
                if ($valor->unidade !== $condicao->unidade) {
                    return false;
                }

                return match ($condicao->comparador) {
                    Comparador::IGUAL => $valor->valor == $condicao->valor,
                    Comparador::DIFERENTE => $valor->valor != $condicao->valor,
                    Comparador::MAIOR => $valor->valor > $condicao->valor,
                    Comparador::MAIOR_OU_IGUAL => $valor->valor >= $condicao->valor,
                    Comparador::MENOR => $valor->valor < $condicao->valor,
                    Comparador::MENOR_OU_IGUAL => $valor->valor <= $condicao->valor,
                };
            }

            if ($condicao instanceof CondicaoObservacao && $valor instanceof ObservacaoVisualRegistro) {
                return $valor->estado === $condicao->estado;
            }

            if ($condicao instanceof CondicaoVazamento && $valor instanceof VazamentoRegistro) {
                return $valor->estado === $condicao->estado;
            }
        }

        return null;
    }
}
