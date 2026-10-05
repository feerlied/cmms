<?php

declare(strict_types=1);

use Application\CMMSProcessor;
use Application\ImportacaoExecucaoService;
use Domain\CaracteristicaProcesso;
use Domain\CaracteristicaProcessoCollection;
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
use Domain\VariavelControladaCollection;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ExecucaoOrdemServicoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\RegistroRepository;
use Infrastructure\Persistence\SqliteDatabase;
use PHPUnit\Framework\TestCase;

final class ImportacaoExecucaoServiceTest extends TestCase {
    private SqliteDatabase $banco;
    private OrdemServicoRepository $ordens_servico;
    private ImportacaoExecucaoService $servico;
    private string $ordem_identificador;

    protected function setUp(): void {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }

        $this->banco = new SqliteDatabase(':memory:');
        $this->banco->initializeSchema();
        $equipamentos = new EquipamentoRepository($this->banco->connection());
        $manutencoes = new ManutencaoRepository($this->banco);
        $registros = new RegistroRepository($this->banco->connection());
        $this->ordens_servico = new OrdemServicoRepository($this->banco);
        $vinculos = new ExecucaoOrdemServicoRepository($this->banco->connection());
        $equipamentos->save(new Equipamento(
            'bomba_01',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            TipoServico::BOMBEAMENTO_AGUA,
            TipoProduto::AGUA,
            new CaracteristicaProcessoCollection(new CaracteristicaProcesso(
                VariavelControlada::PRESSAO,
                10,
                UnidadeMedida::BAR
            )),
            new VariavelControladaCollection(VariavelControlada::PRESSAO)
        ), new DateTimeImmutable('2026-10-01 08:00:00-03:00'));
        $manutencoes->save(new Manutencao(
            'corr_bomba_01',
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
        $ordem = new OrdemServico(
            'bomba_01',
            'corr_bomba_01',
            TipoManutencao::CORRETIVA,
            '2026-10-10',
            new DateTimeImmutable('2026-10-10 08:00:00-03:00'),
            'reparar_bomba',
            Prioridade::ALTA,
            new Tempo(2, UnidadeTempo::HORA),
            2,
            new Tempo(1, UnidadeTempo::DIA)
        );
        $this->ordens_servico->save($ordem);
        $this->ordem_identificador = $ordem->identificador;
        $this->servico = new ImportacaoExecucaoService(
            $this->banco,
            new CMMSProcessor(),
            $equipamentos,
            $manutencoes,
            $registros,
            $this->ordens_servico,
            $vinculos
        );
    }

    public function testeImport_StatusEmAberto_MantemOSAbertaEPersisteVinculo(): void {
        $resultado = $this->servico->import($this->executionCode('execucao_01', 'em_aberto'));

        self::assertTrue($resultado->isSuccess());
        self::assertSame($this->ordem_identificador, $resultado->ordem_servico->identificador);
        self::assertSame(StatusOrdemServico::EM_ABERTO, $resultado->ordem_servico->status);
        self::assertSame($this->ordem_identificador, $this->linkedOrder('execucao_01'));
        self::assertSame(1, $this->countRows('registro'));
        self::assertSame(1, $this->countRows('execucao_ordem_servico'));
    }

    public function testeImport_StatusEmAbertoDepoisDeEmExecucao_NaoReabreOS(): void {
        $this->servico->import($this->executionCode('execucao_01', 'em_execucao'));

        $resultado = $this->servico->import($this->executionCode('execucao_02', 'em_aberto'));

        self::assertTrue($resultado->isSuccess());
        self::assertSame(StatusOrdemServico::EM_EXECUCAO, $resultado->ordem_servico->status);
        self::assertSame($this->ordem_identificador, $this->linkedOrder('execucao_02'));
    }

    public function testeImport_EmExecucaoEConclusao_ReenvioIdenticoNaoDuplica(): void {
        $codigo_execucao = $this->executionCode('execucao_01', 'em_execucao');
        $primeira = $this->servico->import($codigo_execucao);
        $reenvio = $this->servico->import($codigo_execucao);

        self::assertTrue($primeira->isSuccess());
        self::assertTrue($reenvio->isSuccess());
        self::assertSame(StatusOrdemServico::EM_EXECUCAO, $primeira->ordem_servico->status);
        self::assertSame($primeira->ordem_servico->identificador, $reenvio->ordem_servico->identificador);
        self::assertSame(1, $this->countRows('registro'));
        self::assertSame(1, $this->countRows('execucao_ordem_servico'));

        $conclusao = $this->servico->import($this->executionCode('execucao_02', 'concluido'));
        self::assertTrue($conclusao->isSuccess());
        self::assertSame(StatusOrdemServico::CONCLUIDA, $conclusao->ordem_servico->status);
        self::assertNull($this->ordens_servico->findActiveByPair('bomba_01', 'corr_bomba_01'));
        self::assertSame($this->ordem_identificador, $this->linkedOrder('execucao_02'));
        self::assertSame(2, $this->countRows('registro'));
        self::assertSame(2, $this->countRows('execucao_ordem_servico'));
        self::assertSame(StatusOrdemServico::CONCLUIDA, $this->servico
            ->import($this->executionCode('execucao_02', 'concluido'))->ordem_servico->status);
        self::assertSame(2, $this->countRows('registro'));
    }

    public function testeImport_Cancelamento_RegistraDataEEncerraOS(): void {
        $resultado = $this->servico->import($this->executionCode('execucao_01', 'cancelado'));

        self::assertTrue($resultado->isSuccess());
        self::assertSame(StatusOrdemServico::CANCELADA, $resultado->ordem_servico->status);
        self::assertSame('2026-10-10', $resultado->ordem_servico->data_cancelamento->format('Y-m-d'));
        self::assertNull($this->ordens_servico->findActiveByPair('bomba_01', 'corr_bomba_01'));
        self::assertSame(1, $this->countRows('execucao_ordem_servico'));
    }

    public function testeImport_RegistroComMesmoNomeEConteudoDiferente_RetornaDiagnosticoSemEscrita(): void {
        $this->servico->import($this->executionCode('execucao_01', 'em_aberto', 11));

        $resultado = $this->servico->import($this->executionCode('execucao_01', 'em_aberto', 12));

        self::assertFalse($resultado->isSuccess());
        self::assertSame('CMMS-EXE-002', $resultado->processamento->diagnosticos[0]->codigo);
        self::assertSame(1, $this->countRows('registro'));
        self::assertSame(1, $this->countRows('execucao_ordem_servico'));
    }

    public function testeImport_OrigemInexistente_NaoGravaRegistro(): void {
        $resultado = $this->servico->import($this->executionCode('execucao_01', 'concluido', 11, 'outra_manutencao'));

        self::assertFalse($resultado->isSuccess());
        self::assertSame('CMMS-EXE-004', $resultado->processamento->diagnosticos[0]->codigo);
        self::assertSame(0, $this->countRows('registro'));
        self::assertSame(0, $this->countRows('execucao_ordem_servico'));
    }

    public function testeImport_OrigemDeOutroEquipamento_NaoAssociaOS(): void {
        (new EquipamentoRepository($this->banco->connection()))->save(new Equipamento(
            'bomba_02',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            TipoServico::BOMBEAMENTO_AGUA,
            TipoProduto::AGUA
        ), new DateTimeImmutable('2026-10-01'));
        (new ManutencaoRepository($this->banco))->save(new Manutencao(
            'corr_bomba_02',
            TipoManutencao::CORRETIVA,
            'bomba_02',
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

        $resultado = $this->servico->import($this->executionCode(
            'execucao_01', 'concluido', 11, 'corr_bomba_02'
        ));

        self::assertFalse($resultado->isSuccess());
        self::assertSame('CMMS-EXE-004', $resultado->processamento->diagnosticos[0]->codigo);
        self::assertSame(0, $this->countRows('registro'));
        self::assertSame(0, $this->countRows('execucao_ordem_servico'));
    }

    public function testeImport_SemOSAtivaDepoisDeConclusao_NaoReabreNemGrava(): void {
        $this->servico->import($this->executionCode('execucao_01', 'concluido'));

        $resultado = $this->servico->import($this->executionCode('execucao_02', 'em_execucao'));

        self::assertFalse($resultado->isSuccess());
        self::assertSame('CMMS-EXE-005', $resultado->processamento->diagnosticos[0]->codigo);
        self::assertSame(StatusOrdemServico::CONCLUIDA, $this->ordens_servico
            ->findByIdentifier($this->ordem_identificador)->status);
        self::assertSame(1, $this->countRows('registro'));
    }

    public function testeImport_LeituraSemBlocoDeExecucao_RejeitaFormaDaEntrada(): void {
        $resultado = $this->servico->import(<<<'DSL'
registro leitura_01 {
    equipamento bomba_01
    data 10/10/2026-08:30
    valores {
        pressao 11 bar
    }
}
DSL);

        self::assertFalse($resultado->isSuccess());
        self::assertSame('CMMS-EXE-001', $resultado->processamento->diagnosticos[0]->codigo);
        self::assertSame(0, $this->countRows('registro'));
    }

    private function executionCode(
        string $nome,
        string $status,
        int $pressao = 11,
        string $origem = 'corr_bomba_01'
    ): string {
        return <<<DSL
registro {$nome} {
    equipamento bomba_01
    data 10/10/2026-08:30
    origem {$origem}
    tempo_execucao 1 hora
    status {$status}
    valores {
        pressao {$pressao} bar
    }
}
DSL;
    }

    private function linkedOrder(string $registro_identificador): ?string {
        $comando = $this->banco->connection()->prepare(
            'SELECT os.identificador
             FROM execucao_ordem_servico AS eos
             JOIN registro AS r ON r.id = eos.registro_id
             JOIN ordem_servico AS os ON os.id = eos.ordem_servico_id
             WHERE r.nome = :registro'
        );
        $comando->execute(['registro' => $registro_identificador]);
        $identificador = $comando->fetchColumn();

        return $identificador === false ? null : $identificador;
    }

    private function countRows(string $tabela): int {
        return (int) $this->banco->connection()->query("SELECT COUNT(*) FROM {$tabela}")->fetchColumn();
    }
}
