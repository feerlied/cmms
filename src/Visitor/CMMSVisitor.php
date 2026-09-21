<?php

namespace Visitor;

use CMMSParserBaseVisitor;
use Domain\CaracteristicaProcesso;
use Domain\CaracteristicaProcessoCollection;
use Domain\CondicaoCorretiva;
use Domain\CondicaoNumerica;
use Domain\CondicaoObservacao;
use Domain\CondicaoVazamento;
use Domain\Enums\Comparador;
use Domain\Enums\EstadoObservacao;
use Domain\Enums\EstadoVazamento;
use Domain\Enums\OperadorLogico;
use Domain\Enums\Prioridade;
use Domain\Enums\StatusRegistro;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoManutencao;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\ExecucaoRegistro;
use Domain\HorasOperacaoRegistro;
use Domain\Manutencao;
use Domain\ObservacaoVisualRegistro;
use Domain\Registro;
use Domain\Tempo;
use Domain\ValorNumericoRegistro;
use Domain\ValorRegistradoCollection;
use Domain\VariavelControladaCollection;
use Domain\VazamentoRegistro;


class CMMSVisitor extends CMMSParserBaseVisitor {
    public function visitPrograma($context) {
        $resultado = [];

        foreach ($context->declaracao() as $declaracao) {
            $valor = $this->visit($declaracao);

            if ($valor !== null) {
                $resultado[] = $valor;
            }
        }

        return $resultado;
    }

    public function visitDeclaracao($context) {
        return $this->visitChildren($context);
    }

    public function visitDeclaracaoEquipamento($context) {
        $nome = $context->IDENTIFICADOR()->getText();

        $tipo = TipoEquipamento::fromDsl($context
            ->tipoEquipamentoDeclarado()
            ->tipoEquipamento()
            ->getText());

        $servico = TipoServico::fromDsl($context
            ->servicoEquipamento()
            ->tipoServico()
            ->getText());

        $produto = TipoProduto::fromDsl($context
            ->produtoEquipamento()
            ->tipoProduto()
            ->getText());

        $caracteristicas_processo = $this->createProcessCharacteristics(
            $context->caracteristicasProcesso()
        );

        $variaveis_controladas = $this->createControlledVariables(
            $context->variaveisControladas()
        );

        return new Equipamento(
            nome: $nome,
            tipo: $tipo,
            servico: $servico,
            produto: $produto,
            caracteristicas_processo: $caracteristicas_processo,
            variaveis_controladas: $variaveis_controladas
        );
    }

    public function visitManutencaoPreventiva($context) {
        $gatilho_calendario = $context
            ->gatilhoPreventivo()
            ->gatilhoCalendario();

        $gatilho = $this->createTime(
            $gatilho_calendario->NUMERO()->getText(),
            $gatilho_calendario->unidadeTempo()->getText()
        );

        return $this->createMaintenance(
            context: $context,
            tipo: TipoManutencao::PREVENTIVA,
            gatilho: $gatilho
        );
    }

    public function visitManutencaoCorretiva($context) {
        $gatilho = $this->createCorrectiveCondition(
            $context->gatilhoCorretivo()->condicao()
        );

        $prazo = $this->createTime(
            $context->NUMERO(1)->getText(),
            $context->unidadeTempo(1)->getText()
        );

        return $this->createMaintenance(
            context: $context,
            tipo: TipoManutencao::CORRETIVA,
            gatilho: $gatilho,
            prazo: $prazo
        );
    }

    public function visitRegistro($context) {
        $contexto_execucao = $context->registroExe();
        $contexto_relatorio = $context->relatorioRegistro();
        $contexto_observacao = $context->observacaoRegistro();

        return new Registro(
            nome: $context->IDENTIFICADOR()->getText(),
            equipamento_identificador: $context->equipamentoIdentificador()->IDENTIFICADOR()->getText(),
            data: $this->createRecordDate($context->dataRegistro()->unidadeDataHora()),
            valores: $this->createRecordedValues($context->blocoValores()),
            execucao: $contexto_execucao === null ? null : $this->createRecordExecution($contexto_execucao),
            relatorio: $contexto_relatorio === null ? null : $this->parseText($contexto_relatorio->TEXTO()->getText()),
            observacao: $contexto_observacao === null ? null : $this->parseText($contexto_observacao->TEXTO()->getText())
        );
    }

