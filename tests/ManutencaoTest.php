<?php

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Domain\CondicaoCorretiva;
use Domain\CondicaoCollection;
use Domain\CondicaoNumerica;
use Domain\CondicaoObservacao;
use Domain\CondicaoVazamento;
use Domain\Enums\Comparador;
use Domain\Enums\EstadoObservacao;
use Domain\Enums\EstadoVazamento;
use Domain\Enums\OperadorLogico;
use Domain\Enums\Prioridade;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\Manutencao;
use Domain\OperadorLogicoCollection;
use Domain\Tempo;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Visitor\CMMSVisitor;


final class ManutencaoTest extends TestCase {
    public function testeVisitor_PreventivaMinimaValida_ArmazenaTodosOsCamposObrigatorios(): void {
        $codigo =
"manutencao preventiva preventiva_minima {
    equipamento bomba_minima
    a_cada 1 dia
    procedimento inspecionar
    prioridade baixa
    duracao 1 hora
    homem_hora 1
}";

        $manutencao = $this->transformMaintenance($codigo);

        self::assertSame('preventiva_minima', $manutencao->nome);
        self::assertSame(TipoManutencao::PREVENTIVA, $manutencao->tipo);
        self::assertSame('bomba_minima', $manutencao->equipamento_identificador);
        self::assertInstanceOf(Tempo::class, $manutencao->gatilho);
        self::assertSame(1, $manutencao->gatilho->valor);
        self::assertSame(UnidadeTempo::DIA, $manutencao->gatilho->unidade);
        self::assertSame('inspecionar', $manutencao->procedimento_identificador);
        self::assertSame(Prioridade::BAIXA, $manutencao->prioridade);
        self::assertSame(1, $manutencao->duracao->valor);
        self::assertSame(UnidadeTempo::HORA, $manutencao->duracao->unidade);
        self::assertSame(1, $manutencao->homem_hora);
        self::assertNull($manutencao->prazo);
    }

    public function testeVisitor_PreventivaCompleta_ConverteGrafiasEValoresDecimais(): void {
        $codigo =
"manutenção preventiva revisao_completa {
    equipamento bomba_cr10
    a cada 15 minuto
    procedimento revisar_rolamentos
    prioridade média
    duração 2.5 dia
    HH 3.75
}";

        $manutencao = $this->transformMaintenance($codigo);

        self::assertSame('revisao_completa', $manutencao->nome);
        self::assertSame(TipoManutencao::PREVENTIVA, $manutencao->tipo);
        self::assertSame('bomba_cr10', $manutencao->equipamento_identificador);
        self::assertSame(15, $manutencao->gatilho->valor);
        self::assertSame(UnidadeTempo::MINUTO, $manutencao->gatilho->unidade);
        self::assertSame('revisar_rolamentos', $manutencao->procedimento_identificador);
        self::assertSame(Prioridade::MEDIA, $manutencao->prioridade);
        self::assertSame(2.5, $manutencao->duracao->valor);
        self::assertSame(UnidadeTempo::DIA, $manutencao->duracao->unidade);
        self::assertSame(3.75, $manutencao->homem_hora);
        self::assertNull($manutencao->prazo);
    }

    public static function preventiveTriggerForms(): iterable {
        yield 'a_cada em horas' => ['a_cada', '6', 'hora', 6, UnidadeTempo::HORA];
        yield 'a cada em minutos' => ['a cada', '12', 'minuto', 12, UnidadeTempo::MINUTO];
        yield 'cada em dias' => ['cada', '30', 'dia', 30, UnidadeTempo::DIA];
    }

    #[DataProvider('preventiveTriggerForms')]
    public function testeVisitor_FormaDeGatilhoPreventivo_CriaTempoDeCalendario(
        string $forma_gatilho,
        string $valor_dsl,
        string $unidade_dsl,
        int $valor_esperado,
        UnidadeTempo $unidade_esperada
    ): void {
        $codigo = $this->createPreventiveCode("{$forma_gatilho} {$valor_dsl} {$unidade_dsl}");

        $manutencao = $this->transformMaintenance($codigo);

        self::assertInstanceOf(Tempo::class, $manutencao->gatilho);
        self::assertSame($valor_esperado, $manutencao->gatilho->valor);
        self::assertSame($unidade_esperada, $manutencao->gatilho->unidade);
    }

    public function testeVisitor_CorretivaMinimaValida_ArmazenaCondicaoSimplesEPrazo(): void {
        $codigo =
"manutençao corretiva corretiva_minima {
    equipamento trocador_01
    quando temperatura maior 80 celsius
    procedimento resfriar_e_inspecionar
    prioridade alta
    duraçao 2 hora
    prazo 1 dia
    hh 4
}";

        $manutencao = $this->transformMaintenance($codigo);
        $condicoes = $manutencao->gatilho->getConditions();

        self::assertSame('corretiva_minima', $manutencao->nome);
        self::assertSame(TipoManutencao::CORRETIVA, $manutencao->tipo);
        self::assertSame('trocador_01', $manutencao->equipamento_identificador);
        self::assertInstanceOf(CondicaoCorretiva::class, $manutencao->gatilho);
        self::assertCount(1, $condicoes);
        self::assertInstanceOf(CondicaoNumerica::class, $condicoes[0]);
        self::assertSame(VariavelControlada::TEMPERATURA, $condicoes[0]->variavel);
        self::assertSame(Comparador::MAIOR, $condicoes[0]->comparador);
        self::assertSame(80, $condicoes[0]->valor);
        self::assertSame(UnidadeMedida::CELSIUS, $condicoes[0]->unidade);
        self::assertSame([], $manutencao->gatilho->getOperators());
        self::assertSame('resfriar_e_inspecionar', $manutencao->procedimento_identificador);
        self::assertSame(Prioridade::ALTA, $manutencao->prioridade);
        self::assertSame(2, $manutencao->duracao->valor);
        self::assertSame(UnidadeTempo::HORA, $manutencao->duracao->unidade);
        self::assertSame(4, $manutencao->homem_hora);
        self::assertSame(1, $manutencao->prazo->valor);
        self::assertSame(UnidadeTempo::DIA, $manutencao->prazo->unidade);
    }

    public function testeVisitor_CorretivaCompleta_PreservaCondicoesOperadoresEPropriedades(): void {
        $codigo =
"manutencão corretiva corretiva_completa {
    equipamento bomba_cr10
    quando vibracao maior_ou_igual 7.5 mm/s e observacao visual anormal ou vazamento grave
    procedimento substituir_rolamentos
    prioridade crítica
    duracão 4.5 hora
    prazo 30 minuto
    homem hora 8.25
}";

        $manutencao = $this->transformMaintenance($codigo);
        $condicoes = $manutencao->gatilho->getConditions();

        self::assertSame('corretiva_completa', $manutencao->nome);
        self::assertSame(TipoManutencao::CORRETIVA, $manutencao->tipo);
        self::assertSame('bomba_cr10', $manutencao->equipamento_identificador);
        self::assertCount(3, $condicoes);
        self::assertInstanceOf(CondicaoNumerica::class, $condicoes[0]);
        self::assertSame(VariavelControlada::VIBRACAO, $condicoes[0]->variavel);
        self::assertSame(Comparador::MAIOR_OU_IGUAL, $condicoes[0]->comparador);
        self::assertSame(7.5, $condicoes[0]->valor);
        self::assertSame(UnidadeMedida::MM_POR_S, $condicoes[0]->unidade);
        self::assertInstanceOf(CondicaoObservacao::class, $condicoes[1]);
        self::assertSame(EstadoObservacao::ANORMAL, $condicoes[1]->estado);
        self::assertInstanceOf(CondicaoVazamento::class, $condicoes[2]);
        self::assertSame(EstadoVazamento::GRAVE, $condicoes[2]->estado);
        self::assertSame([OperadorLogico::E, OperadorLogico::OU], $manutencao->gatilho->getOperators());
        self::assertSame('substituir_rolamentos', $manutencao->procedimento_identificador);
        self::assertSame(Prioridade::CRITICA, $manutencao->prioridade);
        self::assertSame(4.5, $manutencao->duracao->valor);
        self::assertSame(UnidadeTempo::HORA, $manutencao->duracao->unidade);
        self::assertSame(8.25, $manutencao->homem_hora);
        self::assertSame(30, $manutencao->prazo->valor);
        self::assertSame(UnidadeTempo::MINUTO, $manutencao->prazo->unidade);
    }

    public static function comparatorForms(): iterable {
        yield 'igual' => ['igual', Comparador::IGUAL];
        yield 'diferente' => ['diferente', Comparador::DIFERENTE];
        yield 'maior' => ['maior', Comparador::MAIOR];
        yield 'maior ou igual com sublinhado' => ['maior_ou_igual', Comparador::MAIOR_OU_IGUAL];
        yield 'maior igual com sublinhado' => ['maior_igual', Comparador::MAIOR_OU_IGUAL];
        yield 'maior ou igual com espaços' => ['maior ou igual', Comparador::MAIOR_OU_IGUAL];
        yield 'maior igual com espaços' => ['maior igual', Comparador::MAIOR_OU_IGUAL];
        yield 'menor' => ['menor', Comparador::MENOR];
        yield 'menor ou igual com sublinhado' => ['menor_ou_igual', Comparador::MENOR_OU_IGUAL];
        yield 'menor igual com sublinhado' => ['menor_igual', Comparador::MENOR_OU_IGUAL];
        yield 'menor ou igual com espaços' => ['menor ou igual', Comparador::MENOR_OU_IGUAL];
        yield 'menor igual com espaços' => ['menor igual', Comparador::MENOR_OU_IGUAL];
    }

    #[DataProvider('comparatorForms')]
    public function testeVisitor_ComparadorSuportado_ArmazenaEnumCorrespondente(
        string $comparador_dsl,
        Comparador $comparador_esperado
    ): void {
        $codigo = $this->createCorrectiveCode("pressao {$comparador_dsl} 10 bar");

        $manutencao = $this->transformMaintenance($codigo);
        $condicao = $manutencao->gatilho->getConditions()[0];

        self::assertInstanceOf(CondicaoNumerica::class, $condicao);
        self::assertSame($comparador_esperado, $condicao->comparador);
    }

    public static function numericConditionVariables(): iterable {
        yield 'vazão' => ['vazao', '10', 'm3/h', VariavelControlada::VAZAO, UnidadeMedida::M3_POR_HORA];
        yield 'pressão' => ['pressao', '10', 'bar', VariavelControlada::PRESSAO, UnidadeMedida::BAR];
        yield 'pressão de descarga' => ['pressao_descarga', '10', 'bar', VariavelControlada::PRESSAO_DESCARGA, UnidadeMedida::BAR];
        yield 'pressão de sucção' => ['pressao_succao', '10', 'bar', VariavelControlada::PRESSAO_SUCCAO, UnidadeMedida::BAR];
        yield 'vibração' => ['vibracao', '2.5', 'mm/s', VariavelControlada::VIBRACAO, UnidadeMedida::MM_POR_S];
        yield 'temperatura' => ['temperatura', '25', 'celsius', VariavelControlada::TEMPERATURA, UnidadeMedida::CELSIUS];
        yield 'rotação' => ['rotacao', '2900', 'rpm', VariavelControlada::ROTACAO, UnidadeMedida::RPM];
    }

    #[DataProvider('numericConditionVariables')]
    public function testeVisitor_VariavelNumericaEmCondicao_ArmazenaVariavelValorEUnidade(
        string $variavel_dsl,
        string $valor_dsl,
        string $unidade_dsl,
        VariavelControlada $variavel_esperada,
        UnidadeMedida $unidade_esperada
    ): void {
        $codigo = $this->createCorrectiveCode("{$variavel_dsl} igual {$valor_dsl} {$unidade_dsl}");

        $manutencao = $this->transformMaintenance($codigo);
        $condicao = $manutencao->gatilho->getConditions()[0];
        $valor_esperado = str_contains($valor_dsl, '.') ? (float) $valor_dsl : (int) $valor_dsl;

        self::assertInstanceOf(CondicaoNumerica::class, $condicao);
        self::assertSame($variavel_esperada, $condicao->variavel);
        self::assertSame($valor_esperado, $condicao->valor);
        self::assertSame($unidade_esperada, $condicao->unidade);
    }

    public static function observationStates(): iterable {
        yield 'normal' => ['normal', EstadoObservacao::NORMAL];
        yield 'anormal' => ['anormal', EstadoObservacao::ANORMAL];
    }

    #[DataProvider('observationStates')]
    public function testeVisitor_EstadoDeObservacao_ArmazenaCondicaoVisual(
        string $estado_dsl,
        EstadoObservacao $estado_esperado
    ): void {
        $codigo = $this->createCorrectiveCode("observacao_visual {$estado_dsl}");

        $manutencao = $this->transformMaintenance($codigo);
        $condicao = $manutencao->gatilho->getConditions()[0];

        self::assertInstanceOf(CondicaoObservacao::class, $condicao);
        self::assertSame($estado_esperado, $condicao->estado);
    }

    public static function leakStates(): iterable {
        yield 'ausente' => ['ausente', EstadoVazamento::AUSENTE];
        yield 'leve' => ['leve', EstadoVazamento::LEVE];
        yield 'moderado' => ['moderado', EstadoVazamento::MODERADO];
        yield 'grave' => ['grave', EstadoVazamento::GRAVE];
    }

    #[DataProvider('leakStates')]
    public function testeVisitor_EstadoDeVazamento_ArmazenaCondicaoDeVazamento(
        string $estado_dsl,
        EstadoVazamento $estado_esperado
    ): void {
        $codigo = $this->createCorrectiveCode("vazamento {$estado_dsl}");

        $manutencao = $this->transformMaintenance($codigo);
        $condicao = $manutencao->gatilho->getConditions()[0];

        self::assertInstanceOf(CondicaoVazamento::class, $condicao);
        self::assertSame($estado_esperado, $condicao->estado);
    }

    public static function priorityForms(): iterable {
        yield 'baixa' => ['baixa', Prioridade::BAIXA];
        yield 'media sem acento' => ['media', Prioridade::MEDIA];
        yield 'média com acento' => ['média', Prioridade::MEDIA];
        yield 'alta' => ['alta', Prioridade::ALTA];
        yield 'critica sem acento' => ['critica', Prioridade::CRITICA];
        yield 'crítica com acento' => ['crítica', Prioridade::CRITICA];
    }

    #[DataProvider('priorityForms')]
    public function testeVisitor_PrioridadeSuportada_ArmazenaEnumCorrespondente(
        string $prioridade_dsl,
        Prioridade $prioridade_esperada
    ): void {
        $codigo = $this->createPreventiveCode('a_cada 1 dia', $prioridade_dsl);

        $manutencao = $this->transformMaintenance($codigo);

        self::assertSame($prioridade_esperada, $manutencao->prioridade);
    }

    public function testPreventivaGuardaGatilhoDeCalendario(): void {
        $gatilho = new Tempo(30, UnidadeTempo::DIA);
        $manutencao = new Manutencao(
            'inspecao_bomba', TipoManutencao::PREVENTIVA, 'bomba_cr10', $gatilho,
            'inspecionar', Prioridade::MEDIA, new Tempo(2, UnidadeTempo::HORA), 3.5
        );

        self::assertSame($gatilho, $manutencao->gatilho);
        self::assertSame('bomba_cr10', $manutencao->equipamento_identificador);
        self::assertSame('inspecionar', $manutencao->procedimento_identificador);
        self::assertSame(Prioridade::MEDIA, $manutencao->prioridade);
        self::assertSame(3.5, $manutencao->homem_hora);
        self::assertNull($manutencao->prazo);
    }

    public function testCorretivaPreservaCondicoesEOperadores(): void {
        $pressao = new CondicaoNumerica(VariavelControlada::PRESSAO, Comparador::MAIOR, 10, UnidadeMedida::BAR);
        $observacao = new CondicaoObservacao(EstadoObservacao::ANORMAL);
        $vazamento = new CondicaoVazamento(EstadoVazamento::GRAVE);
        $gatilho = new CondicaoCorretiva($pressao);
        $gatilho->add(OperadorLogico::E, $observacao);
        $gatilho->add(OperadorLogico::OU, $vazamento);
        $prazo = new Tempo(1, UnidadeTempo::DIA);

        $manutencao = new Manutencao(
            'reparo_bomba', TipoManutencao::CORRETIVA, 'bomba_cr10', $gatilho,
            'reparar', Prioridade::CRITICA, new Tempo(4, UnidadeTempo::HORA), 8, $prazo
        );

        self::assertSame([$pressao, $observacao, $vazamento], $manutencao->gatilho->getConditions());
        self::assertSame([OperadorLogico::E, OperadorLogico::OU], $manutencao->gatilho->getOperators());
        self::assertInstanceOf(CondicaoCollection::class, $gatilho->condicoes);
        self::assertInstanceOf(OperadorLogicoCollection::class, $gatilho->operadores);
        self::assertSame($prazo, $manutencao->prazo);
    }

    public function testPreventivaNaoAceitaPrazo(): void {
        $this->expectException(InvalidArgumentException::class);
        new Manutencao(
            'inspecao', TipoManutencao::PREVENTIVA, 'bomba_cr10', new Tempo(30, UnidadeTempo::DIA),
            'inspecionar', Prioridade::BAIXA, new Tempo(1, UnidadeTempo::HORA), 1,
            new Tempo(2, UnidadeTempo::DIA)
        );
    }

    public function testCorretivaExigePrazo(): void {
        $this->expectException(InvalidArgumentException::class);
        new Manutencao(
            'reparo', TipoManutencao::CORRETIVA, 'bomba_cr10',
            new CondicaoCorretiva(new CondicaoVazamento(EstadoVazamento::LEVE)),
            'reparar', Prioridade::ALTA, new Tempo(2, UnidadeTempo::HORA), 2
        );
    }

    private function transformMaintenance(string $codigo): Manutencao {
        $lexer = new CMMSLexer(InputStream::fromString($codigo));
        $parser = new CMMSParser(new CommonTokenStream($lexer));
        $objetos = (new CMMSVisitor())->visit($parser->programa());

        self::assertSame(0, $parser->getNumberOfSyntaxErrors());
        self::assertCount(1, $objetos);
        self::assertInstanceOf(Manutencao::class, $objetos[0]);

        return $objetos[0];
    }

    private function createPreventiveCode(string $gatilho, string $prioridade = 'baixa'): string {
        return
"manutencao preventiva preventiva_teste {
    equipamento equipamento_teste
    {$gatilho}
    procedimento procedimento_teste
    prioridade {$prioridade}
    duracao 1 hora
    homem_hora 1
}";
    }

    private function createCorrectiveCode(string $condicao): string {
        return
"manutencao corretiva corretiva_teste {
    equipamento equipamento_teste
    quando {$condicao}
    procedimento procedimento_teste
    prioridade alta
    duracao 1 hora
    prazo 1 dia
    homem_hora 1
}";
    }
}
