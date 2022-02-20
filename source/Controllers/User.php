<?php

namespace Source\Controllers;

use Source\Core\Controller;
use Source\Models\User as ModelsUser;
use Source\Validation\User as ValidationUser;

class User extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Listar usuarios
     *
     * @return void
     */
    public function index(): void
    {
        $users = (new ModelsUser)->find('')->order('id desc')->fetch(true);
        echo $this->view->render("views/user/index", array(
            "title" => "Lista de usuarios",
            "sub_title" => "Mostrar todos usuarios do sistema",
            "users" => $users,
        ));
    }

    /**
     * Editar | Criar
     *
     * @param array|null $data
     * @return void
     */
    public function createOrUpdate(?array $data = null)
    {
        // Ajax
        if (!empty($data['csrf_token'])) {
            $user_validate = new ValidationUser;
            if (!$user_validate->save($data)) {
                $json['message'] = $user_validate->message()->render();
                (!empty($user_validate->fieldError()) ? $json['error'] = '[name=' . $user_validate->fieldError() . ']' : null);
                echo json_encode($json);
                return;
            }

            $json["message"] = $this->message->success('Usuario ' . (!empty($id) ? 'atualizado' : 'criado') . ' com sucesso')->flash();
            $json["redirect"] = get_route('admin.users');
            echo json_encode($json);
            return;
        }

        // View
        $id = (!empty($data['id']) && is_numeric($data['id']) ? $data['id'] : null);
        $user = (new ModelsUser)->findById($id);
        echo $this->view->render("views/user/create-or-update", array(
            "title" => ($user ? 'Editar usuario' : 'Novo usuario'),
            "sub_title" => ($user ? "Atualizar os dados do usuario {$user->name}" : 'Criar um novo usuario'),
            "user" => $user,
        ));
    }


    /**
     * Apagar usuario
     *
     * @param array|null $data
     * @return void
     */
    public function destroy(array $data = null): void
    {
        $user_validate = new ValidationUser;
        if (!$user_validate->destroy($data)) {
            $json['message'] = $user_validate->message()->render();
            echo json_encode($json);
            return;
        }

        $json['reload'] = true;
        $json['message'] =  $this->message->success('Usuario removido com sucesso')->flash();
        echo json_encode($json);
        return;
    }
}
