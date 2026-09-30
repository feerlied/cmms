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
use PDO;
use Throwable;

final class ManutencaoRepository {
    public function __construct(private readonly SqliteDatabase $banco) {}

    public function save(Manutencao $manutencao): void {
        $conexao = $this->banco->connection();
        if (!$conexao->inTransaction()) {
            $this->banco->transaction(fn (): mixed => $this->insertMaintenance($conexao, $manutencao));
            return;
        }

        $conexao->exec('SAVEPOINT manutencao_repository_save');
        try {
            $this->insertMaintenance($conexao, $manutencao);
            $conexao->exec('RELEASE SAVEPOINT manutencao_repository_save');
        } catch (Throwable $erro) {
            $conexao->exec('ROLLBACK TO SAVEPOINT manutencao_repository_save');
            $conexao->exec('RELEASE SAVEPOINT manutencao_repository_save');
            throw $erro;
        }
    }

    private function insertMaintenance(PDO $conexao, Manutencao $manutencao): void {
        $gatilho = $manutencao->gatilho instanceof Tempo ? $manutencao->gatilho : null;
        $prazo = $manutencao->prazo;
        $comando = $conexao->prepare(
            'INSERT INTO manutencao (
                nome, equipamento_id, tipo, gatilho_valor, gatilho_valor_tipo, gatilho_unidade,
                procedimento_identificador, prioridade, duracao_valor, duracao_valor_tipo,
                duracao_unidade, homem_hora, homem_hora_tipo, prazo_valor, prazo_valor_tipo, prazo_unidade
            ) VALUES (
                :nome, (SELECT id FROM equipamento WHERE nome = :equipamento_nome), :tipo,
                :gatilho_valor, :gatilho_valor_tipo, :gatilho_unidade,
                :procedimento_identificador, :prioridade, :duracao_valor, :duracao_valor_tipo,
                :duracao_unidade, :homem_hora, :homem_hora_tipo, :prazo_valor, :prazo_valor_tipo, :prazo_unidade
            )'
        );
        $comando->execute([
            'nome' => $manutencao->nome,
            'equipamento_nome' => $manutencao->equipamento_identificador,
            'tipo' => $manutencao->tipo->value,
            'gatilho_valor' => $gatilho?->valor,
            'gatilho_valor_tipo' => $gatilho === null ? null : get_debug_type($gatilho->valor),
            'gatilho_unidade' => $gatilho?->unidade->value,
            'procedimento_identificador' => $manutencao->procedimento_identificador,
            'prioridade' => $manutencao->prioridade->value,
            'duracao_valor' => $manutencao->duracao->valor,
            'duracao_valor_tipo' => get_debug_type($manutencao->duracao->valor),
            'duracao_unidade' => $manutencao->duracao->unidade->value,
            'homem_hora' => $manutencao->homem_hora,
            'homem_hora_tipo' => get_debug_type($manutencao->homem_hora),
            'prazo_valor' => $prazo?->valor,
            'prazo_valor_tipo' => $prazo === null ? null : get_debug_type($prazo->valor),
            'prazo_unidade' => $prazo?->unidade->value,
        ]);

        if (!$manutencao->gatilho instanceof CondicaoCorretiva) {
            return;
        }