    private function createProcessCharacteristics($context): CaracteristicaProcessoCollection {
        $caracteristicas_processo = new CaracteristicaProcessoCollection();

        foreach ($context->caracteristicaProcesso() as $caracteristica) {
            $caracteristicas_processo->add(new CaracteristicaProcesso(
                variavel: VariavelControlada::fromDsl($caracteristica->variavelNumerica()->getText()),
                valor: $this->parseNumber($caracteristica->NUMERO()->getText()),
                unidade: UnidadeMedida::fromDsl($caracteristica->unidade()->getText())
            ));
        }

        return $caracteristicas_processo;
    }

    private function createControlledVariables($context): VariavelControladaCollection {
        $variaveis_controladas = new VariavelControladaCollection();

        foreach ($context->declaracaoVariavelControlada() as $variavel) {
            $variaveis_controladas->add(VariavelControlada::fromDsl($variavel->getText()));
        }

        return $variaveis_controladas;
    }

    private function createMaintenance(
        $context,
        TipoManutencao $tipo,
        Tempo|CondicaoCorretiva $gatilho,
        ?Tempo $prazo = null
    ): Manutencao {
        $indice_homem_hora = $tipo === TipoManutencao::PREVENTIVA ? 1 : 2;
        $unidade_duracao = $tipo === TipoManutencao::PREVENTIVA
            ? $context->unidadeTempo()
            : $context->unidadeTempo(0);

        return new Manutencao(
            nome: $context->IDENTIFICADOR(0)->getText(),
            tipo: $tipo,
            equipamento_identificador: $context->equipamentoIdentificador()->IDENTIFICADOR()->getText(),
            gatilho: $gatilho,
            procedimento_identificador: $context->IDENTIFICADOR(1)->getText(),
            prioridade: Prioridade::fromDsl($context->statusPrioridade()->getText()),
            duracao: $this->createTime($context->NUMERO(0)->getText(), $unidade_duracao->getText()),
            homem_hora: $this->parseNumber($context->NUMERO($indice_homem_hora)->getText()),
            prazo: $prazo
        );
    }

    private function createTime(string $numero, string $unidade): Tempo {
        return new Tempo(
            valor: $this->parseNumber($numero),
            unidade: UnidadeTempo::fromDsl($unidade)
        );
    }

