<?php

namespace Domain\Enums;

enum UnidadeMedida: string {
    case MM_POR_S = 'mm/s';
    case CELSIUS = 'celsius';
    case BAR = 'bar';
    case M3_POR_HORA = 'm3/h';
    case HORA = 'hora';
    case MINUTO = 'minuto';
    case DIA = 'dia';
    case RPM = 'rpm';
    case LITRO = 'litro';

    public static function fromDsl(string $texto): self {
        return match ($texto) {
            'mm_por_seg', 'mm_seg', 'mm por seg', 'mm seg', 'mm/s', 'mm/seg' => self::MM_POR_S,
            'celsius', 'C' => self::CELSIUS,
            'bar' => self::BAR,
            'm3_por_hora', 'm3_hora', 'm3 por hora', 'm3 hora', 'm3/h', 'm3/hora' => self::M3_POR_HORA,
            'hora' => self::HORA,
            'minuto' => self::MINUTO,
            'dia' => self::DIA,
            'rpm' => self::RPM,
            'litro' => self::LITRO,
            default => throw new \ValueError("Unidade de medida inválida: {$texto}"),
        };
    }
}
