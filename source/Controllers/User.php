<?php

namespace Source\Controllers;

use Source\Core\Controller;


class User extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Listar | Editar | Criar
     *
     * @param array|null $data
     * @return void
     */
    public function users(?array $data = null)
    {
        // Ajax
        if (!empty($data['csrf_token'])) {
            if (!csrf_check($data)) {
                $json["message"] = $this->message->error('Sessão expirou, por favor atualize a página.')->render();
                echo json_encode($json);
                return;
            }

            $name = (!empty($data['name']) ? filter_var($data['name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null);
            if (empty($name)) {
                $json['error'] = '[name=name]';
                $json["message"] = $this->message->error('Por favor informe um nome valido')->render();
                echo json_encode($json);
                return;
            }

            if (empty($data['email']) || !is_email($data['email'])) {
                $json['error'] = '[name=email]';
                $json["message"] = $this->message->error('Por favor informe um e-mail valido')->render();
                echo json_encode($json);
                return;
            }


            $json["message"] = $this->message->success('Dados validados com sucesso')->render();
            echo json_encode($json);
            return;
        }

        // View

        echo $this->view->render("users", array(
            "title" => "Usuarios",
        ));
    }
}
