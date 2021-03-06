<?php
ob_start();

error_reporting(E_ALL);
ini_set("display_errors", "On");


require  'vendor/autoload.php';

// use Source\Core\Router\Router;
use Source\Core\Session;
use CoffeeCode\Router\Router; 

$route = new Router(url(), ":");

/**
 * APP ROUTES
 */

/** Dashboard Routes */
$route->namespace("Source\Controllers")->group(null);
$route->get("/", "User:index");
$route->get("/users", "User:index", 'admin.users');
$route->get("/user/{id}", "User:createOrUpdate", 'admin.user');
$route->post("/user", "User:createOrUpdate", 'admin.user.createOrUpdate'); 
$route->post("/user/destroy", "User:destroy", 'admin.user.destroy');


/** Salvar rota na sessão */
$session  = new \Source\Core\Session();
$session->set('route', $route);



/** Error Routes */
$route->namespace("Source\Controllers")->group(null);
$route->get("/ops/{errorcode}", "Error:error");

/** Dispachar a rota */
$route->dispatch();

/** Error Redirect */
if ($route->error()) {
    $route->redirect("/ops/{$route->error()}");
}

ob_end_flush();
