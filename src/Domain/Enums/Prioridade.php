<?php

namespace Domain\Enums;

enum Prioridade: string {
    case BAIXA = 'baixa';
    case MEDIA = 'media';
    case ALTA = 'alta';
    case CRITICA = 'critica';

    public static function fromDsl(string $texto): self {
        return match ($texto) {
            'baixa' => self::BAIXA,
            'media', 'média' => self::MEDIA,
            'alta' => self::ALTA,
            'critica', 'crítica' => self::CRITICA,
            default => throw new \ValueError("Prioridade inválida: {$texto}"),
        };
    }
}
