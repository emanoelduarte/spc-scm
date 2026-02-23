<?php

namespace App\admsDaman\Models\Repository;

use App\admsDaman\Models\Services\DbConnection;

class UsersRepository extends DbConnection
{
    public function getAllUsers() {
        $this->getConnection();
    }
    
}