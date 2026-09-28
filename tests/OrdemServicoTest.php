<?php

declare(strict_types=1);

use Domain\Enums\Prioridade;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeTempo;
use Domain\OrdemServico;
use Domain\Tempo;
use PHPUnit\Framework\TestCase;

final class OrdemServicoTest extends TestCase {
    public function testeConstrutor_ManutencaoCorretiva_PreservaDadosDoPlanoEIniciaEmAberto(): void {
        $duracao = new Tempo(2, UnidadeTempo::HORA);
        $prazo = new Tempo(1, UnidadeTempo::DIA);
        $data_referencia = new DateTimeImmutable('2026-09-10 08:30');

        $ordem_servico = new OrdemServico(
            equipamento_identificador: 'bomba_cr10',
            manutencao_identificador: 'reparar_bomba_cr10',
            tipo_manutencao: TipoManutencao::CORRETIVA,
            chave_evento: 'bomba_cr10:reparar_bomba_cr10:2026-09-10',
            data_referencia: $data_referencia,
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::ALTA,
            duracao: $duracao,
            homem_hora: 2,
            prazo: $prazo,
        );

        self::assertSame('bomba_cr10', $ordem_servico->equipamento_identificador);
        self::assertSame('reparar_bomba_cr10', $ordem_servico->manutencao_identificador);
        self::assertSame(TipoManutencao::CORRETIVA, $ordem_servico->tipo_manutencao);
        self::assertSame('bomba_cr10:reparar_bomba_cr10:2026-09-10', $ordem_servico->chave_evento);
        self::assertSame($data_referencia, $ordem_servico->data_referencia);
        self::assertSame('reparar_bomba', $ordem_servico->procedimento_identificador);
        self::assertSame(Prioridade::ALTA, $ordem_servico->prioridade);
        self::assertSame($duracao, $ordem_servico->duracao);
        self::assertSame(2, $ordem_servico->homem_hora);
        self::assertSame($prazo, $ordem_servico->prazo);
        self::assertSame(StatusOrdemServico::EM_ABERTO, $ordem_servico->status);
    }

    public function testeConstrutor_ManutencaoPreventiva_AceitaPrazoAusente(): void {
        $ordem_servico = new OrdemServico(
            equipamento_identificador: 'bomba_cr10',
            manutencao_identificador: 'inspecao_bomba_cr10',
            tipo_manutencao: TipoManutencao::PREVENTIVA,
            chave_evento: 'bomba_cr10:inspecao_bomba_cr10:2026-10-10',
            data_referencia: new DateTimeImmutable('2026-10-10 00:00'),
            procedimento_identificador: 'inspecionar_bomba',
            prioridade: Prioridade::MEDIA,
            duracao: new Tempo(1, UnidadeTempo::HORA),
            homem_hora: 1.5,
        );

        self::assertSame(TipoManutencao::PREVENTIVA, $ordem_servico->tipo_manutencao);
        self::assertNull($ordem_servico->prazo);
        self::assertSame(StatusOrdemServico::EM_ABERTO, $ordem_servico->status);
    }

    public function testeStatusOrdemServico_CasosDefinidos_ExpoeValoresPersistiveis(): void {
        self::assertSame('em_aberto', StatusOrdemServico::EM_ABERTO->value);
        self::assertSame('em_execucao', StatusOrdemServico::EM_EXECUCAO->value);
        self::assertSame('concluida', StatusOrdemServico::CONCLUIDA->value);
        self::assertSame('cancelada', StatusOrdemServico::CANCELADA->value);
    }
}
