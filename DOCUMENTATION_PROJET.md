# Documentation Du Projet Tom Troc

Ce document résume le rôle de chaque fichier principal du site pour faciliter la reprise du projet.

## Vue d'ensemble

Le projet suit une structure MVC simple :

- `public/` : point d'entrée web et assets publics
- `config/` : configuration générale, base de données, routes
- `app/Core/` : coeur technique minimal du framework maison
- `app/Models/` : accès aux données métiers
- `app/Controllers/` : logique applicative et navigation
- `app/Views/` : templates HTML/PHP du site

## Point d'entrée

- [public/index.php](/opt/lampp/htdocs/tomtroc/public/index.php)
  Lance la session PHP, charge l'application et exécute le routeur.

## Configuration

- [config/config.php](/opt/lampp/htdocs/tomtroc/config/config.php)
  Contient la configuration générale du site, notamment `base_url`.

- [config/database.php](/opt/lampp/htdocs/tomtroc/config/database.php)
  Définit les paramètres de connexion MySQL locale.

- [config/routes.php](/opt/lampp/htdocs/tomtroc/config/routes.php)
  Déclare une table de routes HTTP. Le projet utilise aussi un enregistrement direct dans `App::run()`.

## Coeur technique

- [app/Core/App.php](/opt/lampp/htdocs/tomtroc/app/Core/App.php)
  Enregistre les routes principales puis délègue la requête au routeur.

- [app/Core/Router.php](/opt/lampp/htdocs/tomtroc/app/Core/Router.php)
  Associe une URL à une méthode de contrôleur et exécute le bon handler.

- [app/Core/Controller.php](/opt/lampp/htdocs/tomtroc/app/Core/Controller.php)
  Classe parente des contrôleurs, avec aide au rendu.

- [app/Core/View.php](/opt/lampp/htdocs/tomtroc/app/Core/View.php)
  Charge le header, la vue demandée et le footer.

- [app/Core/Model.php](/opt/lampp/htdocs/tomtroc/app/Core/Model.php)
  Ouvre la connexion PDO et la partage aux modèles.

- [app/Core/Auth.php](/opt/lampp/htdocs/tomtroc/app/Core/Auth.php)
  Gère la session utilisateur : connecté, id courant, protections.

- [app/Core/Url.php](/opt/lampp/htdocs/tomtroc/app/Core/Url.php)
  Calcule l'URL de base du projet pour les liens internes.

## Modèles métiers

- [app/Models/User.php](/opt/lampp/htdocs/tomtroc/app/Models/User.php)
  Gère les comptes utilisateurs : recherche, création, profil, mot de passe, avatar.

- [app/Models/Book.php](/opt/lampp/htdocs/tomtroc/app/Models/Book.php)
  Gère les livres : catalogue, fiches, images, descriptions et fallbacks de démonstration.

- [app/Models/Message.php](/opt/lampp/htdocs/tomtroc/app/Models/Message.php)
  Gère les conversations, messages, non lus et contacts.

## Contrôleurs

- [app/Controllers/HomeController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/HomeController.php)
  Prépare la page d'accueil avec les derniers livres.

- [app/Controllers/AuthController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/AuthController.php)
  Gère l'inscription, la connexion et la déconnexion.

- [app/Controllers/BookController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/BookController.php)
  Gère la liste des livres, la fiche détail, les formulaires d'ajout/édition et la suppression.

- [app/Controllers/MessageController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/MessageController.php)
  Gère la messagerie, l'ouverture d'un fil et l'envoi des messages.

- [app/Controllers/AccountController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/AccountController.php)
  Gère la page "Mon compte" et la mise à jour du profil connecté.

- [app/Controllers/ProfileController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/ProfileController.php)
  Affiche le profil public d'un membre et sa bibliothèque.

- [app/Controllers/AdminController.php](/opt/lampp/htdocs/tomtroc/app/Controllers/AdminController.php)
  Regroupe les écrans de gestion administrateur.

## Layouts communs

