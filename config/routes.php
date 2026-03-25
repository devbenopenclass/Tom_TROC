<?php
// Table de routage principale du site.
// Chaque URL HTTP est reliée ici à une méthode de contrôleur.
declare(strict_types=1);

return [
    'GET' => [
        '/' => 'HomeController@index',

        '/register' => 'AuthController@registerForm',
        '/login' => 'AuthController@loginForm',
        '/logout' => 'AuthController@logout',

        '/books' => 'BookController@index',
        '/books/show' => 'BookController@show',

        '/profile' => 'UserController@show',

        '/account' => 'AccountController@index',
        '/admin/books' => 'AdminController@books',
        '/admin/members' => 'AdminController@members',

        '/library' => 'BookController@myBooks',
        '/library/create' => 'BookController@createForm',
        '/library/edit' => 'BookController@editForm',

        '/messages' => 'MessageController@index',
        '/messages/thread' => 'MessageController@thread',
    ],
    'POST' => [
        '/register' => 'AuthController@register',
        '/login' => 'AuthController@login',

        '/account' => 'AccountController@update',
        '/admin/books/status' => 'AdminController@updateBookStatus',
        '/admin/books/delete' => 'AdminController@deleteBook',

        '/library/create' => 'BookController@store',
        '/library/edit' => 'BookController@update',
        '/library/delete' => 'BookController@delete',

        '/messages/send' => 'MessageController@send',
    ],
];
