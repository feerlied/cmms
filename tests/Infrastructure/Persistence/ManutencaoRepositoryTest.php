<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

use Domain\CondicaoCorretiva;
use Domain\CondicaoNumerica;
use Domain\CondicaoObservacao;
use Domain\CondicaoVazamento;
use Domain\Enums\Comparador;
use Domain\Enums\EstadoObservacao;
use Domain\Enums\EstadoVazamento;
use Domain\Enums\OperadorLogico;
use Domain\Enums\Prioridade;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\Manutencao;
use Domain\Tempo;
use PDOException;
use PHPUnit\Framework\TestCase;

final class ManutencaoRepositoryTest extends TestCase
{
    public function testeSave_ManutencaoPreventiva_PreservaTodosOsCampos(): void
    {
        $repositorio = $this->createRepository();
        $manutencao = new Manutencao(
            nome: 'prev1',
            tipo: TipoManutencao::PREVENTIVA,
            equipamento_identificador: 'eq1',
            gatilho: new Tempo(30.0, UnidadeTempo::DIA),
            procedimento_identificador: 'inspecionar_bomba',
            prioridade: Prioridade::MEDIA,
            duracao: new Tempo(1.0, UnidadeTempo::HORA),
            homem_hora: 2.0
        );

        $repositorio->save($manutencao);
        $recuperada = $repositorio->findByIdentifier('prev1');

        self::assertEquals($manutencao, $recuperada);
        self::assertSame(30.0, $recuperada->gatilho->valor);
        self::assertSame(1.0, $recuperada->duracao->valor);
        self::assertSame(2.0, $recuperada->homem_hora);
        self::assertNull($recuperada->prazo);
    }

    public function testeSave_ManutencaoCorretiva_PreservaCondicoesOperadoresEPrazo(): void
    {
        $repositorio = $this->createRepository();
        $gatilho = new CondicaoCorretiva(new CondicaoNumerica(
            VariavelControlada::PRESSAO,
            Comparador::MAIOR_OU_IGUAL,
            7.0,
            UnidadeMedida::BAR
        ));
        $gatilho->add(OperadorLogico::E, new CondicaoObservacao(EstadoObservacao::ANORMAL));
        $gatilho->add(OperadorLogico::OU, new CondicaoVazamento(EstadoVazamento::GRAVE));
        $manutencao = new Manutencao(
            nome: 'corr1',
            tipo: TipoManutencao::CORRETIVA,
            equipamento_identificador: 'eq1',
            gatilho: $gatilho,
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::CRITICA,
            duracao: new Tempo(3.0, UnidadeTempo::HORA),
            homem_hora: 6,
            prazo: new Tempo(1.0, UnidadeTempo::DIA)
        );

        $repositorio->save($manutencao);
        $recuperada = $repositorio->findByIdentifier('corr1');

        self::assertEquals($manutencao, $recuperada);
        self::assertSame([OperadorLogico::E, OperadorLogico::OU], $recuperada->gatilho->getOperators());
        self::assertCount(3, $recuperada->gatilho->getConditions());
        self::assertSame(7.0, $recuperada->gatilho->getConditions()[0]->valor);
        self::assertSame(3.0, $recuperada->duracao->valor);
        self::assertSame(6, $recuperada->homem_hora);
        self::assertSame(1.0, $recuperada->prazo->valor);
    }

    public function testeFindByEquipment_EquipamentosDistintos_RetornaSomenteManutencoesDoEquipamento(): void
    {
        $repositorio = $this->createRepository();
        $repositorio->save($this->createPreventiveMaintenance('prev_b', 'eq1'));
        $repositorio->save($this->createPreventiveMaintenance('prev_a', 'eq1'));
        $repositorio->save($this->createPreventiveMaintenance('prev_c', 'eq2'));

        self::assertSame(
            ['prev_a', 'prev_b'],
            array_map(static fn(Manutencao $manutencao): string => $manutencao->nome, $repositorio->findByEquipment('eq1'))
        );
        self::assertSame([], $repositorio->findByEquipment('eq_inexistente'));
        self::assertNull($repositorio->findByIdentifier('man_inexistente'));
    }

