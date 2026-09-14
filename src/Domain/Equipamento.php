<?php

namespace Domain;

use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;

require_once __DIR__ . '/CaracteristicaProcessoCollection.php';
require_once __DIR__ . '/VariavelControladaCollection.php';
require_once __DIR__ . '/Enums/TipoEquipamento.php';
require_once __DIR__ . '/Enums/TipoServico.php';
require_once __DIR__ . '/Enums/TipoProduto.php';

class Equipamento
{
    public CaracteristicaProcessoCollection $caracteristicas_processo;
    public VariavelControladaCollection $variaveis_controladas;

    public function __construct(
        public string                     $nome,
        public TipoEquipamento            $tipo,
        public TipoServico                $servico,
        public TipoProduto                $produto,
        ?CaracteristicaProcessoCollection $caracteristicas_processo = null,
        ?VariavelControladaCollection $variaveis_controladas = null
    ) {
        $this->caracteristicas_processo = $caracteristicas_processo ?? new CaracteristicaProcessoCollection();
        $this->variaveis_controladas = $variaveis_controladas ?? new VariavelControladaCollection();
    }
}
