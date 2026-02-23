<?php

namespace App\admsDaman\Helpers;

/**
 * Converter a controller enviada na URL para o formato da classe
 * 
 * @author Emanoel <emanoel.c.duarte@hotmail.com>
 */
class SlugController 
{
    /**
     * Método estático pode ser chamado diretamente na classe, sem a necessidade de criar uma instância (objeto) da classe.
     * Converter o valor obtido da URL, por exemplo "sobre-empresa" convertido para o nome da Classe "SobreEmpresa"
     * Utilizando as funções para converter tudo para minusculo, converter traço pelo espaço, converter cadas letra da palavra para maiuscula e remover os espaços em branco.
     * 
     * @param $slugController Nome da classe
     * @return string Retorna a controller, por exemplo "sobre-empresa" convertido para o nome da Classe "SobreEmpresa"
     */
    public static function slugController(string $slugController): string
    {

        // Converter para minusculo
        $slugController = strtolower($slugController);

        // Converter o traço para espaço em branco
        $slugController = str_replace("-", " ", $slugController);

        // Converrter a primeira letra de cada palavra para maiusculo
        $slugController = ucwords($slugController);

        // Retirar o espaço em branco
        $slugController = str_replace(" ", "", $slugController);

        // Retorna a controller convertida

        return $slugController;
    }
}