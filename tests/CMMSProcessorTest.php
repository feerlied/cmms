<?php

use Application\CMMSProcessor;
use Application\ProcessingStatus;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticSeverity;
use Domain\Equipamento;
use Domain\Manutencao;
use Domain\Registro;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CMMSProcessorTest extends TestCase {
    public function testeProcess_ProgramaMinimoValido_RetornaEquipamentoDeDomain(): void {
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
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertTrue($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SUCCESS, $resultado->status);
        self::assertSame([], $resultado->diagnosticos);
        self::assertCount(1, $resultado->objetos);
        self::assertInstanceOf(Equipamento::class, $resultado->objetos[0]);
        self::assertSame('bomba_cr10', $resultado->objetos[0]->nome);
    }

    public function testeProcess_ProgramaCompletoValido_RetornaTodasAsEntidadesDeDomainNaOrdemDoCodigo(): void {
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
        observacao_visual
        vazamento
    }
}

manutencao preventiva inspecao_bomba_cr10 {
    equipamento bomba_cr10
    a_cada 1 dia
    procedimento inspecionar_bomba
    prioridade media
    duracao 2 hora
    homem_hora 2
}

manutencao corretiva reparar_bomba_cr10 {
    equipamento bomba_cr10
    quando pressao maior 12 bar e observacao_visual anormal ou vazamento grave
    procedimento reparar_bomba
    prioridade alta
    duracao 2 hora
    prazo 1 dia
    homem_hora 2
}

registro leitura_bomba_cr10 {
    equipamento bomba_cr10
    data 10/09/2026-08:30
    valores {
        pressao 13 bar
        observacao_visual anormal
        vazamento grave
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertTrue($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SUCCESS, $resultado->status);
        self::assertSame([], $resultado->diagnosticos);
        self::assertCount(4, $resultado->objetos);
        self::assertInstanceOf(Equipamento::class, $resultado->objetos[0]);
        self::assertInstanceOf(Manutencao::class, $resultado->objetos[1]);
        self::assertInstanceOf(Manutencao::class, $resultado->objetos[2]);
        self::assertInstanceOf(Registro::class, $resultado->objetos[3]);
        self::assertSame('bomba_cr10', $resultado->objetos[0]->nome);
        self::assertSame('inspecao_bomba_cr10', $resultado->objetos[1]->nome);
        self::assertSame('reparar_bomba_cr10', $resultado->objetos[2]->nome);
        self::assertSame('leitura_bomba_cr10', $resultado->objetos[3]->nome);
    }

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
        self::assertSame(ProcessingStatus::SUCCESS, $resultado->status);
        self::assertSame([], $resultado->diagnosticos);
        self::assertCount(3, $resultado->objetos);
        self::assertInstanceOf(Equipamento::class, $resultado->objetos[0]);
        self::assertInstanceOf(Manutencao::class, $resultado->objetos[1]);
        self::assertInstanceOf(Registro::class, $resultado->objetos[2]);
        self::assertSame('bomba_cr10', $resultado->objetos[0]->nome);
        self::assertSame('inspecao_bomba_cr10', $resultado->objetos[1]->nome);
        self::assertSame('leitura_bomba_cr10', $resultado->objetos[2]->nome);
    }

    public function testeProcess_EquipamentoComManutencaoCorretivaERegistroRelacionados_RetornaProgramaValido(): void {
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
        observacao_visual
        vazamento
    }
}

manutencao corretiva reparar_bomba_cr10 {
    equipamento bomba_cr10
    quando pressao maior 12 bar e observacao_visual anormal ou vazamento grave
    procedimento reparar_bomba
    prioridade alta
    duracao 2 hora
    prazo 1 dia
    homem_hora 2
}

