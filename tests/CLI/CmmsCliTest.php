<?php

declare(strict_types=1);

namespace CLI;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PDO;
use PHPUnit\Framework\TestCase;

final class CmmsCliTest extends TestCase
{
    /** @var list<string> */
    private array $arquivos_temporarios = [];

    protected function setUp(): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('O driver PDO SQLite não está disponível neste PHP.');
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->arquivos_temporarios as $arquivo) {
            if (is_file($arquivo)) {
                unlink($arquivo);
            }
        }
    }

    public function testeComandos_InitCadastroLeituraEPreventiva_ProcessosSeparadosGeramOrdensPersistidas(): void
    {
        $banco = $this->createTemporaryFile('');
        $cadastro = $this->createTemporaryFile($this->registrationCode());
        $leitura = $this->createTemporaryFile($this->readingCode());

        [$saida_init, $erros_init, $status_init] = $this->runForm('init', $banco);
        self::assertSame(0, $status_init);
        self::assertStringContainsString('Escolha a ação que deseja executar:', $saida_init);
        self::assertStringContainsString('Nome ou caminho do banco SQLite:', $saida_init);
        self::assertStringContainsString('Banco SQLite inicializado.', $saida_init);
        self::assertSame('', $erros_init);

        [$saida_cadastro, $erros_cadastro, $status_cadastro] = $this->runForm('cadastro', $banco, $cadastro);
        self::assertSame(0, $status_cadastro);
        self::assertStringContainsString('Como deseja informar o código da DSL?', $saida_cadastro);
        self::assertStringContainsString('Nome ou caminho do arquivo DSL:', $saida_cadastro);
        self::assertStringContainsString('Cadastro concluído.', $saida_cadastro);
        self::assertSame('', $erros_cadastro);

        [$saida_leitura, $erros_leitura, $status_leitura] = $this->runForm('leitura', $banco, $leitura);
        self::assertSame(0, $status_leitura);
        self::assertStringContainsString('OS corretiva: equipamento=bomba_01 manutencao=reparo_01 status=em_aberto', $saida_leitura);
        self::assertStringContainsString('prazo=1 dia', $saida_leitura);
        self::assertSame('', $erros_leitura);

        [$saida_preventiva, $erros_preventiva, $status_preventiva] = $this->runForm(
            'preventiva',
            $banco,
            'bomba_01',
            '2030-01-01T08:30:00-03:00',
        );
        self::assertSame(0, $status_preventiva);
        self::assertStringContainsString('OS preventiva: equipamento=bomba_01 manutencao=inspecao_01 status=em_aberto', $saida_preventiva);
        self::assertSame('', $erros_preventiva);

        $conexao = new PDO('sqlite:' . $banco);
        self::assertSame(1, (int)$conexao->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
        self::assertSame(2, (int)$conexao->query('SELECT COUNT(*) FROM manutencao')->fetchColumn());
        self::assertSame(1, (int)$conexao->query('SELECT COUNT(*) FROM registro')->fetchColumn());
        self::assertSame(2, (int)$conexao->query('SELECT COUNT(*) FROM ordem_servico')->fetchColumn());
    }

    public function testeComandos_PreventivaExecucaoEOrdensEmProcessosSeparados_ConcluiEAgendaProximaOcorrencia(): void
    {
        $agora = new DateTimeImmutable('now', new DateTimeZone('America/Sao_Paulo'));
        $banco = $this->createTemporaryFile('');
        $cadastro = $this->createTemporaryFile($this->registrationCode());
        $execucao = $this->createTemporaryFile($this->executionCode($agora->add(new DateInterval('P2D'))));
        $inicio = $agora->sub(new DateInterval('P1D'))->format(DateTimeInterface::ATOM);
        $primeira_avaliacao = $agora->add(new DateInterval('P2D'))->format(DateTimeInterface::ATOM);
        $proxima_avaliacao = $agora->add(new DateInterval('P3D'))->format(DateTimeInterface::ATOM);
        $fim = $agora->add(new DateInterval('P4D'))->format(DateTimeInterface::ATOM);

        $this->runForm('init', $banco);
        $this->runForm('cadastro', $banco, $cadastro);

        [$saida_primeira_preventiva, $erros_primeira_preventiva, $status_primeira_preventiva] = $this->runForm(
            'preventiva',
            $banco,
            'bomba_01',
            $primeira_avaliacao,
        );
        self::assertSame(0, $status_primeira_preventiva);
        self::assertSame('', $erros_primeira_preventiva);
        self::assertStringContainsString('OS preventiva: equipamento=bomba_01 manutencao=inspecao_01 status=em_aberto', $saida_primeira_preventiva);
        self::assertMatchesRegularExpression('/identificador=(os_[a-f0-9]+)/', $saida_primeira_preventiva);
        preg_match('/identificador=(os_[a-f0-9]+)/', $saida_primeira_preventiva, $primeira_ordem);

        [$saida_execucao, $erros_execucao, $status_execucao] = $this->runForm('execucao', $banco, $execucao);
        self::assertSame(0, $status_execucao);
        self::assertSame('', $erros_execucao);
        self::assertStringContainsString('Execução importada: os=' . $primeira_ordem[1] . ' status=concluida', $saida_execucao);

        [$saida_proxima_preventiva, $erros_proxima_preventiva, $status_proxima_preventiva] = $this->runForm(
            'preventiva',
            $banco,
            'bomba_01',
            $proxima_avaliacao,
        );
        self::assertSame(0, $status_proxima_preventiva);
        self::assertSame('', $erros_proxima_preventiva);
        self::assertStringContainsString('OS preventiva: equipamento=bomba_01 manutencao=inspecao_01 status=em_aberto', $saida_proxima_preventiva);
        self::assertMatchesRegularExpression('/identificador=(os_[a-f0-9]+)/', $saida_proxima_preventiva);
        preg_match('/identificador=(os_[a-f0-9]+)/', $saida_proxima_preventiva, $proxima_ordem);
        self::assertNotSame($primeira_ordem[1], $proxima_ordem[1]);

        [$saida_ordens, $erros_ordens, $status_ordens] = $this->runForm(
            'ordens',
            $banco,
            $inicio,
            $fim,
            'bomba_01',
        );
        self::assertSame(0, $status_ordens);
        self::assertSame('', $erros_ordens);
        self::assertStringContainsString('identificador=' . $primeira_ordem[1], $saida_ordens);
        self::assertStringContainsString('identificador=' . $proxima_ordem[1], $saida_ordens);
        self::assertStringContainsString('data_referencia=', $saida_ordens);
        self::assertStringContainsString('status=concluida', $saida_ordens);
        self::assertStringContainsString('status=em_aberto', $saida_ordens);

        [$saida_concluidas, $erros_concluidas, $status_concluidas] = $this->runForm(
            'ordens',
            $banco,
            $inicio,
            $fim,
            'bomba_01',
            'concluida',
        );
        self::assertSame(0, $status_concluidas);
        self::assertSame('', $erros_concluidas);
        self::assertStringContainsString('identificador=' . $primeira_ordem[1], $saida_concluidas);
        self::assertStringNotContainsString('identificador=' . $proxima_ordem[1], $saida_concluidas);
    }

    public function testeComandoLeitura_EntradaComErroLexico_ImprimeDiagnosticoERetornaFalhaDaDsl(): void
    {
        $banco = $this->createTemporaryFile('');
        $arquivo_invalido = $this->createTemporaryFile('@');

        $this->runForm('init', $banco);
        [$saida, $erros, $status] = $this->runForm('leitura', $banco, $arquivo_invalido);

        self::assertSame(1, $status);
        self::assertStringContainsString('Nome ou caminho do arquivo DSL:', $saida);
        self::assertStringContainsString('[CMMS-LEX-001] lexical:', $erros);
        self::assertStringContainsString('(linha 1, coluna 1)', $erros);
    }

    public function testeComandoPreventiva_InstanteForaDoFormatoIso_RetornaErroDeUso(): void
    {
        $banco = $this->createTemporaryFile('');

        $this->runForm('init', $banco);
        [$saida, $erros, $status] = $this->runForm('preventiva', $banco, 'bomba_01', '29/09/2026');

        self::assertSame(2, $status);
        self::assertStringContainsString('Instante da avaliação (ISO 8601):', $saida);
        self::assertStringContainsString('O instante deve estar no formato ISO 8601', $erros);
    }

    public function testeComandoExecucao_RegistroSemBlocoDeExecucao_RetornaDiagnosticoSemantico(): void
    {
        $banco = $this->createTemporaryFile('');
        $cadastro = $this->createTemporaryFile($this->registrationCode());
        $leitura = $this->createTemporaryFile($this->readingCode());

        $this->runForm('init', $banco);
        $this->runForm('cadastro', $banco, $cadastro);
        [$saida, $erros, $status] = $this->runForm('execucao', $banco, $leitura);

        self::assertSame(1, $status);
        self::assertStringContainsString('Nome ou caminho do arquivo DSL:', $saida);
        self::assertStringContainsString('[CMMS-EXE-001] semantic:', $erros);
    }

    public function testeComandoOrdens_IntervaloInvalido_RetornaErroOperacional(): void
    {
        $banco = $this->createTemporaryFile('');

        $this->runForm('init', $banco);
        [$saida, $erros, $status] = $this->runForm(
            'ordens',
            $banco,
            '2026-09-29T08:30:00-03:00',
            '2026-09-29T08:30:00-03:00',
        );

        self::assertSame(2, $status);
        self::assertStringContainsString('Fim do período (ISO 8601):', $saida);
        self::assertStringContainsString('O fim do período deve ser posterior ao início', $erros);
    }

    public function testeComandoCadastro_TextoDigitadoNoTerminal_ImportaSemArquivo(): void
    {
        $banco = $this->createTemporaryFile('');
        $entrada = implode("\n", [
            'cadastro',
            $banco,
            'texto',
            $this->registrationCode(),
            'FIM',
            '',
        ]);

        [$saida, $erros, $status] = $this->runInteractive($entrada);

        self::assertSame(0, $status);
        self::assertSame('', $erros);
        self::assertStringContainsString('Digite ou cole o código da DSL.', $saida);
        self::assertStringContainsString('Cadastro concluído.', $saida);

        $conexao = new PDO('sqlite:' . $banco);
        self::assertSame(1, (int)$conexao->query('SELECT COUNT(*) FROM equipamento')->fetchColumn());
        self::assertSame(2, (int)$conexao->query('SELECT COUNT(*) FROM manutencao')->fetchColumn());
    }

    /** @return array{string, string, int} */
    private function runForm(string $acao, string $banco, string ...$campos): array
    {
        if (in_array($acao, ['cadastro', 'leitura', 'execucao'], true)) {
            $campos = ['arquivo', ...$campos];
        }

        if ($acao === 'ordens') {
            $campos = array_pad($campos, 4, '');
        }

        return $this->runInteractive(implode("\n", [$acao, $banco, ...$campos, '']));
    }

    /** @return array{string, string, int} */
    private function runInteractive(string $entrada): array
    {
        $processo = proc_open(
            [PHP_BINARY, dirname(__DIR__, 2) . '/scripts/cmms.php'],
            [
                0 => ['pipe', 'r'],
                1 => ['pipe', 'w'],
                2 => ['pipe', 'w'],
            ],
            $pipes,
            dirname(__DIR__, 2),
        );

        if (!is_resource($processo)) {
            self::fail('Não foi possível iniciar o processo da CLI.');
        }

        fwrite($pipes[0], $entrada);
        fclose($pipes[0]);
        $saida = stream_get_contents($pipes[1]);
        $erros = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [$saida, $erros, proc_close($processo)];
    }

    private function createTemporaryFile(string $conteudo): string
    {
        $arquivo = tempnam(sys_get_temp_dir(), 'cmms-cli-');

        if ($arquivo === false) {
            self::fail('Não foi possível criar o arquivo temporário.');
        }

        if (file_put_contents($arquivo, $conteudo) === false) {
            self::fail('Não foi possível escrever o arquivo temporário.');
        }

        $this->arquivos_temporarios[] = $arquivo;

        return $arquivo;
    }

    private function registrationCode(): string
    {
        return <<<'DSL'
equipamento bomba_01 {
    tipo bomba_centrifuga
    servico bombeamento_de_agua
    produto agua
    caracteristicas_processo {
        pressao 10 bar
    }
    variaveis_controladas {
        pressao
    }
}

manutencao corretiva reparo_01 {
    equipamento bomba_01
    quando pressao maior 12 bar
    procedimento reparar_bomba
    prioridade alta
    duracao 2 hora
    prazo 1 dia
    homem_hora 2
}

manutencao preventiva inspecao_01 {
    equipamento bomba_01
    a_cada 1 dia
    procedimento inspecionar_bomba
    prioridade media
    duracao 1 hora
    homem_hora 1
}
DSL;
    }

    private function readingCode(): string
    {
        return <<<'DSL'
registro leitura_01 {
    equipamento bomba_01
    data 29/09/2026-09:00
    valores {
        pressao 13 bar
    }
}
DSL;
    }

    private function executionCode(DateTimeImmutable $data): string
    {
        $data_dsl = $data->format('d/m/Y-H:i');

        return <<<DSL
registro execucao_inspecao_01 {
    equipamento bomba_01
    data {$data_dsl}
    origem inspecao_01
    tempo_execucao 1 hora
    status concluido
    valores {
        pressao 10 bar
    }
}
DSL;
    }
}
