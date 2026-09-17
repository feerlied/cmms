<?php

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Domain\CaracteristicaProcesso;
use Domain\CaracteristicaProcessoCollection;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\VariavelControladaCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Visitor\CMMSVisitor;


final class EquipamentoTest extends TestCase
{
    public static function exemplosEquipamento(): iterable {

        yield 'Equipamento' => [
            "expected" => [
                "nome" => "bomba_cr10",
                "tipo" => TipoEquipamento::BOMBA_CENTRIFUGA,
                "servico" => TipoServico::BOMBEAMENTO_AGUA,
                "produto" => TipoProduto::AGUA,
            ],
            "code" =>
"equipamento bomba_cr10 {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua

    caracteristicas_processo {
                vazao 12 m3/h
        pressao 10 bar
        temperatura 25 celsius
        rotacao 2900 rpm
    }

    variaveis_controladas {
                vazao
        pressao
        vibracao
        temperatura
        horas_operacao
        observacao_visual
        vazamento
    }
}"
        ];

        yield 'Equipamento 2' => [
            "expected" => [
                "nome" => "bomba_dosadora01",
                "tipo" => TipoEquipamento::BOMBA_ALTERNATIVA,
                "servico" => TipoServico::BOMBEAMENTO_AGUA,
                "produto" => TipoProduto::AGUA,
            ],
            "code" =>
"equipamento bomba_dosadora01 {
    tipo bomba_alternativa

    servico bombeamento_de_agua

    produto agua

    caracteristicas_processo {
        vazao 60 m3/h
        pressao 10 bar
        temperatura 25 celsius
    }

    variaveis_controladas {
        vazao
        pressao
        temperatura
        horas_operacao
        observacao_visual
        vazamento
    }
}"
        ];
    }

    #[DataProvider('exemplosEquipamento')]
    public function testVisitor_CriaEquipamento(string $code, array $expected): void {
        $lexer = new CMMSLexer(InputStream::fromString($code));
        $parser = new CMMSParser(new CommonTokenStream($lexer));
        $equipamento = (new CMMSVisitor())->visit($parser->programa());

        self::assertCount(1, $equipamento);
        self::assertInstanceOf(Equipamento::class, $equipamento[0]);
        self::assertSame($expected["nome"], $equipamento[0]->nome);
        self::assertSame($expected["tipo"], $equipamento[0]->tipo);
        self::assertSame($expected["servico"], $equipamento[0]->servico);
        self::assertSame($expected["produto"], $equipamento[0]->produto);
        self::assertInstanceOf(CaracteristicaProcessoCollection::class, $equipamento[0]->caracteristicas_processo);
        self::assertInstanceOf(VariavelControladaCollection::class, $equipamento[0]->variaveis_controladas);
    }

    public function testCollections_SalvaObjetosNoEquipamento(): void {
        $vazao = new CaracteristicaProcesso(VariavelControlada::VAZAO, 12, UnidadeMedida::M3_POR_HORA);
        $pressao = new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10.5, UnidadeMedida::BAR);
        $caracteristicas = new CaracteristicaProcessoCollection($vazao);
        $caracteristicas->add($pressao);

        $variavel_vazao = VariavelControlada::VAZAO;
        $variavel_pressao = VariavelControlada::PRESSAO;
        $variaveis = new VariavelControladaCollection($variavel_vazao);
        $variaveis->add($variavel_pressao);

        $equipamento = new Equipamento(
            'bomba_cr10',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            TipoServico::BOMBEAMENTO_AGUA,
            TipoProduto::AGUA,
            $caracteristicas,
            $variaveis
        );

        self::assertSame([$vazao, $pressao], $equipamento->caracteristicas_processo->all());
        self::assertSame([$variavel_vazao, $variavel_pressao], $equipamento->variaveis_controladas->all());
        self::assertSame(10.5, $equipamento->caracteristicas_processo->all()[1]->valor);
        self::assertSame(UnidadeMedida::BAR, $equipamento->caracteristicas_processo->all()[1]->unidade);
        self::assertSame(VariavelControlada::VAZAO, $equipamento->variaveis_controladas->all()[0]);
    }

    public function testCaracteristicaProcesso_VariavelNaoNumerica_Rejeitada(): void {
        $this->expectException(InvalidArgumentException::class);
        new CaracteristicaProcesso(VariavelControlada::VAZAMENTO, 1, UnidadeMedida::BAR);
    }

    public function testVisitor_DeclaracaoMinimaValida_RetornaEquipamentoComTodasAsPropriedadesObrigatorias(): void {
        $codigo =
"equipamento bomba_minima {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        vazao 1 m3/h
    }
    variaveis_controladas {
        vazao
    }
}";

        $equipamento = $this->transformEquipment($codigo);

        self::assertSame('bomba_minima', $equipamento->nome);
        self::assertSame(TipoEquipamento::BOMBA_CENTRIFUGA, $equipamento->tipo);
        self::assertSame(TipoServico::BOMBEAMENTO_AGUA, $equipamento->servico);
        self::assertSame(TipoProduto::AGUA, $equipamento->produto);

        $caracteristicas_processo = $equipamento->caracteristicas_processo->all();
        self::assertCount(1, $caracteristicas_processo);
        self::assertSame(VariavelControlada::VAZAO, $caracteristicas_processo[0]->variavel);
        self::assertSame(1, $caracteristicas_processo[0]->valor);
        self::assertSame(UnidadeMedida::M3_POR_HORA, $caracteristicas_processo[0]->unidade);
        self::assertSame([VariavelControlada::VAZAO], $equipamento->variaveis_controladas->all());
    }

    public function testVisitor_DeclaracaoCompleta_RetornaTodoConteudoTransformado(): void {
        $codigo =
"equipamento bomba_completa {
    tipo bomba centrífuga
    serviço bombeamento de água
    produto água
    características processo {
        vazão 0 m3/h
        pressão 10.5 bar
        pressão de descarga 11 bar
        pressão de sucção 9.25 bar
        vibração 2 mm/s
        temperatura 25 C
        rotação 2900 rpm
    }
    variáveis controladas {
        vazão
        pressão
        pressão de descarga
        pressão de sucção
        vibração
        temperatura
        rotação
        horas operação
        observacao visual
        vazamento
    }
}";

        $equipamento = $this->transformEquipment($codigo);

        self::assertSame('bomba_completa', $equipamento->nome);
        self::assertSame(TipoEquipamento::BOMBA_CENTRIFUGA, $equipamento->tipo);
        self::assertSame(TipoServico::BOMBEAMENTO_AGUA, $equipamento->servico);
        self::assertSame(TipoProduto::AGUA, $equipamento->produto);

        $caracteristicas_processo = $equipamento->caracteristicas_processo->all();
        $caracteristicas_esperadas = [
            [VariavelControlada::VAZAO, 0, UnidadeMedida::M3_POR_HORA],
            [VariavelControlada::PRESSAO, 10.5, UnidadeMedida::BAR],
            [VariavelControlada::PRESSAO_DESCARGA, 11, UnidadeMedida::BAR],
            [VariavelControlada::PRESSAO_SUCCAO, 9.25, UnidadeMedida::BAR],
            [VariavelControlada::VIBRACAO, 2, UnidadeMedida::MM_POR_S],
            [VariavelControlada::TEMPERATURA, 25, UnidadeMedida::CELSIUS],
            [VariavelControlada::ROTACAO, 2900, UnidadeMedida::RPM],
        ];

        self::assertCount(count($caracteristicas_esperadas), $caracteristicas_processo);

        foreach ($caracteristicas_esperadas as $indice => [$variavel, $valor, $unidade]) {
            self::assertSame($variavel, $caracteristicas_processo[$indice]->variavel);
            self::assertSame($valor, $caracteristicas_processo[$indice]->valor);
            self::assertSame($unidade, $caracteristicas_processo[$indice]->unidade);
        }

        self::assertSame([
            VariavelControlada::VAZAO,
            VariavelControlada::PRESSAO,
            VariavelControlada::PRESSAO_DESCARGA,
            VariavelControlada::PRESSAO_SUCCAO,
            VariavelControlada::VIBRACAO,
            VariavelControlada::TEMPERATURA,
            VariavelControlada::ROTACAO,
            VariavelControlada::HORAS_OPERACAO,
            VariavelControlada::OBSERVACAO_VISUAL,
            VariavelControlada::VAZAMENTO,
        ], $equipamento->variaveis_controladas->all());
    }

    public static function combinacoesDeEquipamento(): iterable {
        yield 'bomba centrífuga bombeando água' => [
            'bomba_centrifuga',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            'bombeamento_de_agua',
            TipoServico::BOMBEAMENTO_AGUA,
            'agua',
            TipoProduto::AGUA,
        ];

        yield 'bomba alternativa bombeando diesel' => [
            'bomba alternativa',
            TipoEquipamento::BOMBA_ALTERNATIVA,
            'bombeamento de diesel',
            TipoServico::BOMBEAMENTO_DIESEL,
            'diesel',
            TipoProduto::DIESEL,
        ];

        yield 'trocador resfriando gasolina' => [
            'trocador de calor',
            TipoEquipamento::TROCADOR_CALOR,
            'resfriamento de gasolina',
            TipoServico::RESFRIAMENTO_GASOLINA,
            'gasolina',
            TipoProduto::GASOLINA,
        ];

        yield 'tanque armazenando gasolina' => [
            'tanque de gasolina',
            TipoEquipamento::TANQUE_GASOLINA,
            'armazenamento de gasolina',
            TipoServico::ARMAZENAMENTO_GASOLINA,
            'gasolina',
            TipoProduto::GASOLINA,
        ];

        yield 'bomba centrífuga bombeando gasolina' => [
            'bomba centrífuga',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            'bombeamento de gasolina',
            TipoServico::BOMBEAMENTO_GASOLINA,
            'gasolina',
            TipoProduto::GASOLINA,
        ];
    }

    #[DataProvider('combinacoesDeEquipamento')]
    public function testVisitor_TiposServicosEProdutosPrevistos_RetornaCombinacaoTransformada(
        string $tipo_dsl,
        TipoEquipamento $tipo_esperado,
        string $servico_dsl,
        TipoServico $servico_esperado,
        string $produto_dsl,
        TipoProduto $produto_esperado
    ): void {
        $codigo =
"equipamento equipamento_teste {
    tipo {$tipo_dsl}
    servico {$servico_dsl}
    produto {$produto_dsl}
    caracteristicas_processo {
        temperatura 20 celsius
    }
    variaveis_controladas {
        temperatura
    }
}";

        $equipamento = $this->transformEquipment($codigo);

        self::assertSame($tipo_esperado, $equipamento->tipo);
        self::assertSame($servico_esperado, $equipamento->servico);
        self::assertSame($produto_esperado, $equipamento->produto);
        self::assertSame(VariavelControlada::TEMPERATURA, $equipamento->caracteristicas_processo->all()[0]->variavel);
        self::assertSame([VariavelControlada::TEMPERATURA], $equipamento->variaveis_controladas->all());
    }

    public static function valoresNumericosEUnidades(): iterable {
        yield 'zero em milímetros por segundo' => ['0', 'mm/s', 0, UnidadeMedida::MM_POR_S];
        yield 'decimal em celsius' => ['25.5', 'celsius', 25.5, UnidadeMedida::CELSIUS];
        yield 'inteiro em bar' => ['10', 'bar', 10, UnidadeMedida::BAR];
        yield 'decimal em metros cúbicos por hora' => ['12.75', 'm3/h', 12.75, UnidadeMedida::M3_POR_HORA];
        yield 'inteiro em hora' => ['1', 'hora', 1, UnidadeMedida::HORA];
        yield 'inteiro em minuto' => ['30', 'minuto', 30, UnidadeMedida::MINUTO];
        yield 'inteiro em dia' => ['2', 'dia', 2, UnidadeMedida::DIA];
        yield 'inteiro em rpm' => ['2900', 'rpm', 2900, UnidadeMedida::RPM];
        yield 'decimal em litro' => ['1.5', 'litro', 1.5, UnidadeMedida::LITRO];
    }

    #[DataProvider('valoresNumericosEUnidades')]
    public function testVisitor_ValoresNumericosEUnidadesPrevistos_PreservaValorEConverteUnidade(
        string $valor_dsl,
        string $unidade_dsl,
        int|float $valor_esperado,
        UnidadeMedida $unidade_esperada
    ): void {
        $codigo =
"equipamento equipamento_numerico {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        temperatura {$valor_dsl} {$unidade_dsl}
    }
    variaveis_controladas {
        temperatura
    }
}";

        $equipamento = $this->transformEquipment($codigo);
        $caracteristica = $equipamento->caracteristicas_processo->all()[0];

        self::assertSame(VariavelControlada::TEMPERATURA, $caracteristica->variavel);
        self::assertSame($valor_esperado, $caracteristica->valor);
        self::assertSame($unidade_esperada, $caracteristica->unidade);
    }

    private function transformEquipment(string $codigo): Equipamento {
        $lexer = new CMMSLexer(InputStream::fromString($codigo));
        $parser = new CMMSParser(new CommonTokenStream($lexer));
        $objetos = (new CMMSVisitor())->visit($parser->programa());

        self::assertCount(1, $objetos);
        self::assertInstanceOf(Equipamento::class, $objetos[0]);

        return $objetos[0];
    }
}
