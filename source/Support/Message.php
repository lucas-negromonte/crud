<?php

namespace Source\Support;

use Source\Core\Session;

/**
 * Message Support
 * @package Source\Support
 */
class Message
{
    private $text;
    private $type;
    private $icon;

    /**
     * Executado automaticamente quando é dado um echo no objeto
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * getText: recupera o texto da mensagem
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * getType: recupera a classe que será atribuida a mensagem
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function info($message)
    {
        $this->type = "message info bg-info"; // border-info text-info";
        $this->icon = "bi-info-circle";
        $this->text = $this->filter($message);
        return $this;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function success($message)
    {
        $this->type = "message success bg-success"; // border-success text-success";
        $this->icon = "bi-check-circle";
        $this->text = $this->filter($message);
        return $this;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function warning($message)
    {
        $this->type = "message warning bg-warning"; // border-warning text-warning";
        $this->icon = "bi-shield-exclamation";
        $this->text = $this->filter($message);
        return $this;
    }

    /**
     * @param string $message
     * @return Message
     */
    public function error($message)
    {
        $this->type = "message error bg-danger"; // border-danger text-danger";
        $this->icon = "bi-x-circle";
        $this->text = $this->filter($message);
        return $this;
    }

    /**
     * render: retorna a mensagem para o controller
     * @return string
     */
    public function render()
    {
        return "
            <div class='{$this->getType()}'>
                <i class='bi {$this->icon} me-2'></i>
                {$this->getText()}
                <span class='close'>x</span>
            </div>";
    }

    
    /**
     * @return void
     */
    public function json()
    {
        return json_encode(array("error", $this->getText()));
    }

    /**
     * flash: quando enviar um formulario e tiver redirecionando atribuimos a mensagem a uma sessão e apagamos
     * @return void
     */
    public function flash()
    {
        $session = new Session();
        $session->set("flash", $this);
    }

    /**
     * filter: filtra o conteúdo da mensagem
     * @param string $message
     * @return void
     */
    private function filter($message)
    {
        return filter_var($message, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }
}



/**
 * @return \Source\Core\Session
 */
function session()
{
    return new \Source\Core\Session();
}



/**
 * @return null|string
 */
function flash_message()
{
    // $session = new \Source\Core\Session();
    // if ($session->flash()) {
    //     echo $session->flash();
    // }
    if ($flash = session()->flash()) {
        echo $flash;
    }
    return null;
}



/**
 * @param string $url
 * @return void
 */
function redirect($url)
{
    header("HTTP/1.1 302 Redirect");
    if ($url == url() || empty($url)) {
        header("Location: " . get_route('admin.home'));
        exit;
    }

    if (filter_var($url, FILTER_VALIDATE_URL)) {
        header("Location: {$url}");
        exit;
    }

    if (filter_input(INPUT_GET, "route", FILTER_DEFAULT) != $url) {
        $location = url($url);
        header("Location: {$location}");
        exit();
    }
}
