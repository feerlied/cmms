<?php

namespace Domain\Enums;

enum TipoEquipamento: string {
    case BOMBA_CENTRIFUGA = 'bomba_centrifuga';
    case BOMBA_ALTERNATIVA = 'bomba_alternativa';
    case TROCADOR_CALOR = 'trocador_de_calor';
    case TANQUE_GASOLINA = 'tanque_de_gasolina';

    public static function fromDsl(string $texto): self {
        return match ($texto) {
            'bomba_centrifuga', 'bomba_centrífuga', 'bomba centrífuga', 'bomba centrifuga' => self::BOMBA_CENTRIFUGA,
            'bomba_alternativa', 'bomba alternativa' => self::BOMBA_ALTERNATIVA,
            'trocador_de_calor', 'trocador_calor', 'trocador de calor', 'trocador calor' => self::TROCADOR_CALOR,
            'tanque_de_gasolina', 'tanque_gasolina', 'tanque de gasolina', 'tanque gasolina' => self::TANQUE_GASOLINA,
            default => throw new \ValueError("Tipo de equipamento inválido: {$texto}"),
        };
    }
}
