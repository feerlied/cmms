<?php /** @noinspection ALL */

declare(strict_types=1);

namespace Infrastructure\Persistence;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Domain\CaracteristicaProcesso;
use Domain\CaracteristicaProcessoCollection;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\VariavelControladaCollection;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use ValueError;

final class EquipamentoRepositoryTest extends TestCase
{
    private PDO $pdo;
    private EquipamentoRepository $repositorio;

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }

        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();
        $this->pdo = $banco->connection();
        $this->repositorio = new EquipamentoRepository($this->pdo);
    }

    public function testeFindByIdentifier_EquipamentoCompleto_ReconstroiEnumsColecoesENumeros(): void
    {
        $equipamento = $this->createEquipment();
        $this->repositorio->save($equipamento, new DateTimeImmutable('2026-09-28 08:00:00-03:00'));

        $recuperado = $this->repositorio->findByIdentifier('bomba_01');

        self::assertInstanceOf(Equipamento::class, $recuperado);
        self::assertSame($equipamento->nome, $recuperado->nome);
        self::assertSame(TipoEquipamento::BOMBA_CENTRIFUGA, $recuperado->tipo);
        self::assertSame(TipoServico::BOMBEAMENTO_AGUA, $recuperado->servico);
        self::assertSame(TipoProduto::AGUA, $recuperado->produto);
        self::assertSame([
            VariavelControlada::PRESSAO,
            VariavelControlada::TEMPERATURA,
            VariavelControlada::OBSERVACAO_VISUAL,
        ], $recuperado->variaveis_controladas->all());

        $caracteristicas = $recuperado->caracteristicas_processo->all();
        self::assertCount(2, $caracteristicas);
        self::assertSame(VariavelControlada::PRESSAO, $caracteristicas[0]->variavel);
        self::assertSame(10.5, $caracteristicas[0]->valor);
        self::assertSame(UnidadeMedida::BAR, $caracteristicas[0]->unidade);
        self::assertSame(VariavelControlada::TEMPERATURA, $caracteristicas[1]->variavel);
        self::assertSame(30.0, $caracteristicas[1]->valor);
        self::assertSame(UnidadeMedida::CELSIUS, $caracteristicas[1]->unidade);
    }

    public function testeSave_EquipamentoReenviado_FalhaSemAlterarCadastroOuDados(): void
    {
        $primeiro_cadastro = new DateTimeImmutable('2026-09-28 08:00:00.123456-03:00');
        $this->repositorio->save($this->createEquipment(), $primeiro_cadastro);
        $duplicado = new Equipamento(
            'bomba_01',
            TipoEquipamento::BOMBA_ALTERNATIVA,
            TipoServico::BOMBEAMENTO_DIESEL,
            TipoProduto::DIESEL
        );

        try {
            $this->repositorio->save($duplicado, new DateTimeImmutable('2026-10-01 12:00:00-03:00'));
            self::fail('Uma inserção com nome duplicado deveria falhar.');
        } catch (PDOException $erro) {
            self::assertSame('23000', $erro->getCode());
        }

        $linha = $this->pdo->query('SELECT primeiro_cadastro FROM equipamento')->fetch(PDO::FETCH_ASSOC);
        self::assertSame('2026-09-28 11:00:00', $linha['primeiro_cadastro']);
        self::assertSame(1, (int)$this->pdo->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
        $recuperado = $this->repositorio->findByIdentifier('bomba_01');
        self::assertSame(TipoEquipamento::BOMBA_CENTRIFUGA, $recuperado->tipo);
        self::assertSame(TipoProduto::AGUA, $recuperado->produto);
        self::assertCount(2, $recuperado->caracteristicas_processo->all());
        self::assertCount(3, $recuperado->variaveis_controladas->all());
    }

    public function testeFindAll_EquipamentosSalvos_RetornaTodosOrdenados(): void
    {
        self::assertSame([], $this->repositorio->findAll());
        $this->repositorio->save(new Equipamento(
            'z_bomba',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            TipoServico::BOMBEAMENTO_AGUA,
            TipoProduto::AGUA
        ), new DateTimeImmutable('2026-09-28'));
        $this->repositorio->save($this->createEquipment(), new DateTimeImmutable('2026-09-28'));

        self::assertSame(['bomba_01', 'z_bomba'], array_map(
            static fn(Equipamento $equipamento): string => $equipamento->nome,
            $this->repositorio->findAll()
        ));
        self::assertCount(2, $this->repositorio->findAll());
    }

    public function testeSave_EquipamentoComColecoes_RelacionaLinhasFilhasPeloId(): void
    {
        $this->repositorio->save($this->createEquipment(), new DateTimeImmutable('2026-09-28'));

        $equipamento_id = (int) $this->pdo->query("SELECT id FROM equipamento WHERE nome = 'bomba_01'")->fetchColumn();
        self::assertGreaterThan(0, $equipamento_id);
        self::assertSame(
            [$equipamento_id, $equipamento_id],
            array_map('intval', $this->pdo->query(
                'SELECT equipamento_id FROM equipamento_caracteristica ORDER BY posicao'
            )->fetchAll(PDO::FETCH_COLUMN))
        );
        self::assertSame(
            [$equipamento_id, $equipamento_id, $equipamento_id],
            array_map('intval', $this->pdo->query(
                'SELECT equipamento_id FROM equipamento_variavel ORDER BY posicao'
            )->fetchAll(PDO::FETCH_COLUMN))
        );
    }

    public function testeFindByIdentifier_IdentificadorInexistente_RetornaNull(): void
    {
        $this->repositorio->save($this->createEquipment(), new DateTimeImmutable('2026-09-28'));

        self::assertNull($this->repositorio->findByIdentifier('bomba_01\' OR 1=1 --'));
    }

    public function testeFindFirstRegistrationAt_EquipamentoSalvo_NormalizaUtcETruncaMicrossegundos(): void
    {
        $primeiro_cadastro = new DateTimeImmutable('2026-09-28 08:00:00.123456-03:00');
        $this->repositorio->save($this->createEquipment(), $primeiro_cadastro);

        $recuperado = $this->repositorio->findFirstRegistrationAt('bomba_01');

        self::assertInstanceOf(DateTimeImmutable::class, $recuperado);
        self::assertSame('2026-09-28T11:00:00.000000+00:00', $recuperado->format('Y-m-d\TH:i:s.uP'));
        self::assertSame('UTC', $recuperado->getTimezone()->getName());
    }

    public function testeFindFirstRegistrationAt_FusoNomeado_NormalizaInstanteParaUtc(): void
    {
        $fuso = new DateTimeZone('America/New_York');
        $primeiro_cadastro = new DateTimeImmutable('2026-03-08 01:30:00.123456', $fuso);
        $this->repositorio->save($this->createEquipment(), $primeiro_cadastro);

        $recuperado = $this->repositorio->findFirstRegistrationAt('bomba_01');
        self::assertSame($primeiro_cadastro->format('U'), $recuperado->format('U'));
        self::assertSame('UTC', $recuperado->getTimezone()->getName());
        self::assertSame('2026-03-08T06:30:00.000000+00:00', $recuperado->format('Y-m-d\TH:i:s.uP'));
        self::assertSame(
            '2026-03-08T07:30:00.000000+00:00',
            $recuperado->add(new DateInterval('PT3600S'))->format('Y-m-d\TH:i:s.uP')
        );
        self::assertEquals($this->createEquipment(), $this->repositorio->findByIdentifier('bomba_01'));
    }

    public function testeFindFirstRegistrationAt_IdentificadorInexistente_RetornaNull(): void
    {
        self::assertNull($this->repositorio->findFirstRegistrationAt('inexistente'));
    }

    public function testeFindByIdentifier_TipoInvalido_PropagaValueError(): void
    {
        $this->pdo->exec("INSERT INTO equipamento (nome, primeiro_cadastro, tipo, servico, produto)
            VALUES ('bomba_01', '2026-09-28 00:00:00', 'invalido', 'invalido', 'invalido')");

        $this->expectException(ValueError::class);
        $this->repositorio->findByIdentifier('bomba_01');
    }

    public function testeFindByIdentifier_ValorInteiroEDecimal_PreservaTiposNumericos(): void
    {
        $equipamento = new Equipamento(
            'bomba_02',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            TipoServico::BOMBEAMENTO_AGUA,
            TipoProduto::AGUA,
            new CaracteristicaProcessoCollection(
                new CaracteristicaProcesso(VariavelControlada::PRESSAO, 30, UnidadeMedida::BAR),
                new CaracteristicaProcesso(VariavelControlada::TEMPERATURA, 30.0, UnidadeMedida::CELSIUS)
            )
        );
        $this->repositorio->save($equipamento, new DateTimeImmutable('2026-09-28'));

        $caracteristicas = $this->repositorio->findByIdentifier('bomba_02')->caracteristicas_processo->all();
        self::assertSame(30, $caracteristicas[0]->valor);
        self::assertSame(30.0, $caracteristicas[1]->valor);
        self::assertSame(
            ['int', 'float'],
            $this->pdo->query('SELECT valor_tipo FROM equipamento_caracteristica ORDER BY posicao')
                ->fetchAll(PDO::FETCH_COLUMN)
        );
    }

    public function testeSave_FalhaNaTabelaFilha_DesfazInsercaoPrincipal(): void
    {
        $this->pdo->exec(
            "CREATE TRIGGER falha_caracteristica BEFORE INSERT ON equipamento_caracteristica
             BEGIN SELECT RAISE(ABORT, 'falha simulada'); END"
        );

        try {
            $this->repositorio->save($this->createEquipment(), new DateTimeImmutable('2026-09-28'));
            self::fail('A falha na tabela filha deveria ser propagada.');
        } catch (PDOException $erro) {
            self::assertSame('23000', $erro->getCode());
        }

        self::assertSame(0, (int)$this->pdo->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
        self::assertFalse($this->pdo->inTransaction());
    }

    public function testeSave_TransacaoExterna_NaoFinalizaTransacaoDoServico(): void
    {
        $this->pdo->beginTransaction();
        $this->repositorio->save($this->createEquipment(), new DateTimeImmutable('2026-09-28'));

        self::assertTrue($this->pdo->inTransaction());
        self::assertSame(1, (int)$this->pdo->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());

        $this->pdo->rollBack();
        self::assertSame(0, (int)$this->pdo->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
    }

    public function testeSave_FalhaNaTabelaFilhaEmTransacaoExterna_ReverteSomenteEquipamento(): void
    {
        $this->pdo->beginTransaction();
        $this->pdo->exec(
            "CREATE TRIGGER falha_caracteristica_externa BEFORE INSERT ON equipamento_caracteristica
             BEGIN SELECT RAISE(ABORT, 'falha simulada'); END"
        );

        try {
            $this->repositorio->save($this->createEquipment(), new DateTimeImmutable('2026-09-28'));
            self::fail('A falha na tabela filha deveria ser propagada.');
        } catch (PDOException) {
            self::assertTrue($this->pdo->inTransaction());
            self::assertSame(0, (int)$this->pdo->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
        } finally {
            $this->pdo->rollBack();
        }
    }

    private function createEquipment(): Equipamento
    {
        return new Equipamento(
            'bomba_01',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            TipoServico::BOMBEAMENTO_AGUA,
            TipoProduto::AGUA,
            new CaracteristicaProcessoCollection(
                new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10.5, UnidadeMedida::BAR),
                new CaracteristicaProcesso(VariavelControlada::TEMPERATURA, 30.0, UnidadeMedida::CELSIUS)
            ),
            new VariavelControladaCollection(
                VariavelControlada::PRESSAO,
                VariavelControlada::TEMPERATURA,
                VariavelControlada::OBSERVACAO_VISUAL
            )
        );
    }
}
