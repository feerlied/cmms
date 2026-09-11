<?php

/*
 * Generated from CMMSParser.g4 by ANTLR 4.13.2
 */

namespace {
	use Antlr\Antlr4\Runtime\Atn\ATN;
	use Antlr\Antlr4\Runtime\Atn\ATNDeserializer;
	use Antlr\Antlr4\Runtime\Atn\ParserATNSimulator;
	use Antlr\Antlr4\Runtime\Dfa\DFA;
	use Antlr\Antlr4\Runtime\Error\Exceptions\FailedPredicateException;
	use Antlr\Antlr4\Runtime\Error\Exceptions\NoViableAltException;
	use Antlr\Antlr4\Runtime\PredictionContexts\PredictionContextCache;
	use Antlr\Antlr4\Runtime\Error\Exceptions\RecognitionException;
	use Antlr\Antlr4\Runtime\RuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\TokenStream;
	use Antlr\Antlr4\Runtime\Vocabulary;
	use Antlr\Antlr4\Runtime\VocabularyImpl;
	use Antlr\Antlr4\Runtime\RuntimeMetaData;
	use Antlr\Antlr4\Runtime\Parser;

	final class CMMSParser extends Parser
	{
		public const EQUIPAMENTO = 1, MANUTENCAO = 2, REGISTRO = 3, PREVENTIVA = 4, 
               CORRETIVA = 5, LEITURA = 6, EXECUCAO = 7, TIPO = 8, SERVICO = 9, 
               PRODUTO = 10, CARACTERISTICAS_PROCESSO = 11, VARIAVEIS_CONTROLADAS = 12, 
               BOMBA_CENTRIFUGA = 13, BOMBA_ALTERNATIVA = 14, TROCADOR_CALOR = 15, 
               TANQUE_GASOLINA = 16, BOMBEAMENTO_AGUA = 17, BOMBEAMENTO_GASOLINA = 18, 
               BOMBEAMENTO_DIESEL = 19, RESFRIAMENTO_GASOLINA = 20, ARMAZENAMENTO_GASOLINA = 21, 
               AGUA = 22, GASOLINA = 23, DIESEL = 24, OBSERVACAO_VISUAL = 25, 
               VAZAO = 26, PRESSAO = 27, PRESSAO_DESCARGA = 28, PRESSAO_SUCCAO = 29, 
               VIBRACAO = 30, TEMPERATURA = 31, ROTACAO = 32, VAZAMENTO = 33, 
               HORAS_OPERACAO = 34, NORMAL = 35, ANORMAL = 36, AUSENTE = 37, 
               LEVE = 38, MODERADO = 39, GRAVE = 40, A_CADA = 41, DE = 42, 
               QUANDO = 43, PROCEDIMENTO = 44, PRIORIDADE = 45, DURACAO = 46, 
               HOMEM_HORA = 47, PRAZO = 48, BAIXA = 49, MEDIA = 50, ALTA = 51, 
               CRITICA = 52, IGUAL = 53, DIFERENTE = 54, MAIOR = 55, MAIOR_OU_IGUAL = 56, 
               MENOR = 57, MENOR_OU_IGUAL = 58, E = 59, OU = 60, ORIGEM = 61, 
               TEMPO_EXECUCAO = 62, STATUS = 63, RELATORIO = 64, VALORES = 65, 
               DATA = 66, OBSERVACAO = 67, EM_ABERTO = 68, EM_EXECUCAO = 69, 
               CONCLUIDO = 70, CANCELADO = 71, MM_POR_S = 72, CELSIUS = 73, 
               BAR = 74, M3_POR_HORA = 75, HORA = 76, MINUTO = 77, DIA = 78, 
               RPM = 79, LITRO = 80, ABRE_CHAVE = 81, FECHA_CHAVE = 82, 
               ABRE_PARENTESE = 83, FECHA_PARENTESE = 84, IGUAL_ATRIBUICAO = 85, 
               VIRGULA = 86, DOIS_PONTOS = 87, BARRA = 88, TRACO = 89, NUMERO = 90, 
               TEXTO = 91, IDENTIFICADOR = 92, COMENTARIO = 93, ESPACO = 94;

		public const RULE_programa = 0, RULE_declaracao = 1, RULE_declaracaoEquipamento = 2, 
               RULE_tipoEquipamentoDeclarado = 3, RULE_tipoEquipamento = 4, 
               RULE_servicoEquipamento = 5, RULE_tipoServico = 6, RULE_produtoEquipamento = 7, 
               RULE_tipoProduto = 8, RULE_caracteristicasProcesso = 9, RULE_caracteristicaProcesso = 10, 
               RULE_variaveisControladas = 11, RULE_declaracaoVariavelControlada = 12, 
               RULE_variavelNumerica = 13, RULE_declaracaoManutencao = 14, 
               RULE_manutencaoPreventiva = 15, RULE_equipamentoIdentificador = 16, 
               RULE_gatilhoPreventivo = 17, RULE_gatilhoCalendario = 18, 
               RULE_manutencaoCorretiva = 19, RULE_gatilhoCorretivo = 20, 
               RULE_condicao = 21, RULE_condicaoSimples = 22, RULE_comparador = 23, 
               RULE_statusPrioridade = 24, RULE_declaracaoRegistro = 25, 
               RULE_registro = 26, RULE_dataRegistro = 27, RULE_unidadeDataHora = 28, 
               RULE_registroExe = 29, RULE_statusRegistro = 30, RULE_relatorioRegistro = 31, 
               RULE_observacaoRegistro = 32, RULE_blocoValores = 33, RULE_valorRegistrado = 34, 
               RULE_estadoObservacao = 35, RULE_estadoVazamento = 36, RULE_unidade = 37, 
               RULE_unidadeTempo = 38;

		/**
		 * @var array<string>
		 */
		public const RULE_NAMES = [
			'programa', 'declaracao', 'declaracaoEquipamento', 'tipoEquipamentoDeclarado', 
			'tipoEquipamento', 'servicoEquipamento', 'tipoServico', 'produtoEquipamento', 
			'tipoProduto', 'caracteristicasProcesso', 'caracteristicaProcesso', 'variaveisControladas', 
			'declaracaoVariavelControlada', 'variavelNumerica', 'declaracaoManutencao', 
			'manutencaoPreventiva', 'equipamentoIdentificador', 'gatilhoPreventivo', 
			'gatilhoCalendario', 'manutencaoCorretiva', 'gatilhoCorretivo', 'condicao', 
			'condicaoSimples', 'comparador', 'statusPrioridade', 'declaracaoRegistro', 
			'registro', 'dataRegistro', 'unidadeDataHora', 'registroExe', 'statusRegistro', 
			'relatorioRegistro', 'observacaoRegistro', 'blocoValores', 'valorRegistrado', 
			'estadoObservacao', 'estadoVazamento', 'unidade', 'unidadeTempo'
		];

		/**
		 * @var array<string|null>
		 */
		private const LITERAL_NAMES = [
		    null, "'equipamento'", null, "'registro'", "'preventiva'", "'corretiva'", 
		    "'leitura'", null, "'tipo'", null, "'produto'", null, null, null, 
		    null, null, null, null, null, null, null, null, null, "'gasolina'", 
		    "'diesel'", null, null, null, null, null, null, "'temperatura'", null, 
		    "'vazamento'", null, "'normal'", "'anormal'", "'ausente'", "'leve'", 
		    "'moderado'", "'grave'", null, "'de'", "'quando'", "'procedimento'", 
		    "'prioridade'", null, null, "'prazo'", "'baixa'", null, "'alta'", 
		    null, "'igual'", "'diferente'", "'maior'", null, "'menor'", null, 
		    "'e'", "'ou'", "'origem'", null, "'status'", null, "'valores'", "'data'", 
		    null, null, null, null, "'cancelado'", null, null, "'bar'", null, 
		    "'hora'", "'minuto'", "'dia'", "'rpm'", "'litro'", "'{'", "'}'", "'('", 
		    "')'", "'='", "','", "':'", "'/'", "'-'"
		];

		/**
		 * @var array<string>
		 */
		private const SYMBOLIC_NAMES = [
		    null, "EQUIPAMENTO", "MANUTENCAO", "REGISTRO", "PREVENTIVA", "CORRETIVA", 
		    "LEITURA", "EXECUCAO", "TIPO", "SERVICO", "PRODUTO", "CARACTERISTICAS_PROCESSO", 
		    "VARIAVEIS_CONTROLADAS", "BOMBA_CENTRIFUGA", "BOMBA_ALTERNATIVA", 
		    "TROCADOR_CALOR", "TANQUE_GASOLINA", "BOMBEAMENTO_AGUA", "BOMBEAMENTO_GASOLINA", 
		    "BOMBEAMENTO_DIESEL", "RESFRIAMENTO_GASOLINA", "ARMAZENAMENTO_GASOLINA", 
		    "AGUA", "GASOLINA", "DIESEL", "OBSERVACAO_VISUAL", "VAZAO", "PRESSAO", 
		    "PRESSAO_DESCARGA", "PRESSAO_SUCCAO", "VIBRACAO", "TEMPERATURA", "ROTACAO", 
		    "VAZAMENTO", "HORAS_OPERACAO", "NORMAL", "ANORMAL", "AUSENTE", "LEVE", 
		    "MODERADO", "GRAVE", "A_CADA", "DE", "QUANDO", "PROCEDIMENTO", "PRIORIDADE", 
		    "DURACAO", "HOMEM_HORA", "PRAZO", "BAIXA", "MEDIA", "ALTA", "CRITICA", 
		    "IGUAL", "DIFERENTE", "MAIOR", "MAIOR_OU_IGUAL", "MENOR", "MENOR_OU_IGUAL", 
		    "E", "OU", "ORIGEM", "TEMPO_EXECUCAO", "STATUS", "RELATORIO", "VALORES", 
		    "DATA", "OBSERVACAO", "EM_ABERTO", "EM_EXECUCAO", "CONCLUIDO", "CANCELADO", 
		    "MM_POR_S", "CELSIUS", "BAR", "M3_POR_HORA", "HORA", "MINUTO", "DIA", 
		    "RPM", "LITRO", "ABRE_CHAVE", "FECHA_CHAVE", "ABRE_PARENTESE", "FECHA_PARENTESE", 
		    "IGUAL_ATRIBUICAO", "VIRGULA", "DOIS_PONTOS", "BARRA", "TRACO", "NUMERO", 
		    "TEXTO", "IDENTIFICADOR", "COMENTARIO", "ESPACO"
		];

