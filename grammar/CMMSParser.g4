parser grammar CMMSParser;

options {
    tokenVocab = CMMSLexer;
}


// ==================================================
// PROGRAMA
// ==================================================

programa
    : declaracao+ EOF
    ;

declaracao
    : declaracaoEquipamento
    | declaracaoManutencao
    | declaracaoRegistro
    ;


// ==================================================
// EQUIPAMENTO
// ==================================================

declaracaoEquipamento
    : EQUIPAMENTO IDENTIFICADOR ABRE_CHAVE
        tipoEquipamentoDeclarado
        servicoEquipamento
        produtoEquipamento
        caracteristicasProcesso
        variaveisControladas
      FECHA_CHAVE
    ;

tipoEquipamentoDeclarado
    : TIPO tipoEquipamento
    ;

tipoEquipamento
    : BOMBA_CENTRIFUGA
    | BOMBA_ALTERNATIVA
    | TROCADOR_CALOR
    | TANQUE_GASOLINA
    ;

servicoEquipamento
    : SERVICO tipoServico
    ;

tipoServico
    : BOMBEAMENTO_AGUA
    | BOMBEAMENTO_GASOLINA
    | BOMBEAMENTO_DIESEL
    | RESFRIAMENTO_GASOLINA
    | ARMAZENAMENTO_GASOLINA
    ;

produtoEquipamento
    : PRODUTO tipoProduto
    ;

tipoProduto
    : AGUA
    | GASOLINA
    | DIESEL
    ;


// ==================================================
// CARACTERISTICAS DO PROCESSO
// ==================================================

caracteristicasProcesso
    : CARACTERISTICAS_PROCESSO ABRE_CHAVE
        caracteristicaProcesso+
      FECHA_CHAVE
    ;

caracteristicaProcesso
    : variavelNumerica NUMERO unidade
    ;


// ==================================================
// VARIAVEIS CONTROLADAS
// ==================================================

variaveisControladas
    : VARIAVEIS_CONTROLADAS ABRE_CHAVE
        declaracaoVariavelControlada+
      FECHA_CHAVE
    ;

// declaracao esta sendo sem a unidade de medida de cada medicao
declaracaoVariavelControlada
    : variavelNumerica
    | HORAS_OPERACAO
    | OBSERVACAO_VISUAL
    | VAZAMENTO
    ;

variavelNumerica
    : VAZAO
    | PRESSAO
    | PRESSAO_DESCARGA
    | PRESSAO_SUCCAO
    | VIBRACAO
    | TEMPERATURA
    | ROTACAO
    ;


// ==================================================
// MANUTENCAO
// ==================================================

declaracaoManutencao
    : manutencaoPreventiva
    | manutencaoCorretiva
    ;


// ==================================================
// MANUTENCAO PREVENTIVA
// ==================================================

manutencaoPreventiva
    : MANUTENCAO PREVENTIVA IDENTIFICADOR ABRE_CHAVE
        equipamentoIdentificador
        gatilhoPreventivo
        PROCEDIMENTO IDENTIFICADOR
        PRIORIDADE statusPrioridade
        DURACAO NUMERO unidadeTempo
        HOMEM_HORA NUMERO
      FECHA_CHAVE
    ;

equipamentoIdentificador
    : EQUIPAMENTO IDENTIFICADOR
    ;

gatilhoPreventivo
    : gatilhoCalendario
    ;

gatilhoCalendario
    : A_CADA NUMERO unidadeTempo
    ;

// ==================================================
// MANUTENCAO CORRETIVA
// ==================================================

manutencaoCorretiva
    : MANUTENCAO CORRETIVA IDENTIFICADOR ABRE_CHAVE
        equipamentoIdentificador
        gatilhoCorretivo
        PROCEDIMENTO IDENTIFICADOR
        PRIORIDADE statusPrioridade
        DURACAO NUMERO unidadeTempo
        PRAZO NUMERO unidadeTempo
        HOMEM_HORA NUMERO
      FECHA_CHAVE
    ;

gatilhoCorretivo
    : QUANDO condicao
    ;

condicao
    : condicaoSimples ((E | OU) condicaoSimples)*
    ;

condicaoSimples
    : variavelNumerica comparador NUMERO unidade
    | OBSERVACAO_VISUAL estadoObservacao
    | VAZAMENTO estadoVazamento
    ;

comparador
    : IGUAL
    | DIFERENTE
    | MAIOR
    | MAIOR_OU_IGUAL
    | MENOR
    | MENOR_OU_IGUAL
    ;

statusPrioridade
    : BAIXA
    | MEDIA
    | ALTA
    | CRITICA
    ;


// ==================================================
// REGISTROS
// ==================================================

declaracaoRegistro
    : registro
    ;


// ==================================================
// REGISTRO DE LEITURA
// ==================================================

registro
    : REGISTRO IDENTIFICADOR ABRE_CHAVE
        equipamentoIdentificador
        dataRegistro
        registroExe?
        blocoValores
        relatorioRegistro?
        observacaoRegistro?
      FECHA_CHAVE
    ;

dataRegistro
    : DATA unidadeDataHora
    ;

unidadeDataHora
    : NUMERO BARRA NUMERO BARRA NUMERO TRACO NUMERO DOIS_PONTOS NUMERO
    ;

registroExe
    : ORIGEM IDENTIFICADOR
        TEMPO_EXECUCAO NUMERO unidadeTempo
        STATUS statusRegistro
    ;

statusRegistro
    : EM_ABERTO
    | EM_EXECUCAO
    | CONCLUIDO
    | CANCELADO
    ;

relatorioRegistro
    : RELATORIO TEXTO
    ;

observacaoRegistro
    : OBSERVACAO TEXTO
    ;

// ==================================================
// VALORES REGISTRADOS
// ==================================================

blocoValores
    : VALORES ABRE_CHAVE
        valorRegistrado+
      FECHA_CHAVE
    ;

valorRegistrado
    : variavelNumerica NUMERO unidade
    | HORAS_OPERACAO NUMERO unidadeTempo
    | OBSERVACAO_VISUAL estadoObservacao
    | VAZAMENTO estadoVazamento
    ;

estadoObservacao
    : NORMAL
    | ANORMAL
    ;

estadoVazamento
    : AUSENTE
    | LEVE
    | MODERADO
    | GRAVE
    ;


// ==================================================
// UNIDADES
// ==================================================

unidade
    : MM_POR_S
    | CELSIUS
    | BAR
    | M3_POR_HORA
    | HORA
    | MINUTO
    | DIA
    | RPM
    | LITRO
    ;

unidadeTempo
    : HORA
    | MINUTO
    | DIA
    ;
