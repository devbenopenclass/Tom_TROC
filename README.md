# TomTroc

TomTroc est un site de mise en relation autour de l'échange de livres, développé en PHP avec une architecture MVC.

## Fonctionnalités

- inscription et connexion
- espace `Mon compte`
- modification du pseudo, du mot de passe et de l'avatar
- ajout, modification et suppression de livres
- catalogue public avec recherche
- fiche détail d'un livre
- profil public d'un membre
- messagerie entre membres
- espace d'administration

## Stack technique

- PHP 8.1+
- MySQL ou MariaDB
- Apache
- HTML / CSS / JavaScript léger

## Architecture du projet

```text
app/
  Controllers/
  Core/
  Models/
  Views/
config/
public/
storage/
README.md
```

Points importants :

- `app/Core` contient le coeur du mini framework maison
- `app/Controllers` contient la logique applicative
- `app/Models` contient l'accès aux données
- `app/Views` contient les vues PHP
- `public` contient les assets publics

## Installation locale

### 1. Placer le projet dans le serveur web

Exemple avec XAMPP sous Linux :

```text
/opt/lampp/htdocs/tomtroc
```

### 2. Créer la base de données

Exemple :

```sql
CREATE DATABASE tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configurer la connexion à la base

Le projet charge d'abord :

```text
config/database.local.php
```

Ce fichier est ignoré par Git et permet de garder les vrais accès en local.

Exemple de contenu :

```php
<?php
return [
  'db' => [
    'host' => '127.0.0.1',
    'name' => 'tomtroc',
    'user' => 'root',
    'pass' => '',
    'charset' => 'utf8mb4',
  ],
];
```

Si `database.local.php` n'existe pas, le projet utilise les variables d'environnement définies dans :

```text
config/database.php
```

### 4. Vérifier l'URL de base

Dans :

```text
config/config.php
```

la valeur actuelle est :

```php
'base_url' => '/tomtroc'
```

Elle convient à une installation du type :

```text
http://localhost/tomtroc/
```

## Lancement

### Depuis le navigateur local

```text
http://localhost/tomtroc/
```

### Depuis un téléphone sur le même réseau

Utiliser l'adresse IP locale de la machine qui héberge Apache :

```text
http://IP_DU_PC/tomtroc/
```

Exemple :

```text
http://192.168.4.133/tomtroc/
```

`localhost` et `127.0.0.1` ne fonctionnent pas depuis un autre appareil.

## Routes principales

### Pages publiques

- `/`
- `/register`
- `/login`
- `/books/exchange`
- `/books/show?id=...`
- `/profiles/show?id=...`

### Espace membre

- `/account`
- `/account/profile`
- `/books/create`
- `/books/edit?id=...`
- `/messages`
- `/messages/thread?user=...`

### Administration

- `/admin/books`
- `/admin/members`

## Assets importants

- `public/assets/css/style.css`
- `public/assets/css/account-admin.css`
- `public/assets/css/admin.css`
- `public/assets/img/exchange-covers`
- `public/assets/img/figma`
- `public/assets/uploads`

## Fichiers utiles

- `app/Core/App.php`
- `app/Core/Router.php`
- `app/Core/Url.php`
- `app/Core/View.php`
- `app/Controllers/AuthController.php`
- `app/Controllers/AccountController.php`
- `app/Controllers/BookController.php`
- `app/Controllers/MessageController.php`
- `app/Controllers/AdminController.php`
- `app/Models/User.php`
- `app/Models/Book.php`
- `app/Models/Message.php`
- `config/routes.php`
- `config/config.php`

## Documentation du projet

- `DOCUMENTATION_PROJET.md`
- `DATABASE.md`
- `FLOWS_SITE.md`
- `BASE_CONFORMITE_PROJET.md`
