<?php

/*
 * Generated from CMMSParser.g4 by ANTLR 4.13.2
 */

use Antlr\Antlr4\Runtime\Tree\ParseTreeVisitor;

/**
 * This interface defines a complete generic visitor for a parse tree produced by {@see CMMSParser}.
 */
interface CMMSParserVisitor extends ParseTreeVisitor
{
	/**
	 * Visit a parse tree produced by {@see CMMSParser::programa()}.
	 *
	 * @param Context\ProgramaContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitPrograma(Context\ProgramaContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::declaracao()}.
	 *
	 * @param Context\DeclaracaoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitDeclaracao(Context\DeclaracaoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::declaracaoEquipamento()}.
	 *
	 * @param Context\DeclaracaoEquipamentoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitDeclaracaoEquipamento(Context\DeclaracaoEquipamentoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::tipoEquipamentoDeclarado()}.
	 *
	 * @param Context\TipoEquipamentoDeclaradoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitTipoEquipamentoDeclarado(Context\TipoEquipamentoDeclaradoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::tipoEquipamento()}.
	 *
	 * @param Context\TipoEquipamentoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitTipoEquipamento(Context\TipoEquipamentoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::servicoEquipamento()}.
	 *
	 * @param Context\ServicoEquipamentoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitServicoEquipamento(Context\ServicoEquipamentoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::tipoServico()}.
	 *
	 * @param Context\TipoServicoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitTipoServico(Context\TipoServicoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::produtoEquipamento()}.
	 *
	 * @param Context\ProdutoEquipamentoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitProdutoEquipamento(Context\ProdutoEquipamentoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::tipoProduto()}.
	 *
	 * @param Context\TipoProdutoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitTipoProduto(Context\TipoProdutoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::caracteristicasProcesso()}.
	 *
	 * @param Context\CaracteristicasProcessoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitCaracteristicasProcesso(Context\CaracteristicasProcessoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::caracteristicaProcesso()}.
	 *
	 * @param Context\CaracteristicaProcessoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitCaracteristicaProcesso(Context\CaracteristicaProcessoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::variaveisControladas()}.
	 *
	 * @param Context\VariaveisControladasContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitVariaveisControladas(Context\VariaveisControladasContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::declaracaoVariavelControlada()}.
	 *
	 * @param Context\DeclaracaoVariavelControladaContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitDeclaracaoVariavelControlada(Context\DeclaracaoVariavelControladaContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::variavelNumerica()}.
	 *
	 * @param Context\VariavelNumericaContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitVariavelNumerica(Context\VariavelNumericaContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::declaracaoManutencao()}.
	 *
	 * @param Context\DeclaracaoManutencaoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitDeclaracaoManutencao(Context\DeclaracaoManutencaoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::manutencaoPreventiva()}.
	 *
	 * @param Context\ManutencaoPreventivaContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitManutencaoPreventiva(Context\ManutencaoPreventivaContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::equipamentoIdentificador()}.
	 *
	 * @param Context\EquipamentoIdentificadorContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitEquipamentoIdentificador(Context\EquipamentoIdentificadorContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::gatilhoPreventivo()}.
	 *
	 * @param Context\GatilhoPreventivoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitGatilhoPreventivo(Context\GatilhoPreventivoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::gatilhoCalendario()}.
	 *
	 * @param Context\GatilhoCalendarioContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitGatilhoCalendario(Context\GatilhoCalendarioContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::manutencaoCorretiva()}.
	 *
	 * @param Context\ManutencaoCorretivaContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitManutencaoCorretiva(Context\ManutencaoCorretivaContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::gatilhoCorretivo()}.
	 *
	 * @param Context\GatilhoCorretivoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitGatilhoCorretivo(Context\GatilhoCorretivoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::condicao()}.
	 *
	 * @param Context\CondicaoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitCondicao(Context\CondicaoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::condicaoSimples()}.
	 *
	 * @param Context\CondicaoSimplesContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitCondicaoSimples(Context\CondicaoSimplesContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::comparador()}.
	 *
	 * @param Context\ComparadorContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitComparador(Context\ComparadorContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::statusPrioridade()}.
	 *
	 * @param Context\StatusPrioridadeContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitStatusPrioridade(Context\StatusPrioridadeContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::declaracaoRegistro()}.
	 *
	 * @param Context\DeclaracaoRegistroContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitDeclaracaoRegistro(Context\DeclaracaoRegistroContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::registro()}.
	 *
	 * @param Context\RegistroContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitRegistro(Context\RegistroContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::dataRegistro()}.
	 *
	 * @param Context\DataRegistroContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitDataRegistro(Context\DataRegistroContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::unidadeDataHora()}.
	 *
	 * @param Context\UnidadeDataHoraContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitUnidadeDataHora(Context\UnidadeDataHoraContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::registroExe()}.
	 *
	 * @param Context\RegistroExeContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitRegistroExe(Context\RegistroExeContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::statusRegistro()}.
	 *
	 * @param Context\StatusRegistroContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitStatusRegistro(Context\StatusRegistroContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::relatorioRegistro()}.
	 *
	 * @param Context\RelatorioRegistroContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitRelatorioRegistro(Context\RelatorioRegistroContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::observacaoRegistro()}.
	 *
	 * @param Context\ObservacaoRegistroContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitObservacaoRegistro(Context\ObservacaoRegistroContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::blocoValores()}.
	 *
	 * @param Context\BlocoValoresContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitBlocoValores(Context\BlocoValoresContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::valorRegistrado()}.
	 *
	 * @param Context\ValorRegistradoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitValorRegistrado(Context\ValorRegistradoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::estadoObservacao()}.
	 *
	 * @param Context\EstadoObservacaoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitEstadoObservacao(Context\EstadoObservacaoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::estadoVazamento()}.
	 *
	 * @param Context\EstadoVazamentoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitEstadoVazamento(Context\EstadoVazamentoContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::unidade()}.
	 *
	 * @param Context\UnidadeContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitUnidade(Context\UnidadeContext $context);

	/**
	 * Visit a parse tree produced by {@see CMMSParser::unidadeTempo()}.
	 *
	 * @param Context\UnidadeTempoContext $context The parse tree.
	 *
	 * @return mixed The visitor result.
	 */
	public function visitUnidadeTempo(Context\UnidadeTempoContext $context);
}