		private const SERIALIZED_ATN =
			[4, 1, 94, 300, 2, 0, 7, 0, 2, 1, 7, 1, 2, 2, 7, 2, 2, 3, 7, 3, 2, 4, 
		    7, 4, 2, 5, 7, 5, 2, 6, 7, 6, 2, 7, 7, 7, 2, 8, 7, 8, 2, 9, 7, 9, 
		    2, 10, 7, 10, 2, 11, 7, 11, 2, 12, 7, 12, 2, 13, 7, 13, 2, 14, 7, 
		    14, 2, 15, 7, 15, 2, 16, 7, 16, 2, 17, 7, 17, 2, 18, 7, 18, 2, 19, 
		    7, 19, 2, 20, 7, 20, 2, 21, 7, 21, 2, 22, 7, 22, 2, 23, 7, 23, 2, 
		    24, 7, 24, 2, 25, 7, 25, 2, 26, 7, 26, 2, 27, 7, 27, 2, 28, 7, 28, 
		    2, 29, 7, 29, 2, 30, 7, 30, 2, 31, 7, 31, 2, 32, 7, 32, 2, 33, 7, 
		    33, 2, 34, 7, 34, 2, 35, 7, 35, 2, 36, 7, 36, 2, 37, 7, 37, 2, 38, 
		    7, 38, 1, 0, 4, 0, 80, 8, 0, 11, 0, 12, 0, 81, 1, 0, 1, 0, 1, 1, 1, 
		    1, 1, 1, 3, 1, 89, 8, 1, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 1, 2, 
		    1, 2, 1, 2, 1, 2, 1, 3, 1, 3, 1, 3, 1, 4, 1, 4, 1, 5, 1, 5, 1, 5, 
		    1, 6, 1, 6, 1, 7, 1, 7, 1, 7, 1, 8, 1, 8, 1, 9, 1, 9, 1, 9, 4, 9, 
		    119, 8, 9, 11, 9, 12, 9, 120, 1, 9, 1, 9, 1, 10, 1, 10, 1, 10, 1, 
		    10, 1, 11, 1, 11, 1, 11, 4, 11, 132, 8, 11, 11, 11, 12, 11, 133, 1, 
		    11, 1, 11, 1, 12, 1, 12, 1, 12, 1, 12, 3, 12, 142, 8, 12, 1, 13, 1, 
		    13, 1, 14, 1, 14, 3, 14, 148, 8, 14, 1, 15, 1, 15, 1, 15, 1, 15, 1, 
		    15, 1, 15, 1, 15, 1, 15, 1, 15, 1, 15, 1, 15, 1, 15, 1, 15, 1, 15, 
		    1, 15, 1, 15, 1, 15, 1, 16, 1, 16, 1, 16, 1, 17, 1, 17, 1, 18, 1, 
		    18, 1, 18, 1, 18, 1, 19, 1, 19, 1, 19, 1, 19, 1, 19, 1, 19, 1, 19, 
		    1, 19, 1, 19, 1, 19, 1, 19, 1, 19, 1, 19, 1, 19, 1, 19, 1, 19, 1, 
		    19, 1, 19, 1, 19, 1, 19, 1, 20, 1, 20, 1, 20, 1, 21, 1, 21, 1, 21, 
		    5, 21, 202, 8, 21, 10, 21, 12, 21, 205, 9, 21, 1, 22, 1, 22, 1, 22, 
		    1, 22, 1, 22, 1, 22, 1, 22, 1, 22, 1, 22, 3, 22, 216, 8, 22, 1, 23, 
		    1, 23, 1, 24, 1, 24, 1, 25, 1, 25, 1, 26, 1, 26, 1, 26, 1, 26, 1, 
		    26, 1, 26, 3, 26, 230, 8, 26, 1, 26, 1, 26, 3, 26, 234, 8, 26, 1, 
		    26, 3, 26, 237, 8, 26, 1, 26, 1, 26, 1, 27, 1, 27, 1, 27, 1, 28, 1, 
		    28, 1, 28, 1, 28, 1, 28, 1, 28, 1, 28, 1, 28, 1, 28, 1, 28, 1, 29, 
		    1, 29, 1, 29, 1, 29, 1, 29, 1, 29, 1, 29, 1, 29, 1, 30, 1, 30, 1, 
		    31, 1, 31, 1, 31, 1, 32, 1, 32, 1, 32, 1, 33, 1, 33, 1, 33, 4, 33, 
		    273, 8, 33, 11, 33, 12, 33, 274, 1, 33, 1, 33, 1, 34, 1, 34, 1, 34, 
		    1, 34, 1, 34, 1, 34, 1, 34, 1, 34, 1, 34, 1, 34, 1, 34, 3, 34, 290, 
		    8, 34, 1, 35, 1, 35, 1, 36, 1, 36, 1, 37, 1, 37, 1, 38, 1, 38, 1, 
		    38, 0, 0, 39, 0, 2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 
		    30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52, 54, 56, 58, 60, 62, 
		    64, 66, 68, 70, 72, 74, 76, 0, 12, 1, 0, 13, 16, 1, 0, 17, 21, 1, 
		    0, 22, 24, 1, 0, 26, 32, 1, 0, 59, 60, 1, 0, 53, 58, 1, 0, 49, 52, 
		    1, 0, 68, 71, 1, 0, 35, 36, 1, 0, 37, 40, 1, 0, 72, 80, 1, 0, 76, 
		    78, 279, 0, 79, 1, 0, 0, 0, 2, 88, 1, 0, 0, 0, 4, 90, 1, 0, 0, 0, 
		    6, 100, 1, 0, 0, 0, 8, 103, 1, 0, 0, 0, 10, 105, 1, 0, 0, 0, 12, 108, 
		    1, 0, 0, 0, 14, 110, 1, 0, 0, 0, 16, 113, 1, 0, 0, 0, 18, 115, 1, 
		    0, 0, 0, 20, 124, 1, 0, 0, 0, 22, 128, 1, 0, 0, 0, 24, 141, 1, 0, 
		    0, 0, 26, 143, 1, 0, 0, 0, 28, 147, 1, 0, 0, 0, 30, 149, 1, 0, 0, 
		    0, 32, 166, 1, 0, 0, 0, 34, 169, 1, 0, 0, 0, 36, 171, 1, 0, 0, 0, 
		    38, 175, 1, 0, 0, 0, 40, 195, 1, 0, 0, 0, 42, 198, 1, 0, 0, 0, 44, 
		    215, 1, 0, 0, 0, 46, 217, 1, 0, 0, 0, 48, 219, 1, 0, 0, 0, 50, 221, 
		    1, 0, 0, 0, 52, 223, 1, 0, 0, 0, 54, 240, 1, 0, 0, 0, 56, 243, 1, 
		    0, 0, 0, 58, 253, 1, 0, 0, 0, 60, 261, 1, 0, 0, 0, 62, 263, 1, 0, 
		    0, 0, 64, 266, 1, 0, 0, 0, 66, 269, 1, 0, 0, 0, 68, 289, 1, 0, 0, 
		    0, 70, 291, 1, 0, 0, 0, 72, 293, 1, 0, 0, 0, 74, 295, 1, 0, 0, 0, 
		    76, 297, 1, 0, 0, 0, 78, 80, 3, 2, 1, 0, 79, 78, 1, 0, 0, 0, 80, 81, 
		    1, 0, 0, 0, 81, 79, 1, 0, 0, 0, 81, 82, 1, 0, 0, 0, 82, 83, 1, 0, 
		    0, 0, 83, 84, 5, 0, 0, 1, 84, 1, 1, 0, 0, 0, 85, 89, 3, 4, 2, 0, 86, 
		    89, 3, 28, 14, 0, 87, 89, 3, 50, 25, 0, 88, 85, 1, 0, 0, 0, 88, 86, 
		    1, 0, 0, 0, 88, 87, 1, 0, 0, 0, 89, 3, 1, 0, 0, 0, 90, 91, 5, 1, 0, 
		    0, 91, 92, 5, 92, 0, 0, 92, 93, 5, 81, 0, 0, 93, 94, 3, 6, 3, 0, 94, 
		    95, 3, 10, 5, 0, 95, 96, 3, 14, 7, 0, 96, 97, 3, 18, 9, 0, 97, 98, 
		    3, 22, 11, 0, 98, 99, 5, 82, 0, 0, 99, 5, 1, 0, 0, 0, 100, 101, 5, 
		    8, 0, 0, 101, 102, 3, 8, 4, 0, 102, 7, 1, 0, 0, 0, 103, 104, 7, 0, 
		    0, 0, 104, 9, 1, 0, 0, 0, 105, 106, 5, 9, 0, 0, 106, 107, 3, 12, 6, 
		    0, 107, 11, 1, 0, 0, 0, 108, 109, 7, 1, 0, 0, 109, 13, 1, 0, 0, 0, 
		    110, 111, 5, 10, 0, 0, 111, 112, 3, 16, 8, 0, 112, 15, 1, 0, 0, 0, 
		    113, 114, 7, 2, 0, 0, 114, 17, 1, 0, 0, 0, 115, 116, 5, 11, 0, 0, 
		    116, 118, 5, 81, 0, 0, 117, 119, 3, 20, 10, 0, 118, 117, 1, 0, 0, 
		    0, 119, 120, 1, 0, 0, 0, 120, 118, 1, 0, 0, 0, 120, 121, 1, 0, 0, 
		    0, 121, 122, 1, 0, 0, 0, 122, 123, 5, 82, 0, 0, 123, 19, 1, 0, 0, 
		    0, 124, 125, 3, 26, 13, 0, 125, 126, 5, 90, 0, 0, 126, 127, 3, 74, 
		    37, 0, 127, 21, 1, 0, 0, 0, 128, 129, 5, 12, 0, 0, 129, 131, 5, 81, 
		    0, 0, 130, 132, 3, 24, 12, 0, 131, 130, 1, 0, 0, 0, 132, 133, 1, 0, 
		    0, 0, 133, 131, 1, 0, 0, 0, 133, 134, 1, 0, 0, 0, 134, 135, 1, 0, 
		    0, 0, 135, 136, 5, 82, 0, 0, 136, 23, 1, 0, 0, 0, 137, 142, 3, 26, 
		    13, 0, 138, 142, 5, 34, 0, 0, 139, 142, 5, 25, 0, 0, 140, 142, 5, 
		    33, 0, 0, 141, 137, 1, 0, 0, 0, 141, 138, 1, 0, 0, 0, 141, 139, 1, 
		    0, 0, 0, 141, 140, 1, 0, 0, 0, 142, 25, 1, 0, 0, 0, 143, 144, 7, 3, 
		    0, 0, 144, 27, 1, 0, 0, 0, 145, 148, 3, 30, 15, 0, 146, 148, 3, 38, 
		    19, 0, 147, 145, 1, 0, 0, 0, 147, 146, 1, 0, 0, 0, 148, 29, 1, 0, 
		    0, 0, 149, 150, 5, 2, 0, 0, 150, 151, 5, 4, 0, 0, 151, 152, 5, 92, 
		    0, 0, 152, 153, 5, 81, 0, 0, 153, 154, 3, 32, 16, 0, 154, 155, 3, 
		    34, 17, 0, 155, 156, 5, 44, 0, 0, 156, 157, 5, 92, 0, 0, 157, 158, 
		    5, 45, 0, 0, 158, 159, 3, 48, 24, 0, 159, 160, 5, 46, 0, 0, 160, 161, 
		    5, 90, 0, 0, 161, 162, 3, 76, 38, 0, 162, 163, 5, 47, 0, 0, 163, 164, 
		    5, 90, 0, 0, 164, 165, 5, 82, 0, 0, 165, 31, 1, 0, 0, 0, 166, 167, 
		    5, 1, 0, 0, 167, 168, 5, 92, 0, 0, 168, 33, 1, 0, 0, 0, 169, 170, 
		    3, 36, 18, 0, 170, 35, 1, 0, 0, 0, 171, 172, 5, 41, 0, 0, 172, 173, 
		    5, 90, 0, 0, 173, 174, 3, 76, 38, 0, 174, 37, 1, 0, 0, 0, 175, 176, 
		    5, 2, 0, 0, 176, 177, 5, 5, 0, 0, 177, 178, 5, 92, 0, 0, 178, 179, 
		    5, 81, 0, 0, 179, 180, 3, 32, 16, 0, 180, 181, 3, 40, 20, 0, 181, 
		    182, 5, 44, 0, 0, 182, 183, 5, 92, 0, 0, 183, 184, 5, 45, 0, 0, 184, 
		    185, 3, 48, 24, 0, 185, 186, 5, 46, 0, 0, 186, 187, 5, 90, 0, 0, 187, 
		    188, 3, 76, 38, 0, 188, 189, 5, 48, 0, 0, 189, 190, 5, 90, 0, 0, 190, 
		    191, 3, 76, 38, 0, 191, 192, 5, 47, 0, 0, 192, 193, 5, 90, 0, 0, 193, 
		    194, 5, 82, 0, 0, 194, 39, 1, 0, 0, 0, 195, 196, 5, 43, 0, 0, 196, 
		    197, 3, 42, 21, 0, 197, 41, 1, 0, 0, 0, 198, 203, 3, 44, 22, 0, 199, 
		    200, 7, 4, 0, 0, 200, 202, 3, 44, 22, 0, 201, 199, 1, 0, 0, 0, 202, 
		    205, 1, 0, 0, 0, 203, 201, 1, 0, 0, 0, 203, 204, 1, 0, 0, 0, 204, 
		    43, 1, 0, 0, 0, 205, 203, 1, 0, 0, 0, 206, 207, 3, 26, 13, 0, 207, 
		    208, 3, 46, 23, 0, 208, 209, 5, 90, 0, 0, 209, 210, 3, 74, 37, 0, 
		    210, 216, 1, 0, 0, 0, 211, 212, 5, 25, 0, 0, 212, 216, 3, 70, 35, 
		    0, 213, 214, 5, 33, 0, 0, 214, 216, 3, 72, 36, 0, 215, 206, 1, 0, 
		    0, 0, 215, 211, 1, 0, 0, 0, 215, 213, 1, 0, 0, 0, 216, 45, 1, 0, 0, 
		    0, 217, 218, 7, 5, 0, 0, 218, 47, 1, 0, 0, 0, 219, 220, 7, 6, 0, 0, 
		    220, 49, 1, 0, 0, 0, 221, 222, 3, 52, 26, 0, 222, 51, 1, 0, 0, 0, 
		    223, 224, 5, 3, 0, 0, 224, 225, 5, 92, 0, 0, 225, 226, 5, 81, 0, 0, 
		    226, 227, 3, 32, 16, 0, 227, 229, 3, 54, 27, 0, 228, 230, 3, 58, 29, 
		    0, 229, 228, 1, 0, 0, 0, 229, 230, 1, 0, 0, 0, 230, 231, 1, 0, 0, 
		    0, 231, 233, 3, 66, 33, 0, 232, 234, 3, 62, 31, 0, 233, 232, 1, 0, 
		    0, 0, 233, 234, 1, 0, 0, 0, 234, 236, 1, 0, 0, 0, 235, 237, 3, 64, 
		    32, 0, 236, 235, 1, 0, 0, 0, 236, 237, 1, 0, 0, 0, 237, 238, 1, 0, 
		    0, 0, 238, 239, 5, 82, 0, 0, 239, 53, 1, 0, 0, 0, 240, 241, 5, 66, 
		    0, 0, 241, 242, 3, 56, 28, 0, 242, 55, 1, 0, 0, 0, 243, 244, 5, 90, 
		    0, 0, 244, 245, 5, 88, 0, 0, 245, 246, 5, 90, 0, 0, 246, 247, 5, 88, 
		    0, 0, 247, 248, 5, 90, 0, 0, 248, 249, 5, 89, 0, 0, 249, 250, 5, 90, 
		    0, 0, 250, 251, 5, 87, 0, 0, 251, 252, 5, 90, 0, 0, 252, 57, 1, 0, 
		    0, 0, 253, 254, 5, 61, 0, 0, 254, 255, 5, 92, 0, 0, 255, 256, 5, 62, 
		    0, 0, 256, 257, 5, 90, 0, 0, 257, 258, 3, 76, 38, 0, 258, 259, 5, 
		    63, 0, 0, 259, 260, 3, 60, 30, 0, 260, 59, 1, 0, 0, 0, 261, 262, 7, 
		    7, 0, 0, 262, 61, 1, 0, 0, 0, 263, 264, 5, 64, 0, 0, 264, 265, 5, 
		    91, 0, 0, 265, 63, 1, 0, 0, 0, 266, 267, 5, 67, 0, 0, 267, 268, 5, 
		    91, 0, 0, 268, 65, 1, 0, 0, 0, 269, 270, 5, 65, 0, 0, 270, 272, 5, 
		    81, 0, 0, 271, 273, 3, 68, 34, 0, 272, 271, 1, 0, 0, 0, 273, 274, 
		    1, 0, 0, 0, 274, 272, 1, 0, 0, 0, 274, 275, 1, 0, 0, 0, 275, 276, 
		    1, 0, 0, 0, 276, 277, 5, 82, 0, 0, 277, 67, 1, 0, 0, 0, 278, 279, 
		    3, 26, 13, 0, 279, 280, 5, 90, 0, 0, 280, 281, 3, 74, 37, 0, 281, 
		    290, 1, 0, 0, 0, 282, 283, 5, 34, 0, 0, 283, 284, 5, 90, 0, 0, 284, 
		    290, 3, 76, 38, 0, 285, 286, 5, 25, 0, 0, 286, 290, 3, 70, 35, 0, 
		    287, 288, 5, 33, 0, 0, 288, 290, 3, 72, 36, 0, 289, 278, 1, 0, 0, 
		    0, 289, 282, 1, 0, 0, 0, 289, 285, 1, 0, 0, 0, 289, 287, 1, 0, 0, 
		    0, 290, 69, 1, 0, 0, 0, 291, 292, 7, 8, 0, 0, 292, 71, 1, 0, 0, 0, 
		    293, 294, 7, 9, 0, 0, 294, 73, 1, 0, 0, 0, 295, 296, 7, 10, 0, 0, 
		    296, 75, 1, 0, 0, 0, 297, 298, 7, 11, 0, 0, 298, 77, 1, 0, 0, 0, 13, 
		    81, 88, 120, 133, 141, 147, 203, 215, 229, 233, 236, 274, 289];
		protected static $atn;
		protected static $decisionToDFA;
		protected static $sharedContextCache;

