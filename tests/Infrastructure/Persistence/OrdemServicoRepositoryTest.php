<?php

declare(strict_types=1);

namespace Infrastructure\Persistence;

use DateTimeImmutable;
use DateTimeZone;
use Domain\Enums\Prioridade;
use Domain\Enums\StatusOrdemServico;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeTempo;
use Domain\OrdemServico;
use Domain\Tempo;
use InvalidArgumentException;
use PDOException;
use PHPUnit\Framework\TestCase;

final class OrdemServicoRepositoryTest extends TestCase
{
    public function testeSave_OrdemPreventiva_NormalizaDataParaUtcComSegundos(): void
    {
        $repositorio = $this->createRepository();
        $data_referencia = new DateTimeImmutable('2026-09-28 10:30:15.123456', new DateTimeZone('America/Sao_Paulo'));
        $ordem = new OrdemServico(
            equipamento_identificador: 'eq1',
            manutencao_identificador: 'man1',
            tipo_manutencao: TipoManutencao::PREVENTIVA,
            chave_evento: '2026-09-28T10:30:15',
            data_referencia: $data_referencia,
            procedimento_identificador: 'inspecionar_bomba',
            prioridade: Prioridade::MEDIA,
            duracao: new Tempo(1.5, UnidadeTempo::HORA),
            homem_hora: 2.5
        );

        $repositorio->save($ordem);
        $recuperada = $repositorio->findByEvent('eq1', 'man1', TipoManutencao::PREVENTIVA, '2026-09-28T10:30:15');

        self::assertSame($ordem->identificador, $recuperada->identificador);
        self::assertSame(StatusOrdemServico::EM_ABERTO, $recuperada->status);
        self::assertSame('2026-09-28 13:30:15.000000+00:00', $recuperada->data_referencia->format('Y-m-d H:i:s.uP'));
        self::assertSame('UTC', $recuperada->data_referencia->getTimezone()->getName());
        self::assertSame(1.5, $recuperada->duracao->valor);
        self::assertSame(2.5, $recuperada->homem_hora);
        self::assertNull($recuperada->prazo);
        self::assertNull($repositorio->findByEvent('eq2', 'man1', TipoManutencao::PREVENTIVA, '2026-09-28T10:30:15'));
    }

    public function testeSave_OrdemCorretiva_PreservaTipoEPrazo(): void
    {
        $repositorio = $this->createRepository();
        $ordem = new OrdemServico(
            equipamento_identificador: 'eq1',
            manutencao_identificador: 'man1',
            tipo_manutencao: TipoManutencao::CORRETIVA,
            chave_evento: '2026-09-28',
            data_referencia: new DateTimeImmutable('2026-09-28 08:00:00+00:00'),
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::CRITICA,
            duracao: new Tempo(3, UnidadeTempo::HORA),
            homem_hora: 6,
            prazo: new Tempo(1, UnidadeTempo::DIA)
        );

        $repositorio->save($ordem);

        self::assertEquals(
            $ordem,
            $repositorio->findByEvent('eq1', 'man1', TipoManutencao::CORRETIVA, '2026-09-28')
        );
        self::assertNull($repositorio->findByEvent('eq1', 'man1', TipoManutencao::PREVENTIVA, '2026-09-28'));
    }

    public function testeFindOpenByPair_EventoAberto_RetornaOrdemDoPar(): void
    {
        $repositorio = $this->createRepository();
        $repositorio->save($this->createOrder('evento_recente', '2026-09-29 10:00:00+00:00'));

        self::assertSame('evento_recente', $repositorio->findOpenByPair('eq1', 'man1')->chave_evento);
        self::assertNull($repositorio->findOpenByPair('eq2', 'man1'));
        self::assertNull($repositorio->findOpenByPair('eq2', 'man2'));
        self::assertNull($repositorio->findByEvent('eq1', 'man1', TipoManutencao::CORRETIVA, 'inexistente'));
    }

    public function testeSave_MesmoEventoPersistidoDuasVezes_RejeitaDuplicidade(): void
    {
        $repositorio = $this->createRepository();
        $repositorio->save($this->createOrder('evento1', '2026-09-28 10:00:00+00:00'));

        $this->expectException(PDOException::class);

        $repositorio->save($this->createOrder('evento1', '2026-09-28 11:00:00+00:00'));
    }

