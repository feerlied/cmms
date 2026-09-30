<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

use DateTimeImmutable;
use DateTimeZone;
use Domain\Enums\EstadoObservacao;
use Domain\Enums\EstadoVazamento;
use Domain\Enums\StatusRegistro;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\ExecucaoRegistro;
use Domain\HorasOperacaoRegistro;
use Domain\ObservacaoVisualRegistro;
use Domain\Registro;
use Domain\Tempo;
use Domain\ValorNumericoRegistro;
use Domain\ValorRegistradoCollection;
use Domain\VazamentoRegistro;
use Infrastructure\Persistence\RegistroRepository;
use Infrastructure\Persistence\SqliteDatabase;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

final class RegistroRepositoryTest extends TestCase
{
    private PDO $pdo;
    private RegistroRepository $repositorio;

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }

        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();
        $this->pdo = $banco->connection();
        $this->pdo->exec("INSERT INTO equipamento (
            nome, primeiro_cadastro, primeiro_cadastro_timezone, tipo, servico, produto
        ) VALUES (
            'bomba_01', '2026-09-28T08:00:00.000000-03:00', '-03:00', 'bomba', 'bombeamento', 'agua'
        )");
        $this->repositorio = new RegistroRepository($this->pdo);
    }

    public function testeSaveEFindByIdentifier_RegistroCompleto_ReconstroiTodosOsCampos(): void
    {
        $registro = $this->createCompleteRecord();

        $this->repositorio->save($registro);
        $recuperado = $this->repositorio->findByIdentifier($registro->nome);

        self::assertInstanceOf(Registro::class, $recuperado);
        self::assertSame('registro_01', $recuperado->nome);
        self::assertSame('bomba_01', $recuperado->equipamento_identificador);
        self::assertSame('2026-09-28T08:30:00.123456-03:00', $recuperado->data->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('Relatório de inspeção', $recuperado->relatorio);
        self::assertSame('Sem ruídos anormais', $recuperado->observacao);
        self::assertInstanceOf(ExecucaoRegistro::class, $recuperado->execucao);
        self::assertSame('inspecao_bomba_01', $recuperado->execucao->origem);
        self::assertSame(3.5, $recuperado->execucao->tempo_execucao->valor);
        self::assertSame(UnidadeTempo::HORA, $recuperado->execucao->tempo_execucao->unidade);
        self::assertSame(StatusRegistro::CONCLUIDO, $recuperado->execucao->status);

        $linha = $this->pdo->query("SELECT execucao_tempo_valor_tipo, relatorio, observacao
            FROM registro WHERE nome = 'registro_01'")->fetch(PDO::FETCH_ASSOC);
        self::assertSame('float', $linha['execucao_tempo_valor_tipo']);
        self::assertSame('Relatório de inspeção', $linha['relatorio']);
        self::assertSame('Sem ruídos anormais', $linha['observacao']);

        $valores = $recuperado->valores->all();
        self::assertCount(4, $valores);
        self::assertInstanceOf(ValorNumericoRegistro::class, $valores[0]);
        self::assertSame(VariavelControlada::PRESSAO, $valores[0]->variavel);
        self::assertSame(9.7, $valores[0]->valor);
        self::assertSame(UnidadeMedida::BAR, $valores[0]->unidade);
        self::assertInstanceOf(HorasOperacaoRegistro::class, $valores[1]);
        self::assertSame(1250, $valores[1]->horas_operacao->valor);
        self::assertSame(UnidadeTempo::HORA, $valores[1]->horas_operacao->unidade);
        self::assertInstanceOf(ObservacaoVisualRegistro::class, $valores[2]);
        self::assertSame(EstadoObservacao::ANORMAL, $valores[2]->estado);
        self::assertInstanceOf(VazamentoRegistro::class, $valores[3]);
        self::assertSame(EstadoVazamento::LEVE, $valores[3]->estado);

        $linhas_valores = $this->pdo->query("SELECT v.posicao, v.tipo, v.valor_tipo
            FROM registro_valor v JOIN registro r ON r.id = v.registro_id
            WHERE r.nome = 'registro_01' ORDER BY v.posicao")->fetchAll(PDO::FETCH_ASSOC);
        self::assertSame(['numerico', 'horas_operacao', 'observacao_visual', 'vazamento'],
            array_column($linhas_valores, 'tipo'));
        self::assertSame(['float', 'int', null, null], array_column($linhas_valores, 'valor_tipo'));

        $vinculo = $this->pdo->query("SELECT r.equipamento_id, e.id AS equipamento_id_esperado,
            v.registro_id, r.id AS registro_id_esperado
            FROM registro r
            JOIN equipamento e ON e.nome = 'bomba_01'
            JOIN registro_valor v ON v.registro_id = r.id AND v.posicao = 0
            WHERE r.nome = 'registro_01'")->fetch(PDO::FETCH_ASSOC);
        self::assertSame($vinculo['equipamento_id_esperado'], $vinculo['equipamento_id']);
        self::assertSame($vinculo['registro_id_esperado'], $vinculo['registro_id']);
    }

    public function testeSaveEFindByIdentifier_ValorFloatInteiroEFusoNomeado_PreservaTiposEFuso(): void
    {
        $registro = new Registro(
            nome: 'registro_fuso',
            equipamento_identificador: 'bomba_01',
            data: new DateTimeImmutable('2026-01-15 08:30:00', new DateTimeZone('America/New_York')),
            valores: new ValorRegistradoCollection(
                new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9.0, UnidadeMedida::BAR),
                new HorasOperacaoRegistro(new Tempo(1250.0, UnidadeTempo::HORA)),
            ),
            execucao: new ExecucaoRegistro('inspecao', new Tempo(3.0, UnidadeTempo::HORA), StatusRegistro::CONCLUIDO),
        );

        $this->repositorio->save($registro);
        $recuperado = $this->repositorio->findByIdentifier('registro_fuso');

        self::assertSame('America/New_York', $recuperado->data->getTimezone()->getName());
        self::assertSame($registro->data->format('Y-m-d\TH:i:s.uP'), $recuperado->data->format('Y-m-d\TH:i:s.uP'));
        self::assertSame(9.0, $recuperado->valores->all()[0]->valor);
        self::assertSame(1250.0, $recuperado->valores->all()[1]->horas_operacao->valor);
        self::assertSame(3.0, $recuperado->execucao->tempo_execucao->valor);
    }

    public function testeSave_FalhaNaSegundaLinha_RetornaBancoAoEstadoAnteriorDentroDeTransacaoExterna(): void
    {
        $this->pdo->exec("CREATE TRIGGER falha_segundo_valor BEFORE INSERT ON registro_valor
            WHEN NEW.posicao = 1 BEGIN SELECT RAISE(ABORT, 'Falha simulada'); END");
        $this->pdo->beginTransaction();

        try {
            $this->repositorio->save($this->createCompleteRecord());
            self::fail('A inserção da segunda linha deveria falhar.');
        } catch (PDOException $erro) {
            self::assertTrue($this->pdo->inTransaction());
            self::assertSame(0, (int)$this->pdo->query('SELECT COUNT(*) FROM registro')->fetchColumn());
            self::assertSame(0, (int)$this->pdo->query('SELECT COUNT(*) FROM registro_valor')->fetchColumn());
        } finally {
            $this->pdo->rollBack();
        }
    }

    public function testeSave_IdentificadorDuplicado_RejeitaSegundaInsercao(): void
    {
        $this->repositorio->save($this->createCompleteRecord());

        $this->expectException(PDOException::class);
        $this->repositorio->save($this->createCompleteRecord());
    }

    public function testeFindByIdentifier_IdentificadorInexistente_RetornaNull(): void
    {
        self::assertNull($this->repositorio->findByIdentifier("registro_01' OR 1=1 --"));
    }

    public function testeFindLatestReadingByEquipment_LeiturasEExecucoes_RetornaLeituraMaisRecenteComDesempateEstavel(): void
    {
        $this->repositorio->save($this->createReading('leitura_antiga', '2026-09-28 08:00:00-03:00'));
        $this->repositorio->save($this->createReading('leitura_b', '2026-09-28 10:00:00-03:00'));
        $this->repositorio->save($this->createReading('leitura_a', '2026-09-28 10:00:00-03:00'));
        $this->repositorio->save($this->createCompleteRecord('execucao_mais_recente', '2026-09-28 11:00:00-03:00'));

        $registro = $this->repositorio->findLatestReadingByEquipment('bomba_01');

        self::assertInstanceOf(Registro::class, $registro);
        self::assertSame('leitura_a', $registro->nome);
        self::assertNull($registro->execucao);
    }

    public function testeFindLatestReadingByEquipment_SomenteExecucao_RetornaNull(): void
    {
        $this->repositorio->save($this->createCompleteRecord());

        self::assertNull($this->repositorio->findLatestReadingByEquipment('bomba_01'));
    }

    private function createCompleteRecord(
        string $nome = 'registro_01',
        string $data = '2026-09-28 08:30:00.123456-03:00',
    ): Registro
    {
        return new Registro(
            nome: $nome,
            equipamento_identificador: 'bomba_01',
            data: new DateTimeImmutable($data),
            valores: new ValorRegistradoCollection(
                new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9.7, UnidadeMedida::BAR),
                new HorasOperacaoRegistro(new Tempo(1250, UnidadeTempo::HORA)),
                new ObservacaoVisualRegistro(EstadoObservacao::ANORMAL),
                new VazamentoRegistro(EstadoVazamento::LEVE),
            ),
            execucao: new ExecucaoRegistro(
                origem: 'inspecao_bomba_01',
                tempo_execucao: new Tempo(3.5, UnidadeTempo::HORA),
                status: StatusRegistro::CONCLUIDO,
            ),
            relatorio: 'Relatório de inspeção',
            observacao: 'Sem ruídos anormais',
        );
    }

    private function createReading(string $nome, string $data): Registro
    {
        return new Registro(
            nome: $nome,
            equipamento_identificador: 'bomba_01',
            data: new DateTimeImmutable($data),
            valores: new ValorRegistradoCollection(
                new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9.7, UnidadeMedida::BAR),
            ),
        );
    }
}
