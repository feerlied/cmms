<?php
require_once __DIR__ . '/../Generated/CMMSParserVisitor.php';
require_once __DIR__ . '/../Generated/CMMSParserBaseVisitor.php';
require_once __DIR__ . '/../Domain/Equipamento.php';

class CMMSVisitor extends CMMSParserBaseVisitor
{
    public function visitPrograma($context)
    {
        $resultado = [];

        foreach ($context->declaracao() as $declaracao) {
            $valor = $this->visit($declaracao);

            if ($valor !== null) {
                $resultado[] = $valor;
            }
        }

        return $resultado;
    }

    public function visitDeclaracao($context)
    {
        return $this->visitChildren($context);
    }

    public function visitDeclaracaoEquipamento($context)
    {
        $nome = $context->IDENTIFICADOR()->getText();

        $tipo = $context
            ->tipoEquipamentoDeclarado()
            ->tipoEquipamento()
            ->getText();

        $servico = $context
            ->servicoEquipamento()
            ->tipoServico()
            ->getText();

        $produto = $context
            ->produtoEquipamento()
            ->tipoProduto()
            ->getText();

        return new Equipamento(
            nome: $nome,
            tipo: $tipo,
            servico: $servico,
            produto: $produto
        );
    }
}
