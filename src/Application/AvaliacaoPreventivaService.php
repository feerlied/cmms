<?php

declare(strict_types=1);

namespace Application;

use Domain\CalculadoraPrimeiroVencimentoPreventivo;
use Domain\Enums\TipoManutencao;
use Domain\Enums\StatusOrdemServico;
use Domain\GeradorOrdemServicoPreventiva;
use Domain\OrdemServico;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\SqliteDatabase;
use PDO;

final readonly class AvaliacaoPreventivaService {
    public function __construct(
        private SqliteDatabase $banco,
        private EquipamentoRepository $equipamentos,
        private ManutencaoRepository $manutencoes,
        private OrdemServicoRepository $ordens_servico,
        private CalculadoraPrimeiroVencimentoPreventivo $calculadora,
        private GeradorOrdemServicoPreventiva $gerador,
    ) {}

    /** @return list<OrdemServico> */
    public function evaluate(string $equipamento_identificador, \DateTimeImmutable $agora): array {
        return $this->banco->transaction(function (PDO $conexao) use ($equipamento_identificador, $agora): array {
            $primeiro_cadastro = $this->equipamentos->findFirstRegistrationAt($equipamento_identificador);

            if ($primeiro_cadastro === null) {
                return [];
            }

            $resultado = [];

            foreach ($this->manutencoes->findByEquipment($equipamento_identificador) as $manutencao) {
                if ($manutencao->tipo !== TipoManutencao::PREVENTIVA) {
                    continue;
                }

                $selecionada = null;

                // Percorre o histórico, mas cria no máximo uma ocorrência por manutenção.
                for ($ocorrencia = 1; ; $ocorrencia++) {
                    $vencimento = $this->calculadora->calculate(
                        $manutencao,
                        $primeiro_cadastro,
                        $ocorrencia,
                    );

                    $minuto_atual = intdiv($agora->getTimestamp(), 60);
                    $minuto_vencimento = intdiv($vencimento->getTimestamp(), 60);

                    if ($minuto_vencimento > $minuto_atual) {
                        break;
                    }

                    $candidata = $this->gerador->generate($manutencao, $vencimento, $agora);
                    $existente = $this->ordens_servico->findByEvent(
                        $equipamento_identificador,
                        $manutencao->nome,
                        TipoManutencao::PREVENTIVA,
                        $candidata->chave_evento,
                    );

                    if ($existente !== null) {
                        $selecionada = $existente;

                        if (in_array($existente->status, [
                            StatusOrdemServico::EM_ABERTO,
                            StatusOrdemServico::EM_EXECUCAO,
                        ], true)) {
                            break;
                        }

                        continue;
                    }

                    $impeditiva = $this->ordens_servico->findActiveByPair(
                        $equipamento_identificador,
                        $manutencao->nome,
                    );
                    $selecionada = $impeditiva ?? $candidata;

                    if ($impeditiva === null) {
                        $this->ordens_servico->save($candidata);
                    }

                    break;
                }

                if ($selecionada !== null) {
                    $resultado[] = $selecionada;
                }
            }

            return $resultado;
        });
    }
}
