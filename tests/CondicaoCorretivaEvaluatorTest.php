<?php

declare(strict_types=1);

use Domain\CondicaoCorretiva;
use Domain\CondicaoCorretivaEvaluator;
use Domain\CondicaoNumerica;
use Domain\CondicaoObservacao;
use Domain\CondicaoVazamento;
use Domain\Enums\Comparador;
use Domain\Enums\EstadoObservacao;
use Domain\Enums\EstadoVazamento;
use Domain\Enums\OperadorLogico;
use Domain\Enums\StatusRegistro;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\ExecucaoRegistro;
use Domain\ObservacaoVisualRegistro;
use Domain\Registro;
use Domain\Tempo;
use Domain\ValorNumericoRegistro;
use Domain\ValorRegistradoCollection;
use Domain\VazamentoRegistro;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CondicaoCorretivaEvaluatorTest extends TestCase {
    public static function comparacoesNumericas(): iterable {
        yield 'igual no limite' => [Comparador::IGUAL, 10, true];
        yield 'igual acima do limite' => [Comparador::IGUAL, 11, false];
        yield 'diferente no limite' => [Comparador::DIFERENTE, 10, false];
        yield 'diferente acima do limite' => [Comparador::DIFERENTE, 11, true];
        yield 'maior no limite' => [Comparador::MAIOR, 10, false];
        yield 'maior acima do limite' => [Comparador::MAIOR, 11, true];
        yield 'maior ou igual no limite' => [Comparador::MAIOR_OU_IGUAL, 10, true];
        yield 'maior ou igual abaixo do limite' => [Comparador::MAIOR_OU_IGUAL, 9, false];
        yield 'menor no limite' => [Comparador::MENOR, 10, false];
        yield 'menor abaixo do limite' => [Comparador::MENOR, 9, true];
        yield 'menor ou igual no limite' => [Comparador::MENOR_OU_IGUAL, 10, true];
        yield 'menor ou igual acima do limite' => [Comparador::MENOR_OU_IGUAL, 11, false];
    }

    #[DataProvider('comparacoesNumericas')]
    public function testeEvaluate_ComparadorNumerico_RespeitaLimite(
        Comparador $comparador,
        int $valor,
        bool $esperado
    ): void {
        $condicao = new CondicaoCorretiva(new CondicaoNumerica(
            VariavelControlada::PRESSAO,
            $comparador,
            10,
            UnidadeMedida::BAR
        ));
        $registro = $this->createRecord(new ValorNumericoRegistro(
            VariavelControlada::PRESSAO,
            $valor,
            UnidadeMedida::BAR
        ));

        self::assertSame($esperado, (new CondicaoCorretivaEvaluator())->evaluate($condicao, $registro));
    }

    public function testeEvaluate_ValorDecimalEquivalente_ConsideraIgualdadeNumerica(): void {
        $condicao = new CondicaoCorretiva(new CondicaoNumerica(
            VariavelControlada::PRESSAO,
            Comparador::IGUAL,
            10.0,
            UnidadeMedida::BAR
        ));

        self::assertTrue((new CondicaoCorretivaEvaluator())->evaluate(
            $condicao,
            $this->createRecord(new ValorNumericoRegistro(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR))
        ));
    }

    public function testeEvaluate_EstadosDeObservacaoEVazamento_ComparaIgualdade(): void {
        $condicao = new CondicaoCorretiva(new CondicaoObservacao(EstadoObservacao::ANORMAL));
        $condicao->add(OperadorLogico::E, new CondicaoVazamento(EstadoVazamento::GRAVE));
        $avaliador = new CondicaoCorretivaEvaluator();

        self::assertTrue($avaliador->evaluate($condicao, $this->createRecord(
            new ObservacaoVisualRegistro(EstadoObservacao::ANORMAL),
            new VazamentoRegistro(EstadoVazamento::GRAVE)
        )));
        self::assertFalse($avaliador->evaluate($condicao, $this->createRecord(
            new ObservacaoVisualRegistro(EstadoObservacao::NORMAL),
            new VazamentoRegistro(EstadoVazamento::GRAVE)
        )));
    }

    public function testeEvaluate_OperadoresMisturados_AvaliaDaEsquerdaParaDireita(): void {
        $condicao = new CondicaoCorretiva(new CondicaoNumerica(
            VariavelControlada::PRESSAO,
            Comparador::MAIOR,
            10,
            UnidadeMedida::BAR
        ));
        $condicao->add(OperadorLogico::OU, new CondicaoObservacao(EstadoObservacao::ANORMAL));
        $condicao->add(OperadorLogico::E, new CondicaoVazamento(EstadoVazamento::GRAVE));

        self::assertFalse((new CondicaoCorretivaEvaluator())->evaluate($condicao, $this->createRecord(
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 11, UnidadeMedida::BAR),
            new ObservacaoVisualRegistro(EstadoObservacao::NORMAL),
            new VazamentoRegistro(EstadoVazamento::AUSENTE)
        )));
        self::assertTrue((new CondicaoCorretivaEvaluator())->evaluate($condicao, $this->createRecord(
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9, UnidadeMedida::BAR),
            new ObservacaoVisualRegistro(EstadoObservacao::ANORMAL),
            new VazamentoRegistro(EstadoVazamento::GRAVE)
        )));
    }

    public function testeEvaluate_RamoOuVerdadeiroComVariavelAusente_NaoDispara(): void {
        $condicao = new CondicaoCorretiva(new CondicaoNumerica(
            VariavelControlada::PRESSAO,
            Comparador::MAIOR,
            10,
            UnidadeMedida::BAR
        ));
        $condicao->add(OperadorLogico::OU, new CondicaoObservacao(EstadoObservacao::ANORMAL));

        self::assertFalse((new CondicaoCorretivaEvaluator())->evaluate(
            $condicao,
            $this->createRecord(new ValorNumericoRegistro(VariavelControlada::PRESSAO, 11, UnidadeMedida::BAR))
        ));
    }

    public function testeEvaluate_UnidadeDiferente_NaoComparaNumeros(): void {
        $condicao = new CondicaoCorretiva(new CondicaoNumerica(
            VariavelControlada::PRESSAO,
            Comparador::MAIOR,
            10,
            UnidadeMedida::BAR
        ));

        self::assertFalse((new CondicaoCorretivaEvaluator())->evaluate(
            $condicao,
            $this->createRecord(new ValorNumericoRegistro(VariavelControlada::PRESSAO, 11, UnidadeMedida::LITRO))
        ));
    }

    public function testeEvaluate_RegistroDeExecucao_NaoDisparaCorretiva(): void {
        $condicao = new CondicaoCorretiva(new CondicaoNumerica(
            VariavelControlada::PRESSAO,
            Comparador::MAIOR,
            10,
            UnidadeMedida::BAR
        ));
        $registro = new Registro(
            'execucao_01',
            'equipamento_01',
            new DateTimeImmutable('2026-09-28'),
            new ValorRegistradoCollection(new ValorNumericoRegistro(VariavelControlada::PRESSAO, 11, UnidadeMedida::BAR)),
            new ExecucaoRegistro('corretiva_01', new Tempo(1, UnidadeTempo::HORA), StatusRegistro::CONCLUIDO)
        );

        self::assertFalse((new CondicaoCorretivaEvaluator())->evaluate($condicao, $registro));
    }

    private function createRecord(
        ValorNumericoRegistro|ObservacaoVisualRegistro|VazamentoRegistro ...$valores
    ): Registro {
        return new Registro(
            'leitura_01',
            'equipamento_01',
            new DateTimeImmutable('2026-09-28'),
            new ValorRegistradoCollection(...$valores)
        );
    }
}
