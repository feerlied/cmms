<?php

declare(strict_types=1);

namespace Application;

use Diagnostic\Diagnostic;
use Diagnostic\DiagnosticOrigin;
use Diagnostic\DiagnosticSeverity;
use Domain\CaracteristicaProcesso;
use Domain\CondicaoCorretiva;
use Domain\CondicaoNumerica;
use Domain\CondicaoObservacao;
use Domain\CondicaoVazamento;
use Domain\Enums\TipoManutencao;
use Domain\Enums\UnidadeMedida;
use Domain\Enums\VariavelControlada;
use Domain\Equipamento;
use Domain\HorasOperacaoRegistro;
use Domain\Manutencao;
use Domain\ObservacaoVisualRegistro;
use Domain\Registro;
use Domain\Tempo;
use Domain\ValorNumericoRegistro;
use Domain\VazamentoRegistro;

final class SemanticValidator {
    /**
     * @param list<Equipamento|Manutencao|Registro> $objetos
     * @return list<Diagnostic>
     */
    public function validate(array $objetos): array {
        $indices = $this->createIndexes($objetos);
        $diagnosticos = [];
        $identificadores_vistos = [];

        foreach ($objetos as $objeto) {
            $identificador = $this->getIdentifier($objeto);

            if (isset($identificadores_vistos[$identificador])) {
                $diagnosticos[] = $this->createDiagnostic(
                    'CMMS-SEM-001',
                    "O identificador '{$identificador}' já foi declarado."
                );
            }

            $identificadores_vistos[$identificador] = true;

            if ($objeto instanceof Equipamento) {
                array_push($diagnosticos, ...$this->validateEquipment($objeto));
                continue;
            }

            if ($objeto instanceof Manutencao) {
                array_push($diagnosticos, ...$this->validateMaintenance($objeto, $indices['equipamentos_por_identificador']));
                continue;
            }

            array_push($diagnosticos, ...$this->validateRecord($objeto, $indices['equipamentos_por_identificador']));
        }

        return $diagnosticos;
    }

    /**
     * @param list<Equipamento|Manutencao|Registro> $objetos
     * @return array{equipamentos_por_identificador: array<string, list<Equipamento>>}
     */
    private function createIndexes(array $objetos): array {
        $equipamentos_por_identificador = [];

        foreach ($objetos as $objeto) {
            if (!$objeto instanceof Equipamento) {
                continue;
            }

            $equipamentos_por_identificador[$objeto->nome] ??= [];
            $equipamentos_por_identificador[$objeto->nome][] = $objeto;
        }

        return [
            'equipamentos_por_identificador' => $equipamentos_por_identificador,
        ];
    }

    /**
     * @return list<Diagnostic>
     */
    private function validateEquipment(Equipamento $equipamento): array {
        $diagnosticos = [];
        $variaveis_vistas = [];

        foreach ($equipamento->variaveis_controladas->all() as $variavel) {
            if (isset($variaveis_vistas[$variavel->value])) {
                $diagnosticos[] = $this->createDiagnostic(
                    'CMMS-SEM-008',
                    "O equipamento '{$equipamento->nome}' declara a variável controlada '{$variavel->value}' mais de uma vez."
                );
            }

            $variaveis_vistas[$variavel->value] = true;
        }

        $caracteristicas_vistas = [];

        foreach ($equipamento->caracteristicas_processo->all() as $caracteristica) {
            array_push($diagnosticos, ...$this->validateProcessCharacteristic($equipamento, $caracteristica));

            if (isset($caracteristicas_vistas[$caracteristica->variavel->value])) {
                $diagnosticos[] = $this->createDiagnostic(
                    'CMMS-SEM-009',
                    "O equipamento '{$equipamento->nome}' declara a característica '{$caracteristica->variavel->value}' mais de uma vez."
                );
            }

            $caracteristicas_vistas[$caracteristica->variavel->value] = true;

            if (!$this->hasControlledVariable($equipamento, $caracteristica->variavel)) {
                $diagnosticos[] = $this->createDiagnostic(
                    'CMMS-SEM-012',
                    "A característica '{$caracteristica->variavel->value}' do equipamento '{$equipamento->nome}' não pertence às variáveis controladas."
                );
            }
        }

        return $diagnosticos;
    }

