<?php

namespace App\admsDaman\Models\Services;

use InvalidArgumentException;

/**
 * Service responsável por normalizar e validar
 * o rateio de um lançamento financeiro entre obras.
 *
 * O formulário pode trabalhar de duas formas:
 *
 * 1. Sem rateio:
 *    O usuário seleciona apenas uma obra e o valor total
 *    do lançamento é automaticamente apropriado a ela.
 *
 * 2. Com rateio:
 *    O usuário informa duas ou mais obras e o valor
 *    correspondente a cada uma.
 *
 * Independentemente da forma utilizada pela interface,
 * este Service sempre devolve um array padronizado de
 * alocações para ser persistido no banco.
 */
class PurchaseDocumentAllocationService
{
    /**
     * Normalizar os dados de rateio do lançamento.
     *
     * @param array $data
     * Dados recebidos do formulário.
     *
     * @param string|int|float $documentTotal
     * Valor total REAL do documento.
     *
     * Importante:
     * Para NF-e, este valor deve vir da NF-e recuperada
     * do banco e não de um campo enviado pelo formulário.
     *
     * @return array
     * Lista de alocações já normalizadas.
     */
    public function normalizeAllocations(
        array $data,
        string|int|float $documentTotal
    ): array {

        $documentTotalCents =
            $this->moneyToCents(
                $documentTotal
            );


        if ($documentTotalCents <= 0) {

            throw new InvalidArgumentException(
                'O valor total do lançamento deve ser maior que zero.'
            );
        }


        /*
         * O checkbox só existe no POST quando estiver marcado.
         */
        $hasProration =
            !empty(
                $data['has_proration']
            );


        /*
         * =====================================================
         * LANÇAMENTO SEM RATEIO
         * =====================================================
         *
         * O usuário escolhe apenas uma obra.
         *
         * Internamente ainda criamos uma alocação,
         * representando 100% do valor do documento.
         */
        if (!$hasProration) {

            $projectId =
                (int) (
                    $data[
                        'adms_daman_project_id'
                    ]
                    ?? 0
                );


            if ($projectId <= 0) {

                throw new InvalidArgumentException(
                    'Selecione a obra do lançamento.'
                );
            }


            return [
                [
                    'adms_daman_project_id' =>
                        $projectId,

                    'allocated_amount' =>
                        $this->centsToDecimal(
                            $documentTotalCents
                        ),

                    'observation' =>
                        null,
                ],
            ];
        }


        /*
         * =====================================================
         * LANÇAMENTO COM RATEIO
         * =====================================================
         */
        $allocations =
            $data['allocations']
            ?? [];


        if (!is_array($allocations)) {

            throw new InvalidArgumentException(
                'Os dados do rateio são inválidos.'
            );
        }


        /*
         * Quando o usuário informa que existe rateio,
         * devem existir pelo menos duas obras.
         */
        if (count($allocations) < 2) {

            throw new InvalidArgumentException(
                'Informe pelo menos duas obras para realizar o rateio.'
            );
        }


        $normalizedAllocations = [];

        /*
         * Utilizado para impedir a mesma obra
         * de aparecer duas vezes no rateio.
         */
        $usedProjects = [];

        /*
         * Trabalhamos em centavos para evitar problemas
         * de precisão com números de ponto flutuante.
         */
        $allocatedTotalCents = 0;


        foreach (
            $allocations as $index => $allocation
        ) {

            if (!is_array($allocation)) {

                throw new InvalidArgumentException(
                    'Uma das linhas do rateio é inválida.'
                );
            }


            $projectId =
                (int) (
                    $allocation[
                        'adms_daman_project_id'
                    ]
                    ?? 0
                );


            if ($projectId <= 0) {

                throw new InvalidArgumentException(
                    'Selecione uma obra em todas as linhas do rateio.'
                );
            }


            /*
             * Uma obra não pode aparecer duas vezes.
             */
            if (
                isset(
                    $usedProjects[
                        $projectId
                    ]
                )
            ) {

                throw new InvalidArgumentException(
                    'A mesma obra não pode aparecer mais de uma vez no rateio.'
                );
            }


            $usedProjects[
                $projectId
            ] = true;


            $allocatedAmountCents =
                $this->moneyToCents(
                    $allocation[
                        'allocated_amount'
                    ]
                    ?? 0
                );


            if ($allocatedAmountCents <= 0) {

                throw new InvalidArgumentException(
                    'Todos os valores do rateio devem ser maiores que zero.'
                );
            }


            $allocatedTotalCents +=
                $allocatedAmountCents;


            $observation =
                trim(
                    (string) (
                        $allocation[
                            'observation'
                        ]
                        ?? ''
                    )
                );


            $normalizedAllocations[] = [

                'adms_daman_project_id' =>
                    $projectId,

                'allocated_amount' =>
                    $this->centsToDecimal(
                        $allocatedAmountCents
                    ),

                'observation' =>
                    $observation !== ''
                        ? $observation
                        : null,
            ];
        }


        /*
         * =====================================================
         * VALIDAR FECHAMENTO DO RATEIO
         * =====================================================
         *
         * A soma deve ser EXATAMENTE igual ao valor
         * total do documento.
         */
        if (
            $allocatedTotalCents
            !==
            $documentTotalCents
        ) {

            $differenceCents =
                $documentTotalCents
                - $allocatedTotalCents;


            throw new InvalidArgumentException(
                'O rateio não corresponde ao valor total do lançamento. '
                . 'Diferença: R$ '
                . $this->formatCentsToBr(
                    abs(
                        $differenceCents
                    )
                )
                . '.'
            );
        }


        return $normalizedAllocations;
    }


    /**
     * Converter valor monetário para centavos.
     *
     * Aceita, por exemplo:
     *
     * 1000
     * 1000.50
     * 1000,50
     * 1.000,50
     */
    private function moneyToCents(
        string|int|float $value
    ): int {

        if (
            is_int($value)
            || is_float($value)
        ) {

            return (int) round(
                ((float) $value) * 100
            );
        }


        $value =
            trim(
                (string) $value
            );


        if ($value === '') {
            return 0;
        }


        /*
         * Formato brasileiro:
         *
         * 1.250,75
         */
        if (
            str_contains(
                $value,
                ','
            )
        ) {

            $value =
                str_replace(
                    '.',
                    '',
                    $value
                );

            $value =
                str_replace(
                    ',',
                    '.',
                    $value
                );
        }


        if (!is_numeric($value)) {

            throw new InvalidArgumentException(
                'Foi informado um valor inválido no rateio.'
            );
        }


        return (int) round(
            ((float) $value) * 100
        );
    }


    /**
     * Converter centavos para DECIMAL no formato
     * esperado pelo banco.
     *
     * Exemplo:
     *
     * 125050 => 1250.50
     */
    private function centsToDecimal(
        int $cents
    ): string {

        return number_format(
            $cents / 100,
            2,
            '.',
            ''
        );
    }


    /**
     * Formatar centavos para exibição em mensagens.
     *
     * Exemplo:
     *
     * 125050 => 1.250,50
     */
    private function formatCentsToBr(
        int $cents
    ): string {

        return number_format(
            $cents / 100,
            2,
            ',',
            '.'
        );
    }
}