<?php

declare(strict_types=1);

namespace Application;

use Diagnostic\Diagnostic;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticSeverity;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\StatusRegistro;
use Domain\HorasOperacaoRegistro;
use Domain\ObservacaoVisualRegistro;
use Domain\Registro;
use Domain\ValorNumericoRegistro;
use Domain\VazamentoRegistro;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ExecucaoOrdemServicoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\RegistroRepository;
use Infrastructure\Persistence\SqliteDatabase;

final readonly class ImportacaoExecucaoService {
    public function __construct(
        private SqliteDatabase $banco,
        private CMMSProcessor $processador,
        private EquipamentoRepository $equipamentos,
        private ManutencaoRepository $manutencoes,
        private RegistroRepository $registros,
        private OrdemServicoRepository $ordens_servico,
        private ExecucaoOrdemServicoRepository $vinculos,
    ) {}

    public function import(string $codigo): ImportacaoExecucaoResult {
        $processamento = $this->processador->process($codigo, $this->equipamentos->findAll());

        if (!$processamento->isSuccess()) {
            return new ImportacaoExecucaoResult($processamento, null);
        }

        if (count($processamento->objetos) !== 1
            || !$processamento->objetos[0] instanceof Registro
            || $processamento->objetos[0]->execucao === null) {
            return $this->failure('CMMS-EXE-001', 'A importação exige exatamente um registro de execução.');
        }

        $registro = $processamento->objetos[0];

        return $this->banco->transaction(function () use ($processamento, $registro): ImportacaoExecucaoResult {
            $existente = $this->registros->findByIdentifier($registro->nome);

            if ($existente !== null) {
                if ($this->serializeRecord($existente) !== $this->serializeRecord($registro)) {
                    return $this->failure('CMMS-EXE-002', 'O registro já existe com conteúdo diferente.');
                }

                $ordem_identificador = $this->vinculos->findOrderIdentifierByRecord($registro->nome);
                $ordem = $ordem_identificador === null
                    ? null
                    : $this->ordens_servico->findByIdentifier($ordem_identificador);

                return $ordem === null
                    ? $this->failure('CMMS-EXE-003', 'O registro existente não possui vínculo com uma OS.')
                    : new ImportacaoExecucaoResult($processamento, $ordem);
            }

            $manutencao = $this->manutencoes->findByIdentifier($registro->execucao->origem);

            if ($manutencao === null
                || $manutencao->equipamento_identificador !== $registro->equipamento_identificador) {
                return $this->failure('CMMS-EXE-004', 'A origem não identifica manutenção do equipamento registrado.');
            }

            $ordem = $this->ordens_servico->findActiveByPair(
                $registro->equipamento_identificador,
                $manutencao->nome,
            );

            if ($ordem === null) {
                return $this->failure('CMMS-EXE-005', 'Não há OS ativa para a manutenção e o equipamento.');
            }

            $this->registros->save($registro);
            $this->vinculos->save($registro->nome, $ordem->identificador);

            $novo_status = match ($registro->execucao->status) {
                StatusRegistro::EM_ABERTO => null,
                StatusRegistro::EM_EXECUCAO => StatusOrdemServico::EM_EXECUCAO,
                StatusRegistro::CONCLUIDO => StatusOrdemServico::CONCLUIDA,
                StatusRegistro::CANCELADO => StatusOrdemServico::CANCELADA,
            };

            if ($novo_status !== null) {
                $ordem = $this->ordens_servico->updateStatus(
                    $ordem->identificador,
                    $novo_status,
                    $novo_status === StatusOrdemServico::CANCELADA ? $registro->data : null,
                );
            }

            return new ImportacaoExecucaoResult($processamento, $ordem);
        });
    }

    /** @return array<string, mixed> */
    private function serializeRecord(Registro $registro): array {
        return [
            'nome' => $registro->nome,
            'equipamento' => $registro->equipamento_identificador,
            'data' => $registro->data
                ->setTimezone(new \DateTimeZone('UTC'))
                ->format('Y-m-d H:i:s'),
            'valores' => array_map($this->serializeValue(...), $registro->valores->all()),
            'origem' => $registro->execucao?->origem,
            'tempo_valor' => $registro->execucao?->tempo_execucao->valor,
            'tempo_unidade' => $registro->execucao?->tempo_execucao->unidade->value,
            'status' => $registro->execucao?->status->value,
            'relatorio' => $registro->relatorio,
            'observacao' => $registro->observacao,
        ];
    }

    /** @return array<string, int|float|string> */
    private function serializeValue(
        ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro $valor,
    ): array {
        if ($valor instanceof ValorNumericoRegistro) {
            return [
                'tipo' => 'numerico',
                'variavel' => $valor->variavel->value,
                'valor' => $valor->valor,
                'unidade' => $valor->unidade->value,
            ];
        }

        if ($valor instanceof HorasOperacaoRegistro) {
            return [
                'tipo' => 'horas_operacao',
                'valor' => $valor->horas_operacao->valor,
                'unidade' => $valor->horas_operacao->unidade->value,
            ];
        }

        if ($valor instanceof ObservacaoVisualRegistro) {
            return ['tipo' => 'observacao_visual', 'estado' => $valor->estado->value];
        }

        return ['tipo' => 'vazamento', 'estado' => $valor->estado->value];
    }

    private function failure(string $codigo, string $mensagem): ImportacaoExecucaoResult {
        return new ImportacaoExecucaoResult(new ProcessingResult(
            status: ProcessingStatus::SEMANTIC_FAILURE,
            objetos: [],
            diagnosticos: [new Diagnostic(
                codigo: $codigo,
                mensagem: $mensagem,
                origem: DiagnosticOrigin::SEMANTIC,
                severidade: DiagnosticSeverity::ERROR,
            )],
        ), null);
    }
}
