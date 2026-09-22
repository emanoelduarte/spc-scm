(() => {

    'use strict';


    const historyModal =
        document.getElementById(
            'installmentHistoryModal'
        );

    const reverseModal =
        document.getElementById(
            'reverseInstallmentPaymentModal'
        );


    if (!historyModal || !reverseModal) {
        return;
    }


    const historyTitle =
        document.getElementById(
            'installmentHistoryModalLabel'
        );

    const historyContent =
        document.getElementById(
            'installmentHistoryContent'
        );

    const reversePaymentId =
        document.getElementById(
            'reverse_payment_id'
        );

    const reversePaymentSummary =
        document.getElementById(
            'reversePaymentSummary'
        );

    const reversalReason =
        document.getElementById(
            'reversal_reason'
        );


    /*
     * Evitar inserir texto vindo do banco
     * diretamente no HTML.
     */
    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value ?? '';

        return div.innerHTML;
    }


    /*
     * Formatar valores monetários.
     */
    function formatMoney(value) {

        const number =
            Number(value ?? 0);

        return number.toLocaleString(
            'pt-BR',
            {
                style: 'currency',
                currency: 'BRL'
            }
        );
    }


    /*
     * Formatar data YYYY-MM-DD.
     */
    function formatDate(value) {

        if (!value) {
            return '-';
        }

        const parts =
            String(value).split('-');

        if (parts.length !== 3) {
            return value;
        }

        return (
            parts[2]
            + '/'
            + parts[1]
            + '/'
            + parts[0]
        );
    }


    /*
     * Formatar data/hora vinda do banco.
     */
    function formatDateTime(value) {

        if (!value) {
            return '-';
        }

        const parts =
            String(value)
                .replace('T', ' ')
                .split(' ');

        const date =
            formatDate(
                parts[0]
            );

        if (!parts[1]) {
            return date;
        }

        return (
            date
            + ' '
            + parts[1].substring(0, 5)
        );
    }


    /*
     * Montar uma movimentação do histórico.
     */
    function renderPayment(payment) {

        const reversed =
            payment.status === 'reversed';


        const statusBadge =
            reversed
                ? `
                    <span class="badge bg-secondary">
                        Estornado
                    </span>
                `
                : `
                    <span class="badge bg-success">
                        Ativo
                    </span>
                `;


        let extras = '';


        if (Number(payment.interest_amount) > 0) {

            extras += `
                <div class="small text-muted">
                    Juros:
                    ${formatMoney(
                payment.interest_amount
            )}
                </div>
            `;
        }


        if (Number(payment.penalty_amount) > 0) {

            extras += `
                <div class="small text-muted">
                    Multa:
                    ${formatMoney(
                payment.penalty_amount
            )}
                </div>
            `;
        }


        if (Number(payment.discount_amount) > 0) {

            extras += `
                <div class="small text-muted">
                    Desconto:
                    ${formatMoney(
                payment.discount_amount
            )}
                </div>
            `;
        }


        let observation = '';


        if (payment.observation) {

            observation = `
                <div class="small mt-2">
                    <span class="text-muted">
                        Observação:
                    </span>

                    ${escapeHtml(
                payment.observation
            )}
                </div>
            `;
        }


        let reversal = '';


        if (reversed) {

            reversal = `
                <div
                    class="border-top mt-2 pt-2
                           small text-danger">

                    <div>
                        <strong>
                            Estornado em:
                        </strong>

                        ${formatDateTime(
                payment.reversed_at
            )}
                    </div>

                    <div>
                        <strong>
                            Motivo:
                        </strong>

                        ${escapeHtml(
                payment.reversal_reason
                ?? '-'
            )}
                    </div>

                </div>
            `;

        } else {

            reversal = `
                <div
                    class="border-top mt-2 pt-2
                           text-end">

                    <button
                        type="button"
                        class="btn btn-outline-danger
                               btn-sm btn-reverse-payment"

                        data-payment-id="${Number(
                payment.id
            )}"

                        data-payment-date="${escapeHtml(
                payment.payment_date
            )}"

                        data-payment-total="${Number(
                payment.total_paid
            )}">

                        <i
                            class="fa-solid
                                   fa-arrow-rotate-left
                                   me-1">
                        </i>

                        Estornar

                    </button>

                </div>
            `;
        }


        return `
            <div class="border rounded p-3 mb-3">

                <div
                    class="d-flex
                           justify-content-between
                           align-items-center
                           mb-2">

                    <div class="fw-semibold">

                        ${formatDate(
            payment.payment_date
        )}

                    </div>

                    ${statusBadge}

                </div>


                <div class="row g-2">

                    <div class="col-md-6">

                        <div class="small text-muted">
                            Principal
                        </div>

                        <div class="fw-semibold">
                            ${formatMoney(
            payment.principal_amount
        )}
                        </div>

                    </div>


                    <div class="col-md-6">

                        <div class="small text-muted">
                            Total pago
                        </div>

                        <div class="fw-semibold">
                            ${formatMoney(
            payment.total_paid
        )}
                        </div>

                    </div>

                </div>


                <div class="mt-2">

                    ${extras}

                </div>

                ${observation}

                ${reversal}

            </div>
        `;
    }


    /*
     * Abrir histórico da parcela selecionada.
     */
    historyModal.addEventListener(
        'show.bs.modal',
        function (event) {

            const button =
                event.relatedTarget;


            if (!button) {
                return;
            }


            const installmentNumber =
                button.dataset.installmentNumber
                ?? '';


            historyTitle.innerHTML = `
                <i
                    class="fa-solid
                           fa-clock-rotate-left
                           me-1">
                </i>

                Histórico da
                ${escapeHtml(
                installmentNumber
            )}ª Parcela
            `;


            let payments = [];


            try {

                payments =
                    JSON.parse(
                        button.dataset.payments
                        ?? '[]'
                    );

            } catch (error) {

                console.error(
                    'Erro ao recuperar histórico:',
                    error
                );

                payments = [];
            }


            if (!payments.length) {

                historyContent.innerHTML = `
                    <div
                        class="alert alert-info mb-0">

                        Nenhum pagamento registrado.

                    </div>
                `;

                return;
            }


            historyContent.innerHTML =
                payments
                    .map(renderPayment)
                    .join('');
        }
    );


    /*
     * O histórico é criado dinamicamente.
     *
     * Por isso utilizamos delegação de evento
     * para capturar o botão de estorno.
     */
    historyContent.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '.btn-reverse-payment'
                );


            if (!button) {
                return;
            }


            const paymentId =
                button.dataset.paymentId;

            const paymentDate =
                button.dataset.paymentDate;

            const paymentTotal =
                button.dataset.paymentTotal;


            reversePaymentId.value =
                paymentId;


            reversePaymentSummary.innerHTML = `
                Você está prestes a estornar o
                <strong>
                    pagamento #${escapeHtml(
                paymentId
            )}
                </strong>,

                realizado em

                <strong>
                    ${formatDate(
                paymentDate
            )}
                </strong>,

                no valor de

                <strong>
                    ${formatMoney(
                paymentTotal
            )}
                </strong>.
            `;


            reversalReason.value = '';


            const historyInstance =
                bootstrap.Modal
                    .getOrCreateInstance(
                        historyModal
                    );

            const reverseInstance =
                bootstrap.Modal
                    .getOrCreateInstance(
                        reverseModal
                    );


            /*
             * Fechar primeiro o histórico para não
             * manter dois modais Bootstrap abertos.
             */
            historyModal.addEventListener(
                'hidden.bs.modal',
                function openReverseModal() {

                    reverseInstance.show();
                },
                {
                    once: true
                }
            );


            historyInstance.hide();
        }
    );

    /*
 * ==========================================================
 * VALIDAR FORMULÁRIO DE ESTORNO
 * ==========================================================
 *
 * A validação no JavaScript melhora a experiência do usuário.
 * O Service continua validando novamente no servidor.
 */
    const reversePaymentForm =
        document.getElementById(
            'reverseInstallmentPaymentForm'
        );


    if (reversePaymentForm) {

        reversePaymentForm.addEventListener(
            'submit',
            function (event) {

                const reason =
                    reversalReason.value.trim();


                /*
                 * O motivo do estorno é obrigatório.
                 */
                if (!reason) {

                    event.preventDefault();

                    reversalReason.classList.add(
                        'is-invalid'
                    );

                    reversalReason.focus();

                    return;
                }


                /*
                 * Remover eventual estado de erro
                 * antes de enviar o formulário.
                 */
                reversalReason.classList.remove(
                    'is-invalid'
                );
            }
        );


        /*
         * Remover o erro assim que o usuário
         * começar a informar o motivo.
         */
        reversalReason.addEventListener(
            'input',
            function () {

                if (this.value.trim()) {

                    this.classList.remove(
                        'is-invalid'
                    );
                }
            }
        );
    }

})();