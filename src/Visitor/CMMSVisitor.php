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
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoManutencao;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\UnidadeTempo;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\Manutencao;
use Domain\Tempo;
use Domain\VariavelControladaCollection;


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
        return str_contains($numero, '.') ? (float) $numero : (int) $numero;
    }
}
