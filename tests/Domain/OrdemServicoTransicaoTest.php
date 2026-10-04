<?php

declare(strict_types=1);

namespace Domain;

use DateTimeImmutable;
use Domain\Enums\Prioridade;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeTempo;
use Domain\OrdemServico;
use Domain\Tempo;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class OrdemServicoTransicaoTest extends TestCase
{
    public function testeTransitionTo_CaminhoAprovado_PreservaIdentidadeEDadosDoPlano(): void
    {
        $aberta = $this->createOrder();
        $em_execucao = $aberta->transitionTo(StatusOrdemServico::EM_EXECUCAO);
        $concluida = $em_execucao->transitionTo(StatusOrdemServico::CONCLUIDA);

        self::assertSame(StatusOrdemServico::EM_ABERTO, $aberta->status);
        self::assertSame(StatusOrdemServico::EM_EXECUCAO, $em_execucao->status);
        self::assertSame(StatusOrdemServico::CONCLUIDA, $concluida->status);
        self::assertSame($aberta->identificador, $em_execucao->identificador);
        self::assertSame($aberta->identificador, $concluida->identificador);
        self::assertSame($aberta->duracao, $concluida->duracao);
        self::assertSame($aberta->prazo, $concluida->prazo);
        self::assertNull($concluida->data_cancelamento);
        self::assertSame($concluida, $concluida->transitionTo(StatusOrdemServico::CONCLUIDA));
    }

    public function testeTransitionTo_AbertaDiretamenteConcluida_AceitaTransicao(): void
    {
        self::assertSame(
            StatusOrdemServico::CONCLUIDA,
            $this->createOrder()->transitionTo(StatusOrdemServico::CONCLUIDA)->status
        );
    }

    public function testeTransitionTo_CancelamentoDeAbertaOuEmExecucao_PreservaPrimeiraData(): void
    {
        $data_cancelamento = new DateTimeImmutable('2026-09-29 10:00:00-03:00');
        $aberta = $this->createOrder();
        $cancelada_aberta = $aberta->transitionTo(StatusOrdemServico::CANCELADA, $data_cancelamento);
        $cancelada_em_execucao = $aberta->transitionTo(StatusOrdemServico::EM_EXECUCAO)
            ->transitionTo(StatusOrdemServico::CANCELADA, $data_cancelamento);

        self::assertSame($data_cancelamento, $cancelada_aberta->data_cancelamento);
        self::assertSame($data_cancelamento, $cancelada_em_execucao->data_cancelamento);
        self::assertSame($aberta->identificador, $cancelada_aberta->identificador);
        self::assertSame($cancelada_aberta, $cancelada_aberta->transitionTo(
            StatusOrdemServico::CANCELADA,
            new DateTimeImmutable('2026-09-30 10:00:00-03:00')
        ));
    }

    public function testeTransitionTo_EstadoFinalNaoReabre_RejeitaTransicao(): void
    {
        $concluida = $this->createOrder()->transitionTo(StatusOrdemServico::CONCLUIDA);

        $this->expectException(InvalidArgumentException::class);
        $concluida->transitionTo(StatusOrdemServico::EM_ABERTO);
    }

    public function testeTransitionTo_ConcluidaNaoPodeSerCancelada_RejeitaTransicao(): void
    {
        $concluida = $this->createOrder()->transitionTo(StatusOrdemServico::CONCLUIDA);

        $this->expectException(InvalidArgumentException::class);
        $concluida->transitionTo(StatusOrdemServico::CANCELADA, new DateTimeImmutable('2026-09-29'));
    }

    public function testeTransitionTo_CancelamentoSemData_RejeitaEstadoIncompleto(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createOrder()->transitionTo(StatusOrdemServico::CANCELADA);
    }

    public function testeConstrutor_MesmoEventoEmInstantesDistintos_GeraIdentidadeEstavel(): void
    {
        $primeira = $this->createOrder();
        $segunda = new OrdemServico(
            equipamento_identificador: 'bomba_01',
            manutencao_identificador: 'reparo_01',
            tipo_manutencao: TipoManutencao::CORRETIVA,
            chave_evento: '2026-09-29',
            data_referencia: new DateTimeImmutable('2026-09-29 23:00:00-03:00'),
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::ALTA,
            duracao: new Tempo(2, UnidadeTempo::HORA),
            homem_hora: 2,
            prazo: new Tempo(1, UnidadeTempo::DIA)
        );

        self::assertSame($primeira->identificador, $segunda->identificador);
    }

    private function createOrder(): OrdemServico
    {
        return new OrdemServico(
            equipamento_identificador: 'bomba_01',
            manutencao_identificador: 'reparo_01',
            tipo_manutencao: TipoManutencao::CORRETIVA,
            chave_evento: '2026-09-29',
            data_referencia: new DateTimeImmutable('2026-09-29 08:00:00-03:00'),
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::ALTA,
            duracao: new Tempo(2, UnidadeTempo::HORA),
            homem_hora: 2,
            prazo: new Tempo(1, UnidadeTempo::DIA)
        );
    }
}
