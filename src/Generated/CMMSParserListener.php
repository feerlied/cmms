<?php

/*
 * Generated from CMMSParser.g4 by ANTLR 4.13.2
 */

use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;

/**
 * This interface defines a complete listener for a parse tree produced by
 * {@see CMMSParser}.
 */
interface CMMSParserListener extends ParseTreeListener {
	/**
	 * Enter a parse tree produced by {@see CMMSParser::programa()}.
	 * @param $context The parse tree.
	 */
	public function enterPrograma(Context\ProgramaContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::programa()}.
	 * @param $context The parse tree.
	 */
	public function exitPrograma(Context\ProgramaContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::declaracao()}.
	 * @param $context The parse tree.
	 */
	public function enterDeclaracao(Context\DeclaracaoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::declaracao()}.
	 * @param $context The parse tree.
	 */
	public function exitDeclaracao(Context\DeclaracaoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::declaracaoEquipamento()}.
	 * @param $context The parse tree.
	 */
	public function enterDeclaracaoEquipamento(Context\DeclaracaoEquipamentoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::declaracaoEquipamento()}.
	 * @param $context The parse tree.
	 */
	public function exitDeclaracaoEquipamento(Context\DeclaracaoEquipamentoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::tipoEquipamentoDeclarado()}.
	 * @param $context The parse tree.
	 */
	public function enterTipoEquipamentoDeclarado(Context\TipoEquipamentoDeclaradoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::tipoEquipamentoDeclarado()}.
	 * @param $context The parse tree.
	 */
	public function exitTipoEquipamentoDeclarado(Context\TipoEquipamentoDeclaradoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::tipoEquipamento()}.
	 * @param $context The parse tree.
	 */
	public function enterTipoEquipamento(Context\TipoEquipamentoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::tipoEquipamento()}.
	 * @param $context The parse tree.
	 */
	public function exitTipoEquipamento(Context\TipoEquipamentoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::servicoEquipamento()}.
	 * @param $context The parse tree.
	 */
	public function enterServicoEquipamento(Context\ServicoEquipamentoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::servicoEquipamento()}.
	 * @param $context The parse tree.
	 */
	public function exitServicoEquipamento(Context\ServicoEquipamentoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::tipoServico()}.
	 * @param $context The parse tree.
	 */
	public function enterTipoServico(Context\TipoServicoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::tipoServico()}.
	 * @param $context The parse tree.
	 */
	public function exitTipoServico(Context\TipoServicoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::produtoEquipamento()}.
	 * @param $context The parse tree.
	 */
	public function enterProdutoEquipamento(Context\ProdutoEquipamentoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::produtoEquipamento()}.
	 * @param $context The parse tree.
	 */
	public function exitProdutoEquipamento(Context\ProdutoEquipamentoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::tipoProduto()}.
	 * @param $context The parse tree.
	 */
	public function enterTipoProduto(Context\TipoProdutoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::tipoProduto()}.
	 * @param $context The parse tree.
	 */
	public function exitTipoProduto(Context\TipoProdutoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::caracteristicasProcesso()}.
	 * @param $context The parse tree.
	 */
	public function enterCaracteristicasProcesso(Context\CaracteristicasProcessoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::caracteristicasProcesso()}.
	 * @param $context The parse tree.
	 */
	public function exitCaracteristicasProcesso(Context\CaracteristicasProcessoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::caracteristicaProcesso()}.
	 * @param $context The parse tree.
	 */
	public function enterCaracteristicaProcesso(Context\CaracteristicaProcessoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::caracteristicaProcesso()}.
	 * @param $context The parse tree.
	 */
	public function exitCaracteristicaProcesso(Context\CaracteristicaProcessoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::variaveisControladas()}.
	 * @param $context The parse tree.
	 */
	public function enterVariaveisControladas(Context\VariaveisControladasContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::variaveisControladas()}.
	 * @param $context The parse tree.
	 */
	public function exitVariaveisControladas(Context\VariaveisControladasContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::declaracaoVariavelControlada()}.
	 * @param $context The parse tree.
	 */
	public function enterDeclaracaoVariavelControlada(Context\DeclaracaoVariavelControladaContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::declaracaoVariavelControlada()}.
	 * @param $context The parse tree.
	 */
	public function exitDeclaracaoVariavelControlada(Context\DeclaracaoVariavelControladaContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::variavelNumerica()}.
	 * @param $context The parse tree.
	 */
	public function enterVariavelNumerica(Context\VariavelNumericaContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::variavelNumerica()}.
	 * @param $context The parse tree.
	 */
	public function exitVariavelNumerica(Context\VariavelNumericaContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::declaracaoManutencao()}.
	 * @param $context The parse tree.
	 */
	public function enterDeclaracaoManutencao(Context\DeclaracaoManutencaoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::declaracaoManutencao()}.
	 * @param $context The parse tree.
	 */
	public function exitDeclaracaoManutencao(Context\DeclaracaoManutencaoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::manutencaoPreventiva()}.
	 * @param $context The parse tree.
	 */
	public function enterManutencaoPreventiva(Context\ManutencaoPreventivaContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::manutencaoPreventiva()}.
	 * @param $context The parse tree.
	 */
	public function exitManutencaoPreventiva(Context\ManutencaoPreventivaContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::equipamentoIdentificador()}.
	 * @param $context The parse tree.
	 */
	public function enterEquipamentoIdentificador(Context\EquipamentoIdentificadorContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::equipamentoIdentificador()}.
	 * @param $context The parse tree.
	 */
	public function exitEquipamentoIdentificador(Context\EquipamentoIdentificadorContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::gatilhoPreventivo()}.
	 * @param $context The parse tree.
	 */
	public function enterGatilhoPreventivo(Context\GatilhoPreventivoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::gatilhoPreventivo()}.
	 * @param $context The parse tree.
	 */
	public function exitGatilhoPreventivo(Context\GatilhoPreventivoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::gatilhoCalendario()}.
	 * @param $context The parse tree.
	 */
	public function enterGatilhoCalendario(Context\GatilhoCalendarioContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::gatilhoCalendario()}.
	 * @param $context The parse tree.
	 */
	public function exitGatilhoCalendario(Context\GatilhoCalendarioContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::manutencaoCorretiva()}.
	 * @param $context The parse tree.
	 */
	public function enterManutencaoCorretiva(Context\ManutencaoCorretivaContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::manutencaoCorretiva()}.
	 * @param $context The parse tree.
	 */
	public function exitManutencaoCorretiva(Context\ManutencaoCorretivaContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::gatilhoCorretivo()}.
	 * @param $context The parse tree.
	 */
	public function enterGatilhoCorretivo(Context\GatilhoCorretivoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::gatilhoCorretivo()}.
	 * @param $context The parse tree.
	 */
	public function exitGatilhoCorretivo(Context\GatilhoCorretivoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::condicao()}.
	 * @param $context The parse tree.
	 */
	public function enterCondicao(Context\CondicaoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::condicao()}.
	 * @param $context The parse tree.
	 */
	public function exitCondicao(Context\CondicaoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::condicaoSimples()}.
	 * @param $context The parse tree.
	 */
	public function enterCondicaoSimples(Context\CondicaoSimplesContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::condicaoSimples()}.
	 * @param $context The parse tree.
	 */
	public function exitCondicaoSimples(Context\CondicaoSimplesContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::comparador()}.
	 * @param $context The parse tree.
	 */
	public function enterComparador(Context\ComparadorContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::comparador()}.
	 * @param $context The parse tree.
	 */
	public function exitComparador(Context\ComparadorContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::statusPrioridade()}.
	 * @param $context The parse tree.
	 */
	public function enterStatusPrioridade(Context\StatusPrioridadeContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::statusPrioridade()}.
	 * @param $context The parse tree.
	 */
	public function exitStatusPrioridade(Context\StatusPrioridadeContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::declaracaoRegistro()}.
	 * @param $context The parse tree.
	 */
	public function enterDeclaracaoRegistro(Context\DeclaracaoRegistroContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::declaracaoRegistro()}.
	 * @param $context The parse tree.
	 */
	public function exitDeclaracaoRegistro(Context\DeclaracaoRegistroContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::registro()}.
	 * @param $context The parse tree.
	 */
	public function enterRegistro(Context\RegistroContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::registro()}.
	 * @param $context The parse tree.
	 */
	public function exitRegistro(Context\RegistroContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::dataRegistro()}.
	 * @param $context The parse tree.
	 */
	public function enterDataRegistro(Context\DataRegistroContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::dataRegistro()}.
	 * @param $context The parse tree.
	 */
	public function exitDataRegistro(Context\DataRegistroContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::unidadeDataHora()}.
	 * @param $context The parse tree.
	 */
	public function enterUnidadeDataHora(Context\UnidadeDataHoraContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::unidadeDataHora()}.
	 * @param $context The parse tree.
	 */
	public function exitUnidadeDataHora(Context\UnidadeDataHoraContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::registroExe()}.
	 * @param $context The parse tree.
	 */
	public function enterRegistroExe(Context\RegistroExeContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::registroExe()}.
	 * @param $context The parse tree.
	 */
	public function exitRegistroExe(Context\RegistroExeContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::statusRegistro()}.
	 * @param $context The parse tree.
	 */
	public function enterStatusRegistro(Context\StatusRegistroContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::statusRegistro()}.
	 * @param $context The parse tree.
	 */
	public function exitStatusRegistro(Context\StatusRegistroContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::relatorioRegistro()}.
	 * @param $context The parse tree.
	 */
	public function enterRelatorioRegistro(Context\RelatorioRegistroContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::relatorioRegistro()}.
	 * @param $context The parse tree.
	 */
	public function exitRelatorioRegistro(Context\RelatorioRegistroContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::observacaoRegistro()}.
	 * @param $context The parse tree.
	 */
	public function enterObservacaoRegistro(Context\ObservacaoRegistroContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::observacaoRegistro()}.
	 * @param $context The parse tree.
	 */
	public function exitObservacaoRegistro(Context\ObservacaoRegistroContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::blocoValores()}.
	 * @param $context The parse tree.
	 */
	public function enterBlocoValores(Context\BlocoValoresContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::blocoValores()}.
	 * @param $context The parse tree.
	 */
	public function exitBlocoValores(Context\BlocoValoresContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::valorRegistrado()}.
	 * @param $context The parse tree.
	 */
	public function enterValorRegistrado(Context\ValorRegistradoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::valorRegistrado()}.
	 * @param $context The parse tree.
	 */
	public function exitValorRegistrado(Context\ValorRegistradoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::estadoObservacao()}.
	 * @param $context The parse tree.
	 */
	public function enterEstadoObservacao(Context\EstadoObservacaoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::estadoObservacao()}.
	 * @param $context The parse tree.
	 */
	public function exitEstadoObservacao(Context\EstadoObservacaoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::estadoVazamento()}.
	 * @param $context The parse tree.
	 */
	public function enterEstadoVazamento(Context\EstadoVazamentoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::estadoVazamento()}.
	 * @param $context The parse tree.
	 */
	public function exitEstadoVazamento(Context\EstadoVazamentoContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::unidade()}.
	 * @param $context The parse tree.
	 */
	public function enterUnidade(Context\UnidadeContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::unidade()}.
	 * @param $context The parse tree.
	 */
	public function exitUnidade(Context\UnidadeContext $context): void;
	/**
	 * Enter a parse tree produced by {@see CMMSParser::unidadeTempo()}.
	 * @param $context The parse tree.
	 */
	public function enterUnidadeTempo(Context\UnidadeTempoContext $context): void;
	/**
	 * Exit a parse tree produced by {@see CMMSParser::unidadeTempo()}.
	 * @param $context The parse tree.
	 */
	public function exitUnidadeTempo(Context\UnidadeTempoContext $context): void;
}