    public function testeSave_ManutencaoDeOutroEquipamento_RejeitaOrdem(): void
    {
        $repositorio = $this->createRepository();
        $ordem = new OrdemServico(
            equipamento_identificador: 'eq2',
            manutencao_identificador: 'man1',
            tipo_manutencao: TipoManutencao::CORRETIVA,
            chave_evento: 'evento_invalido',
            data_referencia: new DateTimeImmutable('2026-09-28 10:00:00+00:00'),
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::ALTA,
            duracao: new Tempo(2, UnidadeTempo::HORA),
            homem_hora: 4,
            prazo: new Tempo(1, UnidadeTempo::DIA)
        );

        $this->expectException(InvalidArgumentException::class);
        $repositorio->save($ordem);
    }

    public function testeSave_FloatIntegralEInteiro_PreservaTiposNumericos(): void
    {
        $repositorio = $this->createRepository();
        $ordem = new OrdemServico(
            equipamento_identificador: 'eq1',
            manutencao_identificador: 'man1',
            tipo_manutencao: TipoManutencao::CORRETIVA,
            chave_evento: 'evento_numerico',
            data_referencia: new DateTimeImmutable('2026-09-28 10:00:00+00:00'),
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::ALTA,
            duracao: new Tempo(2.0, UnidadeTempo::HORA),
            homem_hora: 4,
            prazo: new Tempo(1.0, UnidadeTempo::DIA)
        );

        $repositorio->save($ordem);
        $recuperada = $repositorio->findByEvent('eq1', 'man1', TipoManutencao::CORRETIVA, 'evento_numerico');

        self::assertSame(2.0, $recuperada->duracao->valor);
        self::assertSame(4, $recuperada->homem_hora);
        self::assertSame(1.0, $recuperada->prazo->valor);
    }

    public function testeFindByReferencePeriod_LimitesEOrdenacao_RetornaHistoricoNoIntervalo(): void {
        $repositorio = $this->createRepository();
        $inicio = new DateTimeImmutable('2026-09-29 00:00:00', new DateTimeZone('America/Sao_Paulo'));
        $fim = $inicio->modify('+1 day');
        $antes = $this->createOrder('antes', '2026-09-29 02:59:59.999999+00:00');
        $no_inicio = $this->createOrder('inicio', '2026-09-29 03:00:00+00:00');
        $meio_a = $this->createOrder('meio_a', '2026-09-29 12:00:00+00:00');
        $meio_b = $this->createOrder('meio_b', '2026-09-29 12:00:00+00:00');
        $no_fim = $this->createOrder('fim', '2026-09-30 03:00:00+00:00');

        foreach ([$meio_b, $no_fim, $antes, $meio_a, $no_inicio] as $ordem) {
            $repositorio->save($ordem);
            $repositorio->updateStatus($ordem->identificador, StatusOrdemServico::CONCLUIDA);
        }

        $identificadores_no_meio = [$meio_a->identificador, $meio_b->identificador];
        sort($identificadores_no_meio, SORT_STRING);
        $esperados = array_merge([$no_inicio->identificador], $identificadores_no_meio);
        $encontradas = $repositorio->findByReferencePeriod($inicio, $fim);

        self::assertSame($esperados, array_map(
            static fn (OrdemServico $ordem): string => $ordem->identificador,
            $encontradas
        ));
        self::assertSame(
            [StatusOrdemServico::CONCLUIDA, StatusOrdemServico::CONCLUIDA, StatusOrdemServico::CONCLUIDA],
            array_map(static fn (OrdemServico $ordem): StatusOrdemServico => $ordem->status, $encontradas)
        );
    }

    public function testeFindByReferencePeriod_EquipamentoEStatus_FiltraInclusiveEstadosHistoricos(): void {
        $repositorio = $this->createRepository();
        $concluida = $this->createOrder('concluida', '2026-09-29 08:00:00+00:00');
        $cancelada = $this->createOrder('cancelada', '2026-09-29 09:00:00+00:00');
        $outra_bomba = new OrdemServico(
            equipamento_identificador: 'eq2',
            manutencao_identificador: 'man2',
            tipo_manutencao: TipoManutencao::PREVENTIVA,
            chave_evento: 'aberta',
            data_referencia: new DateTimeImmutable('2026-09-29 10:00:00+00:00'),
            procedimento_identificador: 'inspecionar',
            prioridade: Prioridade::MEDIA,
            duracao: new Tempo(1, UnidadeTempo::HORA),
            homem_hora: 1
        );

        $repositorio->save($concluida);
        $repositorio->updateStatus($concluida->identificador, StatusOrdemServico::CONCLUIDA);
        $repositorio->save($cancelada);
        $repositorio->updateStatus(
            $cancelada->identificador,
            StatusOrdemServico::CANCELADA,
            new DateTimeImmutable('2026-09-29 09:30:00+00:00')
        );
        $repositorio->save($outra_bomba);

        $inicio = new DateTimeImmutable('2026-09-29 00:00:00+00:00');
        $fim = new DateTimeImmutable('2026-09-30 00:00:00+00:00');

        self::assertCount(3, $repositorio->findByReferencePeriod($inicio, $fim));
        self::assertSame(
            [$concluida->identificador, $cancelada->identificador],
            array_map(
                static fn (OrdemServico $ordem): string => $ordem->identificador,
                $repositorio->findByReferencePeriod($inicio, $fim, 'eq1')
            )
        );
        self::assertSame(
            [$cancelada->identificador],
            array_map(
                static fn (OrdemServico $ordem): string => $ordem->identificador,
                $repositorio->findByReferencePeriod($inicio, $fim, null, StatusOrdemServico::CANCELADA)
            )
        );
        self::assertSame(
            [],
            $repositorio->findByReferencePeriod($inicio, $fim, 'eq2', StatusOrdemServico::CANCELADA)
        );
    }

    public function testeFindByReferencePeriod_FimNaoPosteriorAoInicio_RejeitaIntervalo(): void {
        $repositorio = $this->createRepository();
        $data = new DateTimeImmutable('2026-09-29 00:00:00+00:00');

        $this->expectException(InvalidArgumentException::class);
        $repositorio->findByReferencePeriod($data, $data);
    }

    private function createRepository(): OrdemServicoRepository
    {
        $banco = new SqliteDatabase(':memory:');
        $banco->initializeSchema();
        $banco->connection()->exec(<<<'SQL'
            INSERT INTO equipamento
                (nome, primeiro_cadastro, tipo, servico, produto)
            VALUES
                ('eq1', '2026-09-28 10:00:00', 'bomba_centrifuga', 'bombeamento_de_agua', 'agua'),
                ('eq2', '2026-09-28 10:00:00', 'bomba_centrifuga', 'bombeamento_de_agua', 'agua')
            SQL
        );
        $banco->connection()->exec(<<<'SQL'
            INSERT INTO manutencao
                (nome, equipamento_id, tipo, gatilho_valor, gatilho_valor_tipo, gatilho_unidade,
                 procedimento_identificador, prioridade, duracao_valor, duracao_valor_tipo,
                 duracao_unidade, homem_hora, homem_hora_tipo)
            VALUES
                ('man1', (SELECT id FROM equipamento WHERE nome = 'eq1'), 'preventiva', 1, 'int', 'dia', 'inspecionar', 'media', 1, 'int', 'hora', 1, 'int'),
                ('man2', (SELECT id FROM equipamento WHERE nome = 'eq2'), 'preventiva', 1, 'int', 'dia', 'inspecionar', 'media', 1, 'int', 'hora', 1, 'int')
            SQL
        );

        return new OrdemServicoRepository($banco);
    }

    private function createOrder(string $chave_evento, string $data_referencia): OrdemServico
    {
        return new OrdemServico(
            equipamento_identificador: 'eq1',
            manutencao_identificador: 'man1',
            tipo_manutencao: TipoManutencao::CORRETIVA,
            chave_evento: $chave_evento,
            data_referencia: new DateTimeImmutable($data_referencia),
            procedimento_identificador: 'reparar_bomba',
            prioridade: Prioridade::ALTA,
            duracao: new Tempo(2, UnidadeTempo::HORA),
            homem_hora: 4,
            prazo: new Tempo(1, UnidadeTempo::DIA)
        );
    }
}
