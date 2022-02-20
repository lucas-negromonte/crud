<?php

namespace Source\Controllers;

use Source\Core\Controller;

/**
 * Error Controllers
 * @package Source\Controllers 
 */
class Error extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Página de erros
     *
     * @param null|array $data
     * @return void
     */
    public function error(?array $data = null): void
    {

        if (!empty($data['errorcode'])) {
            switch ($data['errorcode']) {
                case '404':
                    $descricao = 'Essa página não existe.';
                    break;
                case '405':
                    $descricao = 'Essa página não foi implementada!.';
                    break;
                default:
                    $descricao = 'Não foi possivel carregar essa pagina!';
                    break;
            }
        } else {
            $descricao = 'Não foi possivel carregar essa pagina!';
        }

        echo $this->view->render("error", array(
            "title" => "Ooops! ",
            "message" => "{$descricao}", //msg("not_found"),
            "noFilter" => true
        ));
    }
}
