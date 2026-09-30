<?php /** @noinspection ALL */

declare(strict_types=1);

namespace Infrastructure\Persistence;

use PDO;
use Throwable;

final class SqliteDatabase {
    private PDO $conexao;

    public function __construct(string $caminho) {
        $this->conexao = new PDO('sqlite:' . $caminho, options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->conexao->exec('PRAGMA foreign_keys = ON');
    }

    public function connection(): PDO {
        return $this->conexao;
    }

    public function initializeSchema(): void {
        $this->transaction(function (PDO $conexao): void {
            $conexao->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS equipamento (
                    id INTEGER PRIMARY KEY,
                    nome TEXT NOT NULL UNIQUE,
                    primeiro_cadastro TEXT NOT NULL,
                    primeiro_cadastro_timezone TEXT NOT NULL,
                    tipo TEXT NOT NULL,
                    servico TEXT NOT NULL,
                    produto TEXT NOT NULL
                )
                SQL);

            $conexao->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS equipamento_caracteristica (
                    id INTEGER PRIMARY KEY,
                    equipamento_id INTEGER NOT NULL,
                    posicao INTEGER NOT NULL,
                    variavel TEXT NOT NULL,
                    valor NUMERIC NOT NULL,
                    valor_tipo TEXT NOT NULL CHECK (valor_tipo IN ('int', 'float')),
                    unidade TEXT NOT NULL,
                    UNIQUE (equipamento_id, posicao),
                    FOREIGN KEY (equipamento_id) REFERENCES equipamento (id)
                )
                SQL);

            $conexao->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS equipamento_variavel (
                    id INTEGER PRIMARY KEY,
                    equipamento_id INTEGER NOT NULL,
                    posicao INTEGER NOT NULL,
                    variavel TEXT NOT NULL,
                    UNIQUE (equipamento_id, posicao),
                    FOREIGN KEY (equipamento_id) REFERENCES equipamento (id)
                )
                SQL);

            $conexao->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS manutencao (
                    id INTEGER PRIMARY KEY,
                    nome TEXT NOT NULL UNIQUE,
                    equipamento_id INTEGER NOT NULL,
                    tipo TEXT NOT NULL,
                    gatilho_valor NUMERIC,
                    gatilho_valor_tipo TEXT CHECK (gatilho_valor_tipo IN ('int', 'float')),
                    gatilho_unidade TEXT,
                    procedimento_identificador TEXT NOT NULL,
                    prioridade TEXT NOT NULL,
                    duracao_valor NUMERIC NOT NULL,
                    duracao_valor_tipo TEXT NOT NULL CHECK (duracao_valor_tipo IN ('int', 'float')),
                    duracao_unidade TEXT NOT NULL,
                    homem_hora NUMERIC NOT NULL,
                    homem_hora_tipo TEXT NOT NULL CHECK (homem_hora_tipo IN ('int', 'float')),
                    prazo_valor NUMERIC,
                    prazo_valor_tipo TEXT CHECK (prazo_valor_tipo IN ('int', 'float')),
                    prazo_unidade TEXT,
                    FOREIGN KEY (equipamento_id) REFERENCES equipamento (id)
                )
                SQL);

            $conexao->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS manutencao_condicao (
                    id INTEGER PRIMARY KEY,
                    manutencao_id INTEGER NOT NULL,
                    posicao INTEGER NOT NULL,
                    operador TEXT,
                    tipo TEXT NOT NULL,
                    variavel TEXT,
                    comparador TEXT,
                    valor NUMERIC,
                    valor_tipo TEXT CHECK (valor_tipo IN ('int', 'float')),
                    unidade TEXT,
                    estado TEXT,
                    UNIQUE (manutencao_id, posicao),
                    FOREIGN KEY (manutencao_id) REFERENCES manutencao (id)
                )
                SQL);

            $conexao->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS registro (
                    id INTEGER PRIMARY KEY,
                    nome TEXT NOT NULL UNIQUE,
                    equipamento_id INTEGER NOT NULL,
                    data TEXT NOT NULL,
                    data_timezone TEXT NOT NULL,
                    execucao_origem TEXT,
                    execucao_tempo_valor NUMERIC,
                    execucao_tempo_valor_tipo TEXT CHECK (execucao_tempo_valor_tipo IN ('int', 'float')),
                    execucao_tempo_unidade TEXT,
                    execucao_status TEXT,
                    relatorio TEXT,
                    observacao TEXT,
                    FOREIGN KEY (equipamento_id) REFERENCES equipamento (id)
                )
                SQL);

            $conexao->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS registro_valor (
                    id INTEGER PRIMARY KEY,
                    registro_id INTEGER NOT NULL,
                    posicao INTEGER NOT NULL,
                    tipo TEXT NOT NULL,
                    variavel TEXT,
                    valor NUMERIC,
                    valor_tipo TEXT CHECK (valor_tipo IN ('int', 'float')),
                    unidade TEXT,
                    estado TEXT,
                    UNIQUE (registro_id, posicao),
                    FOREIGN KEY (registro_id) REFERENCES registro (id)
                )
                SQL);

            $conexao->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS ordem_servico (
                    id INTEGER PRIMARY KEY,
                    identificador TEXT NOT NULL UNIQUE,
                    manutencao_id INTEGER NOT NULL,
                    tipo_evento TEXT NOT NULL,
                    chave_evento TEXT NOT NULL,
                    data_referencia TEXT NOT NULL,
                    data_referencia_timezone TEXT NOT NULL,
                    status TEXT NOT NULL,
                    data_cancelamento TEXT,
                    procedimento_identificador TEXT NOT NULL,
                    prioridade TEXT NOT NULL,
                    duracao_valor NUMERIC NOT NULL,
                    duracao_valor_tipo TEXT NOT NULL CHECK (duracao_valor_tipo IN ('int', 'float')),
                    duracao_unidade TEXT NOT NULL,
                    homem_hora NUMERIC NOT NULL,
                    homem_hora_tipo TEXT NOT NULL CHECK (homem_hora_tipo IN ('int', 'float')),
                    prazo_valor NUMERIC,
                    prazo_valor_tipo TEXT CHECK (prazo_valor_tipo IN ('int', 'float')),
                    prazo_unidade TEXT,
                    UNIQUE (manutencao_id, tipo_evento, chave_evento),
                    FOREIGN KEY (manutencao_id) REFERENCES manutencao (id)
                )
                SQL);

            $conexao->exec(<<<'SQL'
                CREATE UNIQUE INDEX IF NOT EXISTS idx_ordem_servico_manutencao_ativa
                ON ordem_servico (manutencao_id)
                WHERE status IN ('em_aberto', 'em_execucao')
                SQL);

            $conexao->exec(<<<'SQL'
                CREATE TABLE IF NOT EXISTS execucao_ordem_servico (
                    id INTEGER PRIMARY KEY,
                    registro_id INTEGER NOT NULL UNIQUE,
                    ordem_servico_id INTEGER NOT NULL,
                    FOREIGN KEY (registro_id) REFERENCES registro (id),
                    FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servico (id)
                )
                SQL);
        });
    }

    public function transaction(callable $operacao): mixed {
        $this->conexao->beginTransaction();

        try {
            $resultado = $operacao($this->conexao);
            $this->conexao->commit();

            return $resultado;
        } catch (Throwable $erro) {
            $this->conexao->rollBack();
            throw $erro;
        }
    }
}
