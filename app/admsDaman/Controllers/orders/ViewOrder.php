<?php

namespace App\admsDaman\Controllers\orders;

use App\admsDaman\Models\Repository\OrdersRepository;

class ViewOrder
{
    public function index(int $id)
    {
        echo "Carregou a página";

        $viewOrder = new OrdersRepository();
        $order = $viewOrder->getOrder($id);
        var_dump($id, $order);
        exit;
    }
}