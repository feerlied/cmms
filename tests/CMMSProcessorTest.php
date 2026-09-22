<?php

use Application\CMMSProcessor;
use Domain\Equipamento;
use Domain\Manutencao;
use Domain\Registro;
use PHPUnit\Framework\TestCase;

final class CMMSProcessorTest extends TestCase {
    public function testeProcess_ProgramaValidoComTodasAsDeclaracoes_RetornaObjetosDeDomainNaOrdemDoCodigo(): void {
        $codigo =
"equipamento bomba_cr10 {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        pressao 10 bar
    }
    variaveis_controladas {
        pressao
    }
}

manutencao preventiva inspecao_bomba_cr10 {
    equipamento bomba_cr10
    a_cada 30 dia
    procedimento inspecionar_bomba
    prioridade media
    duracao 2 hora
    homem_hora 2
}

registro leitura_bomba_cr10 {
    equipamento bomba_cr10
    data 10/09/2026-08:30
    valores {
        pressao 9.7 bar
    }
}";

        $objetos = (new CMMSProcessor())->process($codigo);

        self::assertCount(3, $objetos);
        self::assertInstanceOf(Equipamento::class, $objetos[0]);
        self::assertInstanceOf(Manutencao::class, $objetos[1]);
        self::assertInstanceOf(Registro::class, $objetos[2]);
        self::assertSame('bomba_cr10', $objetos[0]->nome);
        self::assertSame('inspecao_bomba_cr10', $objetos[1]->nome);
        self::assertSame('leitura_bomba_cr10', $objetos[2]->nome);
    }
}
