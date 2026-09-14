<?php

use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Domain/Enums/TipoEquipamento.php';
require_once __DIR__ . '/../src/Domain/Enums/TipoServico.php';
require_once __DIR__ . '/../src/Domain/Enums/TipoProduto.php';

final class TiposEquipamentoTest extends TestCase {

    public static function grafiasDoLexer(): iterable {
        yield 'bomba centrífuga' => [TipoEquipamento::BOMBA_CENTRIFUGA, ['bomba_centrifuga', 'bomba_centrífuga', 'bomba centrífuga', 'bomba centrifuga']];
        yield 'bomba alternativa' => [TipoEquipamento::BOMBA_ALTERNATIVA, ['bomba_alternativa', 'bomba alternativa']];
        yield 'trocador de calor' => [TipoEquipamento::TROCADOR_CALOR, ['trocador_de_calor', 'trocador_calor', 'trocador de calor', 'trocador calor']];
        yield 'tanque de gasolina' => [TipoEquipamento::TANQUE_GASOLINA, ['tanque_de_gasolina', 'tanque_gasolina', 'tanque de gasolina', 'tanque gasolina']];
        yield 'bombeamento de água' => [TipoServico::BOMBEAMENTO_AGUA, ['bombeamento_de_agua', 'bombeamento_agua', 'bombeamento_de_água', 'bombeamento_água', 'bombeamento de agua', 'bombeamento agua', 'bombeamento de água', 'bombeamento água']];
        yield 'bombeamento de gasolina' => [TipoServico::BOMBEAMENTO_GASOLINA, ['bombeamento_de_gasolina', 'bombeamento_gasolina', 'bombeamento de gasolina', 'bombeamento gasolina']];
        yield 'bombeamento de diesel' => [TipoServico::BOMBEAMENTO_DIESEL, ['bombeamento_de_diesel', 'bombeamento_diesel', 'bombeamento de diesel', 'bombeamento diesel']];
        yield 'resfriamento de gasolina' => [TipoServico::RESFRIAMENTO_GASOLINA, ['resfriamento_de_gasolina', 'resfriamento_gasolina', 'resfriamento de gasolina', 'resfriamento gasolina']];
        yield 'armazenamento de gasolina' => [TipoServico::ARMAZENAMENTO_GASOLINA, ['armazenamento_de_gasolina', 'armazenamento_gasolina', 'armazenamento de gasolina', 'armazenamento gasolina']];
        yield 'água' => [TipoProduto::AGUA, ['agua', 'água']];
        yield 'gasolina' => [TipoProduto::GASOLINA, ['gasolina']];
        yield 'diesel' => [TipoProduto::DIESEL, ['diesel']];
    }

    #[DataProvider('grafiasDoLexer')]
    public function testEnums_AceitaTodasAsGrafiasDoLexer(TipoEquipamento|TipoServico|TipoProduto $esperado, array $grafias): void {
        foreach ($grafias as $grafia) {
            self::assertSame($esperado, $esperado::fromDsl($grafia));
        }
    }
}
