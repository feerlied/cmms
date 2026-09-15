<?php

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
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Domain/Manutencao.php';

final class ManutencaoTest extends TestCase {
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
}
