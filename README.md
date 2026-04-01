# TomTroc

TomTroc est une application PHP MVC de partage et d'echange de livres entre membres.

Le projet permet :
- l'inscription et la connexion
- la gestion d'un compte membre
- l'ajout, la modification et la suppression de livres
- un catalogue public avec recherche
- une fiche detail par livre
- un profil public par membre
- une messagerie entre membres
- un espace d'administration

## Stack technique

- PHP 8.1+
- MySQL ou MariaDB
- Apache
- HTML, CSS, JavaScript leger

## Arborescence

```text
app/
  Controllers/
  Core/
  Models/
  Views/
config/
public/
storage/
LIEN_REPO.txt
README.md
```

## Installation locale

### 1. Placer le projet dans le serveur web

Exemple avec XAMPP sous Linux :

```text
/opt/lampp/htdocs/tomtroc
```

### 2. Creer la base de donnees

Exemple :

```sql
CREATE DATABASE tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importer le schema SQL

Le fichier SQL du projet se trouve ici :

```text
storage/schema.sql
```

Exemple :

```bash
mysql -u root -p tomtroc < storage/schema.sql
```

Ce schema cree les tables principales du projet :
- `users`
- `books`
- `messages`

### 4. Configurer l'acces a la base

Le projet charge en priorite :

```text
config/database.local.php
```

Ce fichier est a creer en local et n'est pas versionne.

Exemple :

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

Si ce fichier n'existe pas, le projet utilise la configuration de secours dans :

```text
config/database.php
```

### 5. Verifier l'URL de base

Dans :

```text
config/config.php
```

la configuration actuelle est :

```php
'base_url' => '/tomtroc'
```

Elle correspond a une installation du type :

```text
http://localhost/tomtroc/
```

## Lancement

### Depuis le navigateur local

```text
http://localhost/tomtroc/
```

### Depuis un telephone sur le meme reseau

Utiliser l'adresse IP locale du PC qui heberge Apache :

```text
http://IP_DU_PC/tomtroc/
```

Exemple :

```text
http://192.168.4.133/tomtroc/
```

`localhost` et `127.0.0.1` ne fonctionnent pas depuis un autre appareil.

## Comptes de test

Le depot ne contient pas de mot de passe en clair.

Compte admin actuellement relie a l'application :
- `id` : `4`
- `username` : `ben`

Le mot de passe doit etre defini dans la base locale utilisee pour les tests.

Pour tester un compte membre standard, le plus simple est de creer un compte via :

```text
/register
```

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

## Fichiers importants

- `app/Core/App.php`
- `app/Core/Router.php`
- `app/Core/Url.php`
- `app/Core/View.php`
- `app/Core/Auth.php`
- `app/Core/Csrf.php`
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
- `storage/schema.sql`

## Assets utiles

- `public/assets/css/style.css`
- `public/assets/css/account-admin.css`
- `public/assets/css/admin.css`
- `public/assets/img/exchange-covers`
- `public/assets/img/figma`
- `public/assets/uploads`

## Livrables presents dans le depot

- `README.md`
- `LIEN_REPO.txt`
- `storage/schema.sql`
