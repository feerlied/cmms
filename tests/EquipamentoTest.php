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
}
