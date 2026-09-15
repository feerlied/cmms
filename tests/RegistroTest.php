<?php

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
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Domain/Registro.php';

final class RegistroTest extends TestCase {
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
}
