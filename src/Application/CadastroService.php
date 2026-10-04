<?php

declare(strict_types=1);

namespace Application;

use Diagnostic\Diagnostic;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticSeverity;
use Domain\Equipamento;
use Domain\Manutencao;
use Domain\Registro;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\SqliteDatabase;
use PDO;

final readonly class CadastroService {
    public function __construct(
        private SqliteDatabase $banco,
        private EquipamentoRepository $equipamentos,
        private ManutencaoRepository $manutencoes,
        private CMMSProcessor $processador,
    ) {}

    public function import(string $codigo, \DateTimeImmutable $agora): ProcessingResult {
        $resultado = $this->processador->process($codigo, $this->equipamentos->findAll());

        if (!$resultado->isSuccess()) {
            return $resultado;
        }

        foreach ($resultado->objetos as $objeto) {
            if ($objeto instanceof Registro) {
                return $this->failure('CMMS-CAD-001', 'O cadastro aceita somente equipamentos e manutenções.');
            }
        }

        foreach ($resultado->objetos as $objeto) {
            if ($objeto instanceof Manutencao && $this->manutencoes->findByIdentifier($objeto->nome) !== null) {
                return $this->failure(
                    'CMMS-CAD-002',
                    "A manutenção '{$objeto->nome}' já está cadastrada."
                );
            }
        }

        $this->banco->transaction(function (PDO $conexao) use ($resultado, $agora): void {
            foreach ($resultado->objetos as $objeto) {
                if ($objeto instanceof Equipamento) {
                    $this->equipamentos->save($objeto, $agora);
                }
            }

            foreach ($resultado->objetos as $objeto) {
                if ($objeto instanceof Manutencao) {
                    $this->manutencoes->save($objeto);
                }
            }
        });

        return $resultado;
    }

    private function failure(string $codigo, string $mensagem): ProcessingResult {
        return new ProcessingResult(
            status: ProcessingStatus::SEMANTIC_FAILURE,
            objetos: [],
            diagnosticos: [new Diagnostic(
                codigo: $codigo,
                mensagem: $mensagem,
                origem: DiagnosticOrigin::SEMANTIC,
                severidade: DiagnosticSeverity::ERROR,
            )],
        );
    }
}
