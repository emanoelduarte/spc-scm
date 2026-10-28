<?php

declare(strict_types=1);

namespace App\admsDaman\Models\Services;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\FinancialRenegotiationsRepository;
use App\admsDaman\Models\Repository\SuppliersRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Orquestra a consolidação de parcelas antigas em uma nova dívida.
 *
 * Tudo acontece na mesma transação:
 * 1. bloqueia e valida as parcelas de origem;
 * 2. calcula o principal remanescente real;
 * 3. herda o rateio entre obras;
 * 4. cria o novo lançamento e suas parcelas;
 * 5. registra a composição do acordo;
 * 6. marca as parcelas antigas como RN.
 */
class FinancialRenegotiationService extends DbConnection
{
    private FinancialRenegotiationsRepository $renegotiationsRepository;
    private PurchaseDocumentService $purchaseDocumentService;
    private SuppliersRepository $suppliersRepository;

    public function __construct()
    {
        $this->renegotiationsRepository =
            new FinancialRenegotiationsRepository();

        $this->purchaseDocumentService =
            new PurchaseDocumentService();

        $this->suppliersRepository =
            new SuppliersRepository();
    }

    /**
     * Criar uma renegociação e retornar o ID do novo lançamento financeiro.
     */
    public function create(array $data): int
    {
        $supplierId =
            (int) ($data['adms_daman_supplier_id'] ?? 0);

        $createdBy =
            (int) ($data['created_by'] ?? 0);

        $agreementNumber =
            trim((string) ($data['agreement_number'] ?? ''));

        $agreementDate =
            trim((string) ($data['agreement_date'] ?? ''));

        $sourceInstallmentIds =
            $this->normalizeIds(
                $data['source_installment_ids'] ?? []
            );

        if ($supplierId <= 0) {
            throw new InvalidArgumentException(
                'Selecione o fornecedor da renegociação.'
            );
        }

        $supplier =
            $this->suppliersRepository->getSupplier($supplierId);

        if (
            !$supplier
            || (int) ($supplier['supplier_status'] ?? 0) !== 1
        ) {
            throw new InvalidArgumentException(
                'O fornecedor selecionado não está ativo.'
            );
        }

        if ($createdBy <= 0) {
            throw new InvalidArgumentException(
                'Usuário responsável pela renegociação inválido.'
            );
        }

        if ($agreementNumber === '') {
            throw new InvalidArgumentException(
                'Informe o número ou a referência do acordo.'
            );
        }

        if (!$this->isValidDate($agreementDate)) {
            throw new InvalidArgumentException(
                'Informe uma data válida para a renegociação.'
            );
        }

        if (empty($sourceInstallmentIds)) {
            throw new InvalidArgumentException(
                'Selecione ao menos uma parcela para renegociar.'
            );
        }

        $interestCents =
            $this->moneyToCents($data['interest_amount'] ?? 0);

        $penaltyCents =
            $this->moneyToCents($data['penalty_amount'] ?? 0);

        $discountCents =
            $this->moneyToCents($data['discount_amount'] ?? 0);

        if (
            $interestCents < 0
            || $penaltyCents < 0
            || $discountCents < 0
        ) {
            throw new InvalidArgumentException(
                'Juros, multa e desconto não podem ser negativos.'
            );
        }

        $connection = $this->getConnection();

        try {
            $connection->beginTransaction();

            /*
             * O bloqueio FOR UPDATE impede que uma baixa ou outra
             * renegociação modifique essas parcelas durante a confirmação.
             */
            $sourceInstallments =
                $this->renegotiationsRepository
                    ->lockEligibleInstallments(
                        $sourceInstallmentIds,
                        $supplierId
                    );

            if (
                count($sourceInstallments)
                !== count($sourceInstallmentIds)
            ) {
                throw new RuntimeException(
                    'Uma ou mais parcelas selecionadas não estão mais disponíveis para renegociação. Atualize a tela e tente novamente.'
                );
            }

            $principalCents = 0;
            $sourceDocumentIds = [];

            foreach ($sourceInstallments as $sourceInstallment) {
                $remainingCents =
                    $this->moneyToCents(
                        $sourceInstallment['remaining_amount'] ?? 0
                    );

                if ($remainingCents <= 0) {
                    throw new RuntimeException(
                        'Uma das parcelas selecionadas não possui saldo disponível.'
                    );
                }

                $principalCents += $remainingCents;

                $sourceDocumentIds[] =
                    (int) $sourceInstallment[
                        'adms_daman_purchase_document_id'
                    ];
            }

            $renegotiatedCents =
                $principalCents
                + $interestCents
                + $penaltyCents
                - $discountCents;

            if ($renegotiatedCents <= 0) {
                throw new InvalidArgumentException(
                    'O valor final da renegociação deve ser maior que zero.'
                );
            }

            /*
             * O valor do principal enviado pelo navegador é apenas visual.
             * A fonte oficial é sempre o saldo recalculado no banco.
             */
            $allocations =
                $this->buildRenegotiationAllocations(
                    $sourceInstallments,
                    $renegotiatedCents
                );

            if (empty($allocations)) {
                throw new RuntimeException(
                    'Não foi possível determinar a apropriação por obra da renegociação.'
                );
            }

            $installments =
                $this->normalizeNewInstallments(
                    $data['installments'] ?? []
                );

            if (empty($installments)) {
                throw new InvalidArgumentException(
                    'Gere as novas parcelas da renegociação.'
                );
            }

            $firstProjectId =
                (int) $allocations[0]['adms_daman_project_id'];

            $purchaseData = [
                'financial_entry_type' => 'renegotiation',
                'adms_daman_supplier_id' => $supplierId,
                'document_type' => 'RENEGOCIACAO',
                'document_number' => $agreementNumber,
                'document_date' => $agreementDate,
                'total_value' => $this->centsToBrazilianInput($renegotiatedCents),
                'adms_daman_project_id' => $firstProjectId,
                'adms_daman_user_id' => $createdBy,
                // A renegociação possui cronograma próprio; não depende de uma
                // condição comercial pré-cadastrada.
                'adms_daman_payment_method_id' => null,
                'payment_schedule_pending' => 0,
                'purchase_date' => $agreementDate,
                'observation' => trim((string) ($data['observation'] ?? '')),
                'created_by' => $createdBy,
                'installments' => $installments,
                'has_proration' => count($allocations) > 1 ? 1 : 0,
                'allocations' => $allocations,
            ];

            $purchaseDocumentId =
                $this->purchaseDocumentService
                    ->createManual($purchaseData);

            if ($purchaseDocumentId <= 0) {
                throw new RuntimeException(
                    'Não foi possível criar o novo compromisso financeiro.'
                );
            }

            $renegotiationId =
                $this->renegotiationsRepository->create([
                    'adms_daman_supplier_id' => $supplierId,
                    'adms_daman_purchase_document_id' => $purchaseDocumentId,
                    'agreement_number' => $agreementNumber,
                    'agreement_date' => $agreementDate,
                    'original_amount' => $this->centsToDecimal($principalCents),
                    'interest_amount' => $this->centsToDecimal($interestCents),
                    'penalty_amount' => $this->centsToDecimal($penaltyCents),
                    'discount_amount' => $this->centsToDecimal($discountCents),
                    'renegotiated_amount' => $this->centsToDecimal($renegotiatedCents),
                    'observation' => trim((string) ($data['observation'] ?? '')),
                    'created_by' => $createdBy,
                ]);

            if ($renegotiationId <= 0) {
                throw new RuntimeException(
                    'Não foi possível registrar a composição da renegociação.'
                );
            }

            if (
                !$this->renegotiationsRepository
                    ->createOrigins(
                        $renegotiationId,
                        $sourceInstallments
                    )
            ) {
                throw new RuntimeException(
                    'Não foi possível vincular as parcelas antigas ao acordo.'
                );
            }

            $updatedRows =
                $this->renegotiationsRepository
                    ->markInstallmentsRenegotiated(
                        $sourceInstallmentIds
                    );

            if ($updatedRows !== count($sourceInstallmentIds)) {
                throw new RuntimeException(
                    'Não foi possível marcar todas as parcelas de origem como renegociadas.'
                );
            }

            $this->renegotiationsRepository
                ->closeSourceDocumentsWithoutOpenInstallments(
                    $sourceDocumentIds
                );

            $connection->commit();

            return $purchaseDocumentId;
        } catch (Throwable $err) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            GenerateLog::generateLog(
                'error',
                'Erro ao criar renegociação financeira.',
                [
                    'supplier_id' => $supplierId,
                    'agreement_number' => $agreementNumber,
                    'installment_ids' => $sourceInstallmentIds,
                    'error' => $err->getMessage(),
                ]
            );

            throw $err;
        }
    }

    /**
     * Herdar o rateio das parcelas de origem e aplicá-lo ao valor final do
     * acordo. Juros, multa e desconto seguem a mesma proporção do principal.
     */
    private function buildRenegotiationAllocations(
        array $sourceInstallments,
        int $renegotiatedCents
    ): array {
        $documentIds = [];

        foreach ($sourceInstallments as $installment) {
            $documentIds[] =
                (int) $installment['adms_daman_purchase_document_id'];
        }

        $allocationRows =
            $this->renegotiationsRepository
                ->getAllocationsByDocumentIds($documentIds);

        $allocationsByDocument = [];

        foreach ($allocationRows as $allocation) {
            $documentId =
                (int) $allocation['adms_daman_purchase_document_id'];

            $projectId =
                (int) $allocation['adms_daman_project_id'];

            $amountCents =
                $this->moneyToCents(
                    $allocation['allocated_amount'] ?? 0
                );

            if ($documentId > 0 && $projectId > 0 && $amountCents > 0) {
                $allocationsByDocument[$documentId][$projectId] =
                    ($allocationsByDocument[$documentId][$projectId] ?? 0)
                    + $amountCents;
            }
        }

        $principalByProject = [];

        foreach ($sourceInstallments as $installment) {
            $documentId =
                (int) $installment['adms_daman_purchase_document_id'];

            $remainingCents =
                $this->moneyToCents(
                    $installment['remaining_amount'] ?? 0
                );

            $documentAllocations =
                $allocationsByDocument[$documentId] ?? [];

            /*
             * Compatibilidade com lançamentos antigos que ainda não tenham
             * linhas na tabela de allocations.
             */
            if (empty($documentAllocations)) {
                $fallbackProjectId =
                    (int) ($installment['adms_daman_project_id'] ?? 0);

                if ($fallbackProjectId <= 0) {
                    throw new RuntimeException(
                        'Uma parcela de origem não possui obra vinculada.'
                    );
                }

                $documentAllocations = [
                    $fallbackProjectId => 1,
                ];
            }

            $distributed =
                $this->distributeCents(
                    $remainingCents,
                    $documentAllocations
                );

            foreach ($distributed as $projectId => $amountCents) {
                $principalByProject[$projectId] =
                    ($principalByProject[$projectId] ?? 0)
                    + $amountCents;
            }
        }

        if (empty($principalByProject)) {
            return [];
        }

        $finalByProject =
            $this->distributeCents(
                $renegotiatedCents,
                $principalByProject
            );

        $result = [];

        foreach ($finalByProject as $projectId => $amountCents) {
            $result[] = [
                'adms_daman_project_id' => (int) $projectId,
                'allocated_amount' => $this->centsToDecimal($amountCents),
                'observation' => 'Rateio herdado proporcionalmente das parcelas de origem da renegociação.',
            ];
        }

        return $result;
    }

    /**
     * Distribui centavos por pesos inteiros, garantindo fechamento exato.
     */
    private function distributeCents(int $totalCents, array $weights): array
    {
        $normalizedWeights = [];
        $weightTotal = 0;

        foreach ($weights as $key => $weight) {
            $weight = (int) $weight;

            if ($weight <= 0) {
                continue;
            }

            $normalizedWeights[(int) $key] = $weight;
            $weightTotal += $weight;
        }

        if ($totalCents <= 0 || $weightTotal <= 0) {
            return [];
        }

        $result = [];
        $distributed = 0;
        $keys = array_keys($normalizedWeights);
        $lastKey = end($keys);

        foreach ($normalizedWeights as $key => $weight) {
            if ($key === $lastKey) {
                $share = $totalCents - $distributed;
            } else {
                $share = (int) floor(
                    ($totalCents * $weight) / $weightTotal
                );
                $distributed += $share;
            }

            $result[$key] = $share;
        }

        return $result;
    }

    /**
     * O Service comum de lançamentos exige status explícito nas parcelas.
     * Para o acordo, todas nascem como AV e passam a ser calculadas pela data.
     */
    private function normalizeNewInstallments(array $installments): array
    {
        $result = [];

        foreach ($installments as $index => $installment) {
            if (!is_array($installment)) {
                continue;
            }

            $result[] = [
                'installment_number' => $index + 1,
                'due_date' => trim((string) ($installment['due_date'] ?? '')),
                'original_amount' => $installment['original_amount'] ?? '0',
                'status' => 'AV',
                'observation' => 'Parcela gerada pela renegociação financeira.',
            ];
        }

        return $result;
    }

    private function normalizeIds(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return array_values(
            array_unique(
                array_filter(
                    array_map('intval', $values),
                    static fn(int $id): bool => $id > 0
                )
            )
        );
    }

    private function moneyToCents(mixed $value): int
    {
        if (is_int($value)) {
            return $value * 100;
        }

        if (is_float($value)) {
            return (int) round($value * 100);
        }

        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $value = preg_replace('/[^0-9,.-]/', '', $value) ?? '';

        if ($value === '' || $value === '-' || $value === ',' || $value === '.') {
            return 0;
        }

        $hasComma = str_contains($value, ',');
        $hasDot = str_contains($value, '.');

        if ($hasComma && $hasDot) {
            // Formato brasileiro: 1.234,56
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif ($hasComma) {
            $value = str_replace(',', '.', $value);
        }

        if (!is_numeric($value)) {
            return 0;
        }

        return (int) round(((float) $value) * 100);
    }

    /**
     * O PurchaseDocumentService recebe valores do formulário no padrão
     * brasileiro. Usamos este formato apenas na chamada do motor legado;
     * os valores persistidos nas tabelas próprias continuam DECIMAL com ponto.
     */
    private function centsToBrazilianInput(int $cents): string
    {
        return number_format($cents / 100, 2, ',', '.');
    }

    private function centsToDecimal(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    private function isValidDate(string $date): bool
    {
        if ($date === '') {
            return false;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false
            && $parsed->format('Y-m-d') === $date;
    }
}
