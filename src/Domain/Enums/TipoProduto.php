<?php

namespace Domain\Enums;

enum TipoProduto: string {
    case AGUA = 'agua';
    case GASOLINA = 'gasolina';
    case DIESEL = 'diesel';

    public static function fromDsl(string $texto): self {
        return match ($texto) {
            'agua', 'água' => self::AGUA,
            'gasolina' => self::GASOLINA,
            'diesel' => self::DIESEL,
            default => throw new \ValueError("Tipo de produto inválido: {$texto}"),
        };
    }
}
