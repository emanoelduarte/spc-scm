<?php

namespace App\admsDaman\Models\Services;

use App\admsDaman\Helpers\GenerateLog;
use PDO;
use Throwable;

/**
 * Conexão com o banco de dados.
 *
 * Mantém uma única instância PDO compartilhada entre
 * os repositories durante a execução da aplicação.
 *
 * @author Emanoel Duarte
 */
abstract class DbConnection
{
    /**
     * Conexão compartilhada com o banco de dados.
     *
     * O uso de static garante que todos os repositories
     * utilizem a mesma instância PDO.
     */
    private static ?PDO $connect = null;


    /**
     * Retornar conexão com o banco de dados.
     *
     * Caso a conexão ainda não exista, ela será criada.
     * Nas próximas chamadas, a mesma conexão será retornada.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        try {

            // Criar conexão somente se ainda não existir.
            if (self::$connect === null) {

                self::$connect = new PDO(
                    "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
                    $_ENV['DB_USER'],
                    $_ENV['DB_PASS'],
                    [
                        // Fazer o PDO lançar exceção quando ocorrer erro.
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                        // Utilizar prepared statements nativos do MySQL.
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
            }


            // Retornar sempre a mesma conexão.
            return self::$connect;

        } catch (Throwable $err) {

            GenerateLog::generateLog(
                "alert",
                "Falha na conexão com o banco de dados.",
                [
                    'error' => $err->getMessage()
                ]
            );

            die(
                "Erro 001: Tente novamente, caso o erro persista "
                . "entre em contato com o administrador "
                . "{$_ENV['EMAIL_ADM']}"
            );
        }
    }
}