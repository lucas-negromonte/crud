<?php


/**
 * @param string $path
 * @return string
 */
function url($path = null)
{
    if (stristr($_SERVER['HTTP_HOST'], "localhost")) {
        // if (str_replace("www.", "", $_SERVER['HTTP_HOST']) == "localhost") {

        if ($path) {
            return CONF_URL_TEST .
                "/" .
                ($path[0] == "/" ? mb_substr($path, 1) : $path);
        }

        return CONF_URL_TEST;
    }

    if ($path) {
        return CONF_URL_BASE .
            "/" .
            ($path[0] == "/" ? mb_substr($path, 1) : $path);
    }

    return CONF_URL_BASE;
}



/**
 * Retorna a rota
 *
 * @param string $route_name
 * @param [type] $params
 * @return string|null
 */
function get_route($route_name = '', $params = null)
{
    $session  = new \Source\Core\Session();
    if ($session->has('route')) {
        $route = $session->route->route($route_name);
    }


    if (!empty($params)) {
        if (!is_array($params)) {
            parse_str((string)$params, $params);
        }

        foreach ($params as $key => $value) {
            $route = str_replace('{' . $key . '}', $value, $route);
        }
    }

    return ($route ?? null);
}


/**
 * @param string|null $path
 * @param string $theme
 * @return string
 */
function theme($path = null, $theme = CONF_VIEW_THEME)
{
    if (stristr($_SERVER['HTTP_HOST'], "localhost")) {
        // if (str_replace("www.", "", $_SERVER['HTTP_HOST']) == "localhost") {
        if ($path) {
            return CONF_URL_TEST . "/themes/{$theme}/" . ($path[0] == "/" ? mb_substr($path, 1) : $path);
        }
        return CONF_URL_TEST . "/themes/{$theme}";
    }

    if ($path) {
        return CONF_URL_BASE . "/themes/{$theme}/" . ($path[0] == "/" ? mb_substr($path, 1) : $path);
    }
    return CONF_URL_BASE . "/themes/{$theme}";
}



/**
 * @return null|string
 */
function flash_message()
{
    $session = new \Source\Core\Session();
    $flash = $session->flash();
    if ($flash) {
        echo $flash;
    }
    return;
}

/**
 * @return \Source\Core\Session
 */
function session()
{
    return new \Source\Core\Session();
}



/**
 * @return string 
 */
function csrf()
{
    $session = session();
    $session->set('csrf_token', base64_encode(openssl_random_pseudo_bytes(20)));
    return "<input type='hidden' name='csrf_token' value='{$session->csrf_token}' />";
}

/**
 * @return string 
 */
function csrf_check(?array $data = null)
{
    $session = session();
    if (!empty($session->csrf_token) && !empty($data['csrf_token']) && $data['csrf_token'] == $session->csrf_token) {
        return true;
    }
    return false;
}



/**
 * @param string $email
 * @return bool
 */
function is_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_cpf($cpf)
{
    // Extrai somente os números
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);

    // Verifica se foi informado todos os digitos corretamente
    if (strlen($cpf) != 11) {
        return false;
    }

    // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
    if (preg_match('/(\d)\1{10}/', $cpf)) {
        return false;
    }

    // Faz o calculo para validar o CPF
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) {
            return false;
        }
    }
    return true;
}
