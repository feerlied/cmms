<?php

declare(strict_types=1);

namespace Integration;

use Application\AvaliacaoPreventivaService;
use Application\CadastroService;
use Application\CMMSProcessor;
use Application\ImportacaoLeituraService;
use DateTimeImmutable;
use Domain\CalculadoraPrimeiroVencimentoPreventivo;
use Domain\Enums\Prioridade;
use Domain\GeradorOrdemServicoCorretiva;
use Domain\GeradorOrdemServicoPreventiva;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\RegistroRepository;
use Infrastructure\Persistence\SqliteDatabase;
use PDO;
use PHPUnit\Framework\TestCase;

final class FluxoOrdemServicoPersistenteTest extends TestCase
{
    public function testeFluxoCompleto_OperacoesSeparadasPersistemPlanosLeiturasEOrdensSemDuplicar(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }

        $caminho = tempnam(sys_get_temp_dir(), 'cmms-os-');

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
            self::assertCount(3, $resultado_cadastro->objetos);
            self::assertSame(1, $this->countRows($banco, 'equipamento'));
            self::assertSame(2, $this->countRows($banco, 'manutencao'));
            unset($cadastro, $banco);

            $banco = new SqliteDatabase($caminho);
            $plano_persistido = (new ManutencaoRepository($banco))->findByIdentifier('reparo_01');
            self::assertNotNull($plano_persistido);
            self::assertSame('reparar_bomba', $plano_persistido->procedimento_identificador);
            self::assertSame(Prioridade::ALTA, $plano_persistido->prioridade);
            $importacao = $this->createImportacaoLeituraService($banco);
            $primeira_leitura = $importacao->import($this->readingCode());

            self::assertTrue($primeira_leitura->isSuccess());
            self::assertCount(1, $primeira_leitura->ordens_servico);
            $ordem_corretiva = $primeira_leitura->ordens_servico[0];
            self::assertSame($plano_persistido->procedimento_identificador, $ordem_corretiva->procedimento_identificador);
            self::assertSame($plano_persistido->prioridade, $ordem_corretiva->prioridade);
            self::assertSame($plano_persistido->duracao->valor, $ordem_corretiva->duracao->valor);
            self::assertSame('2026-09-29', $ordem_corretiva->chave_evento);
            self::assertSame(1, $this->countRows($banco, 'registro'));
            self::assertSame(1, $this->countRows($banco, 'ordem_servico'));
            $identificador_corretiva = $banco->connection()
                ->query("SELECT identificador FROM ordem_servico WHERE tipo_evento = 'corretiva'")
                ->fetchColumn();
            unset($importacao, $banco);

            $banco = new SqliteDatabase($caminho);
            $importacao = $this->createImportacaoLeituraService($banco);
            $leitura_reenviada = $importacao->import($this->readingCode());

            self::assertTrue($leitura_reenviada->isSuccess());
            self::assertCount(1, $leitura_reenviada->ordens_servico);
            self::assertSame($ordem_corretiva->chave_evento, $leitura_reenviada->ordens_servico[0]->chave_evento);
            self::assertSame(1, $this->countRows($banco, 'registro'));
            self::assertSame(1, $this->countRows($banco, 'ordem_servico'));
            self::assertSame($identificador_corretiva, $banco->connection()
                ->query("SELECT identificador FROM ordem_servico WHERE tipo_evento = 'corretiva'")
                ->fetchColumn());
            unset($importacao, $banco);

            $banco = new SqliteDatabase($caminho);
            $avaliacao = $this->createAvaliacaoPreventivaService($banco);
            $ordens_preventivas = $avaliacao->evaluate(
                'bomba_01',
                new DateTimeImmutable('2026-09-29 08:30:00-03:00')
            );

            self::assertCount(1, $ordens_preventivas);
            self::assertSame('inspecao_01', $ordens_preventivas[0]->manutencao_identificador);
            self::assertSame('2026-09-29T11:30:00', $ordens_preventivas[0]->chave_evento);
            self::assertSame(2, $this->countRows($banco, 'ordem_servico'));
            $identificador_preventiva = $banco->connection()
                ->query("SELECT identificador FROM ordem_servico WHERE tipo_evento = 'preventiva'")
                ->fetchColumn();
            self::assertNotSame($identificador_corretiva, $identificador_preventiva);
            self::assertSame(['corretiva', 'preventiva'], $banco->connection()
                ->query('SELECT tipo_evento FROM ordem_servico ORDER BY tipo_evento')
                ->fetchAll(PDO::FETCH_COLUMN));
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

    private function createImportacaoLeituraService(SqliteDatabase $banco): ImportacaoLeituraService
    {
        return new ImportacaoLeituraService(
            $banco,
            new CMMSProcessor(),
            new EquipamentoRepository($banco->connection()),
            new ManutencaoRepository($banco),
            new RegistroRepository($banco->connection()),
            new OrdemServicoRepository($banco),
            new GeradorOrdemServicoCorretiva()
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

    private function countRows(SqliteDatabase $banco, string $tabela): int
    {
        return (int)$banco->connection()->query("SELECT COUNT(*) FROM {$tabela}")->fetchColumn();
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

manutencao corretiva reparo_01 {
    equipamento bomba_01
    quando pressao maior 12 bar
    procedimento reparar_bomba
    prioridade alta
    duracao 2 hora
    prazo 1 dia
    homem_hora 2
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

    private function readingCode(): string
    {
        return <<<'DSL'
registro leitura_01 {
    equipamento bomba_01
    data 29/09/2026-09:00
    valores {
        pressao 13 bar
    }
}
DSL;
    }
}
