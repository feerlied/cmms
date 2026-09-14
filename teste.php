<?php

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/src/Generated/CMMSLexer.php';
require_once __DIR__ . '/src/Generated/CMMSParser.php';
require_once __DIR__ . '/src/Visitor/CMMSVisitor.php';

use Antlr\Antlr4\Runtime\CommonTokenStream;
use Antlr\Antlr4\Runtime\InputStream;
use Visitor\CMMSVisitor;

$arquivo = __DIR__ . '/examples/teste.cmms';

if (!file_exists($arquivo)) {
    die("Arquivo não encontrado: {$arquivo}" . PHP_EOL);
}

$codigo = file_get_contents($arquivo);

$input = InputStream::fromString($codigo);

$lexer = new CMMSLexer($input);
$tokens = new CommonTokenStream($lexer);
$parser = new CMMSParser($tokens);

$tree = $parser->programa();

echo "=== PARSE TREE ===" . PHP_EOL;

echo $tree->toStringTree(
    $parser->getRuleNames()
);

echo PHP_EOL . PHP_EOL;

echo "=== RESULTADO SEMÂNTICO ===" . PHP_EOL;

$visitor = new CMMSVisitor();

$resultado = $visitor->visit($tree);

var_dump($resultado);
