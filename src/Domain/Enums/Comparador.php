<?php

namespace Domain\Enums;

enum Comparador: string {
    case IGUAL = 'igual';
    case DIFERENTE = 'diferente';
    case MAIOR = 'maior';
    case MAIOR_OU_IGUAL = 'maior_ou_igual';
    case MENOR = 'menor';
    case MENOR_OU_IGUAL = 'menor_ou_igual';

    public static function fromDsl(string $texto): self {
        return match ($texto) {
            'igual' => self::IGUAL,
            'diferente' => self::DIFERENTE,
            'maior' => self::MAIOR,
            'maior_ou_igual', 'maior_igual', 'maior ou igual', 'maior igual' => self::MAIOR_OU_IGUAL,
            'menor' => self::MENOR,
            'menor_ou_igual', 'menor_igual', 'menor ou igual', 'menor igual' => self::MENOR_OU_IGUAL,
            default => throw new \ValueError("Comparador inválido: {$texto}"),
        };
    }
}
