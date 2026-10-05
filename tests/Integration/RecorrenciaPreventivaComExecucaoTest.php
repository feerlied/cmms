<?php

declare(strict_types=1);

namespace Integration;

use Application\AvaliacaoPreventivaService;
use Application\CadastroService;
use Application\CMMSProcessor;
use Application\ImportacaoExecucaoService;
use DateTimeImmutable;
use Domain\CalculadoraPrimeiroVencimentoPreventivo;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\TipoManutencao;
use Domain\GeradorOrdemServicoPreventiva;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ExecucaoOrdemServicoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\RegistroRepository;
use Infrastructure\Persistence\SqliteDatabase;
use PDO;
use PHPUnit\Framework\TestCase;

final class RecorrenciaPreventivaComExecucaoTest extends TestCase
{
    public function testeFluxoCompleto_ExecucaoConcluidaPermiteProximoVencimentoEPreservaHistorico(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }

        $caminho = tempnam(sys_get_temp_dir(), 'cmms-prev-');

        if ($caminho === false) {
            self::fail('Não foi possível criar o banco SQLite temporário.');
        }

        try {
            $banco = new SqliteDatabase($caminho);
            $banco->initializeSchema();
            $cadastro = $this->createCadastroService($banco);
            $resultado_cadastro = $cadastro->import(
                $this->registrationCode(),
                new DateTimeImmutable('2026-09-28 08:30:00-03:00')
            );
            self::assertTrue($resultado_cadastro->isSuccess());

            $avaliacao = $this->createAvaliacaoPreventivaService($banco);
            $primeira_avaliacao = $avaliacao->evaluate(
                'bomba_01',
                new DateTimeImmutable('2026-09-29 08:30:00-03:00')
            );
            self::assertCount(1, $primeira_avaliacao);
            $primeira_os = $primeira_avaliacao[0];
            self::assertSame(StatusOrdemServico::EM_ABERTO, $primeira_os->status);
            self::assertSame('2026-09-29T11:30:00', $primeira_os->chave_evento);

            $execucao = $this->createImportacaoExecucaoService($banco);
            $resultado_execucao = $execucao->import($this->executionCode());
            self::assertTrue($resultado_execucao->isSuccess());
            self::assertSame($primeira_os->identificador, $resultado_execucao->ordem_servico->identificador);
            self::assertSame(StatusOrdemServico::CONCLUIDA, $resultado_execucao->ordem_servico->status);

            $segunda_avaliacao = $avaliacao->evaluate(
                'bomba_01',
                new DateTimeImmutable('2026-09-30 08:30:00-03:00')
            );
            self::assertCount(1, $segunda_avaliacao);
            $segunda_os = $segunda_avaliacao[0];
            self::assertSame('2026-09-30T11:30:00', $segunda_os->chave_evento);
            self::assertSame(StatusOrdemServico::EM_ABERTO, $segunda_os->status);
            self::assertNotSame($primeira_os->identificador, $segunda_os->identificador);
            self::assertSame(2, $this->countOrders($banco));

            $reavaliacao = $avaliacao->evaluate(
                'bomba_01',
                new DateTimeImmutable('2026-09-30 08:30:00-03:00')
            );
            self::assertCount(1, $reavaliacao);
            self::assertSame($segunda_os->identificador, $reavaliacao[0]->identificador);
            self::assertSame(2, $this->countOrders($banco));
            unset($cadastro, $avaliacao, $execucao, $banco);

            $banco = new SqliteDatabase($caminho);
            $banco->initializeSchema();
            $ordens = new OrdemServicoRepository($banco);
            $primeira_por_id = $ordens->findByIdentifier($primeira_os->identificador);
            $primeira_por_evento = $ordens->findByEvent(
                'bomba_01', 'inspecao_01', TipoManutencao::PREVENTIVA, $primeira_os->chave_evento
            );
            $segunda_por_id = $ordens->findByIdentifier($segunda_os->identificador);
            $segunda_por_evento = $ordens->findByEvent(
                'bomba_01', 'inspecao_01', TipoManutencao::PREVENTIVA, $segunda_os->chave_evento
            );

            self::assertSame($primeira_os->identificador, $primeira_por_id->identificador);
            self::assertSame($primeira_os->identificador, $primeira_por_evento->identificador);
            self::assertSame(StatusOrdemServico::CONCLUIDA, $primeira_por_id->status);
            self::assertSame($segunda_os->identificador, $segunda_por_id->identificador);
            self::assertSame($segunda_os->identificador, $segunda_por_evento->identificador);
            self::assertSame(StatusOrdemServico::EM_ABERTO, $segunda_por_evento->status);
            self::assertSame(2, $this->countOrders($banco));
            self::assertSame($primeira_os->identificador, (new ExecucaoOrdemServicoRepository($banco->connection()))
                ->findOrderIdentifierByRecord('execucao_01'));
            self::assertSame(1, (int)$banco->connection()
                ->query('SELECT COUNT(*) FROM execucao_ordem_servico')->fetchColumn());
        } finally {
            unlink($caminho);
        }
    }

    private function createCadastroService(SqliteDatabase $banco): CadastroService
    {
        return new CadastroService(
            $banco,
            new EquipamentoRepository($banco->connection()),
            new ManutencaoRepository($banco),
            new CMMSProcessor()
        );
    }

    private function createAvaliacaoPreventivaService(SqliteDatabase $banco): AvaliacaoPreventivaService
    {
        return new AvaliacaoPreventivaService(
            $banco,
            new EquipamentoRepository($banco->connection()),
            new ManutencaoRepository($banco),
            new OrdemServicoRepository($banco),
            new CalculadoraPrimeiroVencimentoPreventivo(),
            new GeradorOrdemServicoPreventiva()
        );
    }

    private function createImportacaoExecucaoService(SqliteDatabase $banco): ImportacaoExecucaoService
    {
        return new ImportacaoExecucaoService(
            $banco,
            new CMMSProcessor(),
            new EquipamentoRepository($banco->connection()),
            new ManutencaoRepository($banco),
            new RegistroRepository($banco->connection()),
            new OrdemServicoRepository($banco),
            new ExecucaoOrdemServicoRepository($banco->connection())
        );
    }

    private function countOrders(SqliteDatabase $banco): int
    {
        return (int)$banco->connection()->query('SELECT COUNT(*) FROM ordem_servico')->fetchColumn();
    }

    private function registrationCode(): string
    {
        return <<<'DSL'
equipamento bomba_01 {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        pressao 10 bar
    }
    variaveis_controladas {
        pressao
    }
}

manutencao preventiva inspecao_01 {
    equipamento bomba_01
    a_cada 1 dia
    procedimento inspecionar_bomba
    prioridade media
    duracao 1 hora
    homem_hora 1
}
DSL;
    }

    private function executionCode(): string
    {
        return <<<'DSL'
registro execucao_01 {
    equipamento bomba_01
    data 29/09/2026-09:00
    origem inspecao_01
    tempo_execucao 1 hora
    status concluido
    valores {
        pressao 10 bar
    }
}
DSL;
    }
}
