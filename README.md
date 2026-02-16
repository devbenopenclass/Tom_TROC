# TomTroc (MVC PHP sans librairies)

## 1) Prérequis
- PHP 8.1+
- MySQL/MariaDB
- Apache (ou serveur PHP intégré)

## 2) Installation
1. Créez la base `tomtroc`.
2. Exécutez le schéma SQL : `database/schema.sql`.
3. Configurez les variables d'environnement :
   - `DB_DSN` (ex: `mysql:host=localhost;dbname=tomtroc;charset=utf8mb4`)
   - `DB_USER`
   - `DB_PASS`
   - `APP_BASE_URL` (ex: `/tomtroc_mvc/public` sous Apache, vide avec `php -S`)
   - `APP_DEBUG` (`1` en local, `0` en prod)

## 3) Lancer en local
Depuis la racine du projet :

```bash
php -S localhost:8000 -t public
```

Puis ouvrez : `http://localhost:8000`

## 4) Structure MVC
- Routeur : `public/index.php` + `app/Core/Router.php`
- Contrôleurs : `app/Controllers/*`
- Modèles : `app/Models/*`
- Vues : `app/Views/*` (avec layout commun `layouts/main.php`)

## 5) Fonctionnalités MVP
- Inscription / Connexion
- Accueil avec derniers livres
- Liste des livres disponibles + recherche
- Détail livre + profil propriétaire + messagerie
- Mon compte (édition profil)
- Ma bibliothèque (CRUD)
- Messagerie (conversations + fil)

## 6) Notes
- Upload image : `public/assets/uploads/`
- Statuts livre : `available` / `unavailable`
- Page 404 dédiée : `app/Views/errors/404.php`