    /**
     * @return list<Diagnostic>
     */
    private function validateProcessCharacteristic(Equipamento $equipamento, CaracteristicaProcesso $caracteristica): array {
        return $this->validateNumericUnit(
            variavel: $caracteristica->variavel,
            unidade: $caracteristica->unidade,
            entidade: "característica do equipamento '{$equipamento->nome}'"
        );
    }

    /**
     * @param array<string, list<Equipamento>> $equipamentos_por_identificador
     * @return list<Diagnostic>
     */
    private function validateMaintenance(Manutencao $manutencao, array $equipamentos_por_identificador): array {
        $diagnosticos = [];
        $equipamento = $this->findUniqueEquipment(
            $manutencao->equipamento_identificador,
            $equipamentos_por_identificador
        );

        if ($equipamento === null && !isset($equipamentos_por_identificador[$manutencao->equipamento_identificador])) {
            $diagnosticos[] = $this->createDiagnostic(
                'CMMS-SEM-002',
                "A manutenção '{$manutencao->nome}' referencia o equipamento inexistente '{$manutencao->equipamento_identificador}'."
            );
        }

        if ($manutencao->tipo === TipoManutencao::PREVENTIVA
            && $manutencao->gatilho instanceof Tempo
            && $manutencao->gatilho->valor <= 0) {
            $diagnosticos[] = $this->createDiagnostic(
                'CMMS-SEM-007',
                "O gatilho periódico da manutenção '{$manutencao->nome}' deve ser maior que zero."
            );
        }

        if (!$manutencao->gatilho instanceof CondicaoCorretiva) {
            return $diagnosticos;
        }

        foreach ($manutencao->gatilho->getConditions() as $condicao) {
            if ($condicao instanceof CondicaoNumerica) {
                array_push($diagnosticos, ...$this->validateNumericUnit(
                    variavel: $condicao->variavel,
                    unidade: $condicao->unidade,
                    entidade: "condição da manutenção '{$manutencao->nome}'"
                ));
            }

            if ($equipamento === null) {
                continue;
            }

            $variavel = $this->getConditionVariable($condicao);

            if (!$this->hasControlledVariable($equipamento, $variavel)) {
                $diagnosticos[] = $this->createDiagnostic(
                    'CMMS-SEM-010',
                    "A condição da manutenção '{$manutencao->nome}' usa a variável '{$variavel->value}', que não é controlada pelo equipamento '{$equipamento->nome}'."
                );
            }
        }

        return $diagnosticos;
    }

    /**
     * @param array<string, list<Equipamento>> $equipamentos_por_identificador
     * @return list<Diagnostic>
     */
    private function validateRecord(Registro $registro, array $equipamentos_por_identificador): array {
        $diagnosticos = [];
        $equipamento = $this->findUniqueEquipment(
            $registro->equipamento_identificador,
            $equipamentos_por_identificador
        );

        if ($equipamento === null && !isset($equipamentos_por_identificador[$registro->equipamento_identificador])) {
            $diagnosticos[] = $this->createDiagnostic(
                'CMMS-SEM-003',
                "O registro '{$registro->nome}' referencia o equipamento inexistente '{$registro->equipamento_identificador}'."
            );
        }

        $variaveis_lidas = [];

        foreach ($registro->valores->all() as $valor) {
            if ($valor instanceof ValorNumericoRegistro) {
                array_push($diagnosticos, ...$this->validateNumericUnit(
                    variavel: $valor->variavel,
                    unidade: $valor->unidade,
                    entidade: "valor do registro '{$registro->nome}'"
                ));
            }

            $variavel = $this->getRecordedVariable($valor);

            if (isset($variaveis_lidas[$variavel->value])) {
                $diagnosticos[] = $this->createDiagnostic(
                    'CMMS-SEM-006',
                    "O registro '{$registro->nome}' possui mais de uma leitura para a variável '{$variavel->value}'."
                );
            }

            $variaveis_lidas[$variavel->value] = true;

            if ($equipamento !== null && !$this->hasControlledVariable($equipamento, $variavel)) {
                $diagnosticos[] = $this->createDiagnostic(
                    'CMMS-SEM-005',
                    "O registro '{$registro->nome}' informa a variável '{$variavel->value}', que não é controlada pelo equipamento '{$equipamento->nome}'."
                );
            }
        }

        return $diagnosticos;
    }

