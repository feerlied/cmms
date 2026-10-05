<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

use PDO;

final readonly class ExecucaoOrdemServicoRepository {
    public function __construct(private PDO $pdo) {}

    public function save(string $registro_identificador, string $ordem_servico_identificador): void {
        $comando = $this->pdo->prepare(
            'INSERT INTO execucao_ordem_servico (registro_id, ordem_servico_id)
             SELECT r.id, os.id
             FROM registro AS r
             CROSS JOIN ordem_servico AS os
             WHERE r.nome = :registro AND os.identificador = :ordem_servico'
        );
        $comando->execute([
            'registro' => $registro_identificador,
            'ordem_servico' => $ordem_servico_identificador,
        ]);

        if ($comando->rowCount() !== 1) {
            throw new \InvalidArgumentException('Registro ou ordem de serviço inexistente para a execução.');
        }
    }

    public function findOrderIdentifierByRecord(string $registro_identificador): ?string {
        $comando = $this->pdo->prepare(
            'SELECT os.identificador
             FROM execucao_ordem_servico AS eos
             JOIN registro AS r ON r.id = eos.registro_id
             JOIN ordem_servico AS os ON os.id = eos.ordem_servico_id
             WHERE r.nome = :registro'
        );
        $comando->execute(['registro' => $registro_identificador]);
        $identificador = $comando->fetchColumn();

        return $identificador === false ? null : $identificador;
    }
}
