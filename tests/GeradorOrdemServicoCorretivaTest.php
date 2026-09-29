<?php

declare(strict_types=1);

use Domain\CondicaoCorretiva;
use Domain\CondicaoNumerica;
use Domain\Enums\Comparador;
use Domain\Enums\Prioridade;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\StatusRegistro;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\ExecucaoRegistro;
use Domain\GeradorOrdemServicoCorretiva;
use Domain\Manutencao;
use Domain\OrdemServico;
use Domain\Registro;
use Domain\Tempo;
use Domain\ValorNumericoRegistro;
use Domain\ValorRegistradoCollection;
use PHPUnit\Framework\TestCase;

final class GeradorOrdemServicoCorretivaTest extends TestCase {
    public function testeGenerate_LeituraQueSatisfazCondicao_CriaOrdemComDadosDoPlano(): void {
        $manutencao = $this->createCorrectiveMaintenance();
        $data_leitura = new DateTimeImmutable('2026-09-28 23:59:59-03:00');
        $registro = $this->createRecord(11, $data_leitura);

        $ordem_servico = (new GeradorOrdemServicoCorretiva())->generate($manutencao, $registro);

        self::assertInstanceOf(OrdemServico::class, $ordem_servico);
        self::assertSame('equipamento_01', $ordem_servico->equipamento_identificador);
        self::assertSame('corretiva_01', $ordem_servico->manutencao_identificador);
        self::assertSame(TipoManutencao::CORRETIVA, $ordem_servico->tipo_manutencao);
        self::assertSame('2026-09-28', $ordem_servico->chave_evento);
        self::assertSame($data_leitura, $ordem_servico->data_referencia);
        self::assertSame('reparar_equipamento', $ordem_servico->procedimento_identificador);
        self::assertSame(Prioridade::ALTA, $ordem_servico->prioridade);
        self::assertSame($manutencao->duracao, $ordem_servico->duracao);
        self::assertSame(2.5, $ordem_servico->homem_hora);
        self::assertSame($manutencao->prazo, $ordem_servico->prazo);
        self::assertSame(StatusOrdemServico::EM_ABERTO, $ordem_servico->status);
    }

    public function testeGenerate_CondicaoFalsa_NaoCriaOrdem(): void {
        self::assertNull((new GeradorOrdemServicoCorretiva())->generate(
            $this->createCorrectiveMaintenance(),
            $this->createRecord(10)
        ));
    }

    public function testeGenerate_VariavelExigidaAusente_NaoCriaOrdem(): void {
        $registro = new Registro(
            'leitura_01',
            'equipamento_01',
            new DateTimeImmutable('2026-09-28'),
            new ValorRegistradoCollection(new ValorNumericoRegistro(
                VariavelControlada::TEMPERATURA,
                50,
                UnidadeMedida::CELSIUS
            ))
        );

        self::assertNull((new GeradorOrdemServicoCorretiva())->generate($this->createCorrectiveMaintenance(), $registro));
    }

    public function testeGenerate_EquipamentoDiferente_NaoCriaOrdem(): void {
        self::assertNull((new GeradorOrdemServicoCorretiva())->generate(
            $this->createCorrectiveMaintenance(),
            $this->createRecord(11, equipamento_identificador: 'outro_equipamento')
        ));
    }

    public function testeGenerate_RegistroDeExecucao_NaoCriaOrdem(): void {
        $registro = new Registro(
            'execucao_01',
            'equipamento_01',
            new DateTimeImmutable('2026-09-28'),
            new ValorRegistradoCollection(new ValorNumericoRegistro(
                VariavelControlada::PRESSAO,
                11,
                UnidadeMedida::BAR
            )),
            new ExecucaoRegistro('corretiva_01', new Tempo(1, UnidadeTempo::HORA), StatusRegistro::CONCLUIDO)
        );

        self::assertNull((new GeradorOrdemServicoCorretiva())->generate($this->createCorrectiveMaintenance(), $registro));
    }

    public function testeGenerate_ManutencaoPreventiva_NaoCriaOrdemCorretiva(): void {
        $manutencao = new Manutencao(
            'preventiva_01',
            TipoManutencao::PREVENTIVA,
            'equipamento_01',
            new Tempo(1, UnidadeTempo::DIA),
            'inspecionar_equipamento',
            Prioridade::MEDIA,
            new Tempo(1, UnidadeTempo::HORA),
            1
        );

        self::assertNull((new GeradorOrdemServicoCorretiva())->generate($manutencao, $this->createRecord(11)));
    }

    private function createCorrectiveMaintenance(): Manutencao {
        return new Manutencao(
            'corretiva_01',
            TipoManutencao::CORRETIVA,
            'equipamento_01',
            new CondicaoCorretiva(new CondicaoNumerica(
                VariavelControlada::PRESSAO,
                Comparador::MAIOR,
                10,
                UnidadeMedida::BAR
            )),
            'reparar_equipamento',
            Prioridade::ALTA,
            new Tempo(2, UnidadeTempo::HORA),
            2.5,
            new Tempo(1, UnidadeTempo::DIA)
        );
    }

    private function createRecord(
        int $valor,
        ?DateTimeImmutable $data = null,
        string $equipamento_identificador = 'equipamento_01'
    ): Registro {
        return new Registro(
            'leitura_01',
            $equipamento_identificador,
            $data ?? new DateTimeImmutable('2026-09-28'),
            new ValorRegistradoCollection(new ValorNumericoRegistro(
                VariavelControlada::PRESSAO,
                $valor,
                UnidadeMedida::BAR
            ))
        );
    }
}