		public function __construct(TokenStream $input)
		{
			parent::__construct($input);

			self::initialize();

			$this->interp = new ParserATNSimulator($this, self::$atn, self::$decisionToDFA, self::$sharedContextCache);
		}

		private static function initialize(): void
		{
			if (self::$atn !== null) {
				return;
			}

			RuntimeMetaData::checkVersion('4.13.2', RuntimeMetaData::VERSION);

			$atn = (new ATNDeserializer())->deserialize(self::SERIALIZED_ATN);

			$decisionToDFA = [];
			for ($i = 0, $count = $atn->getNumberOfDecisions(); $i < $count; $i++) {
				$decisionToDFA[] = new DFA($atn->getDecisionState($i), $i);
			}

			self::$atn = $atn;
			self::$decisionToDFA = $decisionToDFA;
			self::$sharedContextCache = new PredictionContextCache();
		}

		public function getGrammarFileName(): string
		{
			return "CMMSParser.g4";
		}

		public function getRuleNames(): array
		{
			return self::RULE_NAMES;
		}

		public function getSerializedATN(): array
		{
			return self::SERIALIZED_ATN;
		}

		public function getATN(): ATN
		{
			return self::$atn;
		}

		public function getVocabulary(): Vocabulary
        {
            static $vocabulary;

			return $vocabulary = $vocabulary ?? new VocabularyImpl(self::LITERAL_NAMES, self::SYMBOLIC_NAMES);
        }

