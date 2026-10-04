<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

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
use PDO;
use Throwable;

final readonly class RegistroRepository {
    public function __construct(private PDO $pdo) {}

    public function save(Registro $registro): void {
        $transacao_existente = $this->pdo->inTransaction();

        if ($transacao_existente) {
            $this->pdo->exec('SAVEPOINT registro_repository_save');
        } else {
            $this->pdo->beginTransaction();
        }

        try {
            $execucao = $registro->execucao;
            $comando = $this->pdo->prepare(
                'INSERT INTO registro (
                    nome, equipamento_id, data,
                    execucao_origem, execucao_tempo_valor, execucao_tempo_valor_tipo,
                    execucao_tempo_unidade, execucao_status, relatorio, observacao
                ) VALUES (
                    :nome, (SELECT id FROM equipamento WHERE nome = :equipamento_nome), :data,
                    :execucao_origem, :execucao_tempo_valor, :execucao_tempo_valor_tipo,
                    :execucao_tempo_unidade, :execucao_status, :relatorio, :observacao
                )'
            );
            $comando->execute([
                'nome' => $registro->nome,
                'equipamento_nome' => $registro->equipamento_identificador,
                'data' => $registro->data
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->format('Y-m-d H:i:s'),
                'execucao_origem' => $execucao?->origem,
                'execucao_tempo_valor' => $execucao?->tempo_execucao->valor,
                'execucao_tempo_valor_tipo' => $execucao === null
                    ? null : $this->numericType($execucao->tempo_execucao->valor),
                'execucao_tempo_unidade' => $execucao?->tempo_execucao->unidade->value,
                'execucao_status' => $execucao?->status->value,
                'relatorio' => $registro->relatorio,
                'observacao' => $registro->observacao,
            ]);
            $registro_id = (int) $this->pdo->lastInsertId();

            $comando_valor = $this->pdo->prepare(
                'INSERT INTO registro_valor (
                    registro_id, posicao, tipo, variavel, valor, valor_tipo, unidade, estado
                ) VALUES (
                    :registro_id, :posicao, :tipo, :variavel, :valor, :valor_tipo, :unidade, :estado
                )'
            );

            foreach ($registro->valores->all() as $posicao => $valor) {
                $comando_valor->execute([
                    'registro_id' => $registro_id,
                    'posicao' => $posicao,
                    ...$this->serializeValue($valor),
                ]);
            }

            if ($transacao_existente) {
                $this->pdo->exec('RELEASE SAVEPOINT registro_repository_save');
            } else {
                $this->pdo->commit();
            }
        } catch (Throwable $erro) {
            if ($transacao_existente) {
                $this->pdo->exec('ROLLBACK TO SAVEPOINT registro_repository_save');
                $this->pdo->exec('RELEASE SAVEPOINT registro_repository_save');
            } else {
                $this->pdo->rollBack();
            }

            throw $erro;
        }
    }

    public function findByIdentifier(string $identificador): ?Registro {
        $comando = $this->pdo->prepare(
            'SELECT r.*, e.nome AS equipamento_nome
             FROM registro r
             JOIN equipamento e ON e.id = r.equipamento_id
             WHERE r.nome = :nome'
        );
        $comando->execute(['nome' => $identificador]);
        $linha = $comando->fetch(PDO::FETCH_ASSOC);

        return $linha === false ? null : $this->hydrate($linha);
    }

    public function findLatestReadingByEquipment(string $equipamento): ?Registro {
        $comando = $this->pdo->prepare(
            'SELECT r.*, e.nome AS equipamento_nome
             FROM registro r
             JOIN equipamento e ON e.id = r.equipamento_id
             WHERE e.nome = :equipamento AND r.execucao_origem IS NULL'
        );
        $comando->execute(['equipamento' => $equipamento]);
        $linhas = $comando->fetchAll(PDO::FETCH_ASSOC);

        usort($linhas, static function (array $linha_a, array $linha_b): int {
            $fuso_utc = new \DateTimeZone('UTC');
            $data_a = new \DateTimeImmutable($linha_a['data'], $fuso_utc);
            $data_b = new \DateTimeImmutable($linha_b['data'], $fuso_utc);
            $comparacao_data = $data_b <=> $data_a;

            return $comparacao_data !== 0
                ? $comparacao_data
                : strcmp($linha_a['nome'], $linha_b['nome']);
        });

        return $linhas === [] ? null : $this->hydrate($linhas[0]);
    }

    private function numericType(int|float $valor): string {
        return is_int($valor) ? 'int' : 'float';
    }

    /**
     * @return array{tipo: string, variavel: ?string, valor: int|float|null,
     *     valor_tipo: ?string, unidade: ?string, estado: ?string}
     */
    private function serializeValue(
        ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro $valor
    ): array {
        if ($valor instanceof ValorNumericoRegistro) {
            return [
                'tipo' => 'numerico',
                'variavel' => $valor->variavel->value,
                'valor' => $valor->valor,
                'valor_tipo' => $this->numericType($valor->valor),
                'unidade' => $valor->unidade->value,
                'estado' => null,
            ];
        }

        if ($valor instanceof HorasOperacaoRegistro) {
            return [
                'tipo' => 'horas_operacao',
                'variavel' => null,
                'valor' => $valor->horas_operacao->valor,
                'valor_tipo' => $this->numericType($valor->horas_operacao->valor),
                'unidade' => $valor->horas_operacao->unidade->value,
                'estado' => null,
            ];
        }

        if ($valor instanceof ObservacaoVisualRegistro) {
            return [
                'tipo' => 'observacao_visual',
                'variavel' => null,
                'valor' => null,
                'valor_tipo' => null,
                'unidade' => null,
                'estado' => $valor->estado->value,
            ];
        }

        return [
            'tipo' => 'vazamento',
            'variavel' => null,
            'valor' => null,
            'valor_tipo' => null,
            'unidade' => null,
            'estado' => $valor->estado->value,
        ];
    }

    /** @param array<string, mixed> $linha */
    private function hydrate(array $linha): Registro {
        $comando = $this->pdo->prepare(
            'SELECT tipo, variavel, valor, valor_tipo, unidade, estado
             FROM registro_valor WHERE registro_id = :registro_id ORDER BY posicao'
        );
        $comando->execute(['registro_id' => $linha['id']]);
        $valores = array_map($this->hydrateValue(...), $comando->fetchAll(PDO::FETCH_ASSOC));

        $execucao = $linha['execucao_origem'] === null ? null : new ExecucaoRegistro(
            origem: $linha['execucao_origem'],
            tempo_execucao: new Tempo(
                valor: $this->numericValue($linha['execucao_tempo_valor'], $linha['execucao_tempo_valor_tipo']),
                unidade: UnidadeTempo::from($linha['execucao_tempo_unidade']),
            ),
            status: StatusRegistro::from($linha['execucao_status']),
        );

        return new Registro(
            nome: $linha['nome'],
            equipamento_identificador: $linha['equipamento_nome'],
            data: new \DateTimeImmutable($linha['data'], new \DateTimeZone('UTC')),
            valores: new ValorRegistradoCollection(...$valores),
            execucao: $execucao,
            relatorio: $linha['relatorio'],
            observacao: $linha['observacao'],
        );
    }

    private function numericValue(int|float|string $valor, string $tipo): int|float {
        return $tipo === 'int' ? (int) $valor : (float) $valor;
    }

    /** @param array<string, mixed> $linha */
    private function hydrateValue(array $linha): ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro {
        return match ($linha['tipo']) {
            'numerico' => new ValorNumericoRegistro(
                variavel: VariavelControlada::from($linha['variavel']),
                valor: $this->numericValue($linha['valor'], $linha['valor_tipo']),
                unidade: UnidadeMedida::from($linha['unidade']),
            ),
            'horas_operacao' => new HorasOperacaoRegistro(new Tempo(
                valor: $this->numericValue($linha['valor'], $linha['valor_tipo']),
                unidade: UnidadeTempo::from($linha['unidade']),
            )),
            'observacao_visual' => new ObservacaoVisualRegistro(
                EstadoObservacao::from($linha['estado'])
            ),
            'vazamento' => new VazamentoRegistro(EstadoVazamento::from($linha['estado'])),
        };
    }
}
