lexer grammar CMMSLexer;

// ==================================================
// DECLARACOES PRINCIPAIS
// ==================================================

EQUIPAMENTO : 'equipamento';
MANUTENCAO  : 'manutencao' | 'manutençao' | 'manutencão' | 'manutenção';
REGISTRO    : 'registro';


// ==================================================
// TIPOS DE MANUTENCAO
// ==================================================

PREVENTIVA : 'preventiva';
CORRETIVA  : 'corretiva';


// ==================================================
// TIPOS DE REGISTRO
// ==================================================

LEITURA
    : 'leitura'
    ;

EXECUCAO
    : 'execucao' | 'execuçao' | 'execucão' | 'execução' 
    ;


// ==================================================
// DADOS DO EQUIPAMENTO
// ==================================================

TIPO
    : 'tipo'
    ;

SERVICO
    : 'servico' | 'serviço'
    ;

PRODUTO
    : 'produto'
    ;

CARACTERISTICAS_PROCESSO
    : 'caracteristicas_processo' | 'características_processo' | 'características processo' | 'caracteristicas processo'
    ;

VARIAVEIS_CONTROLADAS
    : 'variaveis_controladas' | 'variáveis_controladas' | 'variáveis controladas' | 'variaveis controladas'
    ;


// ==================================================
// TIPOS DE EQUIPAMENTO
// ==================================================

BOMBA_CENTRIFUGA
    : 'bomba_centrifuga' | 'bomba_centrífuga' | 'bomba centrífuga' | 'bomba centrifuga'
    ;

BOMBA_ALTERNATIVA
    : 'bomba_alternativa' | 'bomba alternativa'
    ;

TROCADOR_CALOR
    : 'trocador_de_calor' | 'trocador_calor' | 'trocador de calor' | 'trocador calor'
    ;

TANQUE_GASOLINA
    : 'tanque_de_gasolina' | 'tanque_gasolina' | 'tanque de gasolina' | 'tanque gasolina'
    ;


// ==================================================
// TIPOS DE SERVICO
// ==================================================

BOMBEAMENTO_AGUA
    : 'bombeamento_de_agua' | 'bombeamento_agua' | 'bombeamento_de_água' | 'bombeamento_água' | 'bombeamento de agua' | 'bombeamento agua' | 'bombeamento de água' | 'bombeamento água'   
    ;

BOMBEAMENTO_GASOLINA
    : 'bombeamento_de_gasolina' | 'bombeamento_gasolina' | 'bombeamento de gasolina' | 'bombeamento gasolina'
    ;

BOMBEAMENTO_DIESEL
    : 'bombeamento_de_diesel' | 'bombeamento_diesel' | 'bombeamento de diesel' | 'bombeamento diesel'
    ;

RESFRIAMENTO_GASOLINA
    : 'resfriamento_de_gasolina' | 'resfriamento_gasolina' | 'resfriamento de gasolina' | 'resfriamento gasolina'
    ;

ARMAZENAMENTO_GASOLINA
    : 'armazenamento_de_gasolina' | 'armazenamento_gasolina' | 'armazenamento de gasolina' | 'armazenamento gasolina'
    ;


// ==================================================
// PRODUTOS
// ==================================================

AGUA
    : 'agua' | 'água'
    ;

GASOLINA
    : 'gasolina'
    ;

DIESEL
    : 'diesel'
    ;


// ==================================================
// VARIAVEIS CONTROLADAS
// ==================================================

OBSERVACAO_VISUAL
    : 'observacao_visual' | 'observacao visual'
    ;

VAZAO
    : 'vazao' | 'vazão'
    ;

PRESSAO
    : 'pressao' | 'pressão'
    ;

PRESSAO_DESCARGA
    : 'pressao_descarga' | 'pressao_de_descarga' | 'pressao descarga' | 'pressao de descarga' | 'pressão_descarga' | 'pressão_de_descarga' | 'pressão descarga' | 'pressão de descarga'
    ;

PRESSAO_SUCCAO
    : 'pressao_succao' | 'pressao_de_succao' | 'pressao succao' | 'pressao de succao'
    | 'pressão_succao' | 'pressão_de_succao' | 'pressão succao' | 'pressão de succao'
    | 'pressao_succão' | 'pressao_de_succão' | 'pressao succão' | 'pressao de succão'
    | 'pressão_succão' | 'pressão_de_succão' | 'pressão succão' | 'pressão de succão' 
    | 'pressao_sucçao' | 'pressao_de_sucçao' | 'pressao sucçao' | 'pressao de sucçao' 
    | 'pressao_sucção' | 'pressao_de_sucção' | 'pressao sucção' | 'pressao de sucção' 
    | 'pressão_sucçao' | 'pressão_de_sucçao' | 'pressão sucçao' | 'pressão de sucçao'
    | 'pressão_sucção' | 'pressão_de_sucção' | 'pressão sucção' | 'pressão de sucção' 
    ;

VIBRACAO
    : 'vibracao' | 'vibraçao' | 'vibracão' | 'vibração'
    ;

TEMPERATURA
    : 'temperatura'
    ;

ROTACAO
    : 'rotacao' | 'rotaçao' | 'rotacão' | 'rotação' 
    ;

VAZAMENTO
    : 'vazamento'
    ;

