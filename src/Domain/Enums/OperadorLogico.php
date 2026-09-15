<?php

namespace Domain\Enums;

enum OperadorLogico: string {
    case E = 'e';
    case OU = 'ou';

    public static function fromDsl(string $texto): self {
        return self::from($texto);
    }
}
