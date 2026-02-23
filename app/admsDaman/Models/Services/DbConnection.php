<?php

namespace App\admsDaman\Models\Services;

use App\admsDaman\Helpers\GenerateLog;
use Exception;
use PDO;

/**
 * Conexão com o banco de dados
 * 
 * @author Emanoel Duarte <emanoel.c.duarte@hotmail.com>
 */
abstract class DbConnection
{
    /** @var object $connect Recebe a conexão com o banco de dados */
    private object $connect;

    /**
     * Realiza a conexão com o banco de dados.
     * Não realizando a conexão corretamente, para o processamento da página é apresentada a mensagem de erro de conexão com o e-mail do administrador do sistema
     * 
     * @return object $connect retorna a conxão com o banco de dados
     */
    public function getConnection(): object
    {
        try {

         $dbname = "spcdaman";
            // Conexão com a porta
            //$this->connect = new PDO("mysql:host=localhost;port=3306;dbname=" . $dbname, "root", "");

            //Conexão sem a porta
            // Conexão com a porta
            $this->connect = new PDO("mysql:host=localhost;dbname=" . $dbname, "root", "");

            echo "Conexão com o banco de dados realizada com sucesso!";

            return $this->connect;
        }catch(Exception $err) {
            GenerateLog::generateLog("alert", "Falha na conexão com o banco de dados.", ['error' => $err->getMessage()]);

            die("Erro 001: Tente novamente, caso o erro persista entre em contato com o administrador Emanoel Duarte emanoel.c.duarte@hotmail.com");

        }
    }
}