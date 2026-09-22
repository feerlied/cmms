<?php

declare(strict_types=1);

namespace Diagnostic;

final readonly class Diagnostic {

    public function __construct(
        public ?string $codigo,
        public string $mensagem,
        public DiagnosticOrigin $origem,
        public DiagnosticSeverity $severidade,
        public ?DiagnosticRange $range = null,
    ) {
        if ($codigo !== null && trim($codigo) === '') {
            throw new \InvalidArgumentException('O código do diagnóstico não pode ser vazio.');
        }

        if (trim($mensagem) === '') {
            throw new \InvalidArgumentException('A mensagem do diagnóstico não pode ser vazia.');
        }
    }
}
