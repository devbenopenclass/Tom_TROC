# TomTroc

TomTroc est une application web PHP de partage et d'echange de livres entre membres.
Le projet respecte une architecture MVC maison sans librairie PHP externe, avec une couche
`Entity` / `Manager` pour structurer l'acces aux donnees.

## Fonctionnalites

- inscription et connexion
- gestion du compte membre
- suppression de son propre compte
- ajout, modification et suppression de livres
- catalogue public avec recherche
- fiche detail d'un livre
- profil public d'un membre
- messagerie entre membres
- espace d'administration
- attribution du role utilisateur ou administrateur par un admin
- suppression des comptes par l'admin

## Stack technique

- PHP 8.1+
- MySQL ou MariaDB
- Apache
- HTML
- CSS
- PDO

## Structure

```text
app/
  Controllers/
  Core/
  Entities/
  Managers/
  Models/
  Views/
config/
docs/
public/
storage/
README.md
LIEN_REPO.txt
```

## Fichiers principaux

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
- `app/Entities/User.php`
- `app/Entities/Book.php`
- `app/Entities/Message.php`
- `app/Managers/UserManager.php`
- `app/Managers/BookManager.php`
- `app/Managers/MessageManager.php`
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

### 2. Creer la base de donnees

```sql
CREATE DATABASE tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importer les donnees

Le dossier `storage/` contient :

- `storage/schema.sql`
- `storage/tomtroc_demo.sql`

Pour charger la base de demonstration :

```bash
mysql -u root -p tomtroc < storage/tomtroc_demo.sql
```

### 4. Verifier la configuration

Fichiers de configuration :

- `config/config.php`
- `config/database.php`
- `config/database.local.php` si utilise en local

URL de base actuelle :

```php
'base_url' => '/tomtroc'
```

## Lancement

En local :

```text
http://localhost/tomtroc/
```

Depuis un appareil du meme reseau :

```text
http://IP_DU_PC/tomtroc/
```

## Routes principales

Pages publiques :

- `/`
- `/mentions-legales`
- `/politique-confidentialite`
- `/register`
- `/login`
- `/books/exchange`
- `/books/show?id=...`
- `/profiles/show?id=...`

Espace membre :

- `/account`
- `/account/profile`
- `/account/delete`
- `/books/create`
- `/books/edit?id=...`
- `/books/delete`
- `/messages`
- `/messages/thread?user=...`
- `/messages/send`

Administration :

- `/admin/books`
- `/admin/members`
- `/admin/books/status`
- `/admin/books/delete`
- `/admin/members/role`
- `/admin/members/delete`

## Administration

L'admin peut :

- rechercher les livres
- rechercher les membres
- changer la disponibilite d'un livre
- promouvoir un membre en administrateur
- remettre un administrateur en utilisateur
- supprimer un livre
- supprimer n'importe quel compte membre

## Roles

Deux types de comptes sont geres par le projet :

- `user` : compte membre standard
- `admin` : compte ayant acces aux pages `/admin/books` et `/admin/members`

Le changement de role se fait depuis l'espace d'administration des membres.

## Base de donnees

Le schema SQL principal definit trois tables :

- `users`
- `books`
- `messages`

La table `users` peut stocker un role de compte avec le champ `role`.
Les relations entre tables sont definies avec des cles etrangeres et des suppressions en cascade.

## Architecture

Le projet suit l'organisation suivante :

- `public/index.php` : point d'entree unique
- `app/Core/Router.php` : routage GET / POST
- `app/Controllers/` : orchestration des actions
- `app/Views/` : rendu HTML avec layout commun
- `app/Models/` : facades de compatibilite appelees par les controleurs
- `app/Managers/` : acces SQL via PDO
- `app/Entities/` : objets metier hydratant les donnees

Flux technique simplifie :

```text
Requete HTTP
-> public/index.php
-> App
-> Router
-> Controller
-> Model facade
-> Manager
-> Entity / MySQL
-> View
-> HTML
```

## Securite et robustesse

- mots de passe stockes avec `password_hash`
- requetes preparees via PDO
- token CSRF sur les formulaires sensibles
- regeneration de session a la connexion
- controle d'acces pour les espaces membre et admin
- verification MIME sur les uploads d'images
- affichage d'erreurs explicites si une creation ou une modification de livre echoue

## Assets utiles

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
