<?php

declare(strict_types=1);

use Application\AvaliacaoPreventivaService;
use Application\CadastroService;
use Application\CMMSProcessor;
use Application\ImportacaoExecucaoService;
use Application\ImportacaoLeituraService;
use Application\ProcessingResult;
use Diagnostic\Diagnostic;
use Domain\CalculadoraPrimeiroVencimentoPreventivo;
use Domain\Enums\StatusOrdemServico;
use Domain\GeradorOrdemServicoCorretiva;
use Domain\GeradorOrdemServicoPreventiva;
use Domain\OrdemServico;
use Infrastructure\Persistence\EquipamentoRepository;
use Infrastructure\Persistence\ExecucaoOrdemServicoRepository;
use Infrastructure\Persistence\ManutencaoRepository;
use Infrastructure\Persistence\OrdemServicoRepository;
use Infrastructure\Persistence\RegistroRepository;
use Infrastructure\Persistence\SqliteDatabase;

require dirname(__DIR__) . '/vendor/autoload.php';

const EXIT_SUCCESS = 0;
const EXIT_DSL_FAILURE = 1;
const EXIT_USAGE_OR_OPERATION_FAILURE = 2;

function promptField(string $rotulo, bool $obrigatorio = true): string {
    while (true) {
        echo $rotulo;
        $entrada = fgets(STDIN);

        if ($entrada === false) {
            throw new InvalidArgumentException('A entrada foi encerrada antes do preenchimento do formulário.');
        }

        $valor = trim($entrada);

        if (!$obrigatorio || $valor !== '') {
            return $valor;
        }

        echo "Este campo é obrigatório. Tente novamente.\n";
    }
}

/** @param array<string, string> $opcoes */
function promptChoice(string $rotulo, array $opcoes): string {
    while (true) {
        $escolha = mb_strtolower(promptField($rotulo));

        if (isset($opcoes[$escolha])) {
            return $opcoes[$escolha];
        }

        echo "Opção inválida. Tente novamente.\n";
    }
}

function promptAction(): string {
    echo <<<TXT
CMMS DSL

Escolha a ação que deseja executar:
  1. Inicializar banco
  2. Cadastrar equipamentos e manutenções
  3. Importar leitura
  4. Importar execução
  5. Avaliar manutenções preventivas
  6. Consultar ordens de serviço

TXT;

    return promptChoice('Ação [1-6]: ', [
        '1' => 'init',
        'init' => 'init',
        '2' => 'cadastro',
        'cadastro' => 'cadastro',
        '3' => 'leitura',
        'leitura' => 'leitura',
        '4' => 'execucao',
        'execução' => 'execucao',
        'execucao' => 'execucao',
        '5' => 'preventiva',
        'preventiva' => 'preventiva',
        '6' => 'ordens',
        'ordens' => 'ordens',
    ]);
}

function printDiagnostics(ProcessingResult $resultado): void {
    foreach ($resultado->diagnosticos as $diagnostico) {
        printDiagnostic($diagnostico);
    }
}

function printDiagnostic(Diagnostic $diagnostico): void {
    $codigo = $diagnostico->codigo ?? 'SEM-CODIGO';
    $localizacao = '';

    if ($diagnostico->range !== null) {
        $localizacao = sprintf(
            ' (linha %d, coluna %d)',
            $diagnostico->range->inicio->linha + 1,
            $diagnostico->range->inicio->coluna + 1,
        );
    }

    fwrite(
        STDERR,
        sprintf('[%s] %s: %s%s%s', $codigo, $diagnostico->origem->value, $diagnostico->mensagem, $localizacao, PHP_EOL),
    );
}

