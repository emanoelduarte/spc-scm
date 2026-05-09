<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Helpers\GeneratePdfHelper;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

class GeneratePdfPurchasingRepository extends DbConnection
{
    /**
     * Recuperar uma compra específica com seus respectivos itens
     * 
     * @return array|bool compra recuperado do banco de dados
     */

    public function getPurchasing(int $idPurchasing): array|bool
    {

        try {
            $sql = 'SELECT adpu.id, adpu.expected_receipt_date, adpu.service, adpu.delivery_address, adpu.delivery_value, adpu.discount, adpu.created_at, adpu.updated_at,
                 adp.id AS project_id, adp.name AS project_name,
                 ads.legal_name,
                 ads.cnpj,
                 ads.contact_name,
                 ads.phone,
                 ado.id AS order_number,
                 adu.name AS buyer_name,
                 adpm.name AS payment_method
         FROM adms_daman_purchasings AS adpu
         INNER JOIN adms_daman_suppliers AS ads ON ads.id = adpu.adms_daman_supplier_id
         INNER JOIN adms_daman_projects AS adp ON adp.id = adpu.adms_daman_project_id
         INNER JOIN adms_daman_orders AS ado ON ado.id = adpu.adms_daman_order_id
         INNER JOIN adms_daman_users AS adu ON adu.id = adpu.adms_daman_user_id 
         INNER JOIN adms_daman_payment_methods AS adpm ON adpm.id = adpu.adms_daman_payment_methods_id 
         WHERE adpu.id = :id';

            // Preparar a query
            $stmt = $this->getConnection()->prepare($sql);

            //Substituir Links
            $stmt->bindValue(':id', $idPurchasing, PDO::PARAM_INT);

            // Executar a Query
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return false;
            }

            // Buscar itens
            $items = $this->getItems($idPurchasing);

            $result['items'] = $items;

            return $result;
        } catch (Exception $err) {
            GenerateLog::generateLog("error", "Compra não encontrada", ['id' => (int) $idPurchasing]);
            die("Compra não encontrada!" . $err->getMessage());
        }
        return false;
    }

    /**
     * Método para buscar os itens da Compra
     */
    public function getItems(int $idPurchasing): array|bool
    {

        try {

            $sql = "SELECT adpi.adms_daman_purchasing_id, adpi.description, adpi.purchased_quantity, adpi.unit_price, adpi.created_at, adpi.updated_at,
                 admu.name AS measurement_unit
                 FROM adms_daman_purchasing_items AS adpi
                 INNER JOIN adms_daman_measurement_units AS admu ON admu.id=adpi.adms_daman_measurement_units_id
                 WHERE adms_daman_purchasing_id = :idPurchasing";

            $stmt = $this->getConnection()->prepare($sql);

            $stmt->bindValue(':idPurchasing', $idPurchasing, PDO::PARAM_INT);

            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "O pedido não possui itens.", ['name' => $_SESSION['user_name'], 'error' => $e->getMessage()]);

            return false;
        }

        return false;
    }

    private function generatePdf($data, $items): bool
    {

        // === Carrega o HTML-base com placeholders ===
        ob_start();

        $dataPdf = $data;
        $itemsPdf = $items;

        require __DIR__ . '/../../Views/pdfs/viewpdf.php';

        $html_data = ob_get_clean();

        $generatePdf = new GeneratePdfHelper();
        $generatePdf->generatePdfHelper($html_data);
        return true;
    }
}
