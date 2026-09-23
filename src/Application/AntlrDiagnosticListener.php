<?php

declare(strict_types=1);

namespace Application;

use Antlr\Antlr4\Runtime\CharStream;
use Antlr\Antlr4\Runtime\Error\Exceptions\RecognitionException;
use Antlr\Antlr4\Runtime\Error\Listeners\BaseErrorListener;
use Antlr\Antlr4\Runtime\Lexer;
use Antlr\Antlr4\Runtime\Recognizer;
use Antlr\Antlr4\Runtime\Token;
use Diagnostic\Diagnostic;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticPosition;
use Diagnostic\DiagnosticRange;
use Diagnostic\DiagnosticSeverity;

final class AntlrDiagnosticListener extends BaseErrorListener {
    /** @var list<Diagnostic> */
    private array $diagnosticos = [];

    public function __construct(
        private readonly DiagnosticOrigin $origem,
    ) {}

    public function syntaxError(
        Recognizer $recognizer,
        ?object $offendingSymbol,
        int $line,
        int $charPositionInLine,
        string $msg,
        ?RecognitionException $exception,
    ): void {
        $inicio = new DiagnosticPosition(
            linha: max(0, $line - 1),
            coluna: max(0, $charPositionInLine),
        );

        $this->diagnosticos[] = new Diagnostic(
            codigo: $this->createCode(),
            mensagem: $msg,
            origem: $this->origem,
            severidade: DiagnosticSeverity::ERROR,
            range: $this->createRange($inicio, $recognizer, $offendingSymbol),
        );
    }

    /**
     * @return list<Diagnostic>
     */
    public function getDiagnostics(): array {
        return $this->diagnosticos;
    }

    private function createCode(): string {
        return match ($this->origem) {
            DiagnosticOrigin::LEXICAL => 'CMMS-LEX-001',
            DiagnosticOrigin::SYNTACTIC => 'CMMS-SYN-001',
            default => throw new \LogicException('O listener do ANTLR aceita apenas diagnósticos léxicos ou sintáticos.'),
        };
    }

    private function createRange(
        DiagnosticPosition $inicio,
        Recognizer $recognizer,
        ?object $offending_symbol,
    ): DiagnosticRange {
        $texto = $this->getOffendingText($recognizer, $offending_symbol);

        return new DiagnosticRange(
            inicio: $inicio,
            fim: $this->advancePosition($inicio, $texto),
        );
    }

    private function getOffendingText(Recognizer $recognizer, ?object $offending_symbol): string {
        if ($offending_symbol instanceof Token) {
            return $offending_symbol->getType() === Token::EOF
                ? ''
                : ($offending_symbol->getText() ?? '');
        }

        if (!$recognizer instanceof Lexer) {
            return '';
        }

        $input = $recognizer->getInputStream();

        if (!$input instanceof CharStream
            || $recognizer->tokenStartCharIndex < 0
            || $recognizer->getCharIndex() < $recognizer->tokenStartCharIndex) {
            return '';
        }

        return $input->getText(
            $recognizer->tokenStartCharIndex,
            $recognizer->getCharIndex(),
        );
    }

    private function advancePosition(DiagnosticPosition $inicio, string $texto): DiagnosticPosition {
        if ($texto === '') {
            return $inicio;
        }

        $linhas = explode("\n", $texto);

        if (count($linhas) === 1) {
            return new DiagnosticPosition(
                linha: $inicio->linha,
                coluna: $inicio->coluna + mb_strlen($texto),
            );
        }

        return new DiagnosticPosition(
            linha: $inicio->linha + count($linhas) - 1,
            coluna: mb_strlen($linhas[array_key_last($linhas)]),
        );
    }
}
