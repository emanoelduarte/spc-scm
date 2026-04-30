<?php

namespace App\admsDaman\Helpers;

class DiscountCalculator
{
    /**
     * Calcula o valor do desconto com base no valor total e na porcentagem de desconto.
     *
     * @param array com todos os dados para calculo do total e do desconto.
     * @return float O valor do desconto calculado.
     */
    public function calculateDiscount(array $data): float|bool
    {

        $discontType = $data['discount_type'] ?? null;
        $value = $data['discount_value'] ?? null;

        // Calacular desconto para enviar para a models
        if (!empty($value) || !empty($discontType)) {

            if ($value != null && in_array($discontType, ['fixed', 'percent'], true)) {

                $formatedDiscountValue = NormalizeDecimal::normalizeDecimal($data['discount_value']);

                $selectedItems = [];

                foreach ($data['items'] as $item) {
                    if (isset($item['selected_item']) && $item['selected_item'] == 1) {
                        $selectedItems[] = $item;
                    }
                }

                $sub_tot = 0;

                foreach ($selectedItems as $item) {
                    $tot_item = (float)$item['purchased_quantity'] * (float)$item['unit_price'];
                    $sub_tot += $tot_item;
                }

                if ($data['discount_type'] == 'fixed') {

                    $discount_value = $formatedDiscountValue;

                    $data['discount_value'] = $discount_value;
                } else {
                    $discount_value = $sub_tot * ($formatedDiscountValue / 100);
                    $data['discount_value'] = $discount_value;
                }

                return $discount_value;
            }

            return false;
        }

        return (int) 0;
    }
}
