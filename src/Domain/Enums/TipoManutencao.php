<?php

namespace Domain\Enums;

enum TipoManutencao: string {
    case PREVENTIVA = 'preventiva';
    case CORRETIVA = 'corretiva';

    public static function fromDsl(string $texto): self {
        return self::from($texto);
    }
}