HORAS_OPERACAO
    : 'horas_operacao' | 'horas operacao'
    | 'horas_operacão' | 'horas operacão'
    | 'horas_operaçao' | 'horas operaçao'
    | 'horas_operação' | 'horas operação'
    ;


// ==================================================
// ESTADOS DE INSPECAO
// ==================================================

NORMAL
    : 'normal'
    ;

ANORMAL
    : 'anormal'
    ;

AUSENTE
    : 'ausente'
    ;

LEVE
    : 'leve'
    ;

MODERADO
    : 'moderado'
    ;

GRAVE
    : 'grave'
    ;


// ==================================================
// MANUTENCAO
// ==================================================

A_CADA
    : 'a_cada' | 'a cada' | 'cada'
    ;

DE
    : 'de'
    ;

QUANDO
    : 'quando'
    ;

PROCEDIMENTO
    : 'procedimento'
    ;

PRIORIDADE
    : 'prioridade'
    ;

DURACAO
    : 'duracao' | 'duraçao' | 'duracão' | 'duração'
    ;

HOMEM_HORA
    : 'homem_hora' | 'homem hora' | 'hh' | 'HH'
    ;

PRAZO
    : 'prazo'
    ;


// ==================================================
// PRIORIDADES
// ==================================================

BAIXA
    : 'baixa'
    ;

MEDIA
    : 'media' | 'média'
    ;

ALTA
    : 'alta'
    ;

CRITICA
    : 'critica' |  'crítica'
    ;


// ==================================================
// COMPARADORES
// ==================================================

IGUAL
    : 'igual'
    ;

DIFERENTE
    : 'diferente'
    ;

MAIOR
    : 'maior'
    ;

MAIOR_OU_IGUAL
    : 'maior_ou_igual' |  'maior_igual' |  'maior ou igual' |  'maior igual'
    ;

MENOR
    : 'menor'
    ;

MENOR_OU_IGUAL
    : 'menor_ou_igual' | 'menor_igual' | 'menor ou igual' | 'menor igual'
    ;


// ==================================================
// OPERADORES LOGICOS
// ==================================================

E
    : 'e'
    ;

OU
    : 'ou'
    ;


// ==================================================
// CAMPOS DOS REGISTROS
// ==================================================

ORIGEM
    : 'origem'
    ;

TEMPO_EXECUCAO
    : 'tempo_execucao' | 'tempo_de_execucao' | 'tempo execucao' | 'tempo de execucao'
    | 'tempo_execucão' | 'tempo_de_execucão' | 'tempo execucão' | 'tempo de execucão'
    | 'tempo_execuçao' | 'tempo_de_execuçao' | 'tempo execuçao' | 'tempo de execuçao'
    | 'tempo_execução' | 'tempo_de_execução' | 'tempo execução' | 'tempo de execução'
    ;

STATUS
    : 'status'
    ;

RELATORIO
    : 'relatorio' | 'relatório'
    ;

VALORES
    : 'valores'
    ;

DATA
    : 'data'
    ;

OBSERVACAO
    : 'observacao' | 'observaçao' | 'observacão' | 'observação' | 'obs' | 'OBS'
    ;


// ==================================================
// STATUS DO REGISTRO
// ==================================================

EM_ABERTO
    : 'em_aberto' | 'em aberto'
    ;

EM_EXECUCAO
    : 'em_execucao' | 'em execucao' 
    | 'em_execucão' | 'em execucão'
    | 'em_execuçao' | 'em execuçao'
    | 'em_execução' | 'em execução'
    ;

CONCLUIDO
    : 'concluido' | 'concluído'
    ;

CANCELADO
    : 'cancelado'
    ;


// ==================================================
// UNIDADES DE MEDIDA
// ==================================================

MM_POR_S
    : 'mm_por_seg' | 'mm_seg' | 'mm por seg' | 'mm seg' | 'mm/s' | 'mm/seg'
    ;

CELSIUS
    : 'celsius' | 'C' 
    ;

BAR
    : 'bar'
    ;

M3_POR_HORA
    : 'm3_por_hora' | 'm3_hora' | 'm3 por hora' | 'm3 hora' | 'm3/h' | 'm3/hora'
    ;

HORA
    : 'hora'
    ;

MINUTO
    : 'minuto'
    ;

DIA
    : 'dia'
    ;

RPM
    : 'rpm'
    ;

LITRO
    : 'litro'
    ;


// ==================================================
// SIMBOLOS
// ==================================================

ABRE_CHAVE
    : '{'
    ;

FECHA_CHAVE
    : '}'
    ;

ABRE_PARENTESE
    : '('
    ;

FECHA_PARENTESE
    : ')'
    ;

IGUAL_ATRIBUICAO
    : '='
    ;

VIRGULA
    : ','
    ;

DOIS_PONTOS
    : ':'
    ;

BARRA
    : '/'
    ;

TRACO
    : '-'
    ;

// ==================================================
// VALORES GERAIS
// ==================================================

NUMERO
    : [0-9]+ ('.' [0-9]+)?
    ;

TEXTO
    : '"' ~["\r\n]* '"'
    ;

IDENTIFICADOR
    : [a-zA-Z_] [a-zA-Z0-9_]*
    ;


// ==================================================
// COMENTARIOS E ESPACOS
// ==================================================

COMENTARIO
    : '//' ~[\r\n]* -> skip
    ;

ESPACO
    : [ \t\r\n]+ -> skip
    ;
