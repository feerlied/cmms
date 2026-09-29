<?php

declare(strict_types=1);

use Domain\Enums\Prioridade;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeTempo;
use Domain\GeradorOrdemServicoPreventiva;
use Domain\Manutencao;
use Domain\Tempo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GeradorOrdemServicoPreventivaTest extends TestCase {
    public static function instantesDeAvaliacao(): iterable {
        yield 'antes do vencimento' => [
            new DateTimeImmutable('2026-10-10 08:29:59+00:00'),
            false,
        ];

        yield 'no vencimento' => [
            new DateTimeImmutable('2026-10-10 08:30:00+00:00'),
            true,
        ];

        yield 'após o vencimento' => [
            new DateTimeImmutable('2026-10-10 08:30:01+00:00'),
            true,
        ];
    }

    #[DataProvider('instantesDeAvaliacao')]
    public function testeGenerate_PrimeiroVencimento_RetornaOrdemSomenteNoLimiteOuDepois(
        DateTimeImmutable $agora,
        bool $deve_gerar,
    ): void {
        $primeiro_vencimento = new DateTimeImmutable('2026-10-10 08:30:00+00:00');

        $ordem_servico = (new GeradorOrdemServicoPreventiva())->generate(
            $this->createPreventiveMaintenance(),
            $primeiro_vencimento,
            $agora,
        );

        if (!$deve_gerar) {
            self::assertNull($ordem_servico);

            return;
        }

        self::assertNotNull($ordem_servico);
        self::assertSame('bomba_cr10', $ordem_servico->equipamento_identificador);
        self::assertSame('inspecao_bomba_cr10', $ordem_servico->manutencao_identificador);
        self::assertSame(TipoManutencao::PREVENTIVA, $ordem_servico->tipo_manutencao);
        self::assertSame('2026-10-10T08:30:00.000000+00:00', $ordem_servico->chave_evento);
        self::assertSame($primeiro_vencimento, $ordem_servico->data_referencia);
        self::assertSame('inspecionar_bomba', $ordem_servico->procedimento_identificador);
        self::assertSame(Prioridade::MEDIA, $ordem_servico->prioridade);
        self::assertSame(2, $ordem_servico->duracao->valor);
        self::assertSame(UnidadeTempo::HORA, $ordem_servico->duracao->unidade);
        self::assertSame(3.5, $ordem_servico->homem_hora);
        self::assertNull($ordem_servico->prazo);
    }

    public function testeGenerate_DatasNoMesmoMinuto_ConsideraVencimentoAtingido(): void {
        $primeiro_vencimento = new DateTimeImmutable('2026-10-10 08:30:59.999999+00:00');
        $gerador = new GeradorOrdemServicoPreventiva();

        self::assertNull($gerador->generate(
            $this->createPreventiveMaintenance(),
            $primeiro_vencimento,
            new DateTimeImmutable('2026-10-10 08:29:59.999999+00:00'),
        ));

        $ordem_servico = $gerador->generate(
            $this->createPreventiveMaintenance(),
            $primeiro_vencimento,
            new DateTimeImmutable('2026-10-10 08:30:00+00:00'),
        );

        self::assertNotNull($ordem_servico);
        self::assertSame($primeiro_vencimento, $ordem_servico->data_referencia);
    }

    public function testeGenerate_VencimentoComMicrossegundos_PreservaPrecisaoNaChaveDoEvento(): void {
        $primeiro_vencimento = new DateTimeImmutable('2026-10-10 08:30:00.000060-03:00');

        $ordem_servico = (new GeradorOrdemServicoPreventiva())->generate(
            $this->createPreventiveMaintenance(),
            $primeiro_vencimento,
            $primeiro_vencimento,
        );

        self::assertNotNull($ordem_servico);
        self::assertSame('2026-10-10T08:30:00.000060-03:00', $ordem_servico->chave_evento);
        self::assertSame($primeiro_vencimento, $ordem_servico->data_referencia);
    }

    private function createPreventiveMaintenance(): Manutencao {
        return new Manutencao(
            nome: 'inspecao_bomba_cr10',
            tipo: TipoManutencao::PREVENTIVA,
            equipamento_identificador: 'bomba_cr10',
            gatilho: new Tempo(30, UnidadeTempo::DIA),
            procedimento_identificador: 'inspecionar_bomba',
            prioridade: Prioridade::MEDIA,
            duracao: new Tempo(2, UnidadeTempo::HORA),
            homem_hora: 3.5,
        );
    }
}
