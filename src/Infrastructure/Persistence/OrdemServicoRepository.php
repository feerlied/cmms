<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

use DateTimeImmutable;
use DateTimeZone;
use Domain\Enums\Prioridade;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeTempo;
use Domain\OrdemServico;
use Domain\Tempo;
use PDO;

final class OrdemServicoRepository {
    public function __construct(private readonly SqliteDatabase $banco) {}

    public function save(OrdemServico $ordem): void {
        if ($ordem->status !== StatusOrdemServico::EM_ABERTO) {
            throw new \InvalidArgumentException('Uma nova ordem de serviço deve iniciar em aberto.');
        }

        $comando = $this->banco->connection()->prepare(<<<'SQL'
            INSERT INTO ordem_servico (
                identificador, manutencao_id, tipo_evento, chave_evento,
                data_referencia, status, data_cancelamento,
                procedimento_identificador, prioridade, duracao_valor, duracao_valor_tipo,
                duracao_unidade, homem_hora, homem_hora_tipo, prazo_valor, prazo_valor_tipo, prazo_unidade
            ) SELECT
                :identificador, m.id, :tipo_evento, :chave_evento,
                :data_referencia, :status, NULL,
                :procedimento_identificador, :prioridade, :duracao_valor, :duracao_valor_tipo,
                :duracao_unidade, :homem_hora, :homem_hora_tipo, :prazo_valor, :prazo_valor_tipo, :prazo_unidade
            FROM manutencao AS m
            JOIN equipamento AS e ON e.id = m.equipamento_id
            WHERE m.nome = :manutencao
              AND e.nome = :equipamento
            SQL);
        $comando->execute([
            'identificador' => $ordem->identificador,
            'equipamento' => $ordem->equipamento_identificador,
            'manutencao' => $ordem->manutencao_identificador,
            'tipo_evento' => $ordem->tipo_manutencao->value,
            'chave_evento' => $ordem->chave_evento,
            'data_referencia' => $ordem->data_referencia
                ->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d H:i:s'),
            'status' => $ordem->status->value,
            'procedimento_identificador' => $ordem->procedimento_identificador,
            'prioridade' => $ordem->prioridade->value,
            'duracao_valor' => $ordem->duracao->valor,
            'duracao_valor_tipo' => get_debug_type($ordem->duracao->valor),
            'duracao_unidade' => $ordem->duracao->unidade->value,
            'homem_hora' => $ordem->homem_hora,
            'homem_hora_tipo' => get_debug_type($ordem->homem_hora),
            'prazo_valor' => $ordem->prazo?->valor,
            'prazo_valor_tipo' => $ordem->prazo === null ? null : get_debug_type($ordem->prazo->valor),
            'prazo_unidade' => $ordem->prazo?->unidade->value,
        ]);

        if ($comando->rowCount() !== 1) {
            throw new \InvalidArgumentException('A manutenção não pertence ao equipamento da ordem de serviço.');
        }
    }

    public function findByIdentifier(string $identificador): ?OrdemServico {
        $comando = $this->banco->connection()->prepare(<<<'SQL'
            SELECT os.*, m.nome AS manutencao, e.nome AS equipamento
            FROM ordem_servico AS os
            JOIN manutencao AS m ON m.id = os.manutencao_id
            JOIN equipamento AS e ON e.id = m.equipamento_id
            WHERE os.identificador = :identificador
            SQL);
        $comando->execute(['identificador' => $identificador]);
        $linha = $comando->fetch(PDO::FETCH_ASSOC);

        return $linha === false ? null : $this->deserializeServiceOrder($linha);
    }

