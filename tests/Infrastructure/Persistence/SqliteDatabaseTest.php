<?php /** @noinspection ALL */

declare(strict_types=1);

namespace Infrastructure\Persistence;

use PDO;
use PDOException;
use RuntimeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SqliteDatabaseTest extends TestCase
{
    public function testeSchema_DadosDasEntidades_UsaColunasETabelasRelacionais(): void
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();

        $tabelas = [
            'equipamento', 'equipamento_caracteristica', 'equipamento_variavel',
            'manutencao', 'manutencao_condicao', 'registro', 'registro_valor',
            'ordem_servico', 'execucao_ordem_servico',
        ];
        foreach ($tabelas as $tabela) {
            $colunas = $banco->connection()->query("PRAGMA table_info({$tabela})")
                ->fetchAll(PDO::FETCH_ASSOC);
            self::assertNotEmpty($colunas, $tabela);
            self::assertSame('id', $colunas[0]['name'], $tabela);
            self::assertSame(1, $colunas[0]['pk'], $tabela);
            self::assertNotContains('payload_json', array_column($colunas, 'name'));
        }

        foreach (['equipamento', 'manutencao', 'registro'] as $tabela) {
            self::assertContains(
                'nome',
                array_column($banco->connection()->query("PRAGMA table_info({$tabela})")
                    ->fetchAll(PDO::FETCH_ASSOC), 'name')
            );
        }

        foreach (['primeiro_cadastro_timezone', 'data_timezone', 'data_referencia_timezone'] as $coluna_fuso) {
            foreach (['equipamento', 'registro', 'ordem_servico'] as $tabela) {
                self::assertNotContains(
                    $coluna_fuso,
                    array_column($banco->connection()->query("PRAGMA table_info({$tabela})")->fetchAll(PDO::FETCH_ASSOC), 'name')
                );
            }
        }
        self::assertContains(
            'valor_tipo',
            array_column($banco->connection()->query('PRAGMA table_info(registro_valor)')->fetchAll(PDO::FETCH_ASSOC), 'name')
        );
        self::assertNotContains(
            'equipamento_id',
            array_column($banco->connection()->query('PRAGMA table_info(ordem_servico)')->fetchAll(PDO::FETCH_ASSOC), 'name')
        );
    }

    public function testeSchema_Relacoes_UsamIdsComoChavesEstrangeiras(): void
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();

        $relacoes = [
            'equipamento_caracteristica' => ['equipamento_id' => 'equipamento'],
            'equipamento_variavel' => ['equipamento_id' => 'equipamento'],
            'manutencao' => ['equipamento_id' => 'equipamento'],
            'manutencao_condicao' => ['manutencao_id' => 'manutencao'],
            'registro' => ['equipamento_id' => 'equipamento'],
            'registro_valor' => ['registro_id' => 'registro'],
            'ordem_servico' => ['manutencao_id' => 'manutencao'],
            'execucao_ordem_servico' => [
                'registro_id' => 'registro',
                'ordem_servico_id' => 'ordem_servico',
            ],
        ];
        foreach ($relacoes as $tabela => $esperadas) {
            $chaves = $banco->connection()->query("PRAGMA foreign_key_list({$tabela})")
                ->fetchAll(PDO::FETCH_ASSOC);
            $atuais = [];
            foreach ($chaves as $chave) {
                self::assertSame('id', $chave['to'], $tabela);
                $atuais[$chave['from']] = $chave['table'];
            }
            ksort($atuais);
            ksort($esperadas);
            self::assertSame($esperadas, $atuais, $tabela);
        }
    }

    public function testeInitializeSchema_ChamadaRepetida_PreservaDadosEAcessoAsTabelas(): void
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();
        $banco->transaction(static function (PDO $conexao): void {
            self::insertEquipment($conexao, 'eq1');
        });

        $banco->initializeSchema();

        self::assertSame(1, (int)$banco->connection()->query('PRAGMA foreign_keys')->fetchColumn());
        self::assertSame(1, (int)$banco->connection()->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
        self::assertSame(
            [
                'equipamento', 'equipamento_caracteristica', 'equipamento_variavel',
                'execucao_ordem_servico', 'manutencao', 'manutencao_condicao',
                'ordem_servico', 'registro', 'registro_valor',
            ],
            $banco->connection()->query(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
            )->fetchAll(PDO::FETCH_COLUMN)
        );
    }

    public function testeTransaction_ErroDuranteGravacao_ReverteAlteracoes(): void
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();

        try {
            $banco->transaction(static function (PDO $conexao): void {
                self::insertEquipment($conexao, 'eq1');
                throw new RuntimeException('Falha simulada.');
            });
            self::fail('A transação deveria propagar a falha.');
        } catch (RuntimeException $erro) {
            self::assertSame('Falha simulada.', $erro->getMessage());
        }

        self::assertSame(0, (int)$banco->connection()->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
    }

    public function testeSchema_OrdemServicoComManutencaoInexistente_RejeitaReferencia(): void
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();
        $conexao = $banco->connection();
        self::insertEquipment($conexao, 'eq1');
        self::insertMaintenance($conexao, 'man1', 'eq1');

        $this->expectException(PDOException::class);

        $this->insertOrder($conexao, 'os1', '2026-09-28', 'em_aberto', 'man_inexistente');
    }

    public function testeSchema_MesmoEventoEmDuasOrdensServico_RejeitaDuplicidade(): void
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();
        $conexao = $banco->connection();
        self::insertEquipment($conexao, 'eq1');
        self::insertMaintenance($conexao, 'man1', 'eq1');
        $this->insertOrder($conexao, 'os1', '2026-09-28', 'em_aberto', tipo_evento: 'preventiva');
        $conexao->exec("UPDATE ordem_servico SET status = 'concluida' WHERE identificador = 'os1'");

        $this->expectException(PDOException::class);

        $this->insertOrder($conexao, 'os2', '2026-09-28', 'em_aberto', tipo_evento: 'preventiva');
    }

    public static function statusAtivos(): iterable
    {
        yield 'duas OS em aberto' => ['em_aberto', 'em_aberto'];
        yield 'OS em execução impede nova OS aberta' => ['em_execucao', 'em_aberto'];
        yield 'OS aberta impede outra em execução' => ['em_aberto', 'em_execucao'];
    }

    #[DataProvider('statusAtivos')]
    public function testeSchema_EventosDiferentesComMesmoParAtivo_RejeitaSegundaOrdem(
        string $primeiro_status,
        string $segundo_status
    ): void
    {
        $banco = $this->createDatabaseWithMaintenance();
        $this->insertOrder($banco->connection(), 'os1', '2026-09-28', $primeiro_status);

        $this->expectException(PDOException::class);

        $this->insertOrder($banco->connection(), 'os2', '2026-09-29', $segundo_status);
    }

    public static function statusFinalizados(): iterable
    {
        yield 'concluída' => ['concluida'];
        yield 'cancelada' => ['cancelada'];
    }

    #[DataProvider('statusFinalizados')]
    public function testeSchema_OrdemFinalizada_PermiteNovoEventoDoMesmoPar(string $status_final): void
    {
        $banco = $this->createDatabaseWithMaintenance();
        $conexao = $banco->connection();
        $this->insertOrder($conexao, 'os1', '2026-09-28', 'em_aberto');
        $comando = $conexao->prepare(
            'UPDATE ordem_servico SET status = :status, data_cancelamento = :data_cancelamento
             WHERE identificador = :identificador'
        );
        $comando->execute([
            'status' => $status_final,
            'data_cancelamento' => $status_final === 'cancelada' ? '2026-09-28 11:00:00' : null,
            'identificador' => 'os1',
        ]);

        $this->insertOrder($conexao, 'os2', '2026-09-29', 'em_aberto');

        self::assertSame(2, (int)$conexao->query('SELECT COUNT(*) FROM ordem_servico')->fetchColumn());
    }

    public function testeConnection_ReaberturaDoArquivo_PreservaDados(): void
    {
        $caminho = tempnam(sys_get_temp_dir(), 'cmms_sqlite_');
        self::assertNotFalse($caminho);

        try {
            $banco = new SqliteDatabase($caminho);
            $banco->initializeSchema();
            self::insertEquipment($banco->connection(), 'eq1');
            unset($banco);

            $banco = new SqliteDatabase($caminho);

            self::assertSame(1, (int)$banco->connection()->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
        } finally {
            unset($banco);
            unlink($caminho);
        }
    }

    private function createDatabaseWithMaintenance(): SqliteDatabase
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();
        self::insertEquipment($banco->connection(), 'eq1');
        self::insertMaintenance($banco->connection(), 'man1', 'eq1');

        return $banco;
    }

    private static function insertEquipment(PDO $conexao, string $nome): void
    {
        $comando = $conexao->prepare(<<<'SQL'
            INSERT INTO equipamento (
                nome, primeiro_cadastro, tipo, servico, produto
            ) VALUES (:nome, '2026-09-28 10:00:00',
                      'bomba_centrifuga', 'bombeamento_de_agua', 'agua')
            SQL
        );
        $comando->execute(['nome' => $nome]);
    }

    private static function insertMaintenance(PDO $conexao, string $nome, string $equipamento): void
    {
        $comando = $conexao->prepare(<<<'SQL'
            INSERT INTO manutencao (
                nome, equipamento_id, tipo, gatilho_valor, gatilho_valor_tipo, gatilho_unidade,
                procedimento_identificador, prioridade, duracao_valor, duracao_valor_tipo,
                duracao_unidade, homem_hora, homem_hora_tipo
            ) VALUES (:nome, (SELECT id FROM equipamento WHERE nome = :equipamento), 'preventiva', 1, 'int', 'dia',
                      'inspecionar', 'media', 1, 'int', 'hora', 1, 'int')
            SQL
        );
        $comando->execute(['nome' => $nome, 'equipamento' => $equipamento]);
    }

    private function insertOrder(
        PDO    $conexao,
        string $identificador,
        string $chave_evento,
        string $status,
        string $manutencao = 'man1',
        string $tipo_evento = 'corretiva'
    ): void
    {
        $comando = $conexao->prepare(
            'INSERT INTO ordem_servico
             (identificador, manutencao_id, tipo_evento, chave_evento,
              data_referencia, status, data_cancelamento,
              procedimento_identificador, prioridade, duracao_valor, duracao_valor_tipo,
              duracao_unidade, homem_hora, homem_hora_tipo)
             VALUES (:identificador, :manutencao_id, :tipo_evento, :chave_evento,
                     :data_referencia, :status, NULL,
                     :procedimento_identificador, :prioridade, :duracao_valor, :duracao_valor_tipo,
                     :duracao_unidade, :homem_hora, :homem_hora_tipo)'
        );
        $comando->execute([
            'identificador' => $identificador,
            'manutencao_id' => $this->findIdByName($conexao, 'manutencao', $manutencao) ?? -1,
            'tipo_evento' => $tipo_evento,
            'chave_evento' => $chave_evento,
            'data_referencia' => $chave_evento . ' 10:00:00',
            'status' => $status,
            'procedimento_identificador' => 'inspecionar',
            'prioridade' => 'media',
            'duracao_valor' => 1,
            'duracao_valor_tipo' => 'int',
            'duracao_unidade' => 'hora',
            'homem_hora' => 1,
            'homem_hora_tipo' => 'int',
        ]);
    }

    private function findIdByName(PDO $conexao, string $tabela, string $nome): ?int
    {
        $comando = $conexao->prepare("SELECT id FROM {$tabela} WHERE nome = :nome");
        $comando->execute(['nome' => $nome]);
        $id = $comando->fetchColumn();

        return $id === false ? null : (int) $id;
    }
}
