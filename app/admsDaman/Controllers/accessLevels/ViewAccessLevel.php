<?php

namespace App\admsDaman\Controllers\accessLevels;

use App\admsDaman\Helpers\GenerateLog;
use App\admsDaman\Models\Repository\AccessLevelsRepository;
use App\admsDaman\Views\Services\LoadViewService;

/**
 * Controller para visualização de um Nível de Acesso
 * 
 * Esta classe é responsável por exibir as informações detalhadas de um único nível de acesso. Ela recupera os dados do nível de acesso a partir de um repositório, verifica se o nível de acesso existe e carrega a visualização apropriada.
 * Se por acaso não for encontrado o nível de acesso especificado, uma mensagem de erro é exibida e o usuário é redirecionado para a página de listagem de níveis.
 * 
 * @package App\admsDaman\Controllers\accessLevels
 * @author Emanoel <emanoel.c.duarte@hotmail.com> 
 */
class ViewAccessLevel
{

    /** @var array|string|null $dados Recebe os dados que devem ser enviados para a VIEW */
    private array|string|null $data = null;

    /**
     * Método responsável por receber os dados do repositório de um único lével de acesso e exibir os dados e informações que cada um dos níveis de acesso tem.
     * 
     * Se o nível de acesso não for encontrado, registra
     * um erro, exibe uma mensagem e redireciona para a página de lista de níveis de acessos.
     * 
     * @param int|string $id ID do nível.
     * 
     * @return void
     */
    public function index(int|string $id): void
    {
        // Acessa o if se o id for do tipo inteiro
        if (!(int) $id) {
            // Chama o método para salvar no log o erro
            GenerateLog::generateLog("error", "Nível de acesso não encontrado", ['id' => (int)$id]);

            // Cria a mensagem de erro
            $_SESSION['error'] = "Nível de acesso não encontrado!";

            //Redireciona o usuário para a página que lista os Níveis de acesso.
            header("Location: {$_ENV['URL_ADM']}list-access-levels");

            return;
        }

        // Instanciar o repositório para recuperar o registro do banco de dados e fazer as verificações
        $viewAccessLevel = new AccessLevelsRepository();
        $this->data['levelAccess'] = $viewAccessLevel->getAccessLevel((int) $id);



        // Verificar se encontrou registro no banco de dados
        if (!$this->data['levelAccess']) {
            // Chamar o método para salvar o log
            GenerateLog::generateLog("error", "Nível de acesso não encontrado", ['id' => (int) $id]);

            // Criar a mensagem de erro
            $_SESSION['error'] = "Nível de acesso não encontrado!";

            // Redirecionar o usuário para a página listar
            header("Location: {$_ENV['URL_ADM']}list-access-levels");

            return;
        }

        // Chamar o método para salvar o log
        GenerateLog::generateLog("error", "Visualizar o nível de acesso", ['id' => (int) $id]);

        // Criar o título da página
        $this->data['title_head'] = "Visualizar Nível de Acesso";

        // Carregar a VIEW
        $loadView = new LoadViewService("admsDaman/Views/accessLevels/view", $this->data);
        $loadView->loadView();
    }
}
?>