<?php

declare(strict_types=1);

namespace Domain\Enums;

enum StatusOrdemServico: string {
    case EM_ABERTO = 'em_aberto';
    case EM_EXECUCAO = 'em_execucao';
    case CONCLUIDA = 'concluida';
    case CANCELADA = 'cancelada';
}
