<?php

declare(strict_types=1);

use Domain\CalculadoraPrimeiroVencimentoPreventivo;
use Domain\Enums\Prioridade;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeTempo;
use Domain\Manutencao;
use Domain\Tempo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CalculadoraPrimeiroVencimentoPreventivoTest extends TestCase {
    public static function periodosEmMinutosInteiros(): iterable {
        yield 'uma hora e meia' => [
            new Tempo(1.5, UnidadeTempo::HORA),
            '2026-10-10 08:30:00.000000-03:00',
            '2026-10-10 10:00:00.000000-03:00',
        ];

        yield 'meio dia' => [
            new Tempo(0.5, UnidadeTempo::DIA),
            '2026-10-10 08:30:00.000000-03:00',
            '2026-10-10 20:30:00.000000-03:00',
        ];

        yield 'limite do dia civil' => [
            new Tempo(1, UnidadeTempo::HORA),
            '2026-10-10 23:30:00.000000-03:00',
            '2026-10-11 00:30:00.000000-03:00',
        ];

        yield 'um minuto preservando segundos do cadastro' => [
            new Tempo(1, UnidadeTempo::MINUTO),
            '2026-10-10 08:30:45.123456-03:00',
            '2026-10-10 08:31:45.123456-03:00',
        ];
    }

    #[DataProvider('periodosEmMinutosInteiros')]
    public function testeCalculate_PeriodoPreventivo_AdicionaDuracaoExataAoPrimeiroCadastro(
        Tempo $periodo,
        string $primeiro_cadastro_texto,
        string $vencimento_esperado,
    ): void {
        $primeiro_cadastro = new DateTimeImmutable($primeiro_cadastro_texto);

        $vencimento = (new CalculadoraPrimeiroVencimentoPreventivo())->calculate(
            $this->createPreventiveMaintenance($periodo),
            $primeiro_cadastro,
        );

        self::assertSame($vencimento_esperado, $vencimento->format('Y-m-d H:i:s.uP'));
        self::assertSame($primeiro_cadastro->getTimezone()->getName(), $vencimento->getTimezone()->getName());
    }

    public function testeCalculate_PeriodoIgualAZero_RejeitaValorInvalido(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O período preventivo deve ser maior que zero.');

        (new CalculadoraPrimeiroVencimentoPreventivo())->calculate(
            $this->createPreventiveMaintenance(new Tempo(0, UnidadeTempo::DIA)),
            new DateTimeImmutable('2026-10-10 08:30:00+00:00'),
        );
    }

    public function testeCalculate_FracaoDeMinuto_RejeitaSemArredondar(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('O período preventivo não pode ser representado com precisão de minuto.');

        (new CalculadoraPrimeiroVencimentoPreventivo())->calculate(
            $this->createPreventiveMaintenance(new Tempo(0.1, UnidadeTempo::MINUTO)),
            new DateTimeImmutable('2026-10-10 08:30:00+00:00'),
        );
    }

    public function testeCalculate_TerceiraOcorrencia_MultiplicaPeriodoEmMinutos(): void {
        $vencimento = (new CalculadoraPrimeiroVencimentoPreventivo())->calculate(
            $this->createPreventiveMaintenance(new Tempo(30, UnidadeTempo::MINUTO)),
            new DateTimeImmutable('2026-10-10 08:30:45-03:00'),
            3,
        );

        self::assertSame('2026-10-10 10:00:45.000000-03:00', $vencimento->format('Y-m-d H:i:s.uP'));
    }

    public function testeCalculate_OcorrenciaIgualAZero_RejeitaValorInvalido(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A ocorrência preventiva deve ser maior que zero.');

        (new CalculadoraPrimeiroVencimentoPreventivo())->calculate(
            $this->createPreventiveMaintenance(new Tempo(30, UnidadeTempo::MINUTO)),
            new DateTimeImmutable('2026-10-10 08:30:00-03:00'),
            0,
        );
    }

    public function testeCalculate_DiaAtravessandoHorarioDeVerao_PreservaDuracaoDe86400Segundos(): void {
        $primeiro_cadastro = new DateTimeImmutable('2026-03-08 00:30:00 America/New_York');

        $vencimento = (new CalculadoraPrimeiroVencimentoPreventivo())->calculate(
            $this->createPreventiveMaintenance(new Tempo(1, UnidadeTempo::DIA)),
            $primeiro_cadastro,
        );

        self::assertSame('2026-03-09 01:30:00.000000-04:00', $vencimento->format('Y-m-d H:i:s.uP'));
        self::assertSame('America/New_York', $vencimento->getTimezone()->getName());
        self::assertSame(86_400, $vencimento->getTimestamp() - $primeiro_cadastro->getTimestamp());
    }

    private function createPreventiveMaintenance(Tempo $periodo): Manutencao {
        return new Manutencao(
            nome: 'inspecao_bomba_cr10',
            tipo: TipoManutencao::PREVENTIVA,
            equipamento_identificador: 'bomba_cr10',
            gatilho: $periodo,
            procedimento_identificador: 'inspecionar_bomba',
            prioridade: Prioridade::MEDIA,
            duracao: new Tempo(2, UnidadeTempo::HORA),
            homem_hora: 3.5,
        );
    }
}