    public function testeSave_IdentificadorJaPersistido_RejeitaDuplicidade(): void
    {
        $repositorio = $this->createRepository();
        $repositorio->save($this->createPreventiveMaintenance('prev1', 'eq1'));

        $this->expectException(PDOException::class);

        $repositorio->save($this->createPreventiveMaintenance('prev1', 'eq2'));
    }

    public function testeSave_FalhaNaSegundaCondicao_DesfazInsercaoSemEncerrarTransacaoExterna(): void
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();
        $banco->connection()->exec("INSERT INTO equipamento (nome, primeiro_cadastro, primeiro_cadastro_timezone, tipo, servico, produto) VALUES ('eq1', '2026-09-28T10:00:00', 'UTC', 'bomba', 'transferencia', 'agua')");
        $banco->connection()->exec(<<<'SQL'
            CREATE TRIGGER falha_segunda_condicao BEFORE INSERT ON manutencao_condicao
            WHEN NEW.posicao = 1 BEGIN SELECT RAISE(ABORT, 'falha simulada'); END
            SQL
        );

        $gatilho = new CondicaoCorretiva(new CondicaoObservacao(EstadoObservacao::ANORMAL));
        $gatilho->add(OperadorLogico::E, new CondicaoVazamento(EstadoVazamento::GRAVE));
        $manutencao = new Manutencao(
            nome: 'corr_falha',
            tipo: TipoManutencao::CORRETIVA,
            equipamento_identificador: 'eq1',
            gatilho: $gatilho,
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::CRITICA,
            duracao: new Tempo(1, UnidadeTempo::HORA),
            homem_hora: 2,
            prazo: new Tempo(1, UnidadeTempo::DIA)
        );

        $banco->connection()->beginTransaction();
        try {
            try {
                (new ManutencaoRepository($banco))->save($manutencao);
                self::fail('A inserção da segunda condição deveria falhar.');
            } catch (PDOException $erro) {
                self::assertSame('falha simulada', $erro->errorInfo[2]);
            }

            self::assertTrue($banco->connection()->inTransaction());
            self::assertSame(0, (int)$banco->connection()->query('SELECT COUNT(*) FROM manutencao')->fetchColumn());
            self::assertSame(0, (int)$banco->connection()->query('SELECT COUNT(*) FROM manutencao_condicao')->fetchColumn());
        } finally {
            $banco->connection()->rollBack();
        }

        try {
            (new ManutencaoRepository($banco))->save($manutencao);
            self::fail('A inserção da segunda condição deveria falhar sem transação externa.');
        } catch (PDOException) {
            self::assertFalse($banco->connection()->inTransaction());
        }
        self::assertSame(0, (int)$banco->connection()->query('SELECT COUNT(*) FROM manutencao')->fetchColumn());
        self::assertSame(0, (int)$banco->connection()->query('SELECT COUNT(*) FROM manutencao_condicao')->fetchColumn());
    }

    private function createRepository(): ManutencaoRepository
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();
        $banco->connection()->exec("INSERT INTO equipamento (nome, primeiro_cadastro, primeiro_cadastro_timezone, tipo, servico, produto) VALUES ('eq1', '2026-09-28T10:00:00', 'UTC', 'bomba', 'transferencia', 'agua')");
        $banco->connection()->exec("INSERT INTO equipamento (nome, primeiro_cadastro, primeiro_cadastro_timezone, tipo, servico, produto) VALUES ('eq2', '2026-09-28T10:00:00', 'UTC', 'bomba', 'transferencia', 'agua')");

        return new ManutencaoRepository($banco);
    }

    private function createPreventiveMaintenance(string $nome, string $equipamento): Manutencao
    {
        return new Manutencao(
            nome: $nome,
            tipo: TipoManutencao::PREVENTIVA,
            equipamento_identificador: $equipamento,
            gatilho: new Tempo(30, UnidadeTempo::DIA),
            procedimento_identificador: 'inspecionar_bomba',
            prioridade: Prioridade::MEDIA,
            duracao: new Tempo(1, UnidadeTempo::HORA),
            homem_hora: 2
        );
    }
}
