<?php

namespace Source\Validation;

use Source\Models\User as ModelsUser;
use Source\Support\Message;

/**
 * Validações para controller User
 * 
 * @package Source\Validation
 */
class User
{
    /** @var Message */
    private $message;

    /** @var null|string */
    private $field_error;

    public function __construct()
    {
        $this->message = new Message;
    }

    /**
     * Retorna o campo que esta com erro
     *
     * @return void
     */
    public function fieldError()
    {
        return $this->field_error;
    }

    /**
     * Salvar ou atualizar usuario
     *
     * @param array|null $data
     * @return boolean
     */
    public function save(?array $data = null): bool
    {
        $id = (!empty($data['id']) && is_numeric($data['id']) ? $data['id'] : null);

        if (!csrf_check($data)) {
            $this->message->warning('Sessão expirou, por favor atualize a página.');
            return false;
        }

        $name = (!empty($data['name']) ? clear_string($data['name']) : null);
        if (empty($name)) {
            $this->field_error = 'name';
            $this->message->error('Por favor informe um nome valido');
            return false;
        }

        if (strlen($name) < 3) {
            $this->field_error = 'name';
            $this->message->error('Nome não pode ser menor que 3 caracteres');
            return false;
        }


        if (strlen($name) > 50) {
            $this->field_error = 'name';
            $this->message->error('Nome não pode ser maior que 50 caracteres');
            return false;
        }


        if (empty($data['email']) || !is_email($data['email'])) {
            $this->field_error = 'email';
            $this->message->error('Por favor informe um e-mail valido');
            return false;
        }

        if (strlen($data['email']) > 50) {
            $this->field_error = 'email';
            $this->message->error('Email não pode ser maior que 50 caracteres');
            return false;
        }

        $user = (new ModelsUser)->find('email=:email AND id<>:id', "email={$data['email']}&id={$id}")->fetch();
        if ($user) {
            $this->field_error = 'email';
            $this->message->info('Já existe um usuario cadastrado com esse email');
            return false;
        }

        $user = (new ModelsUser)->findById($id);
        if (!$user) {
            $user = new ModelsUser;
        }
        $user->name = $name;
        $user->email = $data['email'];
        if (!$user->save()) {
            $this->message->warning($user->message()->getText());
            return false;
        }

        return true;
    }

    /**
     * Apagar usuario
     *
     * @param array|null $data
     * @return boolean
     */
    public function destroy(?array $data = null): bool
    {
        $id = (!empty($data['id']) && is_numeric($data['id']) ? $data['id'] : null);
        $user = (new ModelsUser)->findById($id);
        if (!$user) {
            $this->message->error('Usuario não encontrado');
            return false;
        }

        if (!$user->destroy()) {
            $this->message->error($user->message()->getText());
            return false;
        }

        return true;
    }


    /**
     * Renderizar a mensagem de erro
     *
     * @return Message
     */
    public function message()
    {
        return $this->message;
    }
}
