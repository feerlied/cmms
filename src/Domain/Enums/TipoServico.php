<?php

namespace Domain\Enums;

enum TipoServico: string {
    case BOMBEAMENTO_AGUA = 'bombeamento_de_agua';
    case BOMBEAMENTO_GASOLINA = 'bombeamento_de_gasolina';
    case BOMBEAMENTO_DIESEL = 'bombeamento_de_diesel';
    case RESFRIAMENTO_GASOLINA = 'resfriamento_de_gasolina';
    case ARMAZENAMENTO_GASOLINA = 'armazenamento_de_gasolina';

    public static function fromDsl(string $texto): self {
        return match ($texto) {
            'bombeamento_de_agua', 'bombeamento_agua', 'bombeamento_de_água', 'bombeamento_água',
            'bombeamento de agua', 'bombeamento agua', 'bombeamento de água', 'bombeamento água' => self::BOMBEAMENTO_AGUA,
            'bombeamento_de_gasolina', 'bombeamento_gasolina', 'bombeamento de gasolina', 'bombeamento gasolina' => self::BOMBEAMENTO_GASOLINA,
            'bombeamento_de_diesel', 'bombeamento_diesel', 'bombeamento de diesel', 'bombeamento diesel' => self::BOMBEAMENTO_DIESEL,
            'resfriamento_de_gasolina', 'resfriamento_gasolina', 'resfriamento de gasolina', 'resfriamento gasolina' => self::RESFRIAMENTO_GASOLINA,
            'armazenamento_de_gasolina', 'armazenamento_gasolina', 'armazenamento de gasolina', 'armazenamento gasolina' => self::ARMAZENAMENTO_GASOLINA,
            default => throw new \ValueError("Tipo de serviço inválido: {$texto}"),
        };
    }
}
