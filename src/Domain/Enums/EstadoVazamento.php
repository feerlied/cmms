<?php

namespace Domain\Enums;

enum EstadoVazamento: string {
    case AUSENTE = 'ausente';
    case LEVE = 'leve';
    case MODERADO = 'moderado';
    case GRAVE = 'grave';

    public static function fromDsl(string $texto): self {
        return self::from($texto);
    }
}
