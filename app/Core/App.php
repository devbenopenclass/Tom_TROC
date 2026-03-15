<?php
namespace App\Core;

require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/Auth.php';

// Simple autoloader for App\ namespace
spl_autoload_register(function (string $class) {
  if (str_starts_with($class, 'App\\')) {
    $path = __DIR__ . '/../' . str_replace('App\\', '', $class) . '.php';
    $path = str_replace('\\', '/', $path);
    if (file_exists($path)) require_once $path;
  }
});

class App
{
  public function run(): void
  {
    $router = new Router();

    // Home
    $router->get('/', 'HomeController@index');

    // Auth
    $router->get('/register', 'AuthController@registerForm');
    $router->post('/register', 'AuthController@register');
    $router->get('/login', 'AuthController@loginForm');
    $router->post('/login', 'AuthController@login');
    $router->post('/logout', 'AuthController@logout');

    // Account
    $router->get('/account', 'AccountController@index');
    $router->get('/account/profile', 'AccountController@editProfileForm');
    $router->post('/account/profile', 'AccountController@updateProfile');

    // Books
    $router->get('/books/exchange', 'BookController@exchange');
    $router->get('/books/show', 'BookController@show');        // ?id=1
    $router->get('/books/create', 'BookController@createForm');
    $router->post('/books/create', 'BookController@create');
    $router->get('/books/edit', 'BookController@editForm');    // ?id=1
    $router->post('/books/edit', 'BookController@update');
    $router->post('/books/delete', 'BookController@delete');

    // Profiles
    $router->get('/profiles/show', 'ProfileController@show');  // ?id=2

    // Messages
    $router->get('/messages', 'MessageController@inbox');
    $router->get('/messages/thread', 'MessageController@thread'); // ?user=2
    $router->post('/messages/send', 'MessageController@send');

    $router->dispatch();
  }
}
