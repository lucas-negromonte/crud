<?php

namespace Source\Core;

/**
 * Session Core
 * @package Source\Core
 */
class Session
{

    /**
     * Session constructor
     */
    public function __construct()
    {
        if (!session_id()) {
            session_start();
        }
    }

    /**
     * @return mixed
     */
    public function __get($name)
    {
        if (!empty($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return null;
    }

    /**
     * @param $name
     * @return boolean
     */
    public function __isset($name)
    {
        return $this->has($name);
    }

    /**
     * @return object|null
     */
    public function all()
    {
        return (object) $_SESSION;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Session
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = (is_array($value) ? (object) $value : $value);
        return $this;
    }

    /**
     * @param string $key
     * @return Session
     */
    public function clearSession($key)
    {
        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * regenerate: regenerar id da sessão criando um novo sem alterar os dados da sessão
     * @return Session
     */
    public function regenerate()
    {
        session_regenerate_id(true);
        return $this;
    }

    /**
     * @return Session
     */
    public function destroy()
    {
        session_destroy();
        return $this;
    }

    /**
     * @return \Source\Support\Message|null
     */
    public function flash()
    {
        if ($this->has("flash")) {
            $flash = $this->flash;
            $this->clearSession("flash");
            return $flash;
        }
        return null;
    }

  
}