    /** @return list<OrdemServico> */
    public function findByReferencePeriod(
        DateTimeImmutable $inicio,
        DateTimeImmutable $fim,
        ?string $equipamento_identificador = null,
        ?StatusOrdemServico $status = null,
    ): array {
        if ($fim <= $inicio) {
            throw new \InvalidArgumentException('O fim do período deve ser posterior ao início.');
        }

        $sql = <<<'SQL'
            SELECT os.*, m.nome AS manutencao, e.nome AS equipamento
            FROM ordem_servico AS os
            JOIN manutencao AS m ON m.id = os.manutencao_id
            JOIN equipamento AS e ON e.id = m.equipamento_id
            WHERE os.data_referencia >= :inicio
              AND os.data_referencia < :fim
            SQL;
        $parametros = [
            'inicio' => $inicio->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
            'fim' => $fim->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
        ];

        if ($equipamento_identificador !== null) {
            $sql .= ' AND e.nome = :equipamento';
            $parametros['equipamento'] = $equipamento_identificador;
        }

        if ($status !== null) {
            $sql .= ' AND os.status = :status';
            $parametros['status'] = $status->value;
        }

        $sql .= ' ORDER BY os.data_referencia, os.identificador';
        $comando = $this->banco->connection()->prepare($sql);
        $comando->execute($parametros);
        $ordens = [];

        while (($linha = $comando->fetch(PDO::FETCH_ASSOC)) !== false) {
            $ordens[] = $this->deserializeServiceOrder($linha);
        }

        return $ordens;
    }

    public function findByEvent(
        string $equipamento_identificador,
        string $manutencao_identificador,
        TipoManutencao $tipo,
        string $chave_evento
    ): ?OrdemServico {
        $comando = $this->banco->connection()->prepare(<<<'SQL'
            SELECT os.*, m.nome AS manutencao, e.nome AS equipamento
            FROM ordem_servico AS os
            JOIN manutencao AS m ON m.id = os.manutencao_id
            JOIN equipamento AS e ON e.id = m.equipamento_id
            WHERE e.nome = :equipamento
              AND m.nome = :manutencao
              AND os.tipo_evento = :tipo_evento
              AND os.chave_evento = :chave_evento
            SQL);
        $comando->execute([
            'equipamento' => $equipamento_identificador,
            'manutencao' => $manutencao_identificador,
            'tipo_evento' => $tipo->value,
            'chave_evento' => $chave_evento,
        ]);
        $linha = $comando->fetch(PDO::FETCH_ASSOC);

        return $linha === false ? null : $this->deserializeServiceOrder($linha);
    }

    public function findOpenByPair(string $equipamento_identificador, string $manutencao_identificador): ?OrdemServico {
        return $this->findActiveByPair($equipamento_identificador, $manutencao_identificador);
    }

    public function findActiveByPair(string $equipamento_identificador, string $manutencao_identificador): ?OrdemServico {
        $comando = $this->banco->connection()->prepare(<<<'SQL'
            SELECT os.*, m.nome AS manutencao, e.nome AS equipamento
            FROM ordem_servico AS os
            JOIN manutencao AS m ON m.id = os.manutencao_id
            JOIN equipamento AS e ON e.id = m.equipamento_id
            WHERE e.nome = :equipamento
              AND m.nome = :manutencao
              AND os.status IN (:status_aberto, :status_execucao)
            ORDER BY os.data_referencia DESC, os.identificador DESC
            LIMIT 1
            SQL);
        $comando->execute([
            'equipamento' => $equipamento_identificador,
            'manutencao' => $manutencao_identificador,
            'status_aberto' => StatusOrdemServico::EM_ABERTO->value,
            'status_execucao' => StatusOrdemServico::EM_EXECUCAO->value,
        ]);
        $linha = $comando->fetch(PDO::FETCH_ASSOC);

        return $linha === false ? null : $this->deserializeServiceOrder($linha);
    }

