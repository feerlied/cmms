<?php

namespace Visitor;

use CMMSParserBaseVisitor;
use Domain\CaracteristicaProcesso;
use Domain\CaracteristicaProcessoCollection;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
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

    private function parseNumber(string $numero): int|float {
        return str_contains($numero, '.') ? (float) $numero : (int) $numero;
    }
}