function printOrders(array $ordens_servico): void {
    if ($ordens_servico === []) {
        echo "Nenhuma ordem de serviço gerada.\n";
        return;
    }

    foreach ($ordens_servico as $ordem_servico) {
        if (!$ordem_servico instanceof OrdemServico) {
            throw new LogicException('O serviço retornou uma ordem de serviço inválida.');
        }

        $prazo = $ordem_servico->prazo === null
            ? '-'
            : $ordem_servico->prazo->valor . ' ' . $ordem_servico->prazo->unidade->value;

        printf(
            "OS %s: equipamento=%s manutencao=%s status=%s identificador=%s data_referencia=%s prazo=%s evento=%s\n",
            $ordem_servico->tipo_manutencao->value,
            $ordem_servico->equipamento_identificador,
            $ordem_servico->manutencao_identificador,
            $ordem_servico->status->value,
            $ordem_servico->identificador,
            $ordem_servico->data_referencia->format(DateTimeInterface::ATOM),
            $prazo,
            $ordem_servico->chave_evento,
        );
    }
}

function readDslFile(string $caminho): string {
    if (!is_file($caminho) || !is_readable($caminho)) {
        throw new InvalidArgumentException("Arquivo DSL não encontrado ou ilegível: {$caminho}");
    }

    $codigo = file_get_contents($caminho);

    if ($codigo === false) {
        throw new RuntimeException("Não foi possível ler o arquivo DSL: {$caminho}");
    }

    return $codigo;
}

function promptDslCode(): string {
    echo <<<TXT

Como deseja informar o código da DSL?
  1. Ler de um arquivo
  2. Digitar ou colar o texto no terminal

TXT;

    $origem = promptChoice('Origem do código [1-2]: ', [
        '1' => 'arquivo',
        'arquivo' => 'arquivo',
        '2' => 'texto',
        'texto' => 'texto',
        'terminal' => 'texto',
    ]);

    if ($origem === 'arquivo') {
        return readDslFile(promptField('Nome ou caminho do arquivo DSL: '));
    }

    echo "Digite ou cole o código da DSL. Para concluir, digite FIM sozinho em uma linha.\n";
    $linhas = [];

    while (($linha = fgets(STDIN)) !== false) {
        if (rtrim($linha, "\r\n") === 'FIM') {
            $codigo = implode('', $linhas);

            if (trim($codigo) === '') {
                throw new InvalidArgumentException('O texto da DSL não pode estar vazio.');
            }

            return $codigo;
        }

        $linhas[] = $linha;
    }

    throw new InvalidArgumentException('A entrada foi encerrada sem a linha FIM.');
}

function parseIsoInstant(string $texto): DateTimeImmutable {
    $instante = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $texto);
    $erros = DateTimeImmutable::getLastErrors();

    if ($instante === false || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0))) {
        throw new InvalidArgumentException('O instante deve estar no formato ISO 8601, por exemplo 2026-09-29T08:30:00-03:00.');
    }

    return $instante;
}

function parseOrderStatus(?string $texto): ?StatusOrdemServico {
    if ($texto === null) {
        return null;
    }

    $status = StatusOrdemServico::tryFrom($texto);

    if ($status === null) {
        throw new InvalidArgumentException(
            'O status deve ser em_aberto, em_execucao, concluida ou cancelada.',
        );
    }

    return $status;
}

function createCadastroService(SqliteDatabase $banco): CadastroService {
    return new CadastroService(
        $banco,
        new EquipamentoRepository($banco->connection()),
        new ManutencaoRepository($banco),
        new CMMSProcessor(),
    );
}

function createImportacaoLeituraService(SqliteDatabase $banco): ImportacaoLeituraService {
    return new ImportacaoLeituraService(
        $banco,
        new CMMSProcessor(),
        new EquipamentoRepository($banco->connection()),
        new ManutencaoRepository($banco),
        new RegistroRepository($banco->connection()),
        new OrdemServicoRepository($banco),
        new GeradorOrdemServicoCorretiva(),
    );
}

function createImportacaoExecucaoService(SqliteDatabase $banco): ImportacaoExecucaoService {
    return new ImportacaoExecucaoService(
        $banco,
        new CMMSProcessor(),
        new EquipamentoRepository($banco->connection()),
        new ManutencaoRepository($banco),
        new RegistroRepository($banco->connection()),
        new OrdemServicoRepository($banco),
        new ExecucaoOrdemServicoRepository($banco->connection()),
    );
}

