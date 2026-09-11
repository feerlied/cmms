<?php

class Equipamento
{
    public function __construct(
        public string $nome,
        public string $tipo,
        public string $servico,
        public string $produto,
        public array $caracteristicasProcesso = [],
        public array $variaveisControladas = []
    ) {
    }
}

finalizar um demo
 -> ver se semantica esta ok -> unidade de medida errada
corner cases
tratamento de erros
ver exemplos no github da oficina de captura de erros -> lexicos e sintaticos 
