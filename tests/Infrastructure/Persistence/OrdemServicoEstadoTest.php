<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

use DateTimeImmutable;
use Domain\CondicaoCorretiva;
use Domain\CondicaoNumerica;
use Domain\Enums\Comparador;
use Domain\Enums\Prioridade;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoManutencao;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\Manutencao;
use Domain\OrdemServico;
use Domain\Tempo;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\SqliteDatabase;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;

final class OrdemServicoEstadoTest extends TestCase
{
    private SqliteDatabase $banco;
    private OrdemServicoRepository $repositorio;

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }

        $this->banco = new SqliteDatabase(':memory:');
        $this->banco->initializeSchema();
        (new EquipamentoRepository($this->banco->connection()))->save(new Equipamento(
            'bomba_01',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            TipoServico::BOMBEAMENTO_AGUA,
            TipoProduto::AGUA
        ), new DateTimeImmutable('2026-09-28'));
        (new ManutencaoRepository($this->banco))->save(new Manutencao(
            'reparo_01',
            TipoManutencao::CORRETIVA,
            'bomba_01',
            new CondicaoCorretiva(new CondicaoNumerica(
                VariavelControlada::PRESSAO,
                Comparador::MAIOR,
                10,
                UnidadeMedida::BAR
            )),
            'reparar_bomba',
            Prioridade::ALTA,
            new Tempo(2, UnidadeTempo::HORA),
            2,
            new Tempo(1, UnidadeTempo::DIA)
        ));
        $this->repositorio = new OrdemServicoRepository($this->banco);
    }

    public function testeUpdateStatus_EmExecucaoDepoisConcluida_PreservaIdentidadeEConsultaHistorico(): void
    {
        $ordem = $this->createOrder();
        $this->repositorio->save($ordem);

        self::assertEquals($ordem, $this->repositorio->findByIdentifier($ordem->identificador));
        self::assertNull($this->repositorio->findByIdentifier('os_inexistente'));

        $em_execucao = $this->repositorio->updateStatus($ordem->identificador, StatusOrdemServico::EM_EXECUCAO);
        self::assertSame(StatusOrdemServico::EM_EXECUCAO, $em_execucao->status);
        self::assertSame($ordem->identificador, $em_execucao->identificador);
        self::assertSame($ordem->identificador, $this->repositorio
            ->findActiveByPair('bomba_01', 'reparo_01')->identificador);
        self::assertSame($ordem->identificador, $this->repositorio
            ->findOpenByPair('bomba_01', 'reparo_01')->identificador);

        $concluida = $this->repositorio->updateStatus($ordem->identificador, StatusOrdemServico::CONCLUIDA);
        self::assertSame(StatusOrdemServico::CONCLUIDA, $concluida->status);
        self::assertSame($ordem->identificador, $concluida->identificador);
        self::assertNull($this->repositorio->findActiveByPair('bomba_01', 'reparo_01'));
        self::assertSame(StatusOrdemServico::CONCLUIDA, $this->repositorio->findByEvent(
            'bomba_01', 'reparo_01', TipoManutencao::CORRETIVA, '2026-09-29'
        )->status);
        self::assertSame($concluida->identificador, $this->repositorio
            ->updateStatus($ordem->identificador, StatusOrdemServico::CONCLUIDA)->identificador);
        self::assertSame(1, $this->countOrders());
    }

    public function testeUpdateStatus_CancelamentoEReenvio_PreservaDataOriginal(): void
    {
        $ordem = $this->createOrder();
        $this->repositorio->save($ordem);
        $data_cancelamento = new DateTimeImmutable('2026-09-29 10:00:00-03:00');

        $cancelada = $this->repositorio->updateStatus(
            $ordem->identificador,
            StatusOrdemServico::CANCELADA,
            $data_cancelamento
        );
        $reenvio = $this->repositorio->updateStatus(
            $ordem->identificador,
            StatusOrdemServico::CANCELADA,
            new DateTimeImmutable('2026-09-30 10:00:00-03:00')
        );

        self::assertSame(StatusOrdemServico::CANCELADA, $cancelada->status);
        self::assertSame($data_cancelamento->getTimestamp(), $cancelada->data_cancelamento->getTimestamp());
        self::assertEquals($cancelada, $reenvio);
        self::assertSame('2026-09-29 13:00:00', $this->banco->connection()
            ->query('SELECT data_cancelamento FROM ordem_servico')->fetchColumn());
        self::assertNull($this->repositorio->findActiveByPair('bomba_01', 'reparo_01'));
        self::assertSame(1, $this->countOrders());
    }

    public function testeUpdateStatus_OrdemFinalNaoReabre_RejeitaSemAlterarBanco(): void
    {
        $ordem = $this->createOrder();
        $this->repositorio->save($ordem);
        $this->repositorio->updateStatus($ordem->identificador, StatusOrdemServico::CONCLUIDA);

        try {
            $this->repositorio->updateStatus($ordem->identificador, StatusOrdemServico::EM_ABERTO);
            self::fail('Uma OS concluída não deve reabrir.');
        } catch (InvalidArgumentException $erro) {
            self::assertStringContainsString('Transição', $erro->getMessage());
        }

        self::assertSame(StatusOrdemServico::CONCLUIDA, $this->repositorio
            ->findByIdentifier($ordem->identificador)->status);
        self::assertSame(1, $this->countOrders());
    }

    public function testeUpdateStatus_IdentificadorInexistente_RetornaNull(): void
    {
        self::assertNull($this->repositorio->updateStatus('os_inexistente', StatusOrdemServico::CONCLUIDA));
    }

    public function testeHasCancellationOnCivilDay_UsaDiaCivilUtc(): void
    {
        $ordem = $this->createOrder();
        $this->repositorio->save($ordem);
        $this->repositorio->updateStatus(
            $ordem->identificador,
            StatusOrdemServico::CANCELADA,
            new DateTimeImmutable('2026-10-10 23:30:00-03:00')
        );

        self::assertTrue($this->repositorio->hasCancellationOnCivilDay(
            'bomba_01', 'reparo_01', new DateTimeImmutable('2026-10-10 23:59:00-03:00')
        ));
        self::assertFalse($this->repositorio->hasCancellationOnCivilDay(
            'bomba_01', 'reparo_01', new DateTimeImmutable('2026-10-10 20:59:00-03:00')
        ));
        self::assertFalse($this->repositorio->hasCancellationOnCivilDay(
            'bomba_01', 'outra_manutencao', new DateTimeImmutable('2026-10-10 23:59:00-03:00')
        ));
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

    private function countOrders(): int
    {
        return (int)$this->banco->connection()->query('SELECT COUNT(*) FROM ordem_servico')->fetchColumn();
    }
}
