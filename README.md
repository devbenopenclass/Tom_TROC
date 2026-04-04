# TomTroc

TomTroc est une application PHP MVC de partage et d'échange de livres entre membres.

## Fonctionnalités

- inscription et connexion
- gestion du compte membre
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
- HTML
- CSS

## Structure du projet

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
LIEN_REPO.txt
```

## Fichiers importants

- `public/index.php`
- `app/Core/App.php`
- `app/Core/Router.php`
- `app/Core/View.php`
- `app/Core/Url.php`
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

## Installation

### 1. Placer le projet dans le serveur web

Exemple avec XAMPP :

```text
/opt/lampp/htdocs/tomtroc
```

### 2. Créer la base de données

```sql
CREATE DATABASE tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importer les données

Le dossier `storage/` contient :

- `storage/schema.sql`
- `storage/tomtroc_demo.sql`

Pour avoir une base de démonstration complète :

```bash
mysql -u root -p tomtroc < storage/tomtroc_demo.sql
```

### 4. Vérifier la configuration

Fichiers de configuration :

- `config/config.php`
- `config/database.php`
- `config/database.local.php` si utilisé en local

L'URL de base actuelle est :

```php
'base_url' => '/tomtroc'
```

## Lancement

En local :

```text
http://localhost/tomtroc/
```

Depuis un appareil du même réseau :

```text
http://IP_DU_PC/tomtroc/
```

## Routes principales

Pages publiques :

- `/`
- `/register`
- `/login`
- `/books/exchange`
- `/books/show?id=...`
- `/profiles/show?id=...`

Espace membre :

- `/account`
- `/account/profile`
- `/books/create`
- `/books/edit?id=...`
- `/messages`
- `/messages/thread?user=...`

Administration :

- `/admin/books`
- `/admin/members`

## Assets

- `public/assets/css/style.css`
- `public/assets/css/account-admin.css`
- `public/assets/css/admin.css`
- `public/assets/img/figma/`
- `public/assets/img/exchange-covers/`
- `public/assets/uploads/`

## Livrables

- `README.md`
- `LIEN_REPO.txt`
- `storage/schema.sql`
- `storage/tomtroc_demo.sql`
