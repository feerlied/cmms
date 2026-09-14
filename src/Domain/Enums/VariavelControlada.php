<?php

namespace Domain\Enums;

enum VariavelControlada: string {
    case VAZAO = 'vazao';
    case PRESSAO = 'pressao';
    case PRESSAO_DESCARGA = 'pressao_descarga';
    case PRESSAO_SUCCAO = 'pressao_succao';
    case VIBRACAO = 'vibracao';
    case TEMPERATURA = 'temperatura';
    case ROTACAO = 'rotacao';
    case HORAS_OPERACAO = 'horas_operacao';
    case OBSERVACAO_VISUAL = 'observacao_visual';
    case VAZAMENTO = 'vazamento';

    public static function fromDsl(string $texto): self {
        return match ($texto) {
            'vazao', 'vazão' => self::VAZAO,
            'pressao', 'pressão' => self::PRESSAO,
            'pressao_descarga', 'pressao_de_descarga', 'pressao descarga', 'pressao de descarga',
            'pressão_descarga', 'pressão_de_descarga', 'pressão descarga', 'pressão de descarga' => self::PRESSAO_DESCARGA,
            'pressao_succao', 'pressao_de_succao', 'pressao succao', 'pressao de succao',
            'pressão_succao', 'pressão_de_succao', 'pressão succao', 'pressão de succao',
            'pressao_succão', 'pressao_de_succão', 'pressao succão', 'pressao de succão',
            'pressão_succão', 'pressão_de_succão', 'pressão succão', 'pressão de succão',
            'pressao_sucçao', 'pressao_de_sucçao', 'pressao sucçao', 'pressao de sucçao',
            'pressao_sucção', 'pressao_de_sucção', 'pressao sucção', 'pressao de sucção',
            'pressão_sucçao', 'pressão_de_sucçao', 'pressão sucçao', 'pressão de sucçao',
            'pressão_sucção', 'pressão_de_sucção', 'pressão sucção', 'pressão de sucção' => self::PRESSAO_SUCCAO,
            'vibracao', 'vibraçao', 'vibracão', 'vibração' => self::VIBRACAO,
            'temperatura' => self::TEMPERATURA,
            'rotacao', 'rotaçao', 'rotacão', 'rotação' => self::ROTACAO,
            'horas_operacao', 'horas operacao', 'horas_operacão', 'horas operacão',
            'horas_operaçao', 'horas operaçao', 'horas_operação', 'horas operação' => self::HORAS_OPERACAO,
            'observacao_visual', 'observacao visual' => self::OBSERVACAO_VISUAL,
            'vazamento' => self::VAZAMENTO,
            default => throw new \ValueError("Variável controlada inválida: {$texto}"),
        };
    }
}
