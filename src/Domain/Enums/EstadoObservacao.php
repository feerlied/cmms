<?php

namespace Domain\Enums;

enum EstadoObservacao: string {
    case NORMAL = 'normal';
    case ANORMAL = 'anormal';

    public static function fromDsl(string $texto): self {
        return self::from($texto);
    }
}
