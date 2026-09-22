<?php

namespace Application;

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Visitor\CMMSVisitor;

final class CMMSProcessor {
    /**
     * @return list<\Domain\Equipamento|\Domain\Manutencao|\Domain\Registro>
     */
    public function process(string $codigo): array {
        $input = InputStream::fromString($codigo);
        $lexer = new \CMMSLexer($input);
        $tokens = new CommonTokenStream($lexer);
        $parser = new \CMMSParser($tokens);
        $arvore = $parser->programa();

        return new CMMSVisitor()->visit($arvore);
    }
}