    /**
     * @return list<Diagnostic>
     */
    private function validateNumericUnit(VariavelControlada $variavel, UnidadeMedida $unidade, string $entidade): array {
        $unidade_esperada = $this->getExpectedUnit($variavel);

        if ($unidade_esperada === null || $unidade === $unidade_esperada) {
            return [];
        }

        return [$this->createDiagnostic(
            'CMMS-SEM-004',
            "A variável '{$variavel->value}' na {$entidade} exige a unidade '{$unidade_esperada->value}', mas recebeu '{$unidade->value}'."
        )];
    }

    private function getExpectedUnit(VariavelControlada $variavel): ?UnidadeMedida {
        return match ($variavel) {
            VariavelControlada::VAZAO => UnidadeMedida::M3_POR_HORA,
            VariavelControlada::PRESSAO,
            VariavelControlada::PRESSAO_DESCARGA,
            VariavelControlada::PRESSAO_SUCCAO => UnidadeMedida::BAR,
            VariavelControlada::VIBRACAO => UnidadeMedida::MM_POR_S,
            VariavelControlada::TEMPERATURA => UnidadeMedida::CELSIUS,
            VariavelControlada::ROTACAO => UnidadeMedida::RPM,
            default => null,
        };
    }

    private function getRecordedVariable(
        ValorNumericoRegistro|HorasOperacaoRegistro|ObservacaoVisualRegistro|VazamentoRegistro $valor
    ): VariavelControlada {
        if ($valor instanceof ValorNumericoRegistro) {
            return $valor->variavel;
        }

        if ($valor instanceof HorasOperacaoRegistro) {
            return VariavelControlada::HORAS_OPERACAO;
        }

        if ($valor instanceof ObservacaoVisualRegistro) {
            return VariavelControlada::OBSERVACAO_VISUAL;
        }

        return VariavelControlada::VAZAMENTO;
    }

    private function getConditionVariable(
        CondicaoNumerica|CondicaoObservacao|CondicaoVazamento $condicao
    ): VariavelControlada {
        if ($condicao instanceof CondicaoNumerica) {
            return $condicao->variavel;
        }

        if ($condicao instanceof CondicaoObservacao) {
            return VariavelControlada::OBSERVACAO_VISUAL;
        }

        return VariavelControlada::VAZAMENTO;
    }

    private function hasControlledVariable(Equipamento $equipamento, VariavelControlada $variavel): bool {
        return in_array($variavel, $equipamento->variaveis_controladas->all(), true);
    }

    /**
     * @param array<string, list<Equipamento>> $equipamentos_por_identificador
     */
    private function findUniqueEquipment(string $identificador, array $equipamentos_por_identificador): ?Equipamento {
        $equipamentos = $equipamentos_por_identificador[$identificador] ?? [];

        return count($equipamentos) === 1 ? $equipamentos[0] : null;
    }

    private function getIdentifier(Equipamento|Manutencao|Registro $objeto): string {
        return $objeto->nome;
    }

    private function createDiagnostic(string $codigo, string $mensagem): Diagnostic {
        return new Diagnostic(
            codigo: $codigo,
            mensagem: $mensagem,
            origem: DiagnosticOrigin::SEMANTIC,
            severidade: DiagnosticSeverity::ERROR,
        );
    }
}