        $manutencao_id = (int) $conexao->lastInsertId();
        $comando = $conexao->prepare(
            'INSERT INTO manutencao_condicao (
                manutencao_id, posicao, operador, tipo, variavel, comparador, valor, valor_tipo, unidade, estado
            ) VALUES (
                :manutencao_id, :posicao, :operador, :tipo, :variavel, :comparador,
                :valor, :valor_tipo, :unidade, :estado
            )'
        );
        $operadores = $manutencao->gatilho->getOperators();
        foreach ($manutencao->gatilho->getConditions() as $posicao => $condicao) {
            $dados = [
                'manutencao_id' => $manutencao_id,
                'posicao' => $posicao,
                'operador' => $posicao === 0 ? null : $operadores[$posicao - 1]->value,
                'variavel' => null,
                'comparador' => null,
                'valor' => null,
                'valor_tipo' => null,
                'unidade' => null,
                'estado' => null,
            ];
            if ($condicao instanceof CondicaoNumerica) {
                $dados['tipo'] = 'numerica';
                $dados['variavel'] = $condicao->variavel->value;
                $dados['comparador'] = $condicao->comparador->value;
                $dados['valor'] = $condicao->valor;
                $dados['valor_tipo'] = get_debug_type($condicao->valor);
                $dados['unidade'] = $condicao->unidade->value;
            } elseif ($condicao instanceof CondicaoObservacao) {
                $dados['tipo'] = 'observacao';
                $dados['estado'] = $condicao->estado->value;
            } else {
                $dados['tipo'] = 'vazamento';
                $dados['estado'] = $condicao->estado->value;
            }
            $comando->execute($dados);
        }
    }

    public function findByIdentifier(string $identificador): ?Manutencao {
        $comando = $this->banco->connection()->prepare(
            'SELECT m.*, e.nome AS equipamento_nome
             FROM manutencao m
             JOIN equipamento e ON e.id = m.equipamento_id
             WHERE m.nome = :nome'
        );
        $comando->execute(['nome' => $identificador]);
        $linha = $comando->fetch(PDO::FETCH_ASSOC);

        return $linha === false ? null : $this->deserializeMaintenance($linha);
    }

    /** @return list<Manutencao> */
    public function findByEquipment(string $equipamento_identificador): array {
        $comando = $this->banco->connection()->prepare(
            'SELECT m.*, e.nome AS equipamento_nome
             FROM manutencao m
             JOIN equipamento e ON e.id = m.equipamento_id
             WHERE e.nome = :equipamento_nome
             ORDER BY m.nome'
        );
        $comando->execute(['equipamento_nome' => $equipamento_identificador]);

        return array_map(
            fn (array $linha): Manutencao => $this->deserializeMaintenance($linha),
            $comando->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    private function deserializeCorrectiveCondition(int $manutencao_id): CondicaoCorretiva {
        $comando = $this->banco->connection()->prepare(
            'SELECT * FROM manutencao_condicao WHERE manutencao_id = :manutencao_id ORDER BY posicao'
        );
        $comando->execute(['manutencao_id' => $manutencao_id]);
        $linhas = $comando->fetchAll(PDO::FETCH_ASSOC);

        if ($linhas === []) {
            throw new \UnexpectedValueException('Manutenção corretiva sem condições persistidas.');
        }

        $gatilho = new CondicaoCorretiva($this->deserializeCondition($linhas[0]));
        foreach (array_slice($linhas, 1) as $linha) {
            $gatilho->add(
                OperadorLogico::from($linha['operador']),
                $this->deserializeCondition($linha)
            );
        }

        return $gatilho;
    }

    private function deserializeCondition(array $linha): CondicaoNumerica|CondicaoObservacao|CondicaoVazamento {
        return match ($linha['tipo']) {
            'numerica' => new CondicaoNumerica(
                VariavelControlada::from($linha['variavel']),
                Comparador::from($linha['comparador']),
                $this->number($linha['valor'], $linha['valor_tipo']),
                UnidadeMedida::from($linha['unidade'])
            ),
            'observacao' => new CondicaoObservacao(EstadoObservacao::from($linha['estado'])),
            'vazamento' => new CondicaoVazamento(EstadoVazamento::from($linha['estado'])),
            default => throw new \UnexpectedValueException('Tipo de condição corretiva não reconhecido.'),
        };
    }

    private function deserializeMaintenance(array $linha): Manutencao {
        $tipo = TipoManutencao::from($linha['tipo']);

        return new Manutencao(
            nome: $linha['nome'],
            tipo: $tipo,
            equipamento_identificador: $linha['equipamento_nome'],
            gatilho: $tipo === TipoManutencao::PREVENTIVA
                ? new Tempo(
                    $this->number($linha['gatilho_valor'], $linha['gatilho_valor_tipo']),
                    UnidadeTempo::from($linha['gatilho_unidade'])
                )
                : $this->deserializeCorrectiveCondition((int) $linha['id']),
            procedimento_identificador: $linha['procedimento_identificador'],
            prioridade: Prioridade::from($linha['prioridade']),
            duracao: new Tempo(
                $this->number($linha['duracao_valor'], $linha['duracao_valor_tipo']),
                UnidadeTempo::from($linha['duracao_unidade'])
            ),
            homem_hora: $this->number($linha['homem_hora'], $linha['homem_hora_tipo']),
            prazo: $linha['prazo_valor'] === null ? null : new Tempo(
                $this->number($linha['prazo_valor'], $linha['prazo_valor_tipo']),
                UnidadeTempo::from($linha['prazo_unidade'])
            )
        );
    }

    private function number(int|float|string $valor, string $tipo): int|float {
        return match ($tipo) {
            'int' => (int) $valor,
            'float' => (float) $valor,
            default => throw new \UnexpectedValueException('Tipo numérico persistido não reconhecido.'),
        };
    }
}
