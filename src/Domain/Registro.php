<?php

namespace Domain;


readonly class Registro {
    public function __construct(
        public string $nome,
        public string $equipamento_identificador,
        public \DateTimeImmutable $data,
        public ValorRegistradoCollection $valores,
        public ?ExecucaoRegistro $execucao = null,
        public ?string $relatorio = null,
        public ?string $observacao = null
    ) {
        if ($valores->all() === []) {
            throw new \InvalidArgumentException('O registro exige pelo menos um valor.');
        }
    }
}
