<?php

declare(strict_types=1);

use Diagnostic\Diagnostic;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticPosition;
use Diagnostic\DiagnosticRange;
use Diagnostic\DiagnosticSeverity;
use PHPUnit\Framework\TestCase;

final class DiagnosticTest extends TestCase {
    public function testeDiagnostic_DadosCompletos_PreservaTodosOsCampos(): void {
        $inicio = new DiagnosticPosition(2, 4);
        $fim = new DiagnosticPosition(2, 11);
        $range = new DiagnosticRange($inicio, $fim);

        $diagnostico = new Diagnostic(
            codigo: 'CMMS-SYN-001',
            mensagem: "Entrada incompatível com a regra 'tipo'.",
            origem: DiagnosticOrigin::SYNTACTIC,
            severidade: DiagnosticSeverity::ERROR,
            range: $range,
        );

        self::assertSame('CMMS-SYN-001', $diagnostico->codigo);
        self::assertSame("Entrada incompatível com a regra 'tipo'.", $diagnostico->mensagem);
        self::assertSame(DiagnosticOrigin::SYNTACTIC, $diagnostico->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $diagnostico->severidade);
        self::assertSame($range, $diagnostico->range);
        self::assertSame(2, $diagnostico->range->inicio->linha);
        self::assertSame(4, $diagnostico->range->inicio->coluna);
        self::assertSame(2, $diagnostico->range->fim->linha);
        self::assertSame(11, $diagnostico->range->fim->coluna);
    }

    public function testeDiagnostic_CodigoERangeAusentes_AceitaCamposOpcionais(): void {
        $diagnostico = new Diagnostic(
            codigo: null,
            mensagem: 'Mensagem de diagnóstico.',
            origem: DiagnosticOrigin::SEMANTIC,
            severidade: DiagnosticSeverity::WARNING,
        );

        self::assertNull($diagnostico->codigo);
        self::assertNull($diagnostico->range);
    }

    public function testeDiagnostic_CodigoVazio_RejeitaDiagnosticoInvalido(): void {
        $this->expectException(InvalidArgumentException::class);

        new Diagnostic(
            codigo: ' ',
            mensagem: 'Mensagem de diagnóstico.',
            origem: DiagnosticOrigin::LEXICAL,
            severidade: DiagnosticSeverity::ERROR,
        );
    }

    public function testeDiagnostic_MensagemVazia_RejeitaDiagnosticoInvalido(): void {
        $this->expectException(InvalidArgumentException::class);

        new Diagnostic(
            codigo: 'CMMS-LEX-001',
            mensagem: ' ',
            origem: DiagnosticOrigin::LEXICAL,
            severidade: DiagnosticSeverity::ERROR,
        );
    }

    public function testeDiagnosticPosition_LinhaNegativa_RejeitaPosicaoInvalida(): void {
        $this->expectException(InvalidArgumentException::class);

        new DiagnosticPosition(-1, 0);
    }

    public function testeDiagnosticPosition_ColunaNegativa_RejeitaPosicaoInvalida(): void {
        $this->expectException(InvalidArgumentException::class);

        new DiagnosticPosition(0, -1);
    }

    public function testeDiagnosticRange_FimAnteriorAoInicio_RejeitaRangeInvalido(): void {
        $this->expectException(InvalidArgumentException::class);

        new DiagnosticRange(
            new DiagnosticPosition(3, 0),
            new DiagnosticPosition(2, 8),
        );
    }

    public function testeDiagnosticRange_InicioIgualAoFim_AceitaRangeVazio(): void {
        $posicao = new DiagnosticPosition(1, 5);
        $range = new DiagnosticRange($posicao, $posicao);

        self::assertSame($posicao, $range->inicio);
        self::assertSame($posicao, $range->fim);
    }
}
