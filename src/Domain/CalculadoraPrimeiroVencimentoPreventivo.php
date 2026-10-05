<?php

declare(strict_types=1);

namespace Domain;

use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeTempo;

final class CalculadoraPrimeiroVencimentoPreventivo {
    public function calculate(
        Manutencao $manutencao,
        \DateTimeImmutable $primeiro_cadastro,
        int $ocorrencia = 1,
    ): \DateTimeImmutable {
        if ($manutencao->tipo !== TipoManutencao::PREVENTIVA
            || !$manutencao->gatilho instanceof Tempo) {
            throw new \InvalidArgumentException('O cálculo de vencimento exige uma manutenção preventiva.');
        }

        if ($ocorrencia < 1) {
            throw new \InvalidArgumentException('A ocorrência preventiva deve ser maior que zero.');
        }

        $minutos_periodo = $this->convertToMinutes($manutencao->gatilho);

        if ($minutos_periodo > intdiv(PHP_INT_MAX, $ocorrencia)) {
            throw new \InvalidArgumentException(
                'O vencimento preventivo não pode ser representado como duração em minutos.'
            );
        }

        $minutos = $minutos_periodo * $ocorrencia;

        return $primeiro_cadastro->add(new \DateInterval("PT{$minutos}M"));
    }

    private function convertToMinutes(Tempo $periodo): int {
        if ($periodo->valor <= 0) {
            throw new \InvalidArgumentException('O período preventivo deve ser maior que zero.');
        }

        $minutos_por_unidade = match ($periodo->unidade) {
            UnidadeTempo::MINUTO => 1,
            UnidadeTempo::HORA => 60,
            UnidadeTempo::DIA => 1_440,
        };
        [$digitos, $escala] = $this->convertToDecimalDigits($periodo->valor);
        $minutos = $this->multiplyByInteger($digitos, $minutos_por_unidade);

        if ($escala > 0) {
            $minutos = str_pad($minutos, $escala + 1, '0', STR_PAD_LEFT);
            $fracao = substr($minutos, -$escala);

            if (trim($fracao, '0') !== '') {
                throw new \InvalidArgumentException(
                    'O período preventivo não pode ser representado com precisão de minuto.'
                );
            }

            $minutos = substr($minutos, 0, -$escala);
        }

        $minutos = ltrim($minutos, '0');

        if ($minutos === '' || $this->isGreaterThanIntMax($minutos)) {
            throw new \InvalidArgumentException(
                'O período preventivo não pode ser representado como duração em minutos.'
            );
        }

        return (int) $minutos;
    }

    /**
     * @return array{string, int}
     */
    private function convertToDecimalDigits(int|float $valor): array {
        $texto = is_int($valor)
            ? (string) $valor
            : json_encode($valor, JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);

        if (!preg_match('/^(\d+)(?:\.(\d+))?(?:e([+-]?\d+))?$/i', $texto, $partes)) {
            throw new \InvalidArgumentException('O período preventivo deve ser numérico e finito.');
        }

        $digitos = ltrim($partes[1] . ($partes[2] ?? ''), '0');
        $escala = strlen($partes[2] ?? '') - (int) ($partes[3] ?? 0);

        if ($escala < 0) {
            $digitos .= str_repeat('0', -$escala);
            $escala = 0;
        }

        return [$digitos === '' ? '0' : $digitos, $escala];
    }

    private function multiplyByInteger(string $numero, int $multiplicador): string {
        $resultado = '';
        $transporte = 0;

        for ($indice = strlen($numero) - 1; $indice >= 0; $indice--) {
            $produto = ((int) $numero[$indice] * $multiplicador) + $transporte;
            $resultado .= (string) ($produto % 10);
            $transporte = intdiv($produto, 10);
        }

        while ($transporte > 0) {
            $resultado .= (string) ($transporte % 10);
            $transporte = intdiv($transporte, 10);
        }

        return strrev($resultado);
    }

    private function isGreaterThanIntMax(string $numero): bool {
        $maximo = (string) PHP_INT_MAX;

        return strlen($numero) > strlen($maximo)
            || (strlen($numero) === strlen($maximo) && strcmp($numero, $maximo) > 0);
    }
}
