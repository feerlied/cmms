<?php

namespace Domain\Enums;

enum UnidadeTempo: string {

    case HORA = 'hora';
    case MINUTO = 'minuto';
    case DIA = 'dia';

    public static function fromDsl(string $texto): self {
        return self::from($texto);
    }
}
