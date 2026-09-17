<?php

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Domain\Enums\EstadoObservacao;
use Domain\Enums\EstadoVazamento;
use Domain\Enums\StatusRegistro;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\ExecucaoRegistro;
use Domain\HorasOperacaoRegistro;
use Domain\ObservacaoVisualRegistro;
use Domain\Registro;
use Domain\Tempo;
use Domain\ValorNumericoRegistro;
use Domain\ValorRegistradoCollection;
use Domain\VazamentoRegistro;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Visitor\CMMSVisitor;


final class RegistroTest extends TestCase {
    public function testeVisitor_DeclaracaoMinima_RetornaRegistroComCamposObrigatorios(): void {
        $codigo =
"registro leitura_cr10 {
    equipamento bomba_cr10
    data 10/09/2026-08:30
    valores {
        pressao 9.7 bar
    }
}";

        $registro = $this->transformRecord($codigo);
        $valores = $registro->valores->all();

        self::assertSame('leitura_cr10', $registro->nome);
        self::assertSame('bomba_cr10', $registro->equipamento_identificador);
        self::assertSame('2026-09-10 08:30', $registro->data->format('Y-m-d H:i'));
        self::assertNull($registro->execucao);
        self::assertNull($registro->relatorio);
        self::assertNull($registro->observacao);
        self::assertInstanceOf(ValorRegistradoCollection::class, $registro->valores);
        self::assertCount(1, $valores);
        self::assertInstanceOf(ValorNumericoRegistro::class, $valores[0]);
        self::assertSame(VariavelControlada::PRESSAO, $valores[0]->variavel);
        self::assertSame(9.7, $valores[0]->valor);
        self::assertSame(UnidadeMedida::BAR, $valores[0]->unidade);
    }

    public function testeVisitor_DeclaracaoCompleta_RetornaTodasAsFormasDeValorECamposOpcionais(): void {
        $codigo =
"registro registro_pos_manutencao {
    equipamento bomba_cr10
    data 10/09/2026-14:45
    origem manutencao_cr10
    tempo de execução 3.5 hora
    status em execução
    valores {
        vazao 11.8 m3/h
        horas operação 1250 hora
        observacao visual normal
        vazamento ausente
    }
    relatório \"Rolamentos inspecionados\"
    observação \"Vibração normal\"
}";

        $registro = $this->transformRecord($codigo);
        $valores = $registro->valores->all();

        self::assertSame('registro_pos_manutencao', $registro->nome);
        self::assertSame('bomba_cr10', $registro->equipamento_identificador);
        self::assertSame('2026-09-10 14:45', $registro->data->format('Y-m-d H:i'));
        self::assertInstanceOf(ExecucaoRegistro::class, $registro->execucao);
        self::assertSame('manutencao_cr10', $registro->execucao->origem);
        self::assertSame(3.5, $registro->execucao->tempo_execucao->valor);
        self::assertSame(UnidadeTempo::HORA, $registro->execucao->tempo_execucao->unidade);
        self::assertSame(StatusRegistro::EM_EXECUCAO, $registro->execucao->status);
        self::assertSame('Rolamentos inspecionados', $registro->relatorio);
        self::assertSame('Vibração normal', $registro->observacao);

        self::assertCount(4, $valores);
        self::assertInstanceOf(ValorNumericoRegistro::class, $valores[0]);
        self::assertSame(VariavelControlada::VAZAO, $valores[0]->variavel);
        self::assertSame(11.8, $valores[0]->valor);
        self::assertSame(UnidadeMedida::M3_POR_HORA, $valores[0]->unidade);
        self::assertInstanceOf(HorasOperacaoRegistro::class, $valores[1]);
        self::assertSame(1250, $valores[1]->horas_operacao->valor);
        self::assertSame(UnidadeTempo::HORA, $valores[1]->horas_operacao->unidade);
        self::assertInstanceOf(ObservacaoVisualRegistro::class, $valores[2]);
        self::assertSame(EstadoObservacao::NORMAL, $valores[2]->estado);
        self::assertInstanceOf(VazamentoRegistro::class, $valores[3]);
        self::assertSame(EstadoVazamento::AUSENTE, $valores[3]->estado);
    }

    public static function statusDeRegistro(): iterable {
        yield 'em aberto' => ['em_aberto', StatusRegistro::EM_ABERTO];
        yield 'em execução' => ['em_execucao', StatusRegistro::EM_EXECUCAO];
        yield 'concluído' => ['concluido', StatusRegistro::CONCLUIDO];
        yield 'cancelado' => ['cancelado', StatusRegistro::CANCELADO];
    }

    #[DataProvider('statusDeRegistro')]
    public function testeVisitor_StatusDaExecucao_TransformaStatusOrigemETempo(
        string $status_dsl,
        StatusRegistro $status_esperado
    ): void {
        $codigo =
"registro registro_execucao {
    equipamento bomba_cr10
    data 11/09/2026-09:15
    origem manutencao_cr10
    tempo_execucao 45 minuto
    status {$status_dsl}
    valores {
        temperatura 40 celsius
    }
}";

        $registro = $this->transformRecord($codigo);

        self::assertInstanceOf(ExecucaoRegistro::class, $registro->execucao);
        self::assertSame('manutencao_cr10', $registro->execucao->origem);
        self::assertSame(45, $registro->execucao->tempo_execucao->valor);
        self::assertSame(UnidadeTempo::MINUTO, $registro->execucao->tempo_execucao->unidade);
        self::assertSame($status_esperado, $registro->execucao->status);
    }

    public function testeVisitor_SomenteRelatorioPresente_PreservaTextoEObservacaoAusente(): void {
        $codigo =
"registro registro_relatorio {
    equipamento bomba_cr10
    data 12/09/2026-10:20
    valores {
        temperatura 41 celsius
    }
    relatorio \"Inspeção concluída sem anomalias\"
}";

        $registro = $this->transformRecord($codigo);

        self::assertSame('Inspeção concluída sem anomalias', $registro->relatorio);
        self::assertNull($registro->observacao);
        self::assertNull($registro->execucao);
    }

    public function testeVisitor_SomenteObservacaoPresente_PreservaTextoERelatorioAusente(): void {
        $codigo =
"registro registro_observacao {
    equipamento bomba_cr10
    data 13/09/2026-11:25
    valores {
        temperatura 42 celsius
    }
    observacao \"Ruído leve durante a partida\"
}";

        $registro = $this->transformRecord($codigo);

        self::assertSame('Ruído leve durante a partida', $registro->observacao);
        self::assertNull($registro->relatorio);
        self::assertNull($registro->execucao);
    }

    public static function variaveisNumericasRegistradas(): iterable {
        yield 'vazão' => ['vazao 11.8 m3/h', VariavelControlada::VAZAO, 11.8, UnidadeMedida::M3_POR_HORA];
        yield 'pressão' => ['pressao 9.7 bar', VariavelControlada::PRESSAO, 9.7, UnidadeMedida::BAR];
        yield 'pressão de descarga' => ['pressao_descarga 10 bar', VariavelControlada::PRESSAO_DESCARGA, 10, UnidadeMedida::BAR];
        yield 'pressão de sucção' => ['pressao_succao 8.5 bar', VariavelControlada::PRESSAO_SUCCAO, 8.5, UnidadeMedida::BAR];
        yield 'vibração' => ['vibracao 2.1 mm/s', VariavelControlada::VIBRACAO, 2.1, UnidadeMedida::MM_POR_S];
        yield 'temperatura' => ['temperatura 42 C', VariavelControlada::TEMPERATURA, 42, UnidadeMedida::CELSIUS];
        yield 'rotação' => ['rotacao 2890 rpm', VariavelControlada::ROTACAO, 2890, UnidadeMedida::RPM];
    }

    #[DataProvider('variaveisNumericasRegistradas')]
    public function testeVisitor_ValorNumerico_TransformaVariavelNumeroEUnidade(
        string $valor_dsl,
        VariavelControlada $variavel_esperada,
        int|float $valor_esperado,
        UnidadeMedida $unidade_esperada
    ): void {
        $codigo =
"registro registro_numerico {
    equipamento bomba_cr10
    data 14/09/2026-12:30
    valores {
        {$valor_dsl}
    }
}";

        $valor = $this->transformRecord($codigo)->valores->all()[0];

        self::assertInstanceOf(ValorNumericoRegistro::class, $valor);
        self::assertSame($variavel_esperada, $valor->variavel);
        self::assertSame($valor_esperado, $valor->valor);
        self::assertSame($unidade_esperada, $valor->unidade);
    }

    public static function unidadesDeValoresNumericos(): iterable {
        yield 'milímetros por segundo' => ['mm/s', UnidadeMedida::MM_POR_S];
        yield 'celsius' => ['celsius', UnidadeMedida::CELSIUS];
        yield 'bar' => ['bar', UnidadeMedida::BAR];
        yield 'metros cúbicos por hora' => ['m3/h', UnidadeMedida::M3_POR_HORA];
        yield 'hora' => ['hora', UnidadeMedida::HORA];
        yield 'minuto' => ['minuto', UnidadeMedida::MINUTO];
        yield 'dia' => ['dia', UnidadeMedida::DIA];
        yield 'rotações por minuto' => ['rpm', UnidadeMedida::RPM];
        yield 'litro' => ['litro', UnidadeMedida::LITRO];
    }

    #[DataProvider('unidadesDeValoresNumericos')]
    public function testeVisitor_UnidadeDeValorNumerico_PreservaUnidadeSuportada(
        string $unidade_dsl,
        UnidadeMedida $unidade_esperada
    ): void {
        $codigo =
"registro registro_unidade {
    equipamento equipamento_teste
    data 15/09/2026-13:35
    valores {
        pressao 2.5 {$unidade_dsl}
    }
}";

        $valor = $this->transformRecord($codigo)->valores->all()[0];

        self::assertInstanceOf(ValorNumericoRegistro::class, $valor);
        self::assertSame(2.5, $valor->valor);
        self::assertSame($unidade_esperada, $valor->unidade);
    }

    public static function unidadesDeHorasOperacao(): iterable {
        yield 'hora' => ['hora', UnidadeTempo::HORA];
        yield 'minuto' => ['minuto', UnidadeTempo::MINUTO];
        yield 'dia' => ['dia', UnidadeTempo::DIA];
    }

    #[DataProvider('unidadesDeHorasOperacao')]
    public function testeVisitor_HorasOperacao_TransformaTempoComUnidade(
        string $unidade_dsl,
        UnidadeTempo $unidade_esperada
    ): void {
        $codigo =
"registro registro_horas {
    equipamento bomba_cr10
    data 16/09/2026-14:40
    valores {
        horas_operacao 1250.5 {$unidade_dsl}
    }
}";

        $valor = $this->transformRecord($codigo)->valores->all()[0];

        self::assertInstanceOf(HorasOperacaoRegistro::class, $valor);
        self::assertSame(1250.5, $valor->horas_operacao->valor);
        self::assertSame($unidade_esperada, $valor->horas_operacao->unidade);
    }

    public static function estadosDeObservacaoVisual(): iterable {
        yield 'normal' => ['normal', EstadoObservacao::NORMAL];
        yield 'anormal' => ['anormal', EstadoObservacao::ANORMAL];
    }

    #[DataProvider('estadosDeObservacaoVisual')]
    public function testeVisitor_ObservacaoVisual_TransformaEstado(
        string $estado_dsl,
        EstadoObservacao $estado_esperado
    ): void {
        $codigo =
"registro registro_observacao_visual {
    equipamento bomba_cr10
    data 17/09/2026-15:45
    valores {
        observacao_visual {$estado_dsl}
    }
}";

        $valor = $this->transformRecord($codigo)->valores->all()[0];

        self::assertInstanceOf(ObservacaoVisualRegistro::class, $valor);
        self::assertSame($estado_esperado, $valor->estado);
    }

    public static function estadosDeVazamento(): iterable {
        yield 'ausente' => ['ausente', EstadoVazamento::AUSENTE];
        yield 'leve' => ['leve', EstadoVazamento::LEVE];
        yield 'moderado' => ['moderado', EstadoVazamento::MODERADO];
        yield 'grave' => ['grave', EstadoVazamento::GRAVE];
    }

    #[DataProvider('estadosDeVazamento')]
    public function testeVisitor_Vazamento_TransformaEstado(
        string $estado_dsl,
        EstadoVazamento $estado_esperado
    ): void {
        $codigo =
"registro registro_vazamento {
    equipamento bomba_cr10
    data 18/09/2026-16:50
    valores {
        vazamento {$estado_dsl}
    }
}";

        $valor = $this->transformRecord($codigo)->valores->all()[0];

        self::assertInstanceOf(VazamentoRegistro::class, $valor);
        self::assertSame($estado_esperado, $valor->estado);
    }

    public function testRegistro_CamposObrigatorios_ExecucaoETextosAusentes(): void {
        $valor = new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9.7, UnidadeMedida::BAR);
        $data = new DateTimeImmutable('2026-09-10 08:30');
        $registro = new Registro('leitura_cr10', 'bomba_cr10', $data, new ValorRegistradoCollection($valor));

        self::assertSame('leitura_cr10', $registro->nome);
        self::assertSame('bomba_cr10', $registro->equipamento_identificador);
        self::assertSame($data, $registro->data);
        self::assertSame([$valor], $registro->valores->all());
        self::assertNull($registro->execucao);
        self::assertNull($registro->relatorio);
        self::assertNull($registro->observacao);
    }

    public function testRegistro_TodosOsCampos_ValoresDeCadaFormaPreservados(): void {
        $vazao = new ValorNumericoRegistro(VariavelControlada::VAZAO, 11.8, UnidadeMedida::M3_POR_HORA);
        $horas = new HorasOperacaoRegistro(new Tempo(1250, UnidadeTempo::HORA));
        $observacao_visual = new ObservacaoVisualRegistro(EstadoObservacao::NORMAL);
        $vazamento = new VazamentoRegistro(EstadoVazamento::AUSENTE);
        $valores = new ValorRegistradoCollection($vazao);
        $valores->add($horas);
        $valores->add($observacao_visual);
        $valores->add($vazamento);
        $execucao = new ExecucaoRegistro('manutencao_cr10', new Tempo(3, UnidadeTempo::HORA), StatusRegistro::CONCLUIDO);

        $registro = new Registro(
            'registro_pos_manutencao', 'bomba_cr10', new DateTimeImmutable('2026-09-10 14:45'),
            $valores, $execucao, 'Rolamentos inspecionados', 'Vibracao normal'
        );

        self::assertSame([$vazao, $horas, $observacao_visual, $vazamento], $registro->valores->all());
        self::assertSame($execucao, $registro->execucao);
        self::assertSame(StatusRegistro::CONCLUIDO, $registro->execucao->status);
        self::assertSame('Rolamentos inspecionados', $registro->relatorio);
        self::assertSame('Vibracao normal', $registro->observacao);
    }

    public function testRegistro_BlocoValoresObrigatorio_ListaVaziaRejeitada(): void {
        $this->expectException(InvalidArgumentException::class);
        new Registro('leitura_cr10', 'bomba_cr10', new DateTimeImmutable('2026-09-10 08:30'), new ValorRegistradoCollection());
    }

    private function transformRecord(string $codigo): Registro {
        $lexer = new CMMSLexer(InputStream::fromString($codigo));
        $parser = new CMMSParser(new CommonTokenStream($lexer));
        $objetos = (new CMMSVisitor())->visit($parser->programa());

        self::assertSame(0, $parser->getNumberOfSyntaxErrors());
        self::assertCount(1, $objetos);
        self::assertInstanceOf(Registro::class, $objetos[0]);

        return $objetos[0];
    }
}