registro leitura_bomba_cr10 {
    equipamento bomba_cr10
    data 10/09/2026-08:30
    valores {
        pressao 13 bar
        observacao_visual anormal
        vazamento grave
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertTrue($resultado->isSuccess());
        self::assertSame([], $resultado->diagnosticos);
        self::assertCount(3, $resultado->objetos);
        self::assertInstanceOf(Equipamento::class, $resultado->objetos[0]);
        self::assertInstanceOf(Manutencao::class, $resultado->objetos[1]);
        self::assertInstanceOf(Registro::class, $resultado->objetos[2]);
    }

    public function testeProcess_ProgramaComErrosSemanticosIndependentes_RetornaTodosOsDiagnosticosPossiveis(): void {
        $codigo =
"equipamento bomba_cr10 {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        pressao 10 litro
    }
    variaveis_controladas {
        temperatura
        temperatura
    }
}

manutencao preventiva inspecao_bomba_cr10 {
    equipamento bomba_inexistente
    a_cada 0 dia
    procedimento inspecionar_bomba
    prioridade media
    duracao 2 hora
    homem_hora 2
}

registro leitura_bomba_cr10 {
    equipamento bomba_cr10
    data 10/09/2026-08:30
    valores {
        pressao 9 litro
        pressao 10 litro
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertSame([
            'CMMS-SEM-008',
            'CMMS-SEM-004',
            'CMMS-SEM-012',
            'CMMS-SEM-002',
            'CMMS-SEM-007',
            'CMMS-SEM-004',
            'CMMS-SEM-005',
            'CMMS-SEM-004',
            'CMMS-SEM-006',
            'CMMS-SEM-005',
        ], array_map(
            static fn ($diagnostico): ?string => $diagnostico->codigo,
            $resultado->diagnosticos
        ));

        foreach ($resultado->diagnosticos as $diagnostico) {
            self::assertSame(DiagnosticOrigin::SEMANTIC, $diagnostico->origem);
            self::assertSame(DiagnosticSeverity::ERROR, $diagnostico->severidade);
        }
    }

    public function testeProcess_IdentificadorDuplicado_RetornaDiagnosticoSemantico(): void {
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

equipamento bomba_cr10 {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        pressao 10 bar
    }
    variaveis_controladas {
        pressao
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-001', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public function testeProcess_CaracteristicaDeProcessoDuplicada_RetornaDiagnosticoSemantico(): void {
        $codigo =
"equipamento bomba_cr10 {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        pressao 10 bar
        pressao 11 bar
    }
    variaveis_controladas {
        pressao
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-009', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public function testeProcess_CondicaoDeManutencaoComVariavelNaoControlada_RetornaDiagnosticoSemantico(): void {
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

manutencao corretiva reparar_bomba_cr10 {
    equipamento bomba_cr10
    quando temperatura maior 30 celsius
    procedimento reparar_bomba
    prioridade alta
    duracao 2 hora
    prazo 1 dia
    homem_hora 2
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-010', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public function testeProcess_ReferenciaDeManutencaoParaEquipamentoInexistente_RetornaDiagnosticoSemantico(): void {
        $codigo =
"manutencao preventiva inspecao_bomba_cr10 {
    equipamento bomba_inexistente
    a_cada 1 dia
    procedimento inspecionar_bomba
    prioridade media
    duracao 2 hora
    homem_hora 2
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-002', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public function testeProcess_ReferenciaDeRegistroParaEquipamentoInexistente_RetornaDiagnosticoSemantico(): void {
        $codigo =
"registro leitura_bomba_cr10 {
    equipamento bomba_inexistente
    data 10/09/2026-08:30
    valores {
        pressao 9 bar
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-003', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public function testeProcess_UnidadeIncompativelComVariavel_RetornaDiagnosticoSemantico(): void {
        $codigo =
"equipamento bomba_cr10 {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        pressao 10 litro
    }
    variaveis_controladas {
        pressao
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-004', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public function testeProcess_ValoresRegistradosDaMesmaVariavel_RetornaDiagnosticoSemantico(): void {
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

registro leitura_bomba_cr10 {
    equipamento bomba_cr10
    data 10/09/2026-08:30
    valores {
        pressao 9 bar
        pressao 10 bar
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-006', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public function testeProcess_IntervaloPreventivoIgualAZero_RetornaDiagnosticoSemanticoSemObjetos(): void {
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
    a_cada 0 dia
    procedimento inspecionar_bomba
    prioridade media
    duracao 2 hora
    homem_hora 2
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-007', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public function testeProcess_DataDeRegistroInvalida_RetornaDiagnosticoSemanticoSemLancarExcecao(): void {
        $codigo =
"registro leitura_bomba_cr10 {
    equipamento bomba_cr10
    data 31/02/2026-08:30
    valores {
        pressao 9.7 bar
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-011', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public function testeProcess_NumeroNaoRepresentavel_RetornaDiagnosticoSemanticoSemLancarExcecao(): void {
        $numero_invalido = '9223372036854775808';
        $codigo =
"registro leitura_bomba_cr10 {
    equipamento bomba_cr10
    data 10/09/2026-08:30
    valores {
        pressao {$numero_invalido} bar
    }
}";

        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::SEMANTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SEM-013', $resultado->diagnosticos[0]->codigo);
        self::assertSame("Número não representável: {$numero_invalido}", $resultado->diagnosticos[0]->mensagem);
        self::assertSame(DiagnosticOrigin::SEMANTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
    }

    public static function programasInvalidos(): iterable {
        yield 'caractere léxico inválido' => [
            'equipamento bomba @',
            DiagnosticOrigin::LEXICAL,
            0,
            18,
            1,
        ];

        yield 'caractere léxico inválido no início' => [
            '@equipamento',
            DiagnosticOrigin::LEXICAL,
            0,
            0,
            1,
        ];

        yield 'caractere léxico inválido em linha posterior' => [
            "equipamento bomba {\n"
            . "    tipo bomba_centrifuga\n"
            . '    #',
            DiagnosticOrigin::LEXICAL,
            2,
            4,
            1,
        ];

        yield 'caractere léxico inválido em identificador' => [
            'registro r { equipamento bom#ba',
            DiagnosticOrigin::LEXICAL,
            0,
            28,
            1,
        ];

        yield 'declaração desconhecida' => [
            'desconhecida item {}',
            DiagnosticOrigin::SYNTACTIC,
            0,
            0,
            1,
        ];

        yield 'equipamento incompleto' => [
            'equipamento bomba {}',
            DiagnosticOrigin::SYNTACTIC,
            0,
            19,
            1,
        ];

        yield 'equipamento com serviço inválido' => [
            'equipamento bomba { tipo bomba_centrifuga servico agua produto agua caracteristicas_processo { pressao 10 bar } variaveis_controladas { pressao } }',
            DiagnosticOrigin::SYNTACTIC,
            0,
            50,
            1,
        ];

        yield 'manutenção preventiva incompleta' => [
            'manutencao preventiva inspecao {}',
            DiagnosticOrigin::SYNTACTIC,
            0,
            32,
            1,
        ];

        yield 'manutenção corretiva com condição inválida' => [
            'manutencao corretiva correcao { equipamento bomba quando pressao maior bar procedimento p prioridade alta duracao 1 hora prazo 1 dia homem_hora 1 }',
            DiagnosticOrigin::SYNTACTIC,
            0,
            71,
            1,
        ];

        yield 'registro incompleto' => [
            'registro r {}',
            DiagnosticOrigin::SYNTACTIC,
            0,
            12,
            1,
        ];

        yield 'valor registrado malformado' => [
            'registro r { equipamento bomba data 01/01/2026-10:00 valores { pressao bar } }',
            DiagnosticOrigin::SYNTACTIC,
            0,
            71,
            1,
        ];

        yield 'delimitador de data ausente' => [
            "registro r {\n"
            . "    equipamento bomba\n"
            . "    data 01 01/2026-10:00\n"
            . "    valores {\n"
            . "        pressao 1 bar\n"
            . "    }\n"
            . '}',
            DiagnosticOrigin::SYNTACTIC,
            2,
            12,
            2,
        ];

        yield 'token inesperado' => [
            'equipamento bomba { inesperado tipo bomba_centrifuga servico bombeamento_agua produto agua caracteristicas_processo { pressao 10 bar } variaveis_controladas { pressao } }',
            DiagnosticOrigin::SYNTACTIC,
            0,
            20,
            1,
        ];

        yield 'entrada truncada' => [
            'registro r { equipamento bomba',
            DiagnosticOrigin::SYNTACTIC,
            0,
            30,
            1,
        ];

        yield 'múltiplos erros recuperáveis' => [
            'equipamento bomba { tipo bomba_centrifuga servico agua produto invalido caracteristicas_processo { pressao 10 bar } variaveis_controladas { pressao } }',
            DiagnosticOrigin::SYNTACTIC,
            0,
            50,
            2,
        ];
    }

    public function testeProcess_CaractereLexicoInvalido_RetornaRangeDoCaractere(): void {
        $resultado = (new CMMSProcessor())->process('equipamento bomba @');

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::LEXICAL_OR_SYNTACTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-LEX-001', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::LEXICAL, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
        self::assertNotNull($resultado->diagnosticos[0]->range);
        self::assertSame(0, $resultado->diagnosticos[0]->range->inicio->linha);
        self::assertSame(18, $resultado->diagnosticos[0]->range->inicio->coluna);
        self::assertSame(0, $resultado->diagnosticos[0]->range->fim->linha);
        self::assertSame(19, $resultado->diagnosticos[0]->range->fim->coluna);
    }

    #[DataProvider('programasInvalidos')]
    public function testeProcess_ProgramaInvalido_RetornaDiagnosticosSemObjetosDeDomain(
        string $codigo,
        DiagnosticOrigin $origem_esperada,
        int $linha_esperada,
        int $coluna_esperada,
        int $quantidade_minima_diagnosticos,
    ): void {
        $resultado = (new CMMSProcessor())->process($codigo);

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::LEXICAL_OR_SYNTACTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertGreaterThanOrEqual($quantidade_minima_diagnosticos, count($resultado->diagnosticos));

        foreach ($resultado->diagnosticos as $diagnostico) {
            self::assertSame($origem_esperada, $diagnostico->origem);
            self::assertSame(
                $origem_esperada === DiagnosticOrigin::LEXICAL ? 'CMMS-LEX-001' : 'CMMS-SYN-001',
                $diagnostico->codigo,
            );
            self::assertSame(DiagnosticSeverity::ERROR, $diagnostico->severidade);
            self::assertNotSame('', $diagnostico->mensagem);
            self::assertNotNull($diagnostico->range);
        }

        $primeiro_diagnostico = $resultado->diagnosticos[0];

        self::assertSame($linha_esperada, $primeiro_diagnostico->range->inicio->linha);
        self::assertSame($coluna_esperada, $primeiro_diagnostico->range->inicio->coluna);
    }

    public function testeProcess_EquipamentoIncompleto_NaoExecutaVisitorERetornaDiagnosticoSintatico(): void {
        $resultado = (new CMMSProcessor())->process('equipamento bomba {}');

        self::assertFalse($resultado->isSuccess());
        self::assertSame(ProcessingStatus::LEXICAL_OR_SYNTACTIC_FAILURE, $resultado->status);
        self::assertSame([], $resultado->objetos);
        self::assertCount(1, $resultado->diagnosticos);
        self::assertSame('CMMS-SYN-001', $resultado->diagnosticos[0]->codigo);
        self::assertSame(DiagnosticOrigin::SYNTACTIC, $resultado->diagnosticos[0]->origem);
        self::assertSame(DiagnosticSeverity::ERROR, $resultado->diagnosticos[0]->severidade);
        self::assertNotNull($resultado->diagnosticos[0]->range);
        self::assertSame(0, $resultado->diagnosticos[0]->range->inicio->linha);
        self::assertSame(19, $resultado->diagnosticos[0]->range->inicio->coluna);
        self::assertSame(0, $resultado->diagnosticos[0]->range->fim->linha);
        self::assertSame(20, $resultado->diagnosticos[0]->range->fim->coluna);
    }
}
