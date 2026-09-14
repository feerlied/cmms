<?php

namespace Domain;

require_once __DIR__ . '/CaracteristicaProcessoCollection.php';
require_once __DIR__ . '/VariavelControladaCollection.php';

class Equipamento
{
    public CaracteristicaProcessoCollection $caracteristicas_processo;
    public VariavelControladaCollection $variaveis_controladas;

    public function __construct(
        public string                     $nome,
        public string                     $tipo,
        public string                     $servico,
        public string                     $produto,
        ?CaracteristicaProcessoCollection $caracteristicas_processo = null,
        ?VariavelControladaCollection $variaveis_controladas = null
    ) {
        $this->caracteristicas_processo = $caracteristicas_processo ?? new CaracteristicaProcessoCollection();
        $this->variaveis_controladas = $variaveis_controladas ?? new VariavelControladaCollection();
    }
}
