<?php
    
namespace App\admsDaman\Models\Repository;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Services\DbConnection;
use Exception;
use PDO;

/**
 *
 * @package App\admsDaman\Models\Repository
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 */
class ResetPasswordRepository extends DbConnection
{

    public function getUser(string $email): array|bool
    {
        // Query para recuperar o registro do banco de dados
        $sql = "SELECT id, email, recover_password, validate_recover_password 
        FROM adms_daman_users 
        WHERE email = :email";

        // Preparar a Querry
        $stmt = $this->getConnection()->prepare($sql);

        // Substituir o link pelo valor
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        // Executar a querry
        $stmt->execute();

        // Ler registro e retornar
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateForgotPassword(array $data): bool
    {

        // Usar try e catch para gerenciar exceção/erro
        try {  // Permanece no try se não houver nenhum erro

            // QUERY para atualizar usuário
            $sql = 'UPDATE adms_daman_users 
            SET recover_password = :recover_password, 
            validate_recover_password = :validate_recover_password, 
            updated_at = :updated_at
            WHERE email = :email LIMIT 1';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':recover_password', $data['recover_password'], PDO::PARAM_STR);
            $stmt->bindValue(':validate_recover_password', date("Y-m-d H:i:s", strtotime('+1hour')), PDO::PARAM_STR);
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
            $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);

            // Executar a QUERY
            return $stmt->execute();
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Recuperar não salva no banco de dados", ['email' => $data['email'], 'error' => $e->getMessage()]);

            return false;
        }
    }

    public function updatePasswordUser(array $data): bool
    {

        // Usar try e catch para gerenciar exceção/erro
        try {  // Permanece no try se não houver nenhum erro

            // QUERY para atualizar usuário
            $sql = 'UPDATE adms_daman_users SET password = :password, recover_password = NULL, validate_recover_password = NULL, updated_at = :updated_at
            WHERE email = :email';

            // Preparar a QUERY
            $stmt = $this->getConnection()->prepare($sql);

            // Substituir os links da QUERY pelo valor
            $stmt->bindValue(':password', password_hash($data['password'], PASSWORD_DEFAULT));
            $stmt->bindValue(':updated_at', date("Y-m-d H:i:s"));
            $stmt->bindValue(':email', $data['email'], PDO::PARAM_STR);

            // Executar a Query
            return $stmt->execute();
        } catch (Exception $e) { // Acessa o catch quando houver erro no try

            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Senha não editada.", ['email' => (string) $data['email'], 'error' => $e->getMessage()]);

            return false;
        }
    }
}
?>