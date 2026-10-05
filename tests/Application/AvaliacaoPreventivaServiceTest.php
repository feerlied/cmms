<?php

declare(strict_types=1);

namespace Application;

use Application\AvaliacaoPreventivaService;
use DateTimeImmutable;
use Domain\CalculadoraPrimeiroVencimentoPreventivo;
use Domain\CondicaoCorretiva;
use Domain\CondicaoNumerica;
use Domain\Enums\Comparador;
use Domain\Enums\Prioridade;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoManutencao;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\GeradorOrdemServicoPreventiva;
use Domain\Manutencao;
use Domain\OrdemServico;
use Domain\Tempo;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\SqliteDatabase;
use PDO;
use PHPUnit\Framework\TestCase;

final class AvaliacaoPreventivaServiceTest extends TestCase
{
    private SqliteDatabase $banco;
    private EquipamentoRepository $equipamentos;
    private ManutencaoRepository $manutencoes;
    private OrdemServicoRepository $ordens_servico;
    private AvaliacaoPreventivaService $avaliacao;

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }

        $this->banco = new SqliteDatabase(':memory:');
        $this->banco->initializeSchema();
        $this->equipamentos = new EquipamentoRepository($this->banco->connection());
        $this->manutencoes = new ManutencaoRepository($this->banco);
        $this->ordens_servico = new OrdemServicoRepository($this->banco);
        $this->avaliacao = new AvaliacaoPreventivaService(
            $this->banco,
            $this->equipamentos,
            $this->manutencoes,
            $this->ordens_servico,
            new CalculadoraPrimeiroVencimentoPreventivo(),
            new GeradorOrdemServicoPreventiva()
        );
    }

    public function testeEvaluate_AntesENoPrimeiroVencimento_CriaSomenteQuandoVencida(): void
    {
        $this->registerEquipment(new DateTimeImmutable('2026-09-28 08:30:00-03:00'));
        $this->manutencoes->save($this->createPreventiveMaintenance());

        self::assertSame([], $this->avaliacao->evaluate('bomba_01', new DateTimeImmutable('2026-09-29 08:29:59-03:00')));
        self::assertSame(0, $this->countOrders());

        $resultado = $this->avaliacao->evaluate('bomba_01', new DateTimeImmutable('2026-09-29 08:30:00-03:00'));

        self::assertCount(1, $resultado);
        self::assertSame('inspecao_01', $resultado[0]->manutencao_identificador);
        self::assertSame('2026-09-29T11:30:00', $resultado[0]->chave_evento);
        self::assertSame('2026-09-29 11:30:00+00:00', $resultado[0]->data_referencia->format('Y-m-d H:i:sP'));
        self::assertSame(StatusOrdemServico::EM_ABERTO, $resultado[0]->status);
        self::assertSame(1, $this->countOrders());
    }

    public function testeEvaluate_CadastroEPeriodoFracionado_CalculaESalvaDataEsperadaPorMinuto(): void
    {
        $this->registerEquipment(new DateTimeImmutable('2026-10-10 08:30:45.123456-03:00'));
        $this->manutencoes->save($this->createPreventiveMaintenance(new Tempo(1.5, UnidadeTempo::HORA)));

        self::assertSame([], $this->avaliacao->evaluate(
            'bomba_01',
            new DateTimeImmutable('2026-10-10 09:59:59.999999-03:00'),
        ));

        $resultado = $this->avaliacao->evaluate(
            'bomba_01',
            new DateTimeImmutable('2026-10-10 10:00:00-03:00'),
        );

        self::assertCount(1, $resultado);
        self::assertSame('2026-10-10 13:00:45.000000+00:00', $resultado[0]->data_referencia->format('Y-m-d H:i:s.uP'));
        self::assertSame('2026-10-10T13:00:45', $resultado[0]->chave_evento);

        $ordem_salva = $this->ordens_servico->findByEvent(
            'bomba_01',
            'inspecao_01',
            TipoManutencao::PREVENTIVA,
            '2026-10-10T13:00:45',
        );

        self::assertNotNull($ordem_salva);
        self::assertSame('2026-10-10 13:00:45.000000+00:00', $ordem_salva->data_referencia->format('Y-m-d H:i:s.uP'));
    }

    public function testeEvaluate_MesmoPrimeiroVencimentoReavaliado_RecuperaOrdemSemDuplicar(): void
    {
        $this->registerEquipment(new DateTimeImmutable('2026-09-28 08:30:00-03:00'));
        $this->manutencoes->save($this->createPreventiveMaintenance());
        $primeira = $this->avaliacao->evaluate('bomba_01', new DateTimeImmutable('2026-09-29 08:30:00-03:00'));

        $segunda = $this->avaliacao->evaluate('bomba_01', new DateTimeImmutable('2026-10-05 12:00:00-03:00'));

        self::assertCount(1, $segunda);
        self::assertNotSame($primeira[0], $segunda[0]);
        self::assertSame($primeira[0]->chave_evento, $segunda[0]->chave_evento);
        self::assertEquals($primeira[0]->data_referencia, $segunda[0]->data_referencia);
        self::assertSame(1, $this->countOrders());
    }

    public function testeEvaluate_ManutencaoCadastradaDepoisDoPrimeiroVencimento_UsaDataOriginalDoEquipamento(): void
    {
        $this->registerEquipment(new DateTimeImmutable('2026-09-01 08:30:00-03:00'));
        $this->manutencoes->save($this->createPreventiveMaintenance());

        $resultado = $this->avaliacao->evaluate('bomba_01', new DateTimeImmutable('2026-09-10 15:00:00-03:00'));

        self::assertCount(1, $resultado);
        self::assertSame('2026-09-02T11:30:00', $resultado[0]->chave_evento);
        self::assertSame('2026-09-02 11:30:00+00:00', $resultado[0]->data_referencia->format('Y-m-d H:i:sP'));
    }

    public function testeEvaluate_OrdemAbertaDeOutroEvento_RecuperaImpedimentoSemCriarOutra(): void
    {
        $this->registerEquipment(new DateTimeImmutable('2026-09-28 08:30:00-03:00'));
        $manutencao = $this->createPreventiveMaintenance();
        $this->manutencoes->save($manutencao);
        $impeditiva = new OrdemServico(
            equipamento_identificador: 'bomba_01',
            manutencao_identificador: 'inspecao_01',
            tipo_manutencao: TipoManutencao::PREVENTIVA,
            chave_evento: 'evento_anterior',
            data_referencia: new DateTimeImmutable('2026-09-20 08:30:00-03:00'),
            procedimento_identificador: $manutencao->procedimento_identificador,
            prioridade: $manutencao->prioridade,
            duracao: $manutencao->duracao,
            homem_hora: $manutencao->homem_hora
        );
        $this->ordens_servico->save($impeditiva);

        $resultado = $this->avaliacao->evaluate('bomba_01', new DateTimeImmutable('2026-09-29 08:30:00-03:00'));

        self::assertCount(1, $resultado);
        self::assertSame('evento_anterior', $resultado[0]->chave_evento);
        self::assertSame(1, $this->countOrders());
    }

    public function testeEvaluate_EquipamentoInexistenteOuSomenteCorretiva_NaoCriaOrdem(): void
    {
        self::assertSame([], $this->avaliacao->evaluate('inexistente', new DateTimeImmutable('2026-09-29')));
        $this->registerEquipment(new DateTimeImmutable('2026-09-28'));
        $this->manutencoes->save(new Manutencao(
            'reparo_01',
            TipoManutencao::CORRETIVA,
            'bomba_01',
            new CondicaoCorretiva(new CondicaoNumerica(
                VariavelControlada::PRESSAO,
                Comparador::MAIOR,
                10,
                UnidadeMedida::BAR
            )),
            'reparar_bomba',
            Prioridade::ALTA,
            new Tempo(2, UnidadeTempo::HORA),
            2,
            new Tempo(1, UnidadeTempo::DIA)
        ));

        self::assertSame([], $this->avaliacao->evaluate('bomba_01', new DateTimeImmutable('2026-10-20')));
        self::assertSame(0, $this->countOrders());
    }

    public function testeEvaluate_ConclusaoComAtraso_CriaSomenteOcorrenciaSeguinteAncoradaNoCadastro(): void
    {
        $this->registerEquipment(new DateTimeImmutable('2026-09-01 08:30:00-03:00'));
        $this->manutencoes->save($this->createPreventiveMaintenance());
        $agora = new DateTimeImmutable('2026-09-10 15:00:00-03:00');
        $primeira = $this->avaliacao->evaluate('bomba_01', $agora)[0];
        $this->ordens_servico->updateStatus($primeira->identificador, StatusOrdemServico::CONCLUIDA);

        $segunda = $this->avaliacao->evaluate('bomba_01', $agora)[0];

        self::assertSame('2026-09-03T11:30:00', $segunda->chave_evento);
        self::assertSame(2, $this->countOrders());
        self::assertSame($segunda->identificador, $this->avaliacao->evaluate('bomba_01', $agora)[0]->identificador);
        self::assertSame(2, $this->countOrders());

        $this->ordens_servico->updateStatus($segunda->identificador, StatusOrdemServico::EM_EXECUCAO);
        self::assertSame($segunda->identificador, $this->avaliacao->evaluate('bomba_01', $agora)[0]->identificador);
        self::assertSame(2, $this->countOrders());

        $this->ordens_servico->updateStatus($segunda->identificador, StatusOrdemServico::CONCLUIDA);
        $terceira = $this->avaliacao->evaluate('bomba_01', $agora)[0];
        self::assertSame('2026-09-04T11:30:00', $terceira->chave_evento);
        self::assertSame(3, $this->countOrders());
    }

    public function testeEvaluate_CanceladaAntesDoProximoVencimento_PreservaEventoHistorico(): void
    {
        $this->registerEquipment(new DateTimeImmutable('2026-09-01 08:30:00-03:00'));
        $this->manutencoes->save($this->createPreventiveMaintenance());
        $primeiro_vencimento = new DateTimeImmutable('2026-09-02 08:30:00-03:00');
        $primeira = $this->avaliacao->evaluate('bomba_01', $primeiro_vencimento)[0];
        $this->ordens_servico->updateStatus(
            $primeira->identificador,
            StatusOrdemServico::CANCELADA,
            $primeiro_vencimento,
        );

        $historica = $this->avaliacao->evaluate('bomba_01', $primeiro_vencimento)[0];
        self::assertSame($primeira->identificador, $historica->identificador);
        self::assertSame(StatusOrdemServico::CANCELADA, $historica->status);
        self::assertSame(1, $this->countOrders());

        $segunda = $this->avaliacao->evaluate('bomba_01', new DateTimeImmutable('2026-09-03 08:30:00-03:00'))[0];
        self::assertSame('2026-09-03T11:30:00', $segunda->chave_evento);
        self::assertSame(2, $this->countOrders());
        self::assertSame($primeira->identificador, $this->avaliacao->evaluate('bomba_01', $primeiro_vencimento)[0]->identificador);
        self::assertSame(2, $this->countOrders());
    }

    public function testeEvaluate_PeriodoDeUmMinuto_GeraOcorrenciasPorMinuto(): void
    {
        $this->registerEquipment(new DateTimeImmutable('2026-09-01 08:30:45.123456-03:00'));
        $this->manutencoes->save($this->createPreventiveMaintenance(new Tempo(1, UnidadeTempo::MINUTO)));

        self::assertSame([], $this->avaliacao->evaluate(
            'bomba_01',
            new DateTimeImmutable('2026-09-01 08:30:59.999999-03:00'),
        ));

        $primeira = $this->avaliacao->evaluate(
            'bomba_01',
            new DateTimeImmutable('2026-09-01 08:31:00-03:00'),
        )[0];
        $this->ordens_servico->updateStatus($primeira->identificador, StatusOrdemServico::CONCLUIDA);

        self::assertSame($primeira->identificador, $this->avaliacao->evaluate(
            'bomba_01', new DateTimeImmutable('2026-09-01 08:31:59.999999-03:00'),
        )[0]->identificador);
        self::assertSame(1, $this->countOrders());

        $segunda = $this->avaliacao->evaluate('bomba_01', new DateTimeImmutable('2026-09-01 08:32:00-03:00'))[0];
        self::assertSame('2026-09-01T11:32:45', $segunda->chave_evento);
        self::assertSame(2, $this->countOrders());
    }

    private function registerEquipment(DateTimeImmutable $primeiro_cadastro): void
    {
        $this->equipamentos->save(new Equipamento(
            'bomba_01',
            TipoEquipamento::BOMBA_CENTRIFUGA,
            TipoServico::BOMBEAMENTO_AGUA,
            TipoProduto::AGUA
        ), $primeiro_cadastro);
    }

    private function createPreventiveMaintenance(?Tempo $periodo = null): Manutencao
    {
        return new Manutencao(
            'inspecao_01',
            TipoManutencao::PREVENTIVA,
            'bomba_01',
            $periodo ?? new Tempo(1, UnidadeTempo::DIA),
            'inspecionar_bomba',
            Prioridade::MEDIA,
            new Tempo(1, UnidadeTempo::HORA),
            2
        );
    }

    private function countOrders(): int
    {
        return (int)$this->banco->connection()->query('SELECT COUNT(*) FROM ordem_servico')->fetchColumn();
    }
}
