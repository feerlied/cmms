<?php /** @noinspection ALL */

declare(strict_types=1);

namespace Infrastructure\Persistence;

use Domain\CaracteristicaProcesso;
use Domain\CaracteristicaProcessoCollection;
use Domain\Enums\TipoEquipamento;
use Domain\Enums\TipoProduto;
use Domain\Enums\TipoServico;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\VariavelControladaCollection;
use PDO;
use Throwable;

final readonly class EquipamentoRepository {
    public function __construct(private PDO $pdo) {}

    public function save(Equipamento $equipamento, \DateTimeImmutable $primeiro_cadastro): void {
        $transacao_existente = $this->pdo->inTransaction();
        if ($transacao_existente) {
            $this->pdo->exec('SAVEPOINT equipamento_repository_save');
        } else {
            $this->pdo->beginTransaction();
        }

        try {
            $comando = $this->pdo->prepare(
                'INSERT INTO equipamento
                    (nome, primeiro_cadastro, tipo, servico, produto)
                 VALUES (:nome, :primeiro_cadastro, :tipo, :servico, :produto)'
            );
            $comando->execute([
                'nome' => $equipamento->nome,
                'primeiro_cadastro' => $primeiro_cadastro
                    ->setTimezone(new \DateTimeZone('UTC'))
                    ->format('Y-m-d H:i:s'),
                'tipo' => $equipamento->tipo->value,
                'servico' => $equipamento->servico->value,
                'produto' => $equipamento->produto->value,
            ]);
            $equipamento_id = (int) $this->pdo->lastInsertId();

            $comando_caracteristica = $this->pdo->prepare(
                'INSERT INTO equipamento_caracteristica
                    (equipamento_id, posicao, variavel, valor, valor_tipo, unidade)
                 VALUES (:equipamento_id, :posicao, :variavel, :valor, :valor_tipo, :unidade)'
            );
            foreach ($equipamento->caracteristicas_processo->all() as $posicao => $caracteristica) {
                $comando_caracteristica->execute([
                    'equipamento_id' => $equipamento_id,
                    'posicao' => $posicao,
                    'variavel' => $caracteristica->variavel->value,
                    'valor' => $caracteristica->valor,
                    'valor_tipo' => is_int($caracteristica->valor) ? 'int' : 'float',
                    'unidade' => $caracteristica->unidade->value,
                ]);
            }

            $comando_variavel = $this->pdo->prepare(
                'INSERT INTO equipamento_variavel (equipamento_id, posicao, variavel)
                 VALUES (:equipamento_id, :posicao, :variavel)'
            );
            foreach ($equipamento->variaveis_controladas->all() as $posicao => $variavel) {
                $comando_variavel->execute([
                    'equipamento_id' => $equipamento_id,
                    'posicao' => $posicao,
                    'variavel' => $variavel->value,
                ]);
            }

            if ($transacao_existente) {
                $this->pdo->exec('RELEASE SAVEPOINT equipamento_repository_save');
            } else {
                $this->pdo->commit();
            }
        } catch (Throwable $erro) {
            if ($transacao_existente) {
                $this->pdo->exec('ROLLBACK TO SAVEPOINT equipamento_repository_save');
                $this->pdo->exec('RELEASE SAVEPOINT equipamento_repository_save');
            } else {
                $this->pdo->rollBack();
            }
            throw $erro;
        }
    }

    public function findByIdentifier(string $identificador): ?Equipamento {
        $comando = $this->pdo->prepare(
            'SELECT id, nome, tipo, servico, produto FROM equipamento WHERE nome = :nome'
        );
        $comando->execute(['nome' => $identificador]);
        $linha = $comando->fetch(PDO::FETCH_ASSOC);

        return $linha === false ? null : $this->hydrate($linha);
    }

    public function findFirstRegistrationAt(string $identificador): ?\DateTimeImmutable {
        $comando = $this->pdo->prepare(
            'SELECT primeiro_cadastro FROM equipamento WHERE nome = :nome'
        );
        $comando->execute(['nome' => $identificador]);
        $linha = $comando->fetch(PDO::FETCH_ASSOC);

        if ($linha === false) {
            return null;
        }

        return new \DateTimeImmutable($linha['primeiro_cadastro'], new \DateTimeZone('UTC'));
    }

    /** @return list<Equipamento> */
    public function findAll(): array {
        $comando = $this->pdo->prepare(
            'SELECT id, nome, tipo, servico, produto FROM equipamento ORDER BY nome'
        );
        $comando->execute();

        return array_map($this->hydrate(...), $comando->fetchAll(PDO::FETCH_ASSOC));
    }

    /** @param array{id: int|string, nome: string, tipo: string, servico: string, produto: string} $linha */
    private function hydrate(array $linha): Equipamento {
        $comando_caracteristicas = $this->pdo->prepare(
            'SELECT variavel, valor, valor_tipo, unidade FROM equipamento_caracteristica
             WHERE equipamento_id = :equipamento_id ORDER BY posicao'
        );
        $comando_caracteristicas->execute(['equipamento_id' => $linha['id']]);
        $caracteristicas = array_map(
            static fn (array $caracteristica): CaracteristicaProcesso => new CaracteristicaProcesso(
                VariavelControlada::from($caracteristica['variavel']),
                $caracteristica['valor_tipo'] === 'int'
                    ? (int) $caracteristica['valor']
                    : (float) $caracteristica['valor'],
                UnidadeMedida::from($caracteristica['unidade'])
            ),
            $comando_caracteristicas->fetchAll(PDO::FETCH_ASSOC)
        );

        $comando_variaveis = $this->pdo->prepare(
            'SELECT variavel FROM equipamento_variavel
             WHERE equipamento_id = :equipamento_id ORDER BY posicao'
        );
        $comando_variaveis->execute(['equipamento_id' => $linha['id']]);
        $variaveis = array_map(
            static fn (array $variavel): VariavelControlada => VariavelControlada::from($variavel['variavel']),
            $comando_variaveis->fetchAll(PDO::FETCH_ASSOC)
        );

        return new Equipamento(
            nome: $linha['nome'],
            tipo: TipoEquipamento::from($linha['tipo']),
            servico: TipoServico::from($linha['servico']),
            produto: TipoProduto::from($linha['produto']),
            caracteristicas_processo: new CaracteristicaProcessoCollection(...$caracteristicas),
            variaveis_controladas: new VariavelControladaCollection(...$variaveis),
        );
    }
}