    public function hasCancellationOnCivilDay(
        string $equipamento_identificador,
        string $manutencao_identificador,
        DateTimeImmutable $data_leitura,
    ): bool {
        $fuso_utc = new DateTimeZone('UTC');
        $inicio_utc = $data_leitura->setTimezone($fuso_utc)->setTime(0, 0);
        $fim_utc = $inicio_utc->modify('+1 day');
        $comando = $this->banco->connection()->prepare(<<<'SQL'
            SELECT 1
            FROM ordem_servico AS os
            JOIN manutencao AS m ON m.id = os.manutencao_id
            JOIN equipamento AS e ON e.id = m.equipamento_id
            WHERE e.nome = :equipamento
              AND m.nome = :manutencao
              AND os.status = :status
              AND os.data_cancelamento >= :inicio
              AND os.data_cancelamento < :fim
            LIMIT 1
            SQL);
        $comando->execute([
            'equipamento' => $equipamento_identificador,
            'manutencao' => $manutencao_identificador,
            'status' => StatusOrdemServico::CANCELADA->value,
            'inicio' => $inicio_utc->format('Y-m-d H:i:s'),
            'fim' => $fim_utc->format('Y-m-d H:i:s'),
        ]);

        return $comando->fetchColumn() !== false;
    }

    public function updateStatus(
        string $identificador,
        StatusOrdemServico $novo_status,
        ?DateTimeImmutable $data_cancelamento = null,
    ): ?OrdemServico {
        $ordem = $this->findByIdentifier($identificador);

        if ($ordem === null) {
            return null;
        }

        $atualizada = $ordem->transitionTo($novo_status, $data_cancelamento);

        if ($atualizada === $ordem) {
            return $ordem;
        }

        $comando = $this->banco->connection()->prepare(<<<'SQL'
            UPDATE ordem_servico
            SET status = :novo_status, data_cancelamento = :data_cancelamento
            WHERE identificador = :identificador AND status = :status_anterior
            SQL);
        $comando->execute([
            'novo_status' => $novo_status->value,
            'data_cancelamento' => $data_cancelamento?->setTimezone(new DateTimeZone('UTC'))
                ->format('Y-m-d H:i:s'),
            'identificador' => $identificador,
            'status_anterior' => $ordem->status->value,
        ]);

        if ($comando->rowCount() !== 1) {
            throw new \RuntimeException('O estado da ordem de serviço mudou durante a atualização.');
        }

        return $this->findByIdentifier($identificador);
    }

    private function deserializeNumber(int|float $valor, string $tipo): int|float {
        return $tipo === 'int' ? (int) $valor : (float) $valor;
    }

    private function deserializeServiceOrder(array $linha): OrdemServico {
        $fuso_utc = new DateTimeZone('UTC');

        $ordem = new OrdemServico(
            equipamento_identificador: $linha['equipamento'],
            manutencao_identificador: $linha['manutencao'],
            tipo_manutencao: TipoManutencao::from($linha['tipo_evento']),
            chave_evento: $linha['chave_evento'],
            data_referencia: new DateTimeImmutable($linha['data_referencia'], $fuso_utc),
            procedimento_identificador: $linha['procedimento_identificador'],
            prioridade: Prioridade::from($linha['prioridade']),
            duracao: new Tempo(
                $this->deserializeNumber($linha['duracao_valor'], $linha['duracao_valor_tipo']),
                UnidadeTempo::from($linha['duracao_unidade'])
            ),
            homem_hora: $this->deserializeNumber($linha['homem_hora'], $linha['homem_hora_tipo']),
            prazo: $linha['prazo_valor'] === null ? null : new Tempo(
                $this->deserializeNumber($linha['prazo_valor'], $linha['prazo_valor_tipo']),
                UnidadeTempo::from($linha['prazo_unidade'])
            ),
            status: StatusOrdemServico::from($linha['status']),
            data_cancelamento: $linha['data_cancelamento'] === null
                ? null
                : new DateTimeImmutable($linha['data_cancelamento'], $fuso_utc),
        );

        if ($ordem->identificador !== $linha['identificador']) {
            throw new \UnexpectedValueException('A identidade persistida da ordem de serviço é inconsistente.');
        }

        return $ordem;
    }
}
