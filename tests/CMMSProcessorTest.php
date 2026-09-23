<?php

use Application\CMMSProcessor;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticSeverity;
use Domain\Equipamento;
use Domain\Manutencao;
use Domain\Registro;
use PHPUnit\Framework\TestCase;

final class CMMSProcessorTest extends TestCase {
    public function testeProcess_ProgramaValidoComTodasAsDeclaracoes_RetornaObjetosDeDomainNaOrdemDoCodigo(): void {
        $codigo =
"equipamento bomba_cr10 {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        pressao 10 bar
    }
    variaveis_controladas {
        pressao
    }
}

manutencao preventiva inspecao_bomba_cr10 {
    equipamento bomba_cr10
    a_cada 30 dia
    procedimento inspecionar_bomba
    prioridade media
    duracao 2 hora
    homem_hora 2
}

registro leitura_bomba_cr10 {
    equipamento bomba_cr10
    data 10/09/2026-08:30
    valores {
        pressao 9.7 bar
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertTrue($resultado->isSuccess());
        self::assertSame([], $resultado->diagnosticos);
        self::assertCount(3, $resultado->objetos);
        self::assertInstanceOf(Equipamento::class, $resultado->objetos[0]);
        self::assertInstanceOf(Manutencao::class, $resultado->objetos[1]);
        self::assertInstanceOf(Registro::class, $resultado->objetos[2]);
        self::assertSame('bomba_cr10', $resultado->objetos[0]->nome);
        self::assertSame('inspecao_bomba_cr10', $resultado->objetos[1]->nome);
        self::assertSame('leitura_bomba_cr10', $resultado->objetos[2]->nome);
    }

    public function testeProcess_CaractereInvalido_RetornaDiagnosticoLexicoSemObjetos(): void {
        $resultado = (new CMMSProcessor())->process('equipamento bomba @');

        self::assertFalse($resultado->isSuccess());
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-LEX-001', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::LEXICAL, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
        self::assertSame("token recognition error at: '@'", $resultado->diagnosticos[0]->mensagem);
        self::assertSame(0, $resultado->diagnosticos[0]->range->inicio->linha);
        self::assertSame(18, $resultado->diagnosticos[0]->range->inicio->coluna);
        self::assertSame(0, $resultado->diagnosticos[0]->range->fim->linha);
        self::assertSame(19, $resultado->diagnosticos[0]->range->fim->coluna);
    }

    public function testeProcess_CampoObrigatorioAusente_NaoExecutaVisitorERetornaDiagnosticoSintatico(): void {
        $resultado = (new CMMSProcessor())->process('equipamento bomba {}');

        self::assertFalse($resultado->isSuccess());
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SYN-001', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SYNTACTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
        self::assertStringContainsString("mismatched input '}'", $resultado->diagnosticos[0]->mensagem);
        self::assertSame(0, $resultado->diagnosticos[0]->range->inicio->linha);
        self::assertSame(19, $resultado->diagnosticos[0]->range->inicio->coluna);
        self::assertSame(0, $resultado->diagnosticos[0]->range->fim->linha);
        self::assertSame(20, $resultado->diagnosticos[0]->range->fim->coluna);
    }
}
