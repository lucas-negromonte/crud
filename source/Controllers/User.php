<?php

namespace Source\Controllers;

use Source\Core\Controller;
use Source\Models\User as ModelsUser;

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
        $id = (!empty($data['id']) && is_numeric($data['id']) ? $data['id'] : null);

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

            $user = (new ModelsUser)->find('email=:email AND id<>:id', "email={$data['email']}&id={$id}")->fetch();
            if ($user) {
                $json['error'] = '[name=email]';
                $json["message"] = $this->message->error('Já existe um usuario cadastrado com esse email')->render();
                echo json_encode($json);
                return;
            }

            $user = (new ModelsUser)->findById($id);
            if (!$user) {
                $user = new ModelsUser;
            }
            $user->name = $data['name'];
            $user->email = $data['email'];
            if (!$user->save()) {
                $json["message"] = $user->message()->render();
                echo json_encode($json);
                return;
            }

            $json["message"] = $this->message->success('Usuario ' . (!empty($id) ? 'atualizado' : 'criado') . ' com sucesso')->flash();
            $json["redirect"] = get_route('admin.users');
            echo json_encode($json);
            return;
        }

        // View
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
        $id = (!empty($data['id']) && is_numeric($data['id']) ? $data['id'] : null);
        $user = (new ModelsUser)->findById($id);
        if (!$user) {
            $json["message"] = $this->message->error('Usuario não encontrado')->render();
            echo json_encode($json);
            return;
        }

        if (!$user->destroy()) {
            $json["message"] = $user->message()->render();
            echo json_encode($json);
            return;
        }

        $json['reload'] = true;
        $json['message'] =  $this->message->success('Usuario removido com sucesso')->flash();
        echo json_encode($json);
        return;
    }
}
