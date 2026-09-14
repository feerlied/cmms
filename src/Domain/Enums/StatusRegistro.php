<?php

namespace Domain\Enums;

enum StatusRegistro: string {
    case EM_ABERTO = 'em_aberto';
    case EM_EXECUCAO = 'em_execucao';
    case CONCLUIDO = 'concluido';
    case CANCELADO = 'cancelado';

    public static function fromDsl(string $texto): self {
        return match ($texto) {
            'em_aberto', 'em aberto' => self::EM_ABERTO,
            'em_execucao', 'em execucao', 'em_execucão', 'em execucão',
            'em_execuçao', 'em execuçao', 'em_execução', 'em execução' => self::EM_EXECUCAO,
            'concluido', 'concluído' => self::CONCLUIDO,
            'cancelado' => self::CANCELADO,
            default => throw new \ValueError("Status do registro inválido: {$texto}"),
        };
    }
}
