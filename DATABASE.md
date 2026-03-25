# Base De Données Tom Troc

Ce document décrit la base `tomtroc` table par table à partir du schéma livré dans [storage/schema.sql](/opt/lampp/htdocs/tomtroc/storage/schema.sql).

## Vue d'ensemble

La base repose sur 3 tables principales :

- `users` : comptes membres
- `books` : livres publiés par les membres
- `messages` : messages privés entre membres

Les relations sont simples :

- un `user` possède plusieurs `books`
- un `user` peut envoyer et recevoir plusieurs `messages`

## Table `users`

Rôle :
- stocke les comptes membres du site
- sert pour l'authentification, le profil public et l'espace `Mon compte`

Colonnes :

- `id`
  Type : `INT UNSIGNED`
  Rôle : identifiant technique unique du membre
  Contraintes : clé primaire, auto-incrémentée

- `username`
  Type : `VARCHAR(50)`
  Rôle : pseudo public affiché sur le site
  Contraintes : obligatoire, unique

- `email`
  Type : `VARCHAR(255)`
  Rôle : adresse de connexion
  Contraintes : obligatoire, unique

- `password_hash`
  Type : `VARCHAR(255)`
  Rôle : mot de passe hashé
  Contraintes : obligatoire

- `avatar`
  Type : `VARCHAR(255)`
  Rôle : chemin vers l'image de profil
  Contraintes : nullable

- `bio`
  Type : `TEXT`
  Rôle : courte présentation du membre
  Contraintes : nullable

- `created_at`
  Type : `DATETIME`
  Rôle : date de création du compte
  Contraintes : obligatoire, valeur par défaut `CURRENT_TIMESTAMP`

- `updated_at`
  Type : `DATETIME`
  Rôle : date de dernière mise à jour
  Contraintes : nullable, mise à jour automatique

Index et contraintes :

- `PRIMARY KEY (id)`
- `UNIQUE KEY uk_users_email (email)`
- `UNIQUE KEY uk_users_username (username)`

Utilisation dans le code :

- [User.php](/opt/lampp/htdocs/tomtroc/app/Models/User.php)
- [AuthController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/AuthController.php)
- [AccountController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/AccountController.php)
- [ProfileController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/ProfileController.php)

## Table `books`

Rôle :
- stocke les livres proposés à l’échange
- alimente le catalogue public, les fiches détail et les bibliothèques membres

Colonnes :

- `id`
  Type : `INT UNSIGNED`
  Rôle : identifiant unique du livre
  Contraintes : clé primaire, auto-incrémentée

- `user_id`
  Type : `INT UNSIGNED`
  Rôle : propriétaire du livre
  Contraintes : obligatoire, clé étrangère vers `users.id`

- `title`
  Type : `VARCHAR(255)`
  Rôle : titre du livre
  Contraintes : obligatoire

- `author`
  Type : `VARCHAR(255)`
  Rôle : auteur du livre
  Contraintes : obligatoire

- `image`
  Type : `VARCHAR(255)`
  Rôle : chemin ou URL de la couverture
  Contraintes : nullable

- `description`
  Type : `LONGTEXT`
  Rôle : description libre du livre
  Contraintes : nullable

- `status`
  Type : `ENUM('available','unavailable','reserved')`
  Rôle : état du livre dans le catalogue
  Contraintes : obligatoire, défaut `available`

- `created_at`
  Type : `DATETIME`
  Rôle : date d’ajout
  Contraintes : obligatoire

- `updated_at`
  Type : `DATETIME`
  Rôle : date de dernière modification
  Contraintes : nullable, mise à jour automatique

Index et contraintes :

- `PRIMARY KEY (id)`
- `KEY idx_books_user_id (user_id)`
- `KEY idx_books_status (status)`
- `KEY idx_books_title (title)`
- `FOREIGN KEY (user_id) REFERENCES users(id)`
  Effet : suppression en cascade si le membre est supprimé

Utilisation dans le code :

- [Book.php](/opt/lampp/htdocs/tomtroc/app/Models/Book.php)
- [BookController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/BookController.php)
- [HomeController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/HomeController.php)
- [ProfileController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/ProfileController.php)

## Table `messages`

Rôle :
- stocke les messages privés entre membres
- alimente la messagerie et le badge de non lus dans le header

Colonnes :

- `id`
  Type : `INT UNSIGNED`
  Rôle : identifiant unique du message
  Contraintes : clé primaire, auto-incrémentée

- `sender_id`
  Type : `INT UNSIGNED`
  Rôle : membre qui envoie le message
  Contraintes : obligatoire, clé étrangère vers `users.id`

- `receiver_id`
  Type : `INT UNSIGNED`
  Rôle : membre destinataire
  Contraintes : obligatoire, clé étrangère vers `users.id`

- `content`
  Type : `TEXT`
  Rôle : contenu textuel du message
  Contraintes : obligatoire

- `is_read`
  Type : `TINYINT(1)`
  Rôle : état lu / non lu
  Contraintes : obligatoire, défaut `0`

- `created_at`
  Type : `DATETIME`
  Rôle : date d’envoi
  Contraintes : obligatoire, défaut `CURRENT_TIMESTAMP`

Index et contraintes :

- `PRIMARY KEY (id)`
- `KEY idx_messages_sender (sender_id)`
- `KEY idx_messages_receiver (receiver_id)`
- `KEY idx_messages_pair_date (sender_id, receiver_id, created_at)`
- `FOREIGN KEY (sender_id) REFERENCES users(id)`
- `FOREIGN KEY (receiver_id) REFERENCES users(id)`

Utilisation dans le code :

- [Message.php](/opt/lampp/htdocs/tomtroc/app/Models/Message.php)
- [MessageController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/MessageController.php)
- [header.php](/opt/lampp/htdocs/tomtroc/app/Views/layouts/header.php)

## Relations entre les tables

### `users` -> `books`

Relation :
- un membre peut posséder plusieurs livres

Clé :
- `books.user_id -> users.id`

Effet métier :
- la bibliothèque affichée sur `Mon compte`
- les profils publics des membres
- le nom du propriétaire sur chaque fiche livre

### `users` -> `messages`

Relation :
- un membre peut envoyer plusieurs messages
- un membre peut recevoir plusieurs messages

Clés :
- `messages.sender_id -> users.id`
- `messages.receiver_id -> users.id`

Effet métier :
- liste des conversations
- fil de discussion
- compteur de messages non lus

## Règles métier importantes

### Authentification

- un compte est identifié par `email` ou `username`
- le mot de passe est stocké hashé

### Livres

- chaque livre appartient à un seul membre
- un membre ne peut modifier ou supprimer que ses propres livres
- l’état du livre contrôle l’affichage des badges et la disponibilité

### Messagerie

- la messagerie relie deux membres par des messages successifs
- le premier envoi est pensé pour partir d’une fiche livre
- ensuite la réponse peut continuer dans le fil existant

## Schéma relationnel simple

```text
users (1) -------- (N) books
  id                  user_id

users (1) -------- (N) messages
  id                  sender_id

users (1) -------- (N) messages
  id                  receiver_id
```

## Source du schéma

Le schéma SQL de référence se trouve ici :

- [storage/schema.sql](/opt/lampp/htdocs/tomtroc/storage/schema.sql)
