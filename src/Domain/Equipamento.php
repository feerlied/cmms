<?php

namespace Domain;

use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;

readonly class Equipamento {
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
