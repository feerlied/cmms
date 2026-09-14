<?php

use Domain\Enums\Comparador;
use Domain\Enums\EstadoObservacao;
use Domain\Enums\EstadoVazamento;
use Domain\Enums\Prioridade;
use Domain\Enums\StatusRegistro;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoManutencao;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../src/Domain/Enums/TipoEquipamento.php';
require_once __DIR__ . '/../src/Domain/Enums/TipoServico.php';
require_once __DIR__ . '/../src/Domain/Enums/TipoProduto.php';
require_once __DIR__ . '/../src/Domain/Enums/Comparador.php';
require_once __DIR__ . '/../src/Domain/Enums/EstadoObservacao.php';
require_once __DIR__ . '/../src/Domain/Enums/EstadoVazamento.php';
require_once __DIR__ . '/../src/Domain/Enums/Prioridade.php';
require_once __DIR__ . '/../src/Domain/Enums/StatusRegistro.php';
require_once __DIR__ . '/../src/Domain/Enums/TipoManutencao.php';
require_once __DIR__ . '/../src/Domain/Enums/UnidadeMedida.php';
require_once __DIR__ . '/../src/Domain/Enums/UnidadeTempo.php';
require_once __DIR__ . '/../src/Domain/Enums/VariavelControlada.php';

final class EnumsTest extends TestCase {

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
        yield 'manutenção preventiva' => [TipoManutencao::PREVENTIVA, ['preventiva']];
        yield 'manutenção corretiva' => [TipoManutencao::CORRETIVA, ['corretiva']];
        yield 'prioridade baixa' => [Prioridade::BAIXA, ['baixa']];
        yield 'prioridade média' => [Prioridade::MEDIA, ['media', 'média']];
        yield 'prioridade alta' => [Prioridade::ALTA, ['alta']];
        yield 'prioridade crítica' => [Prioridade::CRITICA, ['critica', 'crítica']];
        yield 'vazão' => [VariavelControlada::VAZAO, ['vazao', 'vazão']];
        yield 'pressão' => [VariavelControlada::PRESSAO, ['pressao', 'pressão']];
        yield 'pressão de descarga' => [VariavelControlada::PRESSAO_DESCARGA, ['pressao_descarga', 'pressao_de_descarga', 'pressao descarga', 'pressao de descarga', 'pressão_descarga', 'pressão_de_descarga', 'pressão descarga', 'pressão de descarga']];
        yield 'pressão de sucção' => [VariavelControlada::PRESSAO_SUCCAO, [
            'pressao_succao', 'pressao_de_succao', 'pressao succao', 'pressao de succao',
            'pressão_succao', 'pressão_de_succao', 'pressão succao', 'pressão de succao',
            'pressao_succão', 'pressao_de_succão', 'pressao succão', 'pressao de succão',
            'pressão_succão', 'pressão_de_succão', 'pressão succão', 'pressão de succão',
            'pressao_sucçao', 'pressao_de_sucçao', 'pressao sucçao', 'pressao de sucçao',
            'pressao_sucção', 'pressao_de_sucção', 'pressao sucção', 'pressao de sucção',
            'pressão_sucçao', 'pressão_de_sucçao', 'pressão sucçao', 'pressão de sucçao',
            'pressão_sucção', 'pressão_de_sucção', 'pressão sucção', 'pressão de sucção',
        ]];
        yield 'vibração' => [VariavelControlada::VIBRACAO, ['vibracao', 'vibraçao', 'vibracão', 'vibração']];
        yield 'temperatura' => [VariavelControlada::TEMPERATURA, ['temperatura']];
        yield 'rotação' => [VariavelControlada::ROTACAO, ['rotacao', 'rotaçao', 'rotacão', 'rotação']];
        yield 'horas de operação' => [VariavelControlada::HORAS_OPERACAO, ['horas_operacao', 'horas operacao', 'horas_operacão', 'horas operacão', 'horas_operaçao', 'horas operaçao', 'horas_operação', 'horas operação']];
        yield 'observação visual' => [VariavelControlada::OBSERVACAO_VISUAL, ['observacao_visual', 'observacao visual']];
        yield 'vazamento' => [VariavelControlada::VAZAMENTO, ['vazamento']];
        yield 'unidade mm/s' => [UnidadeMedida::MM_POR_S, ['mm_por_seg', 'mm_seg', 'mm por seg', 'mm seg', 'mm/s', 'mm/seg']];
        yield 'unidade celsius' => [UnidadeMedida::CELSIUS, ['celsius', 'C']];
        yield 'unidade bar' => [UnidadeMedida::BAR, ['bar']];
        yield 'unidade m3/h' => [UnidadeMedida::M3_POR_HORA, ['m3_por_hora', 'm3_hora', 'm3 por hora', 'm3 hora', 'm3/h', 'm3/hora']];
        yield 'unidade hora' => [UnidadeMedida::HORA, ['hora']];
        yield 'unidade minuto' => [UnidadeMedida::MINUTO, ['minuto']];
        yield 'unidade dia' => [UnidadeMedida::DIA, ['dia']];
        yield 'unidade rpm' => [UnidadeMedida::RPM, ['rpm']];
        yield 'unidade litro' => [UnidadeMedida::LITRO, ['litro']];
        yield 'tempo hora' => [UnidadeTempo::HORA, ['hora']];
        yield 'tempo minuto' => [UnidadeTempo::MINUTO, ['minuto']];
        yield 'tempo dia' => [UnidadeTempo::DIA, ['dia']];
        yield 'comparador igual' => [Comparador::IGUAL, ['igual']];
        yield 'comparador diferente' => [Comparador::DIFERENTE, ['diferente']];
        yield 'comparador maior' => [Comparador::MAIOR, ['maior']];
        yield 'comparador maior ou igual' => [Comparador::MAIOR_OU_IGUAL, ['maior_ou_igual', 'maior_igual', 'maior ou igual', 'maior igual']];
        yield 'comparador menor' => [Comparador::MENOR, ['menor']];
        yield 'comparador menor ou igual' => [Comparador::MENOR_OU_IGUAL, ['menor_ou_igual', 'menor_igual', 'menor ou igual', 'menor igual']];
        yield 'observação normal' => [EstadoObservacao::NORMAL, ['normal']];
        yield 'observação anormal' => [EstadoObservacao::ANORMAL, ['anormal']];
        yield 'vazamento ausente' => [EstadoVazamento::AUSENTE, ['ausente']];
        yield 'vazamento leve' => [EstadoVazamento::LEVE, ['leve']];
        yield 'vazamento moderado' => [EstadoVazamento::MODERADO, ['moderado']];
        yield 'vazamento grave' => [EstadoVazamento::GRAVE, ['grave']];
        yield 'registro em aberto' => [StatusRegistro::EM_ABERTO, ['em_aberto', 'em aberto']];
        yield 'registro em execução' => [StatusRegistro::EM_EXECUCAO, ['em_execucao', 'em execucao', 'em_execucão', 'em execucão', 'em_execuçao', 'em execuçao', 'em_execução', 'em execução']];
        yield 'registro concluído' => [StatusRegistro::CONCLUIDO, ['concluido', 'concluído']];
        yield 'registro cancelado' => [StatusRegistro::CANCELADO, ['cancelado']];
    }

    #[DataProvider('grafiasDoLexer')]
    public function testEnums_AceitaTodasAsGrafiasDoLexer($esperado, array $grafias): void {
        foreach ($grafias as $grafia) {
            self::assertSame($esperado, $esperado::fromDsl($grafia));
        }
    }
}