function createAvaliacaoPreventivaService(SqliteDatabase $banco): AvaliacaoPreventivaService {
    return new AvaliacaoPreventivaService(
        $banco,
        new EquipamentoRepository($banco->connection()),
        new ManutencaoRepository($banco),
        new OrdemServicoRepository($banco),
        new CalculadoraPrimeiroVencimentoPreventivo(),
        new GeradorOrdemServicoPreventiva(),
    );
}

if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
    fwrite(STDERR, "O driver PDO SQLite não está disponível neste PHP.\n");
    exit(EXIT_USAGE_OR_OPERATION_FAILURE);
}

try {
    if ($argc !== 1) {
        throw new InvalidArgumentException(
            'Esta CLI usa um formulário interativo. Execute apenas: php scripts/cmms.php',
        );
    }

    $comando = promptAction();
    $caminho_banco = promptField('Nome ou caminho do banco SQLite: ');
    $banco = new SqliteDatabase($caminho_banco);
    $banco->initializeSchema();

    if ($comando === 'init') {
        echo "Banco SQLite inicializado.\n";
        exit(EXIT_SUCCESS);
    }

    if ($comando === 'cadastro') {
        $resultado = createCadastroService($banco)->import(promptDslCode(), new DateTimeImmutable());

        if (!$resultado->isSuccess()) {
            printDiagnostics($resultado);
            exit(EXIT_DSL_FAILURE);
        }

        echo "Cadastro concluído.\n";
        exit(EXIT_SUCCESS);
    }

    if ($comando === 'leitura') {
        $resultado = createImportacaoLeituraService($banco)->import(promptDslCode());

        if (!$resultado->isSuccess()) {
            printDiagnostics($resultado->processamento);
            exit(EXIT_DSL_FAILURE);
        }

        printOrders($resultado->ordens_servico);
        exit(EXIT_SUCCESS);
    }

    if ($comando === 'execucao') {
        $resultado = createImportacaoExecucaoService($banco)->import(promptDslCode());

        if (!$resultado->isSuccess()) {
            printDiagnostics($resultado->processamento);
            exit(EXIT_DSL_FAILURE);
        }

        if ($resultado->ordem_servico === null) {
            throw new RuntimeException('A execução concluída não retornou uma ordem de serviço.');
        }

        printf(
            "Execução importada: os=%s status=%s\n",
            $resultado->ordem_servico->identificador,
            $resultado->ordem_servico->status->value,
        );
        exit(EXIT_SUCCESS);
    }

    if ($comando === 'preventiva') {
        $equipamento = promptField('Identificador do equipamento: ');
        $instante = promptField('Instante da avaliação (ISO 8601): ');
        $ordens_servico = createAvaliacaoPreventivaService($banco)->evaluate(
            $equipamento,
            parseIsoInstant($instante),
        );
        printOrders($ordens_servico);
        exit(EXIT_SUCCESS);
    }

    if ($comando === 'ordens') {
        $inicio = promptField('Início do período (ISO 8601): ');
        $fim = promptField('Fim do período (ISO 8601): ');
        $equipamento = promptField('Equipamento (opcional, Enter para todos): ', false);
        $status = promptField(
            'Status (opcional: em_aberto, em_execucao, concluida ou cancelada): ',
            false,
        );
        $ordens_servico = (new OrdemServicoRepository($banco))->findByReferencePeriod(
            parseIsoInstant($inicio),
            parseIsoInstant($fim),
            $equipamento === '' ? null : $equipamento,
            parseOrderStatus($status === '' ? null : $status),
        );
        printOrders($ordens_servico);
        exit(EXIT_SUCCESS);
    }
} catch (Throwable $erro) {
    fwrite(STDERR, "Erro operacional: {$erro->getMessage()}\n");
    exit(EXIT_USAGE_OR_OPERATION_FAILURE);
}
