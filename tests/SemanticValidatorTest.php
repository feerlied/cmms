<?php

declare(strict_types=1);

use Application\SemanticValidator;
use Diagnostic\Diagnostic;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticSeverity;
use Domain\CaracteristicaProcesso;
use Domain\CaracteristicaProcessoCollection;
use Domain\CondicaoCorretiva;
use Domain\CondicaoNumerica;
use Domain\CondicaoObservacao;
use Domain\CondicaoVazamento;
use Domain\Enums\Comparador;
use Domain\Enums\EstadoObservacao;
use Domain\Enums\EstadoVazamento;
use Domain\Enums\Prioridade;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoManutencao;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\HorasOperacaoRegistro;
use Domain\Manutencao;
use Domain\ObservacaoVisualRegistro;
use Domain\Registro;
use Domain\Tempo;
use Domain\ValorNumericoRegistro;
use Domain\ValorRegistradoCollection;
use Domain\VariavelControladaCollection;
use Domain\VazamentoRegistro;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SemanticValidatorTest extends TestCase {
    public function testeValidate_ProgramaComTodasAsRegrasAtendidas_NaoRetornaDiagnosticos(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [
                VariavelControlada::PRESSAO,
                VariavelControlada::TEMPERATURA,
                VariavelControlada::VIBRACAO,
                VariavelControlada::HORAS_OPERACAO,
                VariavelControlada::OBSERVACAO_VISUAL,
                VariavelControlada::VAZAMENTO,
            ],
            caracteristicas_processo: [
                new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR),
                new CaracteristicaProcesso(VariavelControlada::TEMPERATURA, 25, UnidadeMedida::CELSIUS),
            ]
        );
        $manutencao = $this->createPreventiveMaintenance('manutencao_teste', $equipamento->nome, 30);
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9, UnidadeMedida::BAR)
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $manutencao, $registro]);

        self::assertSame([], $diagnosticos);
    }

    public function testeValidate_IdentificadorDuplicado_RetornaDiagnosticDaSegundaDeclaracao(): void {
        $equipamento = $this->createEquipment(nome: 'item');
        $manutencao = $this->createPreventiveMaintenance('item', $equipamento->nome, 1);

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $manutencao]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-001']);
        self::assertStringContainsString("'item'", $diagnosticos[0]->mensagem);
    }

    public function testeValidate_ManutencaoReferenciandoEquipamentoDeclaradoPosteriormente_AceitaReferenciaFutura(): void {
        $equipamento = $this->createEquipment();
        $manutencao = $this->createPreventiveMaintenance('manutencao_teste', $equipamento->nome, 1);

        $diagnosticos = (new SemanticValidator())->validate([$manutencao, $equipamento]);

        self::assertSame([], $diagnosticos);
    }

    public function testeValidate_ManutencaoReferenciandoEquipamentoInexistente_RetornaDiagnostic(): void {
        $manutencao = $this->createPreventiveMaintenance('manutencao_teste', 'equipamento_inexistente', 1);

        $diagnosticos = (new SemanticValidator())->validate([$manutencao]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-002']);
        self::assertStringContainsString("'equipamento_inexistente'", $diagnosticos[0]->mensagem);
    }

    public function testeValidate_RegistroReferenciandoEquipamentoDeclaradoPosteriormente_AceitaReferenciaFutura(): void {
        $equipamento = $this->createEquipment();
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9, UnidadeMedida::BAR)
        );

        $diagnosticos = (new SemanticValidator())->validate([$registro, $equipamento]);

        self::assertSame([], $diagnosticos);
    }

    public function testeValidate_RegistroReferenciandoEquipamentoInexistente_RetornaDiagnostic(): void {
        $registro = $this->createRecord(
            'registro_teste',
            'equipamento_inexistente',
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9, UnidadeMedida::BAR)
        );

        $diagnosticos = (new SemanticValidator())->validate([$registro]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-003']);
        self::assertStringContainsString("'equipamento_inexistente'", $diagnosticos[0]->mensagem);
    }

    public static function unidadesCompativeis(): iterable {
        yield 'vazão' => [VariavelControlada::VAZAO, UnidadeMedida::M3_POR_HORA];
        yield 'pressão' => [VariavelControlada::PRESSAO, UnidadeMedida::BAR];
        yield 'pressão de descarga' => [VariavelControlada::PRESSAO_DESCARGA, UnidadeMedida::BAR];
        yield 'pressão de sucção' => [VariavelControlada::PRESSAO_SUCCAO, UnidadeMedida::BAR];
        yield 'vibração' => [VariavelControlada::VIBRACAO, UnidadeMedida::MM_POR_S];
        yield 'temperatura' => [VariavelControlada::TEMPERATURA, UnidadeMedida::CELSIUS];
        yield 'rotação' => [VariavelControlada::ROTACAO, UnidadeMedida::RPM];
    }

    #[DataProvider('unidadesCompativeis')]
    public function testeValidate_UnidadeCompativelNasFormasNumericas_NaoRetornaDiagnostic(
        VariavelControlada $variavel,
        UnidadeMedida $unidade
    ): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [$variavel],
            caracteristicas_processo: [new CaracteristicaProcesso($variavel, 1, $unidade)]
        );
        $condicao = new CondicaoCorretiva(new CondicaoNumerica($variavel, Comparador::MAIOR, 1, $unidade));
        $manutencao = $this->createCorrectiveMaintenance('manutencao_teste', $equipamento->nome, $condicao);
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new ValorNumericoRegistro($variavel, 1, $unidade)
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $manutencao, $registro]);

        self::assertSame([], $diagnosticos);
    }

    #[DataProvider('unidadesCompativeis')]
    public function testeValidate_UnidadeIncompativelNasFormasNumericas_RetornaDiagnosticParaCadaOcorrencia(
        VariavelControlada $variavel,
        UnidadeMedida $unidade_esperada
    ): void {
        $unidade_incompativel = $unidade_esperada === UnidadeMedida::LITRO
            ? UnidadeMedida::BAR
            : UnidadeMedida::LITRO;
        $equipamento = $this->createEquipment(
            variaveis_controladas: [$variavel],
            caracteristicas_processo: [new CaracteristicaProcesso($variavel, 1, $unidade_incompativel)]
        );
        $condicao = new CondicaoCorretiva(new CondicaoNumerica(
            $variavel,
            Comparador::MAIOR,
            1,
            $unidade_incompativel
        ));
        $manutencao = $this->createCorrectiveMaintenance('manutencao_teste', $equipamento->nome, $condicao);
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new ValorNumericoRegistro($variavel, 1, $unidade_incompativel)
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $manutencao, $registro]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-004', 'CMMS-SEM-004', 'CMMS-SEM-004']);
    }

    public function testeValidate_RegistroComVariavelControlada_AceitaValorRegistrado(): void {
        $equipamento = $this->createEquipment();
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9, UnidadeMedida::BAR)
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $registro]);

        self::assertSame([], $diagnosticos);
    }

    public function testeValidate_RegistroComVariavelNaoControlada_RetornaDiagnostic(): void {
        $equipamento = $this->createEquipment(variaveis_controladas: [VariavelControlada::TEMPERATURA]);
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9, UnidadeMedida::BAR)
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $registro]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-012', 'CMMS-SEM-005']);
    }

    public function testeValidate_RegistroComLeiturasDeVariaveisDistintas_AceitaValoresNaoNumericos(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [
                VariavelControlada::PRESSAO,
                VariavelControlada::HORAS_OPERACAO,
                VariavelControlada::OBSERVACAO_VISUAL,
                VariavelControlada::VAZAMENTO,
            ],
            caracteristicas_processo: [new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR)]
        );
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9, UnidadeMedida::BAR),
            new HorasOperacaoRegistro(new Tempo(1, UnidadeTempo::HORA)),
            new ObservacaoVisualRegistro(EstadoObservacao::NORMAL),
            new VazamentoRegistro(EstadoVazamento::AUSENTE)
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $registro]);

        self::assertSame([], $diagnosticos);
    }

    public function testeValidate_RegistroComLeituraDuplicadaDeHorasOperacao_RetornaDiagnostic(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [VariavelControlada::HORAS_OPERACAO],
            caracteristicas_processo: []
        );
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new HorasOperacaoRegistro(new Tempo(1, UnidadeTempo::HORA)),
            new HorasOperacaoRegistro(new Tempo(2, UnidadeTempo::HORA))
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $registro]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-006']);
    }

    public function testeValidate_RegistroComLeiturasDuplicadasVisuaisEVazamento_RetornaUmDiagnosticPorVariavel(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [VariavelControlada::OBSERVACAO_VISUAL, VariavelControlada::VAZAMENTO],
            caracteristicas_processo: []
        );
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new ObservacaoVisualRegistro(EstadoObservacao::NORMAL),
            new ObservacaoVisualRegistro(EstadoObservacao::ANORMAL),
            new VazamentoRegistro(EstadoVazamento::AUSENTE),
            new VazamentoRegistro(EstadoVazamento::LEVE)
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $registro]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-006', 'CMMS-SEM-006']);
    }

    public function testeValidate_RegistroComValoresNaoNumericosDeVariaveisNaoControladas_RetornaUmDiagnosticPorValor(): void {
        $equipamento = $this->createEquipment();
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new HorasOperacaoRegistro(new Tempo(1, UnidadeTempo::HORA)),
            new ObservacaoVisualRegistro(EstadoObservacao::ANORMAL),
            new VazamentoRegistro(EstadoVazamento::GRAVE)
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $registro]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-005', 'CMMS-SEM-005', 'CMMS-SEM-005']);
    }

    public static function valoresDeGatilhoPreventivo(): iterable {
        yield 'valor positivo mínimo' => [1, []];
        yield 'zero' => [0, ['CMMS-SEM-007']];
        yield 'valor negativo construído diretamente no Domain' => [-1, ['CMMS-SEM-007']];
    }

    #[DataProvider('valoresDeGatilhoPreventivo')]
    public function testeValidate_GatilhoPreventivo_ValidaValorMaiorQueZero(int $valor, array $codigos_esperados): void {
        $equipamento = $this->createEquipment();
        $manutencao = $this->createPreventiveMaintenance('manutencao_teste', $equipamento->nome, $valor);

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $manutencao]);

        $this->assertDiagnosticCodes($diagnosticos, $codigos_esperados);
    }

    public function testeValidate_EquipamentoComVariaveisControladasDistintas_NaoRetornaDiagnosticDeDuplicidade(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [VariavelControlada::PRESSAO, VariavelControlada::TEMPERATURA],
            caracteristicas_processo: [
                new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR),
                new CaracteristicaProcesso(VariavelControlada::TEMPERATURA, 25, UnidadeMedida::CELSIUS),
            ]
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento]);

        self::assertSame([], $diagnosticos);
    }

    public function testeValidate_EquipamentoComVariavelControladaDuplicada_RetornaDiagnostic(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [VariavelControlada::PRESSAO, VariavelControlada::PRESSAO],
            caracteristicas_processo: [new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR)]
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-008']);
    }

    public function testeValidate_EquipamentoComCaracteristicasDistintas_NaoRetornaDiagnosticDeDuplicidade(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [VariavelControlada::PRESSAO, VariavelControlada::TEMPERATURA],
            caracteristicas_processo: [
                new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR),
                new CaracteristicaProcesso(VariavelControlada::TEMPERATURA, 25, UnidadeMedida::CELSIUS),
            ]
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento]);

        self::assertSame([], $diagnosticos);
    }

    public function testeValidate_EquipamentoComCaracteristicaDuplicada_RetornaDiagnostic(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [VariavelControlada::PRESSAO],
            caracteristicas_processo: [
                new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR),
                new CaracteristicaProcesso(VariavelControlada::PRESSAO, 11, UnidadeMedida::BAR),
            ]
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-009']);
    }

    public function testeValidate_CondicoesCorretivasDeVariaveisControladas_AceitaTodasAsFormas(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [
                VariavelControlada::PRESSAO,
                VariavelControlada::OBSERVACAO_VISUAL,
                VariavelControlada::VAZAMENTO,
            ],
            caracteristicas_processo: [new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR)]
        );
        $condicao = new CondicaoCorretiva(new CondicaoNumerica(
            VariavelControlada::PRESSAO,
            Comparador::MAIOR,
            10,
            UnidadeMedida::BAR
        ));
        $condicao->add(\Domain\Enums\OperadorLogico::E, new CondicaoObservacao(EstadoObservacao::ANORMAL));
        $condicao->add(\Domain\Enums\OperadorLogico::OU, new CondicaoVazamento(EstadoVazamento::GRAVE));
        $manutencao = $this->createCorrectiveMaintenance('manutencao_teste', $equipamento->nome, $condicao);

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $manutencao]);

        self::assertSame([], $diagnosticos);
    }

    public function testeValidate_CondicaoCorretivaComVariavelNaoControlada_RetornaDiagnostic(): void {
        $equipamento = $this->createEquipment(variaveis_controladas: [VariavelControlada::PRESSAO]);
        $condicao = new CondicaoCorretiva(new CondicaoObservacao(EstadoObservacao::ANORMAL));
        $manutencao = $this->createCorrectiveMaintenance('manutencao_teste', $equipamento->nome, $condicao);

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $manutencao]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-010']);
    }

    public function testeValidate_CondicoesCorretivasNumericaEVazamentoNaoControladas_RetornaUmDiagnosticPorCondicao(): void {
        $equipamento = $this->createEquipment();
        $condicao = new CondicaoCorretiva(new CondicaoNumerica(
            VariavelControlada::TEMPERATURA,
            Comparador::MAIOR,
            30,
            UnidadeMedida::CELSIUS
        ));
        $condicao->add(\Domain\Enums\OperadorLogico::E, new CondicaoVazamento(EstadoVazamento::GRAVE));
        $manutencao = $this->createCorrectiveMaintenance('manutencao_teste', $equipamento->nome, $condicao);

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $manutencao]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-010', 'CMMS-SEM-010']);
    }

    public function testeValidate_CaracteristicaEmVariavelControlada_NaoRetornaDiagnostic(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [VariavelControlada::PRESSAO],
            caracteristicas_processo: [new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR)]
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento]);

        self::assertSame([], $diagnosticos);
    }

    public function testeValidate_CaracteristicaForaDasVariaveisControladas_RetornaDiagnostic(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [VariavelControlada::TEMPERATURA],
            caracteristicas_processo: [new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR)]
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento]);

        $this->assertDiagnosticCodes($diagnosticos, ['CMMS-SEM-012']);
    }

    public function testeValidate_ViolacoesIndependentes_RetornaMultiplosDiagnosticos(): void {
        $equipamento = $this->createEquipment(
            variaveis_controladas: [VariavelControlada::TEMPERATURA, VariavelControlada::TEMPERATURA],
            caracteristicas_processo: [new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::LITRO)]
        );
        $registro = $this->createRecord(
            'registro_teste',
            $equipamento->nome,
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 9, UnidadeMedida::LITRO),
            new ValorNumericoRegistro(VariavelControlada::PRESSAO, 10, UnidadeMedida::LITRO)
        );

        $diagnosticos = (new SemanticValidator())->validate([$equipamento, $registro]);

        $this->assertDiagnosticCodes($diagnosticos, [
            'CMMS-SEM-008',
            'CMMS-SEM-004',
            'CMMS-SEM-012',
            'CMMS-SEM-004',
            'CMMS-SEM-005',
            'CMMS-SEM-004',
            'CMMS-SEM-006',
            'CMMS-SEM-005',
        ]);
    }

    /**
     * @param list<VariavelControlada> $variaveis_controladas
     * @param list<CaracteristicaProcesso> $caracteristicas_processo
     */
    private function createEquipment(
        string $nome = 'equipamento_teste',
        array $variaveis_controladas = [VariavelControlada::PRESSAO],
        array $caracteristicas_processo = [new CaracteristicaProcesso(VariavelControlada::PRESSAO, 10, UnidadeMedida::BAR)]
    ): Equipamento {
        return new Equipamento(
            nome: $nome,
            tipo: TipoEquipamento::BOMBA_CENTRIFUGA,
            servico: TipoServico::BOMBEAMENTO_AGUA,
            produto: TipoProduto::AGUA,
            caracteristicas_processo: new CaracteristicaProcessoCollection(...$caracteristicas_processo),
            variaveis_controladas: new VariavelControladaCollection(...$variaveis_controladas),
        );
    }

    private function createPreventiveMaintenance(string $nome, string $equipamento, int|float $gatilho): Manutencao {
        return new Manutencao(
            nome: $nome,
            tipo: TipoManutencao::PREVENTIVA,
            equipamento_identificador: $equipamento,
            gatilho: new Tempo($gatilho, UnidadeTempo::DIA),
            procedimento_identificador: 'procedimento_teste',
            prioridade: Prioridade::MEDIA,
            duracao: new Tempo(1, UnidadeTempo::HORA),
            homem_hora: 1,
        );
    }

    private function createCorrectiveMaintenance(
        string $nome,
        string $equipamento,
        CondicaoCorretiva $gatilho
    ): Manutencao {
        return new Manutencao(
            nome: $nome,
            tipo: TipoManutencao::CORRETIVA,
            equipamento_identificador: $equipamento,
            gatilho: $gatilho,
            procedimento_identificador: 'procedimento_teste',
            prioridade: Prioridade::ALTA,
            duracao: new Tempo(1, UnidadeTempo::HORA),
            homem_hora: 1,
            prazo: new Tempo(1, UnidadeTempo::DIA),
        );
    }

    private function createRecord(
        string $nome,
        string $equipamento,
        ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro ...$valores
    ): Registro {
        return new Registro(
            nome: $nome,
            equipamento_identificador: $equipamento,
            data: new DateTimeImmutable('2026-09-10 08:30'),
            valores: new ValorRegistradoCollection(...$valores),
        );
    }

    /**
     * @param list<Diagnostic> $diagnosticos
     * @param list<string> $codigos_esperados
     */
    private function assertDiagnosticCodes(array $diagnosticos, array $codigos_esperados): void {
        self::assertSame($codigos_esperados, array_map(
            static fn (Diagnostic $diagnostico): ?string => $diagnostico->codigo,
            $diagnosticos
        ));

        foreach ($diagnosticos as $diagnostico) {
            self::assertSame(DiagnosticOrigin::SEMANTIC, $diagnostico->origem);
            self::assertSame(DiagnosticSeverity::ERROR, $diagnostico->severidade);
            self::assertNotSame('', $diagnostico->mensagem);
            self::assertNull($diagnostico->range);
        }
    }
}