    private function createRecordDate($context): \DateTimeImmutable {
        $texto_data = $context->getText();

        if (preg_match('~\A([0-9]{2})/([0-9]{2})/([0-9]{4})-([0-9]{2}):([0-9]{2})\z~', $texto_data, $componentes) !== 1) {
            throw new \InvalidArgumentException("Data do registro inválida: {$texto_data}");
        }

        $data = \DateTimeImmutable::createFromFormat('!d/m/Y-H:i', $texto_data);
        $erros = \DateTimeImmutable::getLastErrors();

        if (!checkdate((int) $componentes[2], (int) $componentes[1], (int) $componentes[3])
            || $data === false
            || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0))) {
            throw new \InvalidArgumentException("Data do registro inválida: {$texto_data}");
        }

        return $data;
    }

    private function createRecordExecution($context): ExecucaoRegistro {
        return new ExecucaoRegistro(
            origem: $context->IDENTIFICADOR()->getText(),
            tempo_execucao: $this->createTime(
                $context->NUMERO()->getText(),
                $context->unidadeTempo()->getText()
            ),
            status: StatusRegistro::fromDsl($context->statusRegistro()->getText())
        );
    }

    private function createRecordedValues($context): ValorRegistradoCollection {
        $valores = new ValorRegistradoCollection();

        foreach ($context->valorRegistrado() as $valor) {
            $valores->add($this->createRecordedValue($valor));
        }

        return $valores;
    }

    private function createRecordedValue($context): ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro {
        if ($context->variavelNumerica() !== null) {
            return new ValorNumericoRegistro(
                variavel: VariavelControlada::fromDsl($context->variavelNumerica()->getText()),
                valor: $this->parseNumber($context->NUMERO()->getText()),
                unidade: UnidadeMedida::fromDsl($context->unidade()->getText())
            );
        }

        if ($context->HORAS_OPERACAO() !== null) {
            return new HorasOperacaoRegistro(
                horas_operacao: $this->createTime(
                    $context->NUMERO()->getText(),
                    $context->unidadeTempo()->getText()
                )
            );
        }

        if ($context->OBSERVACAO_VISUAL() !== null) {
            return new ObservacaoVisualRegistro(
                estado: EstadoObservacao::fromDsl($context->estadoObservacao()->getText())
            );
        }

        return new VazamentoRegistro(
            estado: EstadoVazamento::fromDsl($context->estadoVazamento()->getText())
        );
    }

    private function parseText(string $texto): string {
        return substr($texto, 1, -1);
    }

    private function createCorrectiveCondition($context): CondicaoCorretiva {
        $elementos = [];
        $this->collectConditionElements($context, $elementos);

        $gatilho = new CondicaoCorretiva($this->createSimpleCondition($elementos[0]));

        for ($indice = 1; $indice < count($elementos); $indice += 2) {
            $gatilho->add(
                OperadorLogico::fromDsl($elementos[$indice]->getText()),
                $this->createSimpleCondition($elementos[$indice + 1])
            );
        }

        return $gatilho;
    }

    private function collectConditionElements($context, array &$elementos): void {
        if ($context instanceof \Context\CondicaoSimplesContext) {
            $elementos[] = $context;
            return;
        }

        for ($indice = 0; $indice < $context->getChildCount(); $indice++) {
            $filho = $context->getChild($indice);

            if (in_array($filho->getText(), ['e', 'ou'], true)) {
                $elementos[] = $filho;
                continue;
            }

            if ($filho->getChildCount() > 0) {
                $this->collectConditionElements($filho, $elementos);
            }
        }
    }

    private function createSimpleCondition($context): CondicaoNumerica|CondicaoObservacao|CondicaoVazamento {
        if ($context->variavelNumerica() !== null) {
            return new CondicaoNumerica(
                variavel: VariavelControlada::fromDsl($context->variavelNumerica()->getText()),
                comparador: Comparador::fromDsl($context->comparador()->getText()),
                valor: $this->parseNumber($context->NUMERO()->getText()),
                unidade: UnidadeMedida::fromDsl($context->unidade()->getText())
            );
        }

        if ($context->estadoObservacao() !== null) {
            return new CondicaoObservacao(
                estado: EstadoObservacao::fromDsl($context->estadoObservacao()->getText())
            );
        }

        return new CondicaoVazamento(
            estado: EstadoVazamento::fromDsl($context->estadoVazamento()->getText())
        );
    }

    private function parseNumber(string $numero): int|float {
        if (str_contains($numero, '.')) {
            $valor = (float) $numero;

            if (!is_finite($valor) || ($valor === 0.0 && preg_match('/[1-9]/', $numero) === 1)) {
                throw new \InvalidArgumentException("Número não representável: {$numero}");
            }

            return $valor;
        }

        $numero_sem_zeros = ltrim($numero, '0');

        if ($numero_sem_zeros === '') {
            return 0;
        }

        $limite_inteiro = (string) PHP_INT_MAX;

        if (strlen($numero_sem_zeros) > strlen($limite_inteiro)
            || (strlen($numero_sem_zeros) === strlen($limite_inteiro) && strcmp($numero_sem_zeros, $limite_inteiro) > 0)) {
            throw new \InvalidArgumentException("Número não representável: {$numero}");
        }

        return (int) $numero_sem_zeros;
    }
}
