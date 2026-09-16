<?php

namespace Domain;


class ValorRegistradoCollection {
    /** @var list<ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro> */
    private array $itens = [];

    public function __construct(ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro ...$itens) {
        $this->itens = $itens;
    }

    public function add(ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro $valor): void {
        $this->itens[] = $valor;
    }

    /** @return list<ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro> */
    public function all(): array {
        return $this->itens;
    }
}
