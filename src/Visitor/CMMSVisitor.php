<?php

namespace Visitor;

use CMMSParserBaseVisitor;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Equipamento;


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

        return new Equipamento(
            nome: $nome,
            tipo: $tipo,
            servico: $servico,
            produto: $produto
        );
    }
}