		/**
		 * @throws RecognitionException
		 */
		public function programa(): Context\ProgramaContext
		{
		    $localContext = new Context\ProgramaContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 0, self::RULE_programa);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(79); 
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        do {
		        	$this->setState(78);
		        	$this->declaracao();
		        	$this->setState(81); 
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        } while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & 14) !== 0));
		        $this->setState(83);
		        $this->match(self::EOF);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function declaracao(): Context\DeclaracaoContext
		{
		    $localContext = new Context\DeclaracaoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 2, self::RULE_declaracao);

		    try {
		        $this->setState(88);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::EQUIPAMENTO:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(85);
		            	$this->declaracaoEquipamento();
		            	break;

		            case self::MANUTENCAO:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(86);
		            	$this->declaracaoManutencao();
		            	break;

		            case self::REGISTRO:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(87);
		            	$this->declaracaoRegistro();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function declaracaoEquipamento(): Context\DeclaracaoEquipamentoContext
		{
		    $localContext = new Context\DeclaracaoEquipamentoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 4, self::RULE_declaracaoEquipamento);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(90);
		        $this->match(self::EQUIPAMENTO);
		        $this->setState(91);
		        $this->match(self::IDENTIFICADOR);
		        $this->setState(92);
		        $this->match(self::ABRE_CHAVE);
		        $this->setState(93);
		        $this->tipoEquipamentoDeclarado();
		        $this->setState(94);
		        $this->servicoEquipamento();
		        $this->setState(95);
		        $this->produtoEquipamento();
		        $this->setState(96);
		        $this->caracteristicasProcesso();
		        $this->setState(97);
		        $this->variaveisControladas();
		        $this->setState(98);
		        $this->match(self::FECHA_CHAVE);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function tipoEquipamentoDeclarado(): Context\TipoEquipamentoDeclaradoContext
		{
		    $localContext = new Context\TipoEquipamentoDeclaradoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 6, self::RULE_tipoEquipamentoDeclarado);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(100);
		        $this->match(self::TIPO);
		        $this->setState(101);
		        $this->tipoEquipamento();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function tipoEquipamento(): Context\TipoEquipamentoContext
		{
		    $localContext = new Context\TipoEquipamentoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 8, self::RULE_tipoEquipamento);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(103);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & 122880) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function servicoEquipamento(): Context\ServicoEquipamentoContext
		{
		    $localContext = new Context\ServicoEquipamentoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 10, self::RULE_servicoEquipamento);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(105);
		        $this->match(self::SERVICO);
		        $this->setState(106);
		        $this->tipoServico();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function tipoServico(): Context\TipoServicoContext
		{
		    $localContext = new Context\TipoServicoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 12, self::RULE_tipoServico);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(108);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & 4063232) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function produtoEquipamento(): Context\ProdutoEquipamentoContext
		{
		    $localContext = new Context\ProdutoEquipamentoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 14, self::RULE_produtoEquipamento);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(110);
		        $this->match(self::PRODUTO);
		        $this->setState(111);
		        $this->tipoProduto();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function tipoProduto(): Context\TipoProdutoContext
		{
		    $localContext = new Context\TipoProdutoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 16, self::RULE_tipoProduto);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(113);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & 29360128) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function caracteristicasProcesso(): Context\CaracteristicasProcessoContext
		{
		    $localContext = new Context\CaracteristicasProcessoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 18, self::RULE_caracteristicasProcesso);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(115);
		        $this->match(self::CARACTERISTICAS_PROCESSO);
		        $this->setState(116);
		        $this->match(self::ABRE_CHAVE);
		        $this->setState(118); 
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        do {
		        	$this->setState(117);
		        	$this->caracteristicaProcesso();
		        	$this->setState(120); 
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        } while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & 8522825728) !== 0));
		        $this->setState(122);
		        $this->match(self::FECHA_CHAVE);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function caracteristicaProcesso(): Context\CaracteristicaProcessoContext
		{
		    $localContext = new Context\CaracteristicaProcessoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 20, self::RULE_caracteristicaProcesso);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(124);
		        $this->variavelNumerica();
		        $this->setState(125);
		        $this->match(self::NUMERO);
		        $this->setState(126);
		        $this->unidade();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function variaveisControladas(): Context\VariaveisControladasContext
		{
		    $localContext = new Context\VariaveisControladasContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 22, self::RULE_variaveisControladas);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(128);
		        $this->match(self::VARIAVEIS_CONTROLADAS);
		        $this->setState(129);
		        $this->match(self::ABRE_CHAVE);
		        $this->setState(131); 
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        do {
		        	$this->setState(130);
		        	$this->declaracaoVariavelControlada();
		        	$this->setState(133); 
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        } while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & 34326183936) !== 0));
		        $this->setState(135);
		        $this->match(self::FECHA_CHAVE);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function declaracaoVariavelControlada(): Context\DeclaracaoVariavelControladaContext
		{
		    $localContext = new Context\DeclaracaoVariavelControladaContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 24, self::RULE_declaracaoVariavelControlada);

		    try {
		        $this->setState(141);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::VAZAO:
		            case self::PRESSAO:
		            case self::PRESSAO_DESCARGA:
		            case self::PRESSAO_SUCCAO:
		            case self::VIBRACAO:
		            case self::TEMPERATURA:
		            case self::ROTACAO:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(137);
		            	$this->variavelNumerica();
		            	break;

		            case self::HORAS_OPERACAO:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(138);
		            	$this->match(self::HORAS_OPERACAO);
		            	break;

		            case self::OBSERVACAO_VISUAL:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(139);
		            	$this->match(self::OBSERVACAO_VISUAL);
		            	break;

		            case self::VAZAMENTO:
		            	$this->enterOuterAlt($localContext, 4);
		            	$this->setState(140);
		            	$this->match(self::VAZAMENTO);
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function variavelNumerica(): Context\VariavelNumericaContext
		{
		    $localContext = new Context\VariavelNumericaContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 26, self::RULE_variavelNumerica);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(143);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & 8522825728) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function declaracaoManutencao(): Context\DeclaracaoManutencaoContext
		{
		    $localContext = new Context\DeclaracaoManutencaoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 28, self::RULE_declaracaoManutencao);

		    try {
		        $this->setState(147);
		        $this->errorHandler->sync($this);

		        switch ($this->getInterpreter()->adaptivePredict($this->input, 5, $this->ctx)) {
		        	case 1:
		        	    $this->enterOuterAlt($localContext, 1);
		        	    $this->setState(145);
		        	    $this->manutencaoPreventiva();
		        	break;

		        	case 2:
		        	    $this->enterOuterAlt($localContext, 2);
		        	    $this->setState(146);
		        	    $this->manutencaoCorretiva();
		        	break;
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function manutencaoPreventiva(): Context\ManutencaoPreventivaContext
		{
		    $localContext = new Context\ManutencaoPreventivaContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 30, self::RULE_manutencaoPreventiva);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(149);
		        $this->match(self::MANUTENCAO);
		        $this->setState(150);
		        $this->match(self::PREVENTIVA);
		        $this->setState(151);
		        $this->match(self::IDENTIFICADOR);
		        $this->setState(152);
		        $this->match(self::ABRE_CHAVE);
		        $this->setState(153);
		        $this->equipamentoIdentificador();
		        $this->setState(154);
		        $this->gatilhoPreventivo();
		        $this->setState(155);
		        $this->match(self::PROCEDIMENTO);
		        $this->setState(156);
		        $this->match(self::IDENTIFICADOR);
		        $this->setState(157);
		        $this->match(self::PRIORIDADE);
		        $this->setState(158);
		        $this->statusPrioridade();
		        $this->setState(159);
		        $this->match(self::DURACAO);
		        $this->setState(160);
		        $this->match(self::NUMERO);
		        $this->setState(161);
		        $this->unidadeTempo();
		        $this->setState(162);
		        $this->match(self::HOMEM_HORA);
		        $this->setState(163);
		        $this->match(self::NUMERO);
		        $this->setState(164);
		        $this->match(self::FECHA_CHAVE);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function equipamentoIdentificador(): Context\EquipamentoIdentificadorContext
		{
		    $localContext = new Context\EquipamentoIdentificadorContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 32, self::RULE_equipamentoIdentificador);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(166);
		        $this->match(self::EQUIPAMENTO);
		        $this->setState(167);
		        $this->match(self::IDENTIFICADOR);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function gatilhoPreventivo(): Context\GatilhoPreventivoContext
		{
		    $localContext = new Context\GatilhoPreventivoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 34, self::RULE_gatilhoPreventivo);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(169);
		        $this->gatilhoCalendario();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function gatilhoCalendario(): Context\GatilhoCalendarioContext
		{
		    $localContext = new Context\GatilhoCalendarioContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 36, self::RULE_gatilhoCalendario);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(171);
		        $this->match(self::A_CADA);
		        $this->setState(172);
		        $this->match(self::NUMERO);
		        $this->setState(173);
		        $this->unidadeTempo();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function manutencaoCorretiva(): Context\ManutencaoCorretivaContext
		{
		    $localContext = new Context\ManutencaoCorretivaContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 38, self::RULE_manutencaoCorretiva);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(175);
		        $this->match(self::MANUTENCAO);
		        $this->setState(176);
		        $this->match(self::CORRETIVA);
		        $this->setState(177);
		        $this->match(self::IDENTIFICADOR);
		        $this->setState(178);
		        $this->match(self::ABRE_CHAVE);
		        $this->setState(179);
		        $this->equipamentoIdentificador();
		        $this->setState(180);
		        $this->gatilhoCorretivo();
		        $this->setState(181);
		        $this->match(self::PROCEDIMENTO);
		        $this->setState(182);
		        $this->match(self::IDENTIFICADOR);
		        $this->setState(183);
		        $this->match(self::PRIORIDADE);
		        $this->setState(184);
		        $this->statusPrioridade();
		        $this->setState(185);
		        $this->match(self::DURACAO);
		        $this->setState(186);
		        $this->match(self::NUMERO);
		        $this->setState(187);
		        $this->unidadeTempo();
		        $this->setState(188);
		        $this->match(self::PRAZO);
		        $this->setState(189);
		        $this->match(self::NUMERO);
		        $this->setState(190);
		        $this->unidadeTempo();
		        $this->setState(191);
		        $this->match(self::HOMEM_HORA);
		        $this->setState(192);
		        $this->match(self::NUMERO);
		        $this->setState(193);
		        $this->match(self::FECHA_CHAVE);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function gatilhoCorretivo(): Context\GatilhoCorretivoContext
		{
		    $localContext = new Context\GatilhoCorretivoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 40, self::RULE_gatilhoCorretivo);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(195);
		        $this->match(self::QUANDO);
		        $this->setState(196);
		        $this->condicao();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function condicao(): Context\CondicaoContext
		{
		    $localContext = new Context\CondicaoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 42, self::RULE_condicao);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(198);
		        $this->condicaoSimples();
		        $this->setState(203);
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        while ($_la === self::E || $_la === self::OU) {
		        	$this->setState(199);

		        	$_la = $this->input->LA(1);

		        	if (!($_la === self::E || $_la === self::OU)) {
		        	$this->errorHandler->recoverInline($this);
		        	} else {
		        		if ($this->input->LA(1) === Token::EOF) {
		        		    $this->matchedEOF = true;
		        	    }

		        		$this->errorHandler->reportMatch($this);
		        		$this->consume();
		        	}
		        	$this->setState(200);
		        	$this->condicaoSimples();
		        	$this->setState(205);
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function condicaoSimples(): Context\CondicaoSimplesContext
		{
		    $localContext = new Context\CondicaoSimplesContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 44, self::RULE_condicaoSimples);

		    try {
		        $this->setState(215);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::VAZAO:
		            case self::PRESSAO:
		            case self::PRESSAO_DESCARGA:
		            case self::PRESSAO_SUCCAO:
		            case self::VIBRACAO:
		            case self::TEMPERATURA:
		            case self::ROTACAO:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(206);
		            	$this->variavelNumerica();
		            	$this->setState(207);
		            	$this->comparador();
		            	$this->setState(208);
		            	$this->match(self::NUMERO);
		            	$this->setState(209);
		            	$this->unidade();
		            	break;

		            case self::OBSERVACAO_VISUAL:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(211);
		            	$this->match(self::OBSERVACAO_VISUAL);
		            	$this->setState(212);
		            	$this->estadoObservacao();
		            	break;

		            case self::VAZAMENTO:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(213);
		            	$this->match(self::VAZAMENTO);
		            	$this->setState(214);
		            	$this->estadoVazamento();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function comparador(): Context\ComparadorContext
		{
		    $localContext = new Context\ComparadorContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 46, self::RULE_comparador);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(217);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & 567453553048682496) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function statusPrioridade(): Context\StatusPrioridadeContext
		{
		    $localContext = new Context\StatusPrioridadeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 48, self::RULE_statusPrioridade);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(219);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & 8444249301319680) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function declaracaoRegistro(): Context\DeclaracaoRegistroContext
		{
		    $localContext = new Context\DeclaracaoRegistroContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 50, self::RULE_declaracaoRegistro);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(221);
		        $this->registro();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function registro(): Context\RegistroContext
		{
		    $localContext = new Context\RegistroContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 52, self::RULE_registro);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(223);
		        $this->match(self::REGISTRO);
		        $this->setState(224);
		        $this->match(self::IDENTIFICADOR);
		        $this->setState(225);
		        $this->match(self::ABRE_CHAVE);
		        $this->setState(226);
		        $this->equipamentoIdentificador();
		        $this->setState(227);
		        $this->dataRegistro();
		        $this->setState(229);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::ORIGEM) {
		        	$this->setState(228);
		        	$this->registroExe();
		        }
		        $this->setState(231);
		        $this->blocoValores();
		        $this->setState(233);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::RELATORIO) {
		        	$this->setState(232);
		        	$this->relatorioRegistro();
		        }
		        $this->setState(236);
		        $this->errorHandler->sync($this);
		        $_la = $this->input->LA(1);

		        if ($_la === self::OBSERVACAO) {
		        	$this->setState(235);
		        	$this->observacaoRegistro();
		        }
		        $this->setState(238);
		        $this->match(self::FECHA_CHAVE);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function dataRegistro(): Context\DataRegistroContext
		{
		    $localContext = new Context\DataRegistroContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 54, self::RULE_dataRegistro);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(240);
		        $this->match(self::DATA);
		        $this->setState(241);
		        $this->unidadeDataHora();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function unidadeDataHora(): Context\UnidadeDataHoraContext
		{
		    $localContext = new Context\UnidadeDataHoraContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 56, self::RULE_unidadeDataHora);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(243);
		        $this->match(self::NUMERO);
		        $this->setState(244);
		        $this->match(self::BARRA);
		        $this->setState(245);
		        $this->match(self::NUMERO);
		        $this->setState(246);
		        $this->match(self::BARRA);
		        $this->setState(247);
		        $this->match(self::NUMERO);
		        $this->setState(248);
		        $this->match(self::TRACO);
		        $this->setState(249);
		        $this->match(self::NUMERO);
		        $this->setState(250);
		        $this->match(self::DOIS_PONTOS);
		        $this->setState(251);
		        $this->match(self::NUMERO);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function registroExe(): Context\RegistroExeContext
		{
		    $localContext = new Context\RegistroExeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 58, self::RULE_registroExe);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(253);
		        $this->match(self::ORIGEM);
		        $this->setState(254);
		        $this->match(self::IDENTIFICADOR);
		        $this->setState(255);
		        $this->match(self::TEMPO_EXECUCAO);
		        $this->setState(256);
		        $this->match(self::NUMERO);
		        $this->setState(257);
		        $this->unidadeTempo();
		        $this->setState(258);
		        $this->match(self::STATUS);
		        $this->setState(259);
		        $this->statusRegistro();
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function statusRegistro(): Context\StatusRegistroContext
		{
		    $localContext = new Context\StatusRegistroContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 60, self::RULE_statusRegistro);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(261);

		        $_la = $this->input->LA(1);

		        if (!((((($_la - 68)) & ~0x3f) === 0 && ((1 << ($_la - 68)) & 15) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function relatorioRegistro(): Context\RelatorioRegistroContext
		{
		    $localContext = new Context\RelatorioRegistroContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 62, self::RULE_relatorioRegistro);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(263);
		        $this->match(self::RELATORIO);
		        $this->setState(264);
		        $this->match(self::TEXTO);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function observacaoRegistro(): Context\ObservacaoRegistroContext
		{
		    $localContext = new Context\ObservacaoRegistroContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 64, self::RULE_observacaoRegistro);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(266);
		        $this->match(self::OBSERVACAO);
		        $this->setState(267);
		        $this->match(self::TEXTO);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function blocoValores(): Context\BlocoValoresContext
		{
		    $localContext = new Context\BlocoValoresContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 66, self::RULE_blocoValores);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(269);
		        $this->match(self::VALORES);
		        $this->setState(270);
		        $this->match(self::ABRE_CHAVE);
		        $this->setState(272); 
		        $this->errorHandler->sync($this);

		        $_la = $this->input->LA(1);
		        do {
		        	$this->setState(271);
		        	$this->valorRegistrado();
		        	$this->setState(274); 
		        	$this->errorHandler->sync($this);
		        	$_la = $this->input->LA(1);
		        } while (((($_la) & ~0x3f) === 0 && ((1 << $_la) & 34326183936) !== 0));
		        $this->setState(276);
		        $this->match(self::FECHA_CHAVE);
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function valorRegistrado(): Context\ValorRegistradoContext
		{
		    $localContext = new Context\ValorRegistradoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 68, self::RULE_valorRegistrado);

		    try {
		        $this->setState(289);
		        $this->errorHandler->sync($this);

		        switch ($this->input->LA(1)) {
		            case self::VAZAO:
		            case self::PRESSAO:
		            case self::PRESSAO_DESCARGA:
		            case self::PRESSAO_SUCCAO:
		            case self::VIBRACAO:
		            case self::TEMPERATURA:
		            case self::ROTACAO:
		            	$this->enterOuterAlt($localContext, 1);
		            	$this->setState(278);
		            	$this->variavelNumerica();
		            	$this->setState(279);
		            	$this->match(self::NUMERO);
		            	$this->setState(280);
		            	$this->unidade();
		            	break;

		            case self::HORAS_OPERACAO:
		            	$this->enterOuterAlt($localContext, 2);
		            	$this->setState(282);
		            	$this->match(self::HORAS_OPERACAO);
		            	$this->setState(283);
		            	$this->match(self::NUMERO);
		            	$this->setState(284);
		            	$this->unidadeTempo();
		            	break;

		            case self::OBSERVACAO_VISUAL:
		            	$this->enterOuterAlt($localContext, 3);
		            	$this->setState(285);
		            	$this->match(self::OBSERVACAO_VISUAL);
		            	$this->setState(286);
		            	$this->estadoObservacao();
		            	break;

		            case self::VAZAMENTO:
		            	$this->enterOuterAlt($localContext, 4);
		            	$this->setState(287);
		            	$this->match(self::VAZAMENTO);
		            	$this->setState(288);
		            	$this->estadoVazamento();
		            	break;

		        default:
		        	throw new NoViableAltException($this);
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function estadoObservacao(): Context\EstadoObservacaoContext
		{
		    $localContext = new Context\EstadoObservacaoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 70, self::RULE_estadoObservacao);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(291);

		        $_la = $this->input->LA(1);

		        if (!($_la === self::NORMAL || $_la === self::ANORMAL)) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function estadoVazamento(): Context\EstadoVazamentoContext
		{
		    $localContext = new Context\EstadoVazamentoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 72, self::RULE_estadoVazamento);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(293);

		        $_la = $this->input->LA(1);

		        if (!(((($_la) & ~0x3f) === 0 && ((1 << $_la) & 2061584302080) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function unidade(): Context\UnidadeContext
		{
		    $localContext = new Context\UnidadeContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 74, self::RULE_unidade);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(295);

		        $_la = $this->input->LA(1);

		        if (!((((($_la - 72)) & ~0x3f) === 0 && ((1 << ($_la - 72)) & 511) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}

		/**
		 * @throws RecognitionException
		 */
		public function unidadeTempo(): Context\UnidadeTempoContext
		{
		    $localContext = new Context\UnidadeTempoContext($this->ctx, $this->getState());

		    $this->enterRule($localContext, 76, self::RULE_unidadeTempo);

		    try {
		        $this->enterOuterAlt($localContext, 1);
		        $this->setState(297);

		        $_la = $this->input->LA(1);

		        if (!((((($_la - 76)) & ~0x3f) === 0 && ((1 << ($_la - 76)) & 7) !== 0))) {
		        $this->errorHandler->recoverInline($this);
		        } else {
		        	if ($this->input->LA(1) === Token::EOF) {
		        	    $this->matchedEOF = true;
		            }

		        	$this->errorHandler->reportMatch($this);
		        	$this->consume();
		        }
		    } catch (RecognitionException $exception) {
		        $localContext->exception = $exception;
		        $this->errorHandler->reportError($this, $exception);
		        $this->errorHandler->recover($this, $exception);
		    } finally {
		        $this->exitRule();
		    }

		    return $localContext;
		}
	}
}

namespace Context {
	use Antlr\Antlr4\Runtime\ParserRuleContext;
	use Antlr\Antlr4\Runtime\Token;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeVisitor;
	use Antlr\Antlr4\Runtime\Tree\TerminalNode;
	use Antlr\Antlr4\Runtime\Tree\ParseTreeListener;
	use CMMSParser;
	use CMMSParserVisitor;
	use CMMSParserListener;

	class ProgramaContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_programa;
	    }

	    public function EOF(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::EOF, 0);
	    }

	    /**
	     * @return array<DeclaracaoContext>|DeclaracaoContext|null
	     */
	    public function declaracao(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(DeclaracaoContext::class);
	    	}

	        return $this->getTypedRuleContext(DeclaracaoContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterPrograma($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitPrograma($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitPrograma($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class DeclaracaoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_declaracao;
	    }

	    public function declaracaoEquipamento(): ?DeclaracaoEquipamentoContext
	    {
	    	return $this->getTypedRuleContext(DeclaracaoEquipamentoContext::class, 0);
	    }

	    public function declaracaoManutencao(): ?DeclaracaoManutencaoContext
	    {
	    	return $this->getTypedRuleContext(DeclaracaoManutencaoContext::class, 0);
	    }

	    public function declaracaoRegistro(): ?DeclaracaoRegistroContext
	    {
	    	return $this->getTypedRuleContext(DeclaracaoRegistroContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterDeclaracao($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitDeclaracao($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitDeclaracao($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class DeclaracaoEquipamentoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_declaracaoEquipamento;
	    }

	    public function EQUIPAMENTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::EQUIPAMENTO, 0);
	    }

	    public function IDENTIFICADOR(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::IDENTIFICADOR, 0);
	    }

	    public function ABRE_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ABRE_CHAVE, 0);
	    }

	    public function tipoEquipamentoDeclarado(): ?TipoEquipamentoDeclaradoContext
	    {
	    	return $this->getTypedRuleContext(TipoEquipamentoDeclaradoContext::class, 0);
	    }

	    public function servicoEquipamento(): ?ServicoEquipamentoContext
	    {
	    	return $this->getTypedRuleContext(ServicoEquipamentoContext::class, 0);
	    }

	    public function produtoEquipamento(): ?ProdutoEquipamentoContext
	    {
	    	return $this->getTypedRuleContext(ProdutoEquipamentoContext::class, 0);
	    }

	    public function caracteristicasProcesso(): ?CaracteristicasProcessoContext
	    {
	    	return $this->getTypedRuleContext(CaracteristicasProcessoContext::class, 0);
	    }

	    public function variaveisControladas(): ?VariaveisControladasContext
	    {
	    	return $this->getTypedRuleContext(VariaveisControladasContext::class, 0);
	    }

	    public function FECHA_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::FECHA_CHAVE, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterDeclaracaoEquipamento($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitDeclaracaoEquipamento($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitDeclaracaoEquipamento($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class TipoEquipamentoDeclaradoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_tipoEquipamentoDeclarado;
	    }

	    public function TIPO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::TIPO, 0);
	    }

	    public function tipoEquipamento(): ?TipoEquipamentoContext
	    {
	    	return $this->getTypedRuleContext(TipoEquipamentoContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterTipoEquipamentoDeclarado($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitTipoEquipamentoDeclarado($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitTipoEquipamentoDeclarado($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class TipoEquipamentoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_tipoEquipamento;
	    }

	    public function BOMBA_CENTRIFUGA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::BOMBA_CENTRIFUGA, 0);
	    }

	    public function BOMBA_ALTERNATIVA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::BOMBA_ALTERNATIVA, 0);
	    }

	    public function TROCADOR_CALOR(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::TROCADOR_CALOR, 0);
	    }

	    public function TANQUE_GASOLINA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::TANQUE_GASOLINA, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterTipoEquipamento($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitTipoEquipamento($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitTipoEquipamento($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class ServicoEquipamentoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_servicoEquipamento;
	    }

	    public function SERVICO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::SERVICO, 0);
	    }

	    public function tipoServico(): ?TipoServicoContext
	    {
	    	return $this->getTypedRuleContext(TipoServicoContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterServicoEquipamento($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitServicoEquipamento($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitServicoEquipamento($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class TipoServicoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_tipoServico;
	    }

	    public function BOMBEAMENTO_AGUA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::BOMBEAMENTO_AGUA, 0);
	    }

	    public function BOMBEAMENTO_GASOLINA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::BOMBEAMENTO_GASOLINA, 0);
	    }

	    public function BOMBEAMENTO_DIESEL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::BOMBEAMENTO_DIESEL, 0);
	    }

	    public function RESFRIAMENTO_GASOLINA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::RESFRIAMENTO_GASOLINA, 0);
	    }

	    public function ARMAZENAMENTO_GASOLINA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ARMAZENAMENTO_GASOLINA, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterTipoServico($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitTipoServico($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitTipoServico($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class ProdutoEquipamentoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_produtoEquipamento;
	    }

	    public function PRODUTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PRODUTO, 0);
	    }

	    public function tipoProduto(): ?TipoProdutoContext
	    {
	    	return $this->getTypedRuleContext(TipoProdutoContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterProdutoEquipamento($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitProdutoEquipamento($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitProdutoEquipamento($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class TipoProdutoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_tipoProduto;
	    }

	    public function AGUA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::AGUA, 0);
	    }

	    public function GASOLINA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::GASOLINA, 0);
	    }

	    public function DIESEL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::DIESEL, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterTipoProduto($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitTipoProduto($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitTipoProduto($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class CaracteristicasProcessoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_caracteristicasProcesso;
	    }

	    public function CARACTERISTICAS_PROCESSO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::CARACTERISTICAS_PROCESSO, 0);
	    }

	    public function ABRE_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ABRE_CHAVE, 0);
	    }

	    public function FECHA_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::FECHA_CHAVE, 0);
	    }

	    /**
	     * @return array<CaracteristicaProcessoContext>|CaracteristicaProcessoContext|null
	     */
	    public function caracteristicaProcesso(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(CaracteristicaProcessoContext::class);
	    	}

	        return $this->getTypedRuleContext(CaracteristicaProcessoContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterCaracteristicasProcesso($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitCaracteristicasProcesso($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitCaracteristicasProcesso($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class CaracteristicaProcessoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_caracteristicaProcesso;
	    }

	    public function variavelNumerica(): ?VariavelNumericaContext
	    {
	    	return $this->getTypedRuleContext(VariavelNumericaContext::class, 0);
	    }

	    public function NUMERO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::NUMERO, 0);
	    }

	    public function unidade(): ?UnidadeContext
	    {
	    	return $this->getTypedRuleContext(UnidadeContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterCaracteristicaProcesso($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitCaracteristicaProcesso($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitCaracteristicaProcesso($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class VariaveisControladasContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_variaveisControladas;
	    }

	    public function VARIAVEIS_CONTROLADAS(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::VARIAVEIS_CONTROLADAS, 0);
	    }

	    public function ABRE_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ABRE_CHAVE, 0);
	    }

	    public function FECHA_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::FECHA_CHAVE, 0);
	    }

	    /**
	     * @return array<DeclaracaoVariavelControladaContext>|DeclaracaoVariavelControladaContext|null
	     */
	    public function declaracaoVariavelControlada(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(DeclaracaoVariavelControladaContext::class);
	    	}

	        return $this->getTypedRuleContext(DeclaracaoVariavelControladaContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterVariaveisControladas($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitVariaveisControladas($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitVariaveisControladas($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class DeclaracaoVariavelControladaContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_declaracaoVariavelControlada;
	    }

	    public function variavelNumerica(): ?VariavelNumericaContext
	    {
	    	return $this->getTypedRuleContext(VariavelNumericaContext::class, 0);
	    }

	    public function HORAS_OPERACAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::HORAS_OPERACAO, 0);
	    }

	    public function OBSERVACAO_VISUAL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::OBSERVACAO_VISUAL, 0);
	    }

	    public function VAZAMENTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::VAZAMENTO, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterDeclaracaoVariavelControlada($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitDeclaracaoVariavelControlada($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitDeclaracaoVariavelControlada($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class VariavelNumericaContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_variavelNumerica;
	    }

	    public function VAZAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::VAZAO, 0);
	    }

	    public function PRESSAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PRESSAO, 0);
	    }

	    public function PRESSAO_DESCARGA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PRESSAO_DESCARGA, 0);
	    }

	    public function PRESSAO_SUCCAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PRESSAO_SUCCAO, 0);
	    }

	    public function VIBRACAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::VIBRACAO, 0);
	    }

	    public function TEMPERATURA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::TEMPERATURA, 0);
	    }

	    public function ROTACAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ROTACAO, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterVariavelNumerica($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitVariavelNumerica($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitVariavelNumerica($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class DeclaracaoManutencaoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_declaracaoManutencao;
	    }

	    public function manutencaoPreventiva(): ?ManutencaoPreventivaContext
	    {
	    	return $this->getTypedRuleContext(ManutencaoPreventivaContext::class, 0);
	    }

	    public function manutencaoCorretiva(): ?ManutencaoCorretivaContext
	    {
	    	return $this->getTypedRuleContext(ManutencaoCorretivaContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterDeclaracaoManutencao($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitDeclaracaoManutencao($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitDeclaracaoManutencao($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class ManutencaoPreventivaContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_manutencaoPreventiva;
	    }

	    public function MANUTENCAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MANUTENCAO, 0);
	    }

	    public function PREVENTIVA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PREVENTIVA, 0);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function IDENTIFICADOR(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(CMMSParser::IDENTIFICADOR);
	    	}

	        return $this->getToken(CMMSParser::IDENTIFICADOR, $index);
	    }

	    public function ABRE_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ABRE_CHAVE, 0);
	    }

	    public function equipamentoIdentificador(): ?EquipamentoIdentificadorContext
	    {
	    	return $this->getTypedRuleContext(EquipamentoIdentificadorContext::class, 0);
	    }

	    public function gatilhoPreventivo(): ?GatilhoPreventivoContext
	    {
	    	return $this->getTypedRuleContext(GatilhoPreventivoContext::class, 0);
	    }

	    public function PROCEDIMENTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PROCEDIMENTO, 0);
	    }

	    public function PRIORIDADE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PRIORIDADE, 0);
	    }

	    public function statusPrioridade(): ?StatusPrioridadeContext
	    {
	    	return $this->getTypedRuleContext(StatusPrioridadeContext::class, 0);
	    }

	    public function DURACAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::DURACAO, 0);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function NUMERO(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(CMMSParser::NUMERO);
	    	}

	        return $this->getToken(CMMSParser::NUMERO, $index);
	    }

	    public function unidadeTempo(): ?UnidadeTempoContext
	    {
	    	return $this->getTypedRuleContext(UnidadeTempoContext::class, 0);
	    }

	    public function HOMEM_HORA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::HOMEM_HORA, 0);
	    }

	    public function FECHA_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::FECHA_CHAVE, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterManutencaoPreventiva($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitManutencaoPreventiva($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitManutencaoPreventiva($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class EquipamentoIdentificadorContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_equipamentoIdentificador;
	    }

	    public function EQUIPAMENTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::EQUIPAMENTO, 0);
	    }

	    public function IDENTIFICADOR(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::IDENTIFICADOR, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterEquipamentoIdentificador($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitEquipamentoIdentificador($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitEquipamentoIdentificador($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class GatilhoPreventivoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_gatilhoPreventivo;
	    }

	    public function gatilhoCalendario(): ?GatilhoCalendarioContext
	    {
	    	return $this->getTypedRuleContext(GatilhoCalendarioContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterGatilhoPreventivo($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitGatilhoPreventivo($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitGatilhoPreventivo($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class GatilhoCalendarioContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_gatilhoCalendario;
	    }

	    public function A_CADA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::A_CADA, 0);
	    }

	    public function NUMERO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::NUMERO, 0);
	    }

	    public function unidadeTempo(): ?UnidadeTempoContext
	    {
	    	return $this->getTypedRuleContext(UnidadeTempoContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterGatilhoCalendario($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitGatilhoCalendario($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitGatilhoCalendario($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class ManutencaoCorretivaContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_manutencaoCorretiva;
	    }

	    public function MANUTENCAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MANUTENCAO, 0);
	    }

	    public function CORRETIVA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::CORRETIVA, 0);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function IDENTIFICADOR(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(CMMSParser::IDENTIFICADOR);
	    	}

	        return $this->getToken(CMMSParser::IDENTIFICADOR, $index);
	    }

	    public function ABRE_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ABRE_CHAVE, 0);
	    }

	    public function equipamentoIdentificador(): ?EquipamentoIdentificadorContext
	    {
	    	return $this->getTypedRuleContext(EquipamentoIdentificadorContext::class, 0);
	    }

	    public function gatilhoCorretivo(): ?GatilhoCorretivoContext
	    {
	    	return $this->getTypedRuleContext(GatilhoCorretivoContext::class, 0);
	    }

	    public function PROCEDIMENTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PROCEDIMENTO, 0);
	    }

	    public function PRIORIDADE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PRIORIDADE, 0);
	    }

	    public function statusPrioridade(): ?StatusPrioridadeContext
	    {
	    	return $this->getTypedRuleContext(StatusPrioridadeContext::class, 0);
	    }

	    public function DURACAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::DURACAO, 0);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function NUMERO(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(CMMSParser::NUMERO);
	    	}

	        return $this->getToken(CMMSParser::NUMERO, $index);
	    }

	    /**
	     * @return array<UnidadeTempoContext>|UnidadeTempoContext|null
	     */
	    public function unidadeTempo(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(UnidadeTempoContext::class);
	    	}

	        return $this->getTypedRuleContext(UnidadeTempoContext::class, $index);
	    }

	    public function PRAZO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::PRAZO, 0);
	    }

	    public function HOMEM_HORA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::HOMEM_HORA, 0);
	    }

	    public function FECHA_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::FECHA_CHAVE, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterManutencaoCorretiva($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitManutencaoCorretiva($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitManutencaoCorretiva($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class GatilhoCorretivoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_gatilhoCorretivo;
	    }

	    public function QUANDO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::QUANDO, 0);
	    }

	    public function condicao(): ?CondicaoContext
	    {
	    	return $this->getTypedRuleContext(CondicaoContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterGatilhoCorretivo($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitGatilhoCorretivo($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitGatilhoCorretivo($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class CondicaoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_condicao;
	    }

	    /**
	     * @return array<CondicaoSimplesContext>|CondicaoSimplesContext|null
	     */
	    public function condicaoSimples(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(CondicaoSimplesContext::class);
	    	}

	        return $this->getTypedRuleContext(CondicaoSimplesContext::class, $index);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function E(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(CMMSParser::E);
	    	}

	        return $this->getToken(CMMSParser::E, $index);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function OU(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(CMMSParser::OU);
	    	}

	        return $this->getToken(CMMSParser::OU, $index);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterCondicao($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitCondicao($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitCondicao($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class CondicaoSimplesContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_condicaoSimples;
	    }

	    public function variavelNumerica(): ?VariavelNumericaContext
	    {
	    	return $this->getTypedRuleContext(VariavelNumericaContext::class, 0);
	    }

	    public function comparador(): ?ComparadorContext
	    {
	    	return $this->getTypedRuleContext(ComparadorContext::class, 0);
	    }

	    public function NUMERO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::NUMERO, 0);
	    }

	    public function unidade(): ?UnidadeContext
	    {
	    	return $this->getTypedRuleContext(UnidadeContext::class, 0);
	    }

	    public function OBSERVACAO_VISUAL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::OBSERVACAO_VISUAL, 0);
	    }

	    public function estadoObservacao(): ?EstadoObservacaoContext
	    {
	    	return $this->getTypedRuleContext(EstadoObservacaoContext::class, 0);
	    }

	    public function VAZAMENTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::VAZAMENTO, 0);
	    }

	    public function estadoVazamento(): ?EstadoVazamentoContext
	    {
	    	return $this->getTypedRuleContext(EstadoVazamentoContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterCondicaoSimples($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitCondicaoSimples($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitCondicaoSimples($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class ComparadorContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_comparador;
	    }

	    public function IGUAL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::IGUAL, 0);
	    }

	    public function DIFERENTE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::DIFERENTE, 0);
	    }

	    public function MAIOR(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MAIOR, 0);
	    }

	    public function MAIOR_OU_IGUAL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MAIOR_OU_IGUAL, 0);
	    }

	    public function MENOR(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MENOR, 0);
	    }

	    public function MENOR_OU_IGUAL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MENOR_OU_IGUAL, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterComparador($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitComparador($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitComparador($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class StatusPrioridadeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_statusPrioridade;
	    }

	    public function BAIXA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::BAIXA, 0);
	    }

	    public function MEDIA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MEDIA, 0);
	    }

	    public function ALTA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ALTA, 0);
	    }

	    public function CRITICA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::CRITICA, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterStatusPrioridade($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitStatusPrioridade($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitStatusPrioridade($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class DeclaracaoRegistroContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_declaracaoRegistro;
	    }

	    public function registro(): ?RegistroContext
	    {
	    	return $this->getTypedRuleContext(RegistroContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterDeclaracaoRegistro($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitDeclaracaoRegistro($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitDeclaracaoRegistro($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class RegistroContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_registro;
	    }

	    public function REGISTRO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::REGISTRO, 0);
	    }

	    public function IDENTIFICADOR(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::IDENTIFICADOR, 0);
	    }

	    public function ABRE_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ABRE_CHAVE, 0);
	    }

	    public function equipamentoIdentificador(): ?EquipamentoIdentificadorContext
	    {
	    	return $this->getTypedRuleContext(EquipamentoIdentificadorContext::class, 0);
	    }

	    public function dataRegistro(): ?DataRegistroContext
	    {
	    	return $this->getTypedRuleContext(DataRegistroContext::class, 0);
	    }

	    public function blocoValores(): ?BlocoValoresContext
	    {
	    	return $this->getTypedRuleContext(BlocoValoresContext::class, 0);
	    }

	    public function FECHA_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::FECHA_CHAVE, 0);
	    }

	    public function registroExe(): ?RegistroExeContext
	    {
	    	return $this->getTypedRuleContext(RegistroExeContext::class, 0);
	    }

	    public function relatorioRegistro(): ?RelatorioRegistroContext
	    {
	    	return $this->getTypedRuleContext(RelatorioRegistroContext::class, 0);
	    }

	    public function observacaoRegistro(): ?ObservacaoRegistroContext
	    {
	    	return $this->getTypedRuleContext(ObservacaoRegistroContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterRegistro($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitRegistro($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitRegistro($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class DataRegistroContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_dataRegistro;
	    }

	    public function DATA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::DATA, 0);
	    }

	    public function unidadeDataHora(): ?UnidadeDataHoraContext
	    {
	    	return $this->getTypedRuleContext(UnidadeDataHoraContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterDataRegistro($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitDataRegistro($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitDataRegistro($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class UnidadeDataHoraContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_unidadeDataHora;
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function NUMERO(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(CMMSParser::NUMERO);
	    	}

	        return $this->getToken(CMMSParser::NUMERO, $index);
	    }

	    /**
	     * @return array<TerminalNode>|TerminalNode|null
	     */
	    public function BARRA(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTokens(CMMSParser::BARRA);
	    	}

	        return $this->getToken(CMMSParser::BARRA, $index);
	    }

	    public function TRACO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::TRACO, 0);
	    }

	    public function DOIS_PONTOS(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::DOIS_PONTOS, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterUnidadeDataHora($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitUnidadeDataHora($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitUnidadeDataHora($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class RegistroExeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_registroExe;
	    }

	    public function ORIGEM(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ORIGEM, 0);
	    }

	    public function IDENTIFICADOR(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::IDENTIFICADOR, 0);
	    }

	    public function TEMPO_EXECUCAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::TEMPO_EXECUCAO, 0);
	    }

	    public function NUMERO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::NUMERO, 0);
	    }

	    public function unidadeTempo(): ?UnidadeTempoContext
	    {
	    	return $this->getTypedRuleContext(UnidadeTempoContext::class, 0);
	    }

	    public function STATUS(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::STATUS, 0);
	    }

	    public function statusRegistro(): ?StatusRegistroContext
	    {
	    	return $this->getTypedRuleContext(StatusRegistroContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterRegistroExe($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitRegistroExe($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitRegistroExe($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class StatusRegistroContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_statusRegistro;
	    }

	    public function EM_ABERTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::EM_ABERTO, 0);
	    }

	    public function EM_EXECUCAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::EM_EXECUCAO, 0);
	    }

	    public function CONCLUIDO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::CONCLUIDO, 0);
	    }

	    public function CANCELADO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::CANCELADO, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterStatusRegistro($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitStatusRegistro($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitStatusRegistro($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class RelatorioRegistroContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_relatorioRegistro;
	    }

	    public function RELATORIO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::RELATORIO, 0);
	    }

	    public function TEXTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::TEXTO, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterRelatorioRegistro($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitRelatorioRegistro($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitRelatorioRegistro($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class ObservacaoRegistroContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_observacaoRegistro;
	    }

	    public function OBSERVACAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::OBSERVACAO, 0);
	    }

	    public function TEXTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::TEXTO, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterObservacaoRegistro($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitObservacaoRegistro($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitObservacaoRegistro($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class BlocoValoresContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_blocoValores;
	    }

	    public function VALORES(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::VALORES, 0);
	    }

	    public function ABRE_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ABRE_CHAVE, 0);
	    }

	    public function FECHA_CHAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::FECHA_CHAVE, 0);
	    }

	    /**
	     * @return array<ValorRegistradoContext>|ValorRegistradoContext|null
	     */
	    public function valorRegistrado(?int $index = null)
	    {
	    	if ($index === null) {
	    		return $this->getTypedRuleContexts(ValorRegistradoContext::class);
	    	}

	        return $this->getTypedRuleContext(ValorRegistradoContext::class, $index);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterBlocoValores($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitBlocoValores($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitBlocoValores($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class ValorRegistradoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_valorRegistrado;
	    }

	    public function variavelNumerica(): ?VariavelNumericaContext
	    {
	    	return $this->getTypedRuleContext(VariavelNumericaContext::class, 0);
	    }

	    public function NUMERO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::NUMERO, 0);
	    }

	    public function unidade(): ?UnidadeContext
	    {
	    	return $this->getTypedRuleContext(UnidadeContext::class, 0);
	    }

	    public function HORAS_OPERACAO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::HORAS_OPERACAO, 0);
	    }

	    public function unidadeTempo(): ?UnidadeTempoContext
	    {
	    	return $this->getTypedRuleContext(UnidadeTempoContext::class, 0);
	    }

	    public function OBSERVACAO_VISUAL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::OBSERVACAO_VISUAL, 0);
	    }

	    public function estadoObservacao(): ?EstadoObservacaoContext
	    {
	    	return $this->getTypedRuleContext(EstadoObservacaoContext::class, 0);
	    }

	    public function VAZAMENTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::VAZAMENTO, 0);
	    }

	    public function estadoVazamento(): ?EstadoVazamentoContext
	    {
	    	return $this->getTypedRuleContext(EstadoVazamentoContext::class, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterValorRegistrado($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitValorRegistrado($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitValorRegistrado($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class EstadoObservacaoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_estadoObservacao;
	    }

	    public function NORMAL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::NORMAL, 0);
	    }

	    public function ANORMAL(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::ANORMAL, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterEstadoObservacao($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitEstadoObservacao($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitEstadoObservacao($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class EstadoVazamentoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_estadoVazamento;
	    }

	    public function AUSENTE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::AUSENTE, 0);
	    }

	    public function LEVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::LEVE, 0);
	    }

	    public function MODERADO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MODERADO, 0);
	    }

	    public function GRAVE(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::GRAVE, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterEstadoVazamento($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitEstadoVazamento($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitEstadoVazamento($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class UnidadeContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_unidade;
	    }

	    public function MM_POR_S(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MM_POR_S, 0);
	    }

	    public function CELSIUS(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::CELSIUS, 0);
	    }

	    public function BAR(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::BAR, 0);
	    }

	    public function M3_POR_HORA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::M3_POR_HORA, 0);
	    }

	    public function HORA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::HORA, 0);
	    }

	    public function MINUTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MINUTO, 0);
	    }

	    public function DIA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::DIA, 0);
	    }

	    public function RPM(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::RPM, 0);
	    }

	    public function LITRO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::LITRO, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterUnidade($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitUnidade($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitUnidade($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 

	class UnidadeTempoContext extends ParserRuleContext
	{
		public function __construct(?ParserRuleContext $parent, ?int $invokingState = null)
		{
			parent::__construct($parent, $invokingState);
		}

		public function getRuleIndex(): int
		{
		    return CMMSParser::RULE_unidadeTempo;
	    }

	    public function HORA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::HORA, 0);
	    }

	    public function MINUTO(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::MINUTO, 0);
	    }

	    public function DIA(): ?TerminalNode
	    {
	        return $this->getToken(CMMSParser::DIA, 0);
	    }

		public function enterRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->enterUnidadeTempo($this);
		    }
		}

		public function exitRule(ParseTreeListener $listener): void
		{
			if ($listener instanceof CMMSParserListener) {
			    $listener->exitUnidadeTempo($this);
		    }
		}

		public function accept(ParseTreeVisitor $visitor): mixed
		{
			if ($visitor instanceof CMMSParserVisitor) {
			    return $visitor->visitUnidadeTempo($this);
		    }

			return $visitor->visitChildren($this);
		}
	} 
}