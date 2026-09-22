<?php

$nfesPending = $this->data['nfes_pending'] ?? [];
$nfesChecked = $this->data['nfes_checked'] ?? [];

$nfeSync = $this->data['nfe_sync'] ?? null;

$statusLabels = [
    'authorized' => 'Autorizada',
    'cancelled'  => 'Cancelada',
    'denied'     => 'Denegada',
    'unknown'    => 'Desconhecida',
];

$statusClasses = [
    'authorized' => 'bg-success',
    'cancelled'  => 'bg-danger',
    'denied'     => 'bg-danger',
    'unknown'    => 'bg-secondary',
];


/*
 * ================================================================
 * SITUAÇÃO FINANCEIRA DAS NF-e CONFERIDAS
 * ================================================================
 *
 * O backend anexa em cada NF-e:
 *
 * $nfe['financial']
 *
 * financial_status:
 * pending = FB / parcelas ainda não confirmadas;
 * open    = lançamento com saldo em aberto;
 * paid    = lançamento totalmente quitado.
 */
$financialStatusMeta = [

    'pending' => [
        'label' => 'FB',
        'class' => 'bg-warning text-dark',
        'icon'  => 'fa-clock',
        'title' => 'Parcelas a confirmar',
    ],

    'open' => [
        'label' => 'Em aberto',
        'class' => 'bg-primary',
        'icon'  => 'fa-wallet',
        'title' => 'Possui saldo a pagar',
    ],

    'paid' => [
        'label' => 'Quitado',
        'class' => 'bg-success',
        'icon'  => 'fa-circle-check',
        'title' => 'Documento encerrado',
    ],
];

?>

<div class="container-fluid px-4">

    <!-- ====================================================== -->
    <!-- TÍTULO E BREADCRUMB                                    -->
    <!-- ====================================================== -->

    <div class="mb-1 d-flex flex-column flex-sm-row gap-2">

        <h2 class="mt-3">NF-e Recebidas</h2>

        <ol class="breadcrumb mb-3 mt-0 mt-sm-3 ms-auto">

            <li class="breadcrumb-item">

                <a
                    class="text-decoration-none"
                    href="<?= $_ENV['URL_ADM']; ?>dashboard">

                    Dashboard

                </a>

            </li>

            <li
                class="breadcrumb-item active"
                aria-current="page">

                NF-e Recebidas

            </li>

        </ol>

    </div>


    <?php

    // Alertas do sistema
    include './app/admsDaman/Views/partials/alerts.php';

    ?>

    <div class="card mb-4 border-light shadow">

        <div class="card-header hstack gap-2">

            <span>

                <i class="fa-solid fa-file-invoice me-1"></i>

                Monitoramento de NF-e

            </span>

            <span class="ms-auto d-flex align-items-center gap-3">

                <small class="text-muted">

                    Última atualização:

                    <strong>
                        <?php

                        if (!empty($nfeSync['last_sync_at'])) {

                            $lastSync = new DateTime(
                                $nfeSync['last_sync_at'],
                                new DateTimeZone('UTC')
                            );

                            $lastSync->setTimezone(
                                new DateTimeZone('America/Belem')
                            );

                            echo $lastSync->format('d/m/Y H:i');
                        } else {

                            echo 'Nunca';
                        }

                        ?>
                    </strong>

                </small>

                <a
                    href="<?= $_ENV['URL_ADM']; ?>sync-nfes"
                    class="btn btn-primary btn-sm">

                    <i class="fa-solid fa-rotate me-1"></i>

                    Atualizar NF-e

                </a>

            </span>

        </div>

    </div>


    <!-- ====================================================== -->
    <!-- ACCORDION PRINCIPAL                                    -->
    <!-- ====================================================== -->

    <div
        class="accordion"
        id="nfeAccordion">


        <!-- ================================================== -->
        <!-- PENDENTES DE CONFERÊNCIA                           -->
        <!-- ================================================== -->

        <div class="accordion-item mb-3 border shadow-sm">

            <h2
                class="accordion-header"
                id="headingPending">

                <button
                    class="accordion-button"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapsePending"
                    aria-expanded="true"
                    aria-controls="collapsePending">

                    <div
                        class="d-flex align-items-center
                               justify-content-between
                               w-100 me-3">

                        <span class="fw-semibold">

                            <i class="fa-solid fa-triangle-exclamation me-2"></i>

                            Pendentes de conferência

                        </span>


                        <span class="badge bg-warning text-dark">

                            <?= count($nfesPending); ?>

                        </span>

                    </div>

                </button>

            </h2>


            <div
                id="collapsePending"
                class="accordion-collapse collapse show"
                aria-labelledby="headingPending">

                <div class="accordion-body">


                    <?php if (!empty($nfesPending)): ?>


                        <!-- Cabeçalho -->
                        <div
                            class="row fw-semibold text-muted
                                   border-bottom pb-2 mb-2
                                   d-none d-lg-flex">

                            <div class="col-lg-2">
                                Emissão
                            </div>

                            <div class="col-lg-1">
                                NF-e
                            </div>

                            <div class="col-lg-2">
                                Fornecedor
                            </div>

                            <div class="col-lg-2 text-end">
                                Valor
                            </div>

                            <div class="col-lg-3">
                                Chave de Acesso
                            </div>

                            <div class="col-lg-2 text-end">
                                Ação
                            </div>

                        </div>


                        <!-- Registros -->
                        <?php foreach ($nfesPending as $nfe): ?>


                            <div
                                class="row align-items-center
                                       border-bottom py-3">


                                <!-- Emissão -->
                                <div class="col-lg-2 mb-2 mb-lg-0">

                                    <span class="d-lg-none text-muted small">
                                        Emissão
                                    </span>

                                    <div>

                                        <?= !empty($nfe['issue_date'])
                                            ? date(
                                                'd/m/Y H:i',
                                                strtotime(
                                                    $nfe['issue_date']
                                                )
                                            )
                                            : '-'; ?>

                                    </div>

                                </div>


                                <!-- Número NF-e -->
                                <div class="col-lg-1 mb-2 mb-lg-0">

                                    <span class="d-lg-none text-muted small">
                                        NF-e
                                    </span>

                                    <div class="fw-semibold">

                                        <?= htmlspecialchars(
                                            $nfe['nfe_number']
                                        ); ?>

                                    </div>

                                    <?php

                                    $status = $nfe['status'] ?? 'unknown';

                                    ?>

                                    <span class="badge <?= $statusClasses[$status] ?? 'bg-secondary'; ?>">

                                        <?= $statusLabels[$status] ?? 'Desconhecida'; ?>

                                    </span>

                                </div>


                                <!-- Fornecedor -->
                                <div class="col-lg-2 mb-2 mb-lg-0">

                                    <span class="d-lg-none text-muted small">
                                        Fornecedor
                                    </span>

                                    <div class="fw-semibold">

                                        <?= htmlspecialchars(
                                            $nfe['issuer_name']
                                        ); ?>

                                    </div>

                                </div>

                                <!-- Valor -->
                                <div
                                    class="col-lg-2
                                           text-lg-end
                                           mb-2 mb-lg-0">

                                    <span class="d-lg-none text-muted small">
                                        Valor
                                    </span>

                                    <div class="fw-bold">

                                        R$
                                        <?= number_format(
                                            (float) $nfe['total_value'],
                                            2,
                                            ',',
                                            '.'
                                        ); ?>

                                    </div>

                                </div>

                                <!-- Chave de Acesso -->
                                <div class="col-lg-3 mb-2 mb-lg-0">

                                    <span class="d-lg-none text-muted small">
                                        Chave de Acesso
                                    </span>

                                    <div>

                                        <?= htmlspecialchars(
                                            $nfe['access_key']
                                        ); ?>

                                    </div>

                                </div>


                                <!-- Ação -->
                                <div class="col-lg-2 text-lg-end">

                                    <a
                                        href="<?= $_ENV['URL_ADM']; ?>check-nfe/<?= (int) $nfe['id']; ?>"
                                        class="btn btn-success btn-sm">

                                        <i class="fa-solid fa-check me-1"></i>

                                        Conferir

                                    </a>

                                </div>

                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div
                            class="alert alert-success mb-0"
                            role="alert">

                            <i class="fa-solid fa-circle-check me-1"></i>

                            Nenhuma NF-e pendente de conferência.

                        </div>


                    <?php endif; ?>


                </div>

            </div>

        </div>


        <!-- ================================================== -->
        <!-- NF-e CONFERIDAS                                    -->
        <!-- ================================================== -->

        <div class="accordion-item border shadow-sm">

            <h2
                class="accordion-header"
                id="headingChecked">

                <button
                    class="accordion-button collapsed"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#collapseChecked"
                    aria-expanded="false"
                    aria-controls="collapseChecked">

                    <div
                        class="d-flex align-items-center
                               justify-content-between
                               w-100 me-3">

                        <span class="fw-semibold">

                            <i class="fa-solid fa-circle-check me-2"></i>

                            Conferidas

                        </span>


                        <span class="badge bg-success">

                            <?= count($nfesChecked); ?>

                        </span>

                    </div>

                </button>

            </h2>


            <div
                id="collapseChecked"
                class="accordion-collapse collapse"
                aria-labelledby="headingChecked">

                <div class="accordion-body">


                    <?php if (!empty($nfesChecked)): ?>


                        <!-- ================================================== -->
                        <!-- CABEÇALHO DAS NF-e CONFERIDAS                      -->
                        <!-- ================================================== -->
                        <div
                            class="row fw-semibold text-muted
                                   border-bottom pb-2 mb-2
                                   d-none d-lg-flex">

                            <div class="col-lg-2">
                                Emissão
                            </div>

                            <div class="col-lg-1">
                                NF-e
                            </div>

                            <div class="col-lg-3">
                                Fornecedor
                            </div>

                            <div class="col-lg-2 text-end">
                                Valor
                            </div>

                            <div class="col-lg-2">
                                Situação Financeira
                            </div>

                            <div class="col-lg-2 text-end">
                                Ação
                            </div>

                        </div>


                        <?php foreach ($nfesChecked as $nfe): ?>


                            <?php

                            /*
                             * Resumo financeiro anexado pelo ListNfes.
                             *
                             * Quando a NF-e ainda não foi lançada,
                             * financial será NULL.
                             */
                            $financial =
                                $nfe['financial']
                                ?? null;


                            /*
                             * Manter compatibilidade temporária com o campo
                             * purchase_document_id que já vinha do NfeRepository.
                             */
                            $purchaseDocumentId =
                                (int) (
                                    $financial['purchase_document_id']
                                    ?? $nfe['purchase_document_id']
                                    ?? 0
                                );


                            $financialStatus =
                                $financial['financial_status']
                                ?? null;


                            $financialMeta =
                                $financialStatus
                                ? (
                                    $financialStatusMeta[$financialStatus]
                                    ?? null
                                )
                                : null;


                            $installmentsCount =
                                (int) (
                                    $financial['installments_count']
                                    ?? 0
                                );


                            $totalAmount =
                                (float) (
                                    $financial['total_amount']
                                    ?? $nfe['total_value']
                                    ?? 0
                                );


                            $paidAmount =
                                (float) (
                                    $financial['paid_amount']
                                    ?? 0
                                );


                            $remainingAmount =
                                (float) (
                                    $financial['remaining_amount']
                                    ?? 0
                                );

                            ?>


                            <div
                                class="row align-items-center
                                       border-bottom py-3">


                                <!-- Emissão -->
                                <div class="col-lg-2 mb-3 mb-lg-0">

                                    <span
                                        class="d-lg-none text-muted small">
                                        Emissão
                                    </span>

                                    <div>

                                        <?= !empty($nfe['issue_date'])
                                            ? date(
                                                'd/m/Y H:i',
                                                strtotime(
                                                    $nfe['issue_date']
                                                )
                                            )
                                            : '-'; ?>

                                    </div>

                                </div>


                                <!-- Número da NF-e -->
                                <div class="col-lg-1 mb-3 mb-lg-0">

                                    <span
                                        class="d-lg-none text-muted small">
                                        NF-e
                                    </span>

                                    <div class="fw-semibold">

                                        <?= htmlspecialchars(
                                            $nfe['nfe_number']
                                        ); ?>

                                    </div>

                                </div>


                                <!-- Fornecedor -->
                                <div class="col-lg-3 mb-3 mb-lg-0">

                                    <span
                                        class="d-lg-none text-muted small">
                                        Fornecedor
                                    </span>

                                    <div class="fw-semibold">

                                        <?= htmlspecialchars(
                                            $nfe['issuer_name']
                                        ); ?>

                                    </div>

                                    <div class="small text-muted mt-1">

                                        CNPJ:
                                        <?= htmlspecialchars(
                                            $nfe['issuer_cnpj']
                                                ?? '-'
                                        ); ?>

                                    </div>

                                </div>


                                <!-- Valor -->
                                <div
                                    class="col-lg-2
                                           text-lg-end
                                           mb-3 mb-lg-0">

                                    <span
                                        class="d-lg-none text-muted small">
                                        Valor
                                    </span>

                                    <div class="fw-bold">

                                        R$
                                        <?= number_format(
                                            (float) $nfe['total_value'],
                                            2,
                                            ',',
                                            '.'
                                        ); ?>

                                    </div>

                                </div>


                                <!-- Situação financeira -->
                                <div class="col-lg-2 mb-3 mb-lg-0">

                                    <span
                                        class="d-lg-none text-muted small">
                                        Situação Financeira
                                    </span>


                                    <?php if (!empty($financialMeta)): ?>

                                        <div>

                                            <span
                                                class="badge <?= $financialMeta['class']; ?>">

                                                <i
                                                    class="fa-solid
                                                           <?= $financialMeta['icon']; ?>
                                                           me-1">
                                                </i>

                                                <?= $financialMeta['label']; ?>

                                            </span>

                                        </div>


                                        <div class="small text-muted mt-1">

                                            <?= $financialMeta['title']; ?>

                                        </div>


                                        <?php if ($financialStatus === 'pending'): ?>

                                            <div class="small mt-1">

                                                <span class="text-muted">
                                                    Saldo:
                                                </span>

                                                <strong>

                                                    R$
                                                    <?= number_format(
                                                        $remainingAmount > 0
                                                            ? $remainingAmount
                                                            : $totalAmount,
                                                        2,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </strong>

                                            </div>

                                        <?php else: ?>

                                            <div class="small mt-1">

                                                <span class="text-muted">
                                                    Parcelas:
                                                </span>

                                                <strong>
                                                    <?= $installmentsCount; ?>
                                                </strong>

                                            </div>


                                            <div class="small">

                                                <span class="text-muted">
                                                    Pago:
                                                </span>

                                                <strong>

                                                    R$
                                                    <?= number_format(
                                                        $paidAmount,
                                                        2,
                                                        ',',
                                                        '.'
                                                    ); ?>

                                                </strong>

                                            </div>


                                            <?php if ($financialStatus === 'open'): ?>

                                                <div class="small">

                                                    <span class="text-muted">
                                                        Saldo:
                                                    </span>

                                                    <strong>

                                                        R$
                                                        <?= number_format(
                                                            $remainingAmount,
                                                            2,
                                                            ',',
                                                            '.'
                                                        ); ?>

                                                    </strong>

                                                </div>

                                            <?php endif; ?>

                                        <?php endif; ?>


                                    <?php elseif ($purchaseDocumentId > 0): ?>

                                        <!--
                                            Compatibilidade para o caso em que
                                            existe lançamento, mas o resumo novo
                                            ainda não tenha sido carregado.
                                        -->
                                        <span class="badge bg-secondary">

                                            <i
                                                class="fa-solid
                                                       fa-file-invoice-dollar
                                                       me-1">
                                            </i>

                                            Lançada

                                        </span>


                                    <?php else: ?>

                                        <span class="badge bg-secondary">

                                            <i
                                                class="fa-solid
                                                       fa-minus
                                                       me-1">
                                            </i>

                                            Não lançada

                                        </span>

                                    <?php endif; ?>

                                </div>


                                <!-- Ação -->
                                <div class="col-lg-2 text-lg-end">


                                    <?php if ($purchaseDocumentId > 0): ?>

                                        <a
                                            href="<?= $_ENV['URL_ADM']; ?>view-purchase-document/<?= $purchaseDocumentId; ?>"
                                            class="btn btn-outline-primary btn-sm">

                                            <i class="fa-solid fa-eye me-1"></i>

                                            Ver lançamento

                                        </a>


                                    <?php else: ?>

                                        <div
                                            class="d-flex flex-column
                                                   align-items-lg-end gap-2">

                                            <a
                                                href="<?= $_ENV['URL_ADM']; ?>create-purchase-document/<?= (int) $nfe['id']; ?>"
                                                class="btn btn-primary btn-sm">

                                                <i
                                                    class="fa-solid
                                                           fa-dollar-sign
                                                           me-1">
                                                </i>

                                                Lançar Compra

                                            </a>


                                            <a
                                                href="<?= $_ENV['URL_ADM']; ?>uncheck-nfe/<?= (int) $nfe['id']; ?>"
                                                class="btn btn-outline-secondary btn-sm">

                                                <i
                                                    class="fa-solid
                                                           fa-rotate-left
                                                           me-1">
                                                </i>

                                                Desfazer conferência

                                            </a>

                                        </div>

                                    <?php endif; ?>

                                </div>

                            </div>


                        <?php endforeach; ?>


                    <?php else: ?>


                        <div
                            class="alert alert-secondary mb-0"
                            role="alert">

                            Nenhuma NF-e conferida até o momento.

                        </div>


                    <?php endif; ?>


                </div>

            </div>

        </div>

    </div>

</div>