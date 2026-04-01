<?php
// Table de routage principale du site.
// Chaque URL HTTP est reliée ici à une méthode de contrôleur.
declare(strict_types=1);

return [
    'GET' => [
        '/' => 'HomeController@index',

        '/register' => 'AuthController@registerForm',
        '/login' => 'AuthController@loginForm',

        '/books/exchange' => 'BookController@exchange',
        '/books/exchangege' => 'BookController@exchange',
        '/books/show' => 'BookController@show',
        '/books/create' => 'BookController@createForm',
        '/books/edit' => 'BookController@editForm',

        '/profiles/show' => 'ProfileController@show',

        '/account' => 'AccountController@index',
        '/account/profile' => 'AccountController@editProfileForm',
        '/admin/books' => 'AdminController@books',
        '/admin/members' => 'AdminController@members',

        '/messages' => 'MessageController@inbox',
        '/messages/thread' => 'MessageController@thread',
    ],
    'POST' => [
        '/register' => 'AuthController@register',
        '/login' => 'AuthController@login',
        '/logout' => 'AuthController@logout',

        '/account/profile' => 'AccountController@updateProfile',
        '/admin/books/status' => 'AdminController@updateBookStatus',
        '/admin/books/delete' => 'AdminController@deleteBook',

        '/books/create' => 'BookController@create',
        '/books/edit' => 'BookController@update',
        '/books/delete' => 'BookController@delete',

        '/messages/send' => 'MessageController@send',
    ],
];
