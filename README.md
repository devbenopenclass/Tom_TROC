# TomTroc (MVP MVC PHP)

## Prérequis
- PHP 8.1+ (ou 8.0+)
- MySQL/MariaDB
- Apache (ou `php -S`)

## Installation rapide

1) **Importer la base**
- Crée une base `tomtroc`
- Importe `storage/schema.sql`

2) **Configurer la DB**
- Modifie `config/database.php` (user/pass/dbname)

3) **Vérifier l'URL de base**
- Le projet fonctionne en racine (`http://localhost`) et en sous-dossier (`http://localhost/tomtroc`) grâce à une détection automatique.
- Si besoin, tu peux forcer l'URL dans `config/config.php` via `app.base_url` (ex: `/tomtroc`).

4) **Lancer le serveur**
### Option A (simple) : serveur PHP
Depuis le dossier `public` :
```bash
php -S localhost:8000
```
Puis ouvre http://localhost:8000

### Option B : Apache
- Place le projet dans ton vhost
- Pointe le DocumentRoot sur `/public`

## Routes principales
- `/` accueil
- `/register` inscription
- `/login` connexion
- `/account` mon compte
- `/books/exchange` livres dispo + recherche
- `/messages` messagerie

## Notes
- Upload image optionnel (stocké dans `public/assets/uploads`)
- Messagerie MVP: table `messages` (thread entre 2 utilisateurs)