- [app/Views/layouts/header.php](/opt/lampp/htdocs/tomtroc/app/Views/layouts/header.php)
  Header commun, navigation, logo, badge messagerie et chargement des CSS.

- [app/Views/layouts/footer.php](/opt/lampp/htdocs/tomtroc/app/Views/layouts/footer.php)
  Footer commun, liens légaux et coeur décoratif.

## Pages publiques

- [app/Views/home/index.php](/opt/lampp/htdocs/tomtroc/app/Views/home/index.php)
  Accueil avec hero, derniers livres, étapes et valeurs.

- [app/Views/books/exchange.php](/opt/lampp/htdocs/tomtroc/app/Views/books/exchange.php)
  Catalogue public de tous les livres avec barre de recherche.

- [app/Views/books/show.php](/opt/lampp/htdocs/tomtroc/app/Views/books/show.php)
  Fiche détaillée d'un livre : visuel, description, propriétaire, bouton de message.

- [app/Views/profiles/show.php](/opt/lampp/htdocs/tomtroc/app/Views/profiles/show.php)
  Profil public d'un membre.

## Authentification

- [app/Views/auth/login.php](/opt/lampp/htdocs/tomtroc/app/Views/auth/login.php)
  Formulaire de connexion.

- [app/Views/auth/register.php](/opt/lampp/htdocs/tomtroc/app/Views/auth/register.php)
  Formulaire d'inscription.

## Espace membre

- [app/Views/account/index.php](/opt/lampp/htdocs/tomtroc/app/Views/account/index.php)
  Page "Mon compte" avec carte profil, informations personnelles et bibliothèque.

- [app/Views/account/profile_edit.php](/opt/lampp/htdocs/tomtroc/app/Views/account/profile_edit.php)
  Ancienne page d'édition de profil plus simple.

- [app/Views/books/form.php](/opt/lampp/htdocs/tomtroc/app/Views/books/form.php)
  Formulaire d'ajout ou d'édition d'un livre.

- [app/Views/messages/inbox.php](/opt/lampp/htdocs/tomtroc/app/Views/messages/inbox.php)
  Interface de messagerie avec conversations, fil actif et réponse.

## Administration

- [app/Views/admin/books.php](/opt/lampp/htdocs/tomtroc/app/Views/admin/books.php)
  Tableau de gestion des livres.

- [app/Views/admin/members.php](/opt/lampp/htdocs/tomtroc/app/Views/admin/members.php)
  Tableau de gestion des membres.

## Feuilles de style

- [public/assets/css/style.css](/opt/lampp/htdocs/tomtroc/public/assets/css/style.css)
  CSS principal du site public : accueil, livres, messagerie, responsive.

- [public/assets/css/account-admin.css](/opt/lampp/htdocs/tomtroc/public/assets/css/account-admin.css)
  CSS spécifique à la page "Mon compte".

- [public/assets/css/admin.css](/opt/lampp/htdocs/tomtroc/public/assets/css/admin.css)
  CSS des écrans d'administration.

## Images et assets

- `public/assets/img/`
  Contient les maquettes exportées, les couvertures, les logos et les éléments décoratifs.

- `public/assets/img/exchange-covers/`
  Contient les couvertures utilisées pour les cartes livres.

- `public/assets/img/detail-covers/`
  Contient les visuels dédiés à certaines fiches détail.

- `public/assets/uploads/`
  Dossier des couvertures envoyées par les utilisateurs.

## Points d'attention

- Le projet mélange données réelles en base et catalogue de secours dans `Book.php`.
- Certaines vues sont fortement pilotées par le CSS pour coller à la maquette.
- La messagerie démarre un premier échange depuis une fiche livre, puis permet de répondre dans le fil.
- Le fichier le plus central côté rendu public est [style.css](/opt/lampp/htdocs/tomtroc/public/assets/css/style.css).

## Si tu veux aller plus loin

Pour une prochaine étape, on peut aussi ajouter :

- une carte de navigation des pages du site
- un schéma du flux MVC
- une documentation base de données table par table
