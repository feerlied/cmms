<?php

declare(strict_types=1);

namespace Application;

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Diagnostic\Diagnostic;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticSeverity;
use Visitor\CMMSVisitor;
use Visitor\Exception\InvalidRecordDateException;
use Visitor\Exception\UnrepresentableNumberException;

final class CMMSProcessor {

    public function process(string $codigo): ProcessingResult {
        $input = InputStream::fromString($codigo);
        $lexer = new \CMMSLexer($input);
        $lexer_listener = new AntlrDiagnosticListener(DiagnosticOrigin::LEXICAL);
        $lexer->removeErrorListeners();
        $lexer->addErrorListener($lexer_listener);

        $tokens = new CommonTokenStream($lexer);
        $tokens->fill();

        if ($lexer_listener->getDiagnostics() !== []) {
            return new ProcessingResult([], $lexer_listener->getDiagnostics());
        }

        $parser = new \CMMSParser($tokens);
        $parser_listener = new AntlrDiagnosticListener(DiagnosticOrigin::SYNTACTIC);
        $parser->removeErrorListeners();
        $parser->addErrorListener($parser_listener);
        $arvore = $parser->programa();

        if ($parser_listener->getDiagnostics() !== []) {
            return new ProcessingResult([], $parser_listener->getDiagnostics());
        }

        try {
            $objetos = new CMMSVisitor()->visit($arvore);
        } catch (InvalidRecordDateException $exception) {
            return new ProcessingResult([], [$this->createSemanticDiagnostic(
                'CMMS-SEM-011',
                $exception->getMessage(),
            )]);
        } catch (UnrepresentableNumberException $exception) {
            return new ProcessingResult([], [$this->createSemanticDiagnostic(
                'CMMS-SEM-013',
                $exception->getMessage(),
            )]);
        }

        $diagnosticos = (new SemanticValidator())->validate($objetos);

        return new ProcessingResult(
            objetos: $diagnosticos === [] ? $objetos : [],
            diagnosticos: $diagnosticos,
        );
    }

    private function createSemanticDiagnostic(string $codigo, string $mensagem): Diagnostic {
        return new Diagnostic(
            codigo: $codigo,
            mensagem: $mensagem,
            origem: DiagnosticOrigin::SEMANTIC,
            severidade: DiagnosticSeverity::ERROR,
        );
    }
}
