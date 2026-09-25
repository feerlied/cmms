<?php

declare(strict_types=1);

use Application\CMMSProcessor;

require dirname(__DIR__) . '/vendor/autoload.php';

function normalizeValue(mixed $valor): mixed {
    if ($valor instanceof BackedEnum) {
        return $valor->value;
    }

    if ($valor instanceof DateTimeInterface) {
        return $valor->format('d/m/Y-H:i');
    }

    if (is_array($valor)) {
        return array_map(normalizeValue(...), $valor);
    }

    if (!is_object($valor)) {
        return $valor;
    }

    if (method_exists($valor, 'all')) {
        return normalizeValue($valor->all());
    }

    $campos = ['classe' => (new ReflectionClass($valor))->getShortName()];

    foreach (get_object_vars($valor) as $nome => $conteudo) {
        $campos[$nome] = normalizeValue($conteudo);
    }

    return $campos;
}

if ($argc !== 2 || basename($argv[1]) !== $argv[1] || !str_ends_with($argv[1], '.cmms')) {
    fwrite(STDERR, "Uso: php scripts/run-demo.php nome_do_arquivo.cmms\n");
    exit(2);
}

$nome_arquivo = $argv[1];
$arquivo = dirname(__DIR__) . '/examples/' . $nome_arquivo;

if (!is_file($arquivo) || !is_readable($arquivo)) {
    fwrite(STDERR, "Arquivo não encontrado ou ilegível: {$nome_arquivo}\n");
    exit(2);
}

$codigo = file_get_contents($arquivo);

if ($codigo === false) {
    fwrite(STDERR, "Não foi possível ler: {$nome_arquivo}\n");
    exit(2);
}

$resultado = (new CMMSProcessor())->process($codigo);
echo "=== {$nome_arquivo} ===\n";
echo "Status: {$resultado->status->value}\n";

if ($resultado->isSuccess()) {
    echo json_encode(
        normalizeValue($resultado->objetos),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
    ), "\n";
} else {
    foreach ($resultado->diagnosticos as $diagnostico) {
        $codigo_diagnostico = $diagnostico->codigo ?? 'SEM-CODIGO';
        echo "[{$codigo_diagnostico}] {$diagnostico->mensagem}\n";
    }
}

exit($resultado->isSuccess() ? 0 : 1);
