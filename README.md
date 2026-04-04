# TomTroc

TomTroc est une application web PHP en architecture MVC dédiée au partage et à l'échange de livres entre membres.

Le projet propose :
- une page d'accueil éditoriale
- l'inscription et la connexion
- un espace membre avec gestion du profil
- l'ajout, la modification et la suppression de livres
- un catalogue public avec recherche
- une fiche détail pour chaque livre
- un profil public par membre
- une messagerie entre membres
- un espace d'administration pour les livres et les membres

## Stack

- PHP 8.1+
- MySQL ou MariaDB
- Apache
- HTML
- CSS

## Architecture

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

Fichiers principaux :
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

Exemple avec XAMPP sous Linux :

```text
/opt/lampp/htdocs/tomtroc
```

### 2. Créer la base de données

```sql
CREATE DATABASE tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importer la base

Le dossier `storage/` contient :
- `storage/schema.sql` : structure seule
- `storage/tomtroc_demo.sql` : structure et données de démonstration

Pour récupérer rapidement un environnement de test :

```bash
mysql -u root -p tomtroc < storage/tomtroc_demo.sql
```

### 4. Vérifier la configuration

Configuration de base :
- `config/config.php`
- `config/database.php`

Le projet peut aussi utiliser :
- `config/database.local.php`

Configuration d'URL actuelle :

```php
'base_url' => '/tomtroc'
```

Cela correspond à une installation du type :

```text
http://localhost/tomtroc/
```

## Lancement

Depuis un navigateur local :

```text
http://localhost/tomtroc/
```

Depuis un autre appareil du même réseau :

```text
http://IP_DU_PC/tomtroc/
```

Exemple :

```text
http://192.168.4.133/tomtroc/
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

## Fonctionnalités

### Authentification

- création de compte
- connexion par email ou pseudo
- déconnexion
- protection CSRF sur les formulaires sensibles

### Espace membre

- modification du pseudo
- changement de mot de passe
- upload d'avatar
- bibliothèque personnelle

### Livres

- ajout d'un livre
- édition d'un livre
- suppression d'un livre
- couverture personnalisée ou image de fallback
- statut de disponibilité

### Catalogue public

- recherche texte
- cartes de livres avec vignette
- badge de disponibilité
- fiche détail de livre

### Messagerie

- liste des conversations
- affichage d'un fil
- envoi de message entre membres

### Administration

- gestion des livres
- changement rapide de disponibilité
- suppression d'un livre
- recherche dans les livres
- recherche dans les membres
- navigation rapide entre livres et membres

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
