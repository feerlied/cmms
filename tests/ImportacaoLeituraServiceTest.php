<?php

declare(strict_types=1);

use Application\CMMSProcessor;
use Application\ImportacaoExecucaoService;
use Application\ImportacaoLeituraService;
use Application\ProcessingStatus;
use Diagnostic\DiagnosticOrigin;
use Domain\CaracteristicaProcesso;
use Domain\CaracteristicaProcessoCollection;
use Domain\CondicaoCorretiva;
use Domain\CondicaoNumerica;
use Domain\Enums\Comparador;
use Domain\Enums\Prioridade;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoManutencao;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\GeradorOrdemServicoCorretiva;
use Domain\Manutencao;
use Domain\Tempo;
use Domain\VariavelControladaCollection;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ExecucaoOrdemServicoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\RegistroRepository;
use Infrastructure\Persistence\SqliteDatabase;
use PHPUnit\Framework\TestCase;

final class ImportacaoLeituraServiceTest extends TestCase {
    private SqliteDatabase $banco;
    private PDO $pdo;
    private ImportacaoLeituraService $servico;

    protected function setUp(): void {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }

        $this->banco = new SqliteDatabase(':memory:');
        $this->banco->initializeSchema();
        $this->pdo = $this->banco->connection();
        $equipamento_repository = new EquipamentoRepository($this->pdo);
        $manutencao_repository = new ManutencaoRepository($this->banco);
        $registro_repository = new RegistroRepository($this->pdo);
        $ordem_servico_repository = new OrdemServicoRepository($this->banco);
        $equipamento_repository->save(
            $this->createEquipment(),
            new DateTimeImmutable('2026-10-01 08:00:00-03:00'),
        );
        $manutencao_repository->save($this->createCorrectiveMaintenance());
        $this->servico = new ImportacaoLeituraService(
            banco: $this->banco,
            processador: new CMMSProcessor(),
            equipamento_repository: $equipamento_repository,
            manutencao_repository: $manutencao_repository,
            registro_repository: $registro_repository,
            ordem_servico_repository: $ordem_servico_repository,
            gerador_corretivo: new GeradorOrdemServicoCorretiva(),
        );
    }

    public function testeImport_LeituraNovaComCondicaoCorretivaVerdadeira_GravaRegistroECriaOrdemServico(): void {
        $resultado = $this->servico->import($this->createReadingDsl('leitura_01', '10/10/2026-08:30', 11));

        self::assertTrue($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SUCCESS, $resultado->processamento->status);
        self::assertCount(1, $resultado->ordens_servico);
        self::assertSame('bomba_01', $resultado->ordens_servico[0]->equipamento_identificador);
        self::assertSame('corr_bomba_01', $resultado->ordens_servico[0]->manutencao_identificador);
        self::assertSame('2026-10-10', $resultado->ordens_servico[0]->chave_evento);
        self::assertSame(1, $this->countRows('registro'));
        self::assertSame(1, $this->countRows('ordem_servico'));
    }

    public function testeImport_LeituraComCondicaoCorretivaFalsa_GravaRegistroSemCriarOrdemServico(): void {
        $resultado = $this->servico->import($this->createReadingDsl('leitura_01', '10/10/2026-08:30', 9));

        self::assertTrue($resultado->isSuccess());
        self::assertSame([], $resultado->ordens_servico);
        self::assertSame(1, $this->countRows('registro'));
        self::assertSame(0, $this->countRows('ordem_servico'));
    }

    public function testeImport_ReenvioIdentico_RecuperaOrdemSemRegravarRegistroOuDuplicarOrdem(): void {
        $codigo = $this->createReadingDsl('leitura_01', '10/10/2026-08:30', 11);
        $primeiro_resultado = $this->servico->import($codigo);
        $segundo_resultado = $this->servico->import($codigo);

        self::assertTrue($primeiro_resultado->isSuccess());
        self::assertTrue($segundo_resultado->isSuccess());
        self::assertCount(1, $segundo_resultado->ordens_servico);
        self::assertSame(
            $primeiro_resultado->ordens_servico[0]->chave_evento,
            $segundo_resultado->ordens_servico[0]->chave_evento,
        );
        self::assertSame(1, $this->countRows('registro'));
        self::assertSame(1, $this->countRows('ordem_servico'));
    }

    public function testeImport_ReenvioIdenticoBloqueadoPorOrdemAberta_RecuperaOrdemImpeditiva(): void {
        $primeira_operacao = $this->servico->import(
            $this->createReadingDsl('leitura_09', '09/10/2026-08:30', 11),
        );
        $codigo = $this->createReadingDsl('leitura_10', '10/10/2026-08:30', 11);
        $primeiro_resultado = $this->servico->import($codigo);
        $segundo_resultado = $this->servico->import($codigo);

        self::assertCount(1, $primeira_operacao->ordens_servico);
        self::assertCount(1, $primeiro_resultado->ordens_servico);
        self::assertCount(1, $segundo_resultado->ordens_servico);
        self::assertSame(
            $primeira_operacao->ordens_servico[0]->chave_evento,
            $primeiro_resultado->ordens_servico[0]->chave_evento,
        );
        self::assertSame(
            $primeiro_resultado->ordens_servico[0]->chave_evento,
            $segundo_resultado->ordens_servico[0]->chave_evento,
        );
        self::assertSame(2, $this->countRows('registro'));
        self::assertSame(1, $this->countRows('ordem_servico'));
    }

    public function testeImport_LeituraAntigaRecebidaDepois_NaoCriaOrdemRetroativa(): void {
        $this->servico->import($this->createReadingDsl('leitura_nova', '10/10/2026-10:00', 11));

        $resultado = $this->servico->import($this->createReadingDsl('leitura_antiga', '10/10/2026-09:00', 11));

        self::assertTrue($resultado->isSuccess());
        self::assertSame([], $resultado->ordens_servico);
        self::assertSame(2, $this->countRows('registro'));
        self::assertSame(1, $this->countRows('ordem_servico'));
    }

    public function testeImport_CancelamentoNoDiaDeOutroEvento_BloqueiaAteODiaSeguinte(): void {
        $primeira = $this->servico->import($this->createReadingDsl('leitura_dia_1', '09/10/2026-08:30', 11));
        self::assertCount(1, $primeira->ordens_servico);
        $ordem_cancelada = $primeira->ordens_servico[0];
        $execucao = new ImportacaoExecucaoService(
            $this->banco,
            new CMMSProcessor(),
            new EquipamentoRepository($this->pdo),
            new ManutencaoRepository($this->banco),
            new RegistroRepository($this->pdo),
            new OrdemServicoRepository($this->banco),
            new ExecucaoOrdemServicoRepository($this->pdo),
        );
        $cancelamento = $execucao->import(<<<'DSL'
registro execucao_cancelamento {
    equipamento bomba_01
    data 10/10/2026-00:10
    origem corr_bomba_01
    tempo_execucao 1 hora
    status cancelado
    valores {
        pressao 11 bar
    }
}
DSL);
        self::assertTrue($cancelamento->isSuccess());
        self::assertSame($ordem_cancelada->identificador, $cancelamento->ordem_servico->identificador);

        $mesmo_evento = $this->servico->import($this->createReadingDsl('leitura_dia_1', '09/10/2026-08:30', 11));
        self::assertCount(1, $mesmo_evento->ordens_servico);
        self::assertSame($ordem_cancelada->identificador, $mesmo_evento->ordens_servico[0]->identificador);

        $dia_cancelamento = $this->createReadingDsl('leitura_dia_2', '10/10/2026-23:59', 11);
        $bloqueada = $this->servico->import($dia_cancelamento);
        self::assertTrue($bloqueada->isSuccess());
        self::assertSame([], $bloqueada->ordens_servico);
        self::assertSame([], $this->servico->import($dia_cancelamento)->ordens_servico);
        self::assertSame(1, $this->countRows('ordem_servico'));

        $dia_seguinte = $this->servico->import($this->createReadingDsl('leitura_dia_3', '11/10/2026-00:01', 11));
        self::assertTrue($dia_seguinte->isSuccess());
        self::assertCount(1, $dia_seguinte->ordens_servico);
        self::assertSame('2026-10-11', $dia_seguinte->ordens_servico[0]->chave_evento);
        self::assertNotSame($ordem_cancelada->identificador, $dia_seguinte->ordens_servico[0]->identificador);
        self::assertSame(2, $this->countRows('ordem_servico'));
        self::assertSame(4, $this->countRows('registro'));
    }

    public function testeImport_RegistroComMesmoIdentificadorEConteudoDiferente_RetornaConflitoSemEscrita(): void {
        $this->servico->import($this->createReadingDsl('leitura_01', '10/10/2026-08:30', 11));

        $resultado = $this->servico->import($this->createReadingDsl('leitura_01', '10/10/2026-08:30', 12));

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->processamento->status);
        self::assertSame('CMMS-IMP-002', $resultado->processamento->diagnosticos[0]->codigo);
        self::assertSame(1, $this->countRows('registro'));
        self::assertSame(1, $this->countRows('ordem_servico'));
    }

    public function testeImport_CodigoSintaticamenteInvalido_NaoRealizaEscritas(): void {
        $resultado = $this->servico->import('registro leitura_01 {}');

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::LEXICAL_OR_SYNTACTIC_FAILURE, $resultado->processamento->status);
        self::assertSame(DiagnosticOrigin::SYNTACTIC, $resultado->processamento->diagnosticos[0]->origem);
        self::assertSame(0, $this->countRows('registro'));
        self::assertSame(0, $this->countRows('ordem_servico'));
    }

    public function testeImport_RegistroDeExecucao_NaoRealizaEscritas(): void {
        $codigo =
"registro execucao_01 {
    equipamento bomba_01
    data 10/10/2026-08:30
    origem corr_bomba_01
    tempo_execucao 1 hora
    status concluido
    valores {
        pressao 11 bar
    }
}";

        $resultado = $this->servico->import($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame('CMMS-IMP-001', $resultado->processamento->diagnosticos[0]->codigo);
        self::assertSame(0, $this->countRows('registro'));
        self::assertSame(0, $this->countRows('ordem_servico'));
    }

    public function testeImport_FalhaAoSalvarOrdemServico_ReverteGravacaoDoRegistro(): void {
        $chave_evento = '2026-10-10';
        $identificador = 'os_' . hash('sha256', json_encode([
            'bomba_01',
            'corr_bomba_01',
            'corretiva',
            $chave_evento,
        ], JSON_THROW_ON_ERROR));
        $comando = $this->pdo->prepare(<<<'SQL'
            INSERT INTO ordem_servico (
                identificador, manutencao_id, tipo_evento, chave_evento,
                data_referencia, status, data_cancelamento,
                procedimento_identificador, prioridade, duracao_valor, duracao_valor_tipo,
                duracao_unidade, homem_hora, homem_hora_tipo
            ) VALUES (
                :identificador, (SELECT id FROM manutencao WHERE nome = 'corr_bomba_01'), 'preventiva', 'outro_evento',
                '2026-10-01 00:00:00', 'concluida', NULL,
                'inspecionar', 'media', 1, 'int', 'hora', 1, 'int'
            )
            SQL);
        $comando->execute(['identificador' => $identificador]);

        $this->expectException(PDOException::class);

        try {
            $this->servico->import($this->createReadingDsl('leitura_01', '10/10/2026-08:30', 11));
        } finally {
            self::assertSame(0, $this->countRows('registro'));
            self::assertSame(1, $this->countRows('ordem_servico'));
        }
    }

    private function createEquipment(): Equipamento {
        return new Equipamento(
            nome: 'bomba_01',
            tipo: TipoEquipamento::BOMBA_CENTRIFUGA,
            servico: TipoServico::BOMBEAMENTO_AGUA,
            produto: TipoProduto::AGUA,
            caracteristicas_processo: new CaracteristicaProcessoCollection(
                new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR),
            ),
            variaveis_controladas: new VariavelControladaCollection(VariavelControlada::PRESSAO),
        );
    }

    private function createCorrectiveMaintenance(): Manutencao {
        return new Manutencao(
            nome: 'corr_bomba_01',
            tipo: TipoManutencao::CORRETIVA,
            equipamento_identificador: 'bomba_01',
            gatilho: new CondicaoCorretiva(new CondicaoNumerica(
                VariavelControlada::PRESSAO,
                Comparador::MAIOR,
                10,
                UnidadeMedida::BAR,
            )),
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::ALTA,
            duracao: new Tempo(2, UnidadeTempo::HORA),
            homem_hora: 2,
            prazo: new Tempo(1, UnidadeTempo::DIA),
        );
    }

    private function createReadingDsl(string $nome, string $data, int|float $pressao): string {
        return
"registro {$nome} {
    equipamento bomba_01
    data {$data}
    valores {
        pressao {$pressao} bar
    }
}";
    }

    private function countRows(string $tabela): int {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM {$tabela}")->fetchColumn();
    }
}
