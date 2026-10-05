<?php

declare(strict_types=1);

namespace Application;

use Diagnostic\Diagnostic;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticSeverity;
use Domain\Enums\TipoManutencao;
use Domain\GeradorOrdemServicoCorretiva;
use Domain\HorasOperacaoRegistro;
use Domain\Manutencao;
use Domain\ObservacaoVisualRegistro;
use Domain\OrdemServico;
use Domain\Registro;
use Domain\ValorNumericoRegistro;
use Domain\VazamentoRegistro;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\RegistroRepository;
use Infrastructure\Persistence\SqliteDatabase;

final readonly class ImportacaoLeituraService {
    public function __construct(
        private SqliteDatabase $banco,
        private CMMSProcessor $processador,
        private EquipamentoRepository $equipamento_repository,
        private ManutencaoRepository $manutencao_repository,
        private RegistroRepository $registro_repository,
        private OrdemServicoRepository $ordem_servico_repository,
        private GeradorOrdemServicoCorretiva $gerador_corretivo,
    ) {}

    public function import(string $codigo): ImportacaoLeituraResult {
        $processamento = $this->processador->process(
            $codigo,
            $this->equipamento_repository->findAll(),
        );

        if (!$processamento->isSuccess()) {
            return new ImportacaoLeituraResult($processamento, []);
        }

        $registro = $this->extractReading($processamento);

        if ($registro === null) {
            return new ImportacaoLeituraResult(
                $this->createFailure(
                    'CMMS-IMP-001',
                    'A importação exige exatamente um registro de leitura sem bloco de execução.',
                ),
                [],
            );
        }

        return $this->banco->transaction(function () use ($processamento, $registro): ImportacaoLeituraResult {
            $registro_persistido = $this->registro_repository->findByIdentifier($registro->nome);

            if ($registro_persistido !== null) {
                if (!$this->hasSameContent($registro, $registro_persistido)) {
                    return new ImportacaoLeituraResult(
                        $this->createFailure(
                            'CMMS-IMP-002',
                            "O registro '{$registro->nome}' já existe com conteúdo diferente.",
                        ),
                        [],
                    );
                }

                return new ImportacaoLeituraResult(
                    $processamento,
                    $this->findOrdersForExistingReading($registro_persistido),
                );
            }

            $this->registro_repository->save($registro);
            $leitura_mais_recente = $this->registro_repository->findLatestReadingByEquipment(
                $registro->equipamento_identificador,
            );

            if ($leitura_mais_recente === null || $leitura_mais_recente->nome !== $registro->nome) {
                return new ImportacaoLeituraResult($processamento, []);
            }

            return new ImportacaoLeituraResult(
                $processamento,
                $this->generateOrdersForLatestReading($leitura_mais_recente),
            );
        });
    }

    private function extractReading(ProcessingResult $processamento): ?Registro {
        if (count($processamento->objetos) !== 1
            || !$processamento->objetos[0] instanceof Registro
            || $processamento->objetos[0]->execucao !== null) {
            return null;
        }

        return $processamento->objetos[0];
    }

    /** @return list<OrdemServico> */
    private function findOrdersForExistingReading(Registro $registro): array {
        $ordens_servico = [];

        foreach ($this->manutencao_repository->findByEquipment($registro->equipamento_identificador) as $manutencao) {
            if ($manutencao->tipo !== TipoManutencao::CORRETIVA) {
                continue;
            }

            $ordem_candidata = $this->gerador_corretivo->generate($manutencao, $registro);

            if ($ordem_candidata === null) {
                continue;
            }

            $ordem_existente = $this->ordem_servico_repository->findByEvent(
                $ordem_candidata->equipamento_identificador,
                $ordem_candidata->manutencao_identificador,
                $ordem_candidata->tipo_manutencao,
                $ordem_candidata->chave_evento,
            );

            if ($ordem_existente !== null) {
                $ordens_servico[] = $ordem_existente;
                continue;
            }

            if ($this->ordem_servico_repository->hasCancellationOnCivilDay(
                $ordem_candidata->equipamento_identificador,
                $ordem_candidata->manutencao_identificador,
                $registro->data,
            )) {
                continue;
            }

            $ordem_existente = $this->ordem_servico_repository->findOpenByPair(
                $ordem_candidata->equipamento_identificador,
                $ordem_candidata->manutencao_identificador,
            );

            if ($ordem_existente !== null) {
                $ordens_servico[] = $ordem_existente;
            }
        }

        return $ordens_servico;
    }

    /** @return list<OrdemServico> */
    private function generateOrdersForLatestReading(Registro $registro): array {
        $ordens_servico = [];

        foreach ($this->manutencao_repository->findByEquipment($registro->equipamento_identificador) as $manutencao) {
            if ($manutencao->tipo !== TipoManutencao::CORRETIVA) {
                continue;
            }

            $ordem_candidata = $this->gerador_corretivo->generate($manutencao, $registro);

            if ($ordem_candidata === null) {
                continue;
            }

            $ordem_existente = $this->ordem_servico_repository->findByEvent(
                $ordem_candidata->equipamento_identificador,
                $ordem_candidata->manutencao_identificador,
                $ordem_candidata->tipo_manutencao,
                $ordem_candidata->chave_evento,
            );

            if ($ordem_existente !== null) {
                $ordens_servico[] = $ordem_existente;
                continue;
            }

            if ($this->ordem_servico_repository->hasCancellationOnCivilDay(
                $ordem_candidata->equipamento_identificador,
                $ordem_candidata->manutencao_identificador,
                $registro->data,
            )) {
                continue;
            }

            $ordem_existente = $this->ordem_servico_repository->findOpenByPair(
                $ordem_candidata->equipamento_identificador,
                $ordem_candidata->manutencao_identificador,
            );

            if ($ordem_existente !== null) {
                $ordens_servico[] = $ordem_existente;
                continue;
            }

            $this->ordem_servico_repository->save($ordem_candidata);
            $ordens_servico[] = $ordem_candidata;
        }

        return $ordens_servico;
    }

    private function hasSameContent(Registro $registro_a, Registro $registro_b): bool {
        return $this->serializeRecord($registro_a) === $this->serializeRecord($registro_b);
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
            'execucao' => $registro->execucao === null ? null : [
                'origem' => $registro->execucao->origem,
                'tempo' => [
                    'valor' => $registro->execucao->tempo_execucao->valor,
                    'unidade' => $registro->execucao->tempo_execucao->unidade->value,
                ],
                'status' => $registro->execucao->status->value,
            ],
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
            return [
                'tipo' => 'observacao_visual',
                'estado' => $valor->estado->value,
            ];
        }

        return [
            'tipo' => 'vazamento',
            'estado' => $valor->estado->value,
        ];
    }

    private function createFailure(string $codigo, string $mensagem): ProcessingResult {
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
