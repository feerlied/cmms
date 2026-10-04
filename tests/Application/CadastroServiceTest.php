<?php

declare(strict_types=1);

namespace Application;

use Application\CadastroService;
use Application\CMMSProcessor;
use Application\ProcessingStatus;
use DateTimeImmutable;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticSeverity;
use Domain\Equipamento;
use Domain\Manutencao;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\SqliteDatabase;
use PDO;
use PHPUnit\Framework\TestCase;

final class CadastroServiceTest extends TestCase
{
    private SqliteDatabase $banco;
    private EquipamentoRepository $equipamentos;
    private ManutencaoRepository $manutencoes;
    private CadastroService $cadastro;

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }

        $this->banco = new SqliteDatabase(':memory:');
        $this->banco->initializeSchema();
        $this->equipamentos = new EquipamentoRepository($this->banco->connection());
        $this->manutencoes = new ManutencaoRepository($this->banco);
        $this->cadastro = new CadastroService(
            $this->banco,
            $this->equipamentos,
            $this->manutencoes,
            new CMMSProcessor()
        );
    }

    public function testeImport_EquipamentoDepoisManutencaoIsolada_ResolveReferenciaPersistida(): void
    {
        $primeiro_cadastro = new DateTimeImmutable('2026-09-28 08:30:00-03:00');
        $resultado_equipamento = $this->cadastro->import($this->equipmentCode('bomba_01'), $primeiro_cadastro);
        $resultado_manutencao = $this->cadastro->import(
            $this->maintenanceCode('inspecao_01', 'bomba_01'),
            new DateTimeImmutable('2026-09-29 09:00:00-03:00')
        );

        self::assertTrue($resultado_equipamento->isSuccess());
        self::assertInstanceOf(Equipamento::class, $resultado_equipamento->objetos[0]);
        self::assertTrue($resultado_manutencao->isSuccess());
        self::assertInstanceOf(Manutencao::class, $resultado_manutencao->objetos[0]);
        self::assertSame('bomba_01', $this->manutencoes->findByIdentifier('inspecao_01')->equipamento_identificador);
        self::assertSame('2026-09-28 11:30:00', $this->banco->connection()
            ->query("SELECT primeiro_cadastro FROM equipamento WHERE nome = 'bomba_01'")
            ->fetchColumn());
    }

    public function testeImport_ManutencaoDeclaradaAntesDoEquipamento_GravaNaOrdemDasReferencias(): void
    {
        $codigo = $this->maintenanceCode('inspecao_02', 'bomba_02') . "\n" . $this->equipmentCode('bomba_02');

        $resultado = $this->cadastro->import($codigo, new DateTimeImmutable('2026-09-28'));

        self::assertTrue($resultado->isSuccess());
        self::assertInstanceOf(Manutencao::class, $resultado->objetos[0]);
        self::assertInstanceOf(Equipamento::class, $resultado->objetos[1]);
        self::assertNotNull($this->equipamentos->findByIdentifier('bomba_02'));
        self::assertNotNull($this->manutencoes->findByIdentifier('inspecao_02'));
    }

    public function testeImport_ProgramaComFalhaSemantica_NaoGravaObjetosValidos(): void
    {
        $codigo = $this->equipmentCode('bomba_03') . "\n" . $this->maintenanceCode('inspecao_03', 'inexistente');

        $resultado = $this->cadastro->import($codigo, new DateTimeImmutable('2026-09-28'));

        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertNull($this->equipamentos->findByIdentifier('bomba_03'));
        self::assertNull($this->manutencoes->findByIdentifier('inspecao_03'));
    }

    public function testeImport_ProgramaComRegistro_RejeitaSemGravar(): void
    {
        $codigo = $this->equipmentCode('bomba_04') . "\n" . <<<'DSL'
registro leitura_04 {
    equipamento bomba_04
    data 28/09/2026-10:00
    valores {
        pressao 11 bar
    }
}
DSL;

        $resultado = $this->cadastro->import($codigo, new DateTimeImmutable('2026-09-28'));

        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertSame('CMMS-CAD-001', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
        self::assertNull($this->equipamentos->findByIdentifier('bomba_04'));
    }

    public function testeImport_ManutencaoDuplicadaNoBanco_NaoGravaEquipamentoDoMesmoPrograma(): void
    {
        $this->cadastro->import(
            $this->equipmentCode('bomba_base') . "\n" . $this->maintenanceCode('inspecao_duplicada', 'bomba_base'),
            new DateTimeImmutable('2026-09-28')
        );
        $codigo = $this->equipmentCode('bomba_nova') . "\n"
            . $this->maintenanceCode('inspecao_duplicada', 'bomba_nova');

        $resultado = $this->cadastro->import($codigo, new DateTimeImmutable('2026-09-29'));

        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertSame('CMMS-CAD-002', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
        self::assertNull($this->equipamentos->findByIdentifier('bomba_nova'));
        self::assertSame('bomba_base', $this->manutencoes
            ->findByIdentifier('inspecao_duplicada')->equipamento_identificador);
        self::assertSame(1, (int)$this->banco->connection()
            ->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
    }

    private function equipmentCode(string $identificador): string
    {
        return <<<DSL
equipamento {$identificador} {
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
DSL;
    }

    private function maintenanceCode(string $identificador, string $equipamento_identificador): string
    {
        return <<<DSL
manutencao preventiva {$identificador} {
    equipamento {$equipamento_identificador}
    a_cada 1 dia
    procedimento inspecionar_bomba
    prioridade media
    duracao 1 hora
    homem_hora 2
}
DSL;
    }
}
