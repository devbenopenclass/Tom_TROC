# Tom Troc

Application PHP MVC pour l'échange de livres entre membres.

## Stack

- PHP 8.1+
- MySQL ou MariaDB
- Apache recommandé
- compatible aussi avec le serveur PHP intégré

## Structure rapide

- `public/` : point d'entrée web et assets publics
- `app/` : coeur MVC
- `config/` : configuration
- `storage/schema.sql` : schéma SQL de départ

## Installation propre

### 1. Cloner ou copier le projet

Place le projet dans un dossier accessible par ton serveur web.

Exemple XAMPP :

```bash
/opt/lampp/htdocs/tomtroc
```

### 2. Créer la base de données

Crée une base nommée `tomtroc`.

Exemple SQL :

```sql
CREATE DATABASE tomtroc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importer le schéma

Importe :

- [storage/schema.sql](/opt/lampp/htdocs/tomtroc/storage/schema.sql)

Avec phpMyAdmin ou en ligne de commande.

Exemple :

```bash
mysql -u root -p tomtroc < storage/schema.sql
```

### 4. Configurer la connexion

Vérifie :

- [config/database.php](/opt/lampp/htdocs/tomtroc/config/database.php)

Paramètres attendus par défaut :

- host : `127.0.0.1`
- dbname : `tomtroc`
- user : `root`
- pass : ``

### 5. Vérifier l'URL de base

Fichier :

- [config/config.php](/opt/lampp/htdocs/tomtroc/config/config.php)

Si le projet tourne dans XAMPP sous :

```text
http://localhost/tomtroc
```

alors la valeur correcte est :

```php
'base_url' => '/tomtroc'
```

Si tu pointes directement Apache sur `public/`, tu peux utiliser :

```php
'base_url' => ''
```

## Démarrage

### Option A : avec Apache / XAMPP

Configuration conseillée :

- projet dans `htdocs/tomtroc`
- Apache actif
- `mod_rewrite` actif

Ensuite ouvre :

```text
http://localhost/tomtroc
```

Le projet utilise :

- [index.php](/opt/lampp/htdocs/tomtroc/index.php) comme front controller racine
- [.htaccess](/opt/lampp/htdocs/tomtroc/.htaccess) pour rediriger vers `public/`

### Option B : avec le serveur PHP intégré

Depuis la racine du projet :

```bash
php -S localhost:8000
```

Puis ouvre :

```text
http://localhost:8000
```

## Vérifications si “la page est introuvable”

### Cas 1 : URL incorrecte

Vérifie que tu ouvres bien :

```text
http://localhost/tomtroc
```

et non un sous-chemin faux.

### Cas 2 : Apache n'est pas démarré

Sous XAMPP, démarre Apache.

### Cas 3 : `base_url` incorrect

Vérifie :

- [config/config.php](/opt/lampp/htdocs/tomtroc/config/config.php)

Une mauvaise valeur casse les liens et peut donner des pages introuvables.

### Cas 4 : mod_rewrite ou `.htaccess`

Vérifie que :

- Apache autorise `.htaccess`
- `mod_rewrite` est actif

Le fichier utilisé est :

- [.htaccess](/opt/lampp/htdocs/tomtroc/.htaccess)

### Cas 5 : base de données absente

Le site peut continuer partiellement avec des fallbacks, mais certaines pages membres ou la messagerie risquent de ne pas fonctionner correctement si :

- la base `tomtroc` n'existe pas
- les tables ne sont pas importées

## Routes principales

- `/` : accueil
- `/register` : inscription
- `/login` : connexion
- `/logout` : déconnexion
- `/account` : mon compte
- `/books/exchange` : catalogue
- `/books/show?id=...` : fiche livre
- `/messages` : messagerie
- `/profiles/show?id=...` : profil public

## Fonctionnalités principales

- inscription / connexion
- bibliothèque membre
- catalogue public des livres
- fiche détaillée par livre
- messagerie entre membres
- profil public

## Documentation complémentaire

- architecture projet : [DOCUMENTATION_PROJET.md](/opt/lampp/htdocs/tomtroc/DOCUMENTATION_PROJET.md)
- base de données : [DATABASE.md](/opt/lampp/htdocs/tomtroc/DATABASE.md)
- flux du site : [FLOWS_SITE.md](/opt/lampp/htdocs/tomtroc/FLOWS_SITE.md)

## Conseils de reprise

Si tu reprends le projet plus tard, commence par :

1. vérifier `config/database.php`
2. vérifier `config/config.php`
3. importer `storage/schema.sql`
4. tester l'accueil
5. tester ensuite `login`, `books/exchange`, `account` et `messages`
