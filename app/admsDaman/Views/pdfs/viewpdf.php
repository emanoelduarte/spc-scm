<?php

use App\admsDaman\Helpers\ValueInWords;

opcache_reset();
// echo "Modelo PDF carregado: " . __FILE__ . "<br>";

$logoPath = __DIR__ . '/../../../../public/admsDaman/image/purchasing_order/daman_logo_150px.png';
$logoBase64 = base64_encode(file_get_contents($logoPath));
$logoSrc = 'data:image/png;base64,' . $logoBase64;

if (!file_exists($logoPath)) {
    die("Imagem não encontrada: $logoPath");
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }

        h1,
        h2 {
            text-align: center;
            margin: 0;
        }

        h3 {
            font-size: 10px;
        }

        .header {
            text-align: center;
            margin-top: -40px;
        }

        .logo img {
            width: 150px;
            float: left;
            margin-bottom: -10px;
        }

        td .text-end {
            text-align: right;
        }

        .nome_empresa {
            text-align: center;
            margin: 10px 0;
            font-size: 14px;
        }

        .info-table-top {
            width: 190mm;

        }

        .info-table-top .label {
            font-weight: bold;
            width: 70px;
        }

        .info-table-top .value {
            width: 120px;
        }

        /*********************************** */

        .info-table {
            width: 190mm;
            margin: 10px auto 10px;
            border-collapse: collapse;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 0 6px;
        }

        .info-table .label {
            font-weight: bold;
            min-width: 100px;
        }

        /************************************ */

        .info-table-entrega {
            width: 190mm;
            margin: 0 auto 10px;
            border-collapse: collapse;
        }

        .info-table-entrega td {
            border: 1px solid #000;
            padding: 0 6px;
        }

        .info-table-entrega .label-entrega {
            font-weight: bold;
            max-width: 200px;
        }

        .info-table-entrega .value {
            color: red;
            font-weight: bold;
            width: 85%;
        }

        /******************** */

        .tabela-itens {
            width: 190mm;
            margin: 0 auto;
            border-collapse: collapse;
        }

        .tabela-itens th,
        .tabela-itens td {
            border: 1px solid #000;
            padding: 3px;
            text-align: left;
        }

        .totais {
            width: 190mm;
            margin: 10px auto;
        }

        .totais p {
            margin: 5px 0;
        }

        /************************ */

        table.observacoes {
            width: 190mm;
            margin: 20px auto 10px;
        }

        table.observacoes td {
            border: 1px solid #000;
            border-collapse: collapse;
        }

        table.observacoes td.a {
            text-align: center;
        }

        /**************************************** */

        table.consideracoes-gerais {
            width: 190mm;
            margin: 0 auto 5px;
            line-height: 1;
            font-size: 9px;
        }

        p {
            text-align: justify;
            font-size: 9px;
            line-height: 1;
            margin: 0;
            padding: 0;
        }

        table.consideracoes-gerais td {
            border: 1px solid #000;
            border-collapse: collapse;
        }

        table.consideracoes-gerais .title-consideracoes {
            background-color: #ccc;
            padding: -5px;
            text-align: center;
            column-span: 2;
        }

        /********************************* */

        .assinaturas {
            width: 190mm;
            margin: 10px auto 20px;
            text-align: center;
        }

        .assinaturas td {
            border: 1px solid #000;
            padding-top: 40px;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="logo">
            <img src="<?= $logoSrc ?>" alt="Logo">
        </div>
        <h1>ORDEM DE COMPRA</h1>
    </div>

    <div class="nome_empresa">
        DMA ARQUITETURA E ENGENHARIA LTDA - CNPJ: 42.437.612/0001-96
    </div>

    <?php
    $formatedData = date('d/m/Y', strtotime($dataPdf['created_at']));
    $formatedDataExpected = date('d/m/Y', strtotime($dataPdf['expected_receipt_date']));
    ?>
    <table class="info-table-top">
        <tr>
            <td class="label">OC N°:</td>
            <td class="value"><?= $dataPdf['id'] ?></td>
            <td class="label">Data:</td>
            <td class="value"><?= $formatedData ?></td>
            <td class="label">Prazo de Entrega:</td>
            <td class="value"><?= $formatedDataExpected ?></td>
        </tr>
        <tr>
            <td class="label">Obra:</td>
            <td><?= $dataPdf['project_name'] ?></td>
            <td class="label">Serviço:</td>
            <td colspan="5"><?= $dataPdf['service'] ?></td>
        </tr>
    </table>
    <table class="info-table">
        <tr>
            <td class="label">Fornecedor:</td>

            <td class="label">CNPJ:</td>

            <td class="label">Contato:</td>
            <td class="label">Telefone:</td>

            <td class="label">Forma de Pagamento:</td>
        </tr>
        <tr>

            <td class="value"><?= $dataPdf['legal_name'] ?></td>
            <td class="value"><?= $dataPdf['cnpj'] ?></td>
            <td class="value"><?= $dataPdf['contact_name'] ?></td>
            <td class="value"><?= $dataPdf['phone'] ?></td>
            <td class="value"><?= $dataPdf['payment_method'] ?></td>
        </tr>
    </table>
    <table class="info-table-entrega">
        <tr>
            <td class="label-entrega">Endereço de Entrega:</td>
            <td class="value"><?= $dataPdf['delivery_address'] ?></td>
        </tr>
    </table>

    <table class="tabela-itens">
        <thead>
            <tr>
                <th>Item</th>
                <th>Descrição</th>
                <th>Qtd</th>
                <th>Unid.</th>
                <th>Valor Unit.</th>
                <th>Valor Total</th>
            </tr>
        </thead>
        <?php
        $qtd_items = 0;
        $sub_tot = 0;
        ?>
        <tbody>
            <?php if ($items ?? false): ?>
                <?php foreach ($items as $index => $item): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= $item['description'] ?></td>
                        <td><?= $item['purchased_quantity'] ?></td>
                        <td><?= $item['measurement_unit'] ?></td>
                        <td><?= number_format($item['unit_price'], 2, ',', '.') ?></td>
                        <td>
                            <?php
                            $tot_item = $item['purchased_quantity'] * $item['unit_price']
                            ?>
                            R$ <?= number_format($tot_item, 2, ',', '.'); ?>
                        </td>
                    </tr>
                    <?php
                    $sub_tot += $tot_item;
                    ?>
                <?php endforeach; ?>
                <tr>
                    <td style="text-align: right; font-weight: bold;" colspan="5">Sub Total</td>
                    <td>R$ <?= number_format($sub_tot, 2, ',', '.') ?> </td>
                </tr>
                <tr>
                    <td style="text-align: right; font-weight: bold;" colspan="5">Desconto</td>
                    <?php $discount =  $dataPdf['discount'] ? $dataPdf['discount'] : '0'; ?>
                    <td>R$ <?= number_format($discount, 2, ',', '.'); ?> </td>
                </tr>
                <tr>
                    <td style="text-align: right; font-weight: bold;" colspan="5">Frete</td>
                    <?php $delivery_value =   $dataPdf['delivery_value'] ?  $dataPdf['delivery_value'] : '0'; ?>
                    <td>R$ <?= number_format($delivery_value, 2, ',', '.'); ?> </td>
                </tr>
                <tr>
                    <td style="text-align: right; font-weight: bold;" colspan="5">Total</td>
                    <?php
                    $tot = $sub_tot + $delivery_value - $discount;
                    ?>
                    <td style="font-weight: bold; background: #ffff00;">R$ <?= number_format($tot, 2, ',', '.') ?> </td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Valor por extenso</td>
                    <td colspan="5">
                        <?php
                        $valueInWords = new ValueInWords();
                        $words = $valueInWords->numberToText($tot);
                        ?>
                        <?= strtoupper($words) ?>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="observacoes">
        <tr>
            <td class="a">Observações</td>
            <td>
                <p>1 - Esta Ordem de Compra deverá ser fornecido conforme condições descritas no ORÇAMENTO; </p>
                <p>2 - Citar o numero da Ordem de Compras em suas correspondências e na nota fiscal;</p>
                <p>3 - As notas fiscais devem ser emitidas exclusivamente para esta Ordem de Compra.</p>
                <p>4 - O envio de boletos para pagamento deverá ser realizado por meio digital, para o e-mail:
                    compras.daman@gmail.com
                    . Na ausência do envio digital da fatura e do boleto, não nos responsabilizamos por encargos, multas
                    ou juros decorrentes de atraso no pagamento nesses casos.</p>
            </td>
        </tr>
    </table>
    <table class="consideracoes-gerais">
        <tr>
            <td class="title-consideracoes">
                <h3>CONSIDERAÇÕES GERAIS</h3>
            </td>
        </tr>
    </table>
    <h3>1 - ACEITAÇÃO DA ORDEM DE COMPRA</h3>
    <p>1.1 - Caso o Fornecedor não concorde com as especificações e condições gerais desta Ordem de Compra, deverá
        comunicar à <strong>DMA ARQUITETURA E ENGENHARIA LTDA</strong> no prazo de 24 (vinte e quatro) horas após o seu
        recebimento,
        indicando o motivo da recusa.</p>
    <p>1.2 - A ausência de comunicação dentro desse prazo implicará na aceitação tácita da Ordem de Compra.
    </p>
    <p>1.3 - Em caso de cláusulas conflitantes com a proposta do Fornecedor, prevalecerão as condições desta Ordem de
        Compra.</p>
    <h3>2 - RECEBIMENTO DA ENCOMENDA / DEVOLUÇÕES</h3>
    <p>2.1 - Toda mercadoria deverá ser entregue acompanhada da respectiva nota fiscal, na qual deverá constar, sempre
        que possível, o número desta Ordem de Compra.</p>
    <p>2.2 - Quando não forem estabelecidos prazos e quantidades na Ordem de Compra, entende-se que a DMA ARQUITETURA E
        ENGENHARIA LTDA definirá, em comum acordo com o Fornecedor, uma estratégia de entrega parcelada, a qual deverá
        ser rigorosamente cumprida, reservando-se o direito de devolver, sem aviso prévio, quantidades excedentes ou em
        desacordo com a programação.</p>
    <p>2.3 - A <strong>DMA ARQUITETURA E ENGENHARIA LTDA</strong> não aceitará mercadorias não solicitadas ou em
        desacordo com esta Ordem
        de Compra. Nesses casos, a mercadoria será devolvida imediatamente, sem que caiba à DMA qualquer
        responsabilidade por fretes, avarias, quebras, armazenagem ou quaisquer outros custos.</p>
    <p>2.4 - A <strong>DMA ARQUITETURA E ENGENHARIA LTDA</strong> reserva-se o direito de inspecionar as mercadorias no
        estabelecimento do Fornecedor, durante o processo de fabricação ou antes do embarque.</p>
    <p>2.5 - As mercadorias estarão sujeitas à inspeção e aprovação pela DMA, que terá prazo de 05 (cinco) dias úteis
        para análise, podendo este prazo ser estendido em caso de defeitos ocultos.</p>
    <p>2.6 - Caso as mercadorias não estejam em conformidade com esta Ordem de Compra, serão colocadas à disposição do
        Fornecedor mediante aviso por escrito,(e-mail) ficando a DMA desobrigada quanto à guarda, podendo cobrar
        armazenagem
        (0,01% ao dia) e fretes decorrentes.</p>
    <p>2.6.1 - A DMA reserva-se o direito de debitar do Fornecedor custos decorrentes de não conformidades, mediante
        envio de “Comunicado de Débito”, com prazo de 05 (cinco) dias úteis para manifestação.</p>
    <h3>3 - DIREITO DE PROPRIEDADE</h3>
    <p>3.1 - Todo e qualquer material entregue pela <strong>DMA ARQUITETURA E ENGENHARIA LTDA</strong> ao
        Fornecedor para
        atendimento desta Ordem de Compra, bem como os materiais pagos pela mesma, quer diretamente, quer sob a forma
        de amortização nos custos dos
        produtos a serem fornecidos, tais como amostras, modelos, desenhos, peças, equipamentos,
        ferramentas, material técnico, especificações, etc, serão sempre propriedade exclusiva de nossa
        empresa, não podendo ser cedido a terceiros e ficará em
        poder do Fornecedor que os manterá em comodato mediante contrato, (quando aplicável), obrigando-se a
        devolvê-los após a execução da Ordem de Compra ou quando solicitado, nas condições em que os recebeu
        ressalvado o desgaste decorrente do seu uso
        normal;
    </p>
    <p>3.2 - O fornecedor garante não pender sobre a mercadoria objeto desta Ordem de Compra qualquer dúvida judicial
        ou extra-judicial acerca de patentes, marcas, desenhos, modelos industriais, modelos de utilidades
        ou outros quaisquer privilégios de terceiros,
        pelo que se responsabiliza pelas consequências de quaisquer reclamações sobre infrações, reais ou
        supostas, a tais direitos, inclusive pela defesa da DMA ARQUITETURA E ENGENHARIA LTDA, seus
        sucessores e cessionários, em quaisquer
        procedimentos de terceiros, judiciais ou não, resultante de tais reclamações.
    </p>
    <h3>4 - FATURAS E PAGAMENTOS
    </h3>
    <p>4.1 - O Fornecedor deverá emitir notas fiscais, faturas e duplicatas separadas para cada Ordem de Compra;</p>
    <p>4.2 - As faturas deverão ser enviadas em até 03 (três) dias úteis após a emissão, para o e-mail:
        compras.daman@gmail.com</p>
    <p>4.3 - O pagamento do preço ajustado não implica na aceitação da encomenda;</p>
    <p>4.4 - Nos casos de pagamento “contra apresentação”, este será efetuado em até 15 (quinze) dias após o recebimento
        e validação da mercadoria conforme a Ordem de Compra.
    </p>
    <p>4.5 - O prazo de pagamento será contado a partir da data de entrega da mercadoria no local indicado
        pela <strong>DMA ARQUITETURA E ENGENHARIA LTDA.</strong> Salvo em razão de cobrança via boleto
        bancário, não utrapassando 48hs para entrega do produto, após a emissão do mesmo dos produtos desta Ordem de
        Compra.</p>
    <h3>5 - CANCELAMENTO E ALTERAÇÕES DA ORDEM DE COMPRA
    </h3>
    <p>5.1 - A Ordem de Compra poderá ser cancelada:</p>
    <p>5.1.1 - Se o Fornecedor deixar de cumprir quaisquer de suas cláusulas e/ou especificações;</p>
    <p>5.1.2 - Quando ocorrerem motivos de força maior (paralisação forçada de serviços, incêndio, greves,
        revoluções, etc);
    </p>
    <p>5.2 - A <strong>DMA ARQUITETURA E ENGENHARIA LTDA</strong> reserva-se no direito de cancelar ou
        alterar a Ordem de Compra, mediante aviso escrito, até 15 (quinze) dias antes da data prevista para entrega
    </p>
    <h3>6 - PRESTAÇÃO DE SERVIÇO (MÃO-DE-OBRA)</h3>
    <p>6.1 - Todas as condições retro estipuladas serão aplicáveis, no que couber, aos fornecimentos
        decorrentes da prestação de serviços, sendo que, no caso de não virem a atender rigorosamente as
        especificações da Ordem de Compra e as demais pertinentes à
        natureza do serviço, poderão ser pura e simplesmente recusados, considerando-se, se assim convier à
        <strong>DMA ARQUITETURA E ENGENHARIA LTDA</strong>, cancelado a Ordem de Compra, inclusive pelo seu
        remanescente, cabendo à mesma ainda ser indenizada pelo valor do material fornecido para ser elaborado, bem como
        pelas perdas e danos resultantes da
        execução defeituosa da Ordem de Compra, inclusive dos artigos 389 e 402 do Código Civil Brasileiro.
    </p>
    <h3>7 - FORO</h3>
    <p>7.1 - Fica eleito o Foro da Comarca de Belém-PA para dirimir eventuais pendências relacionadas com
        esta Ordem de Compra, renunciando-se a qualquer outro, por mais privilegiado que seja.</p>


    <table class="assinaturas">
        <tr>
            <td>__________________________<br>Solicitado por</td>
            <td>__________________________<br>Conferido por</td>
            <td>__________________________<br>Recebido por</td>
        </tr>
    </table>

    <div class="footer">
        Belém-PA – Foro da Comarca de Belém – FQ-70 Rev.01
    </div>
</body>

</html>