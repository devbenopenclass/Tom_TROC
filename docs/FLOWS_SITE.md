# Schema Des Flux Du Site

Ce document decrit les parcours reels du projet TomTroc a partir des routes et des controleurs actuellement en place.

## Parcours utilisateur principal

```mermaid
flowchart TD
    A[Accueil /] --> B[Catalogue /books/exchange]
    B --> C[Fiche livre /books/show?id=...]
    C --> D[Profil proprietaire /profiles/show?id=...]
    C --> E{Utilisateur connecte ?}
    E -- Non --> F[Connexion /login ou Inscription /register]
    E -- Oui --> G[Redirection vers /messages/thread?user=...&book=...]
    F --> G
    G --> H[Messagerie /messages]
    H --> I[Premier message ou reponse]
```

## Flux d'authentification

```mermaid
flowchart TD
    A[GET /register ou GET /login] --> B[Soumission formulaire POST]
    B --> C{Donnees valides ?}
    C -- Non --> D[Retour formulaire avec erreur]
    C -- Oui --> E[Compte cree ou session ouverte]
    E --> F[Session regeneree]
    F --> G[Redirection vers /account]
```

## Flux compte membre

```mermaid
flowchart TD
    A[GET /account] --> B[Voir son profil et sa bibliotheque]
    A --> C[GET /account/profile]
    C --> D[Modifier pseudo bio mot de passe avatar]
    D --> E[POST /account/profile]
    E --> F{Validation OK ?}
    F -- Non --> G[Retour Mon compte avec erreur]
    F -- Oui --> H[Compte mis a jour]
    H --> A
    A --> I[POST /account/delete]
    I --> J[Compte supprime puis retour accueil]
```

## Flux livre cote membre

```mermaid
flowchart TD
    A[GET /account] --> B[Ajouter un livre]
    A --> C[Modifier un livre existant]
    A --> D[Supprimer un livre]
    B --> E[GET /books/create]
    E --> F[POST /books/create]
    F --> A
    C --> G[GET /books/edit?id=...]
    G --> H[POST /books/edit]
    H --> A
    D --> I[POST /books/delete]
    I --> A
```

## Flux messagerie

```mermaid
flowchart TD
    A[Fiche livre] --> B[Bouton Envoyer un message]
    B --> C[GET /messages/thread?user=...&book=...]
    C --> D[GET /messages]
    D --> E{Fil deja existant ?}
    E -- Oui --> F[Reponse autorisee]
    E -- Non --> G[Premier message autorise seulement avec contexte livre valide]
    F --> H[POST /messages/send]
    G --> H
    H --> D
```

## Flux profil public

```mermaid
flowchart TD
    A[Catalogue] --> B[Fiche livre]
    B --> C[Profil public du proprietaire]
    C --> D[Voir les livres du membre]
    D --> B
```

## Flux administration

```mermaid
flowchart TD
    A[Utilisateur admin connecte] --> B[GET /admin/books]
    A --> C[GET /admin/members]
    B --> D[Changer statut livre]
    B --> E[Supprimer livre]
    D --> F[POST /admin/books/status]
    E --> G[POST /admin/books/delete]
    C --> H[Changer role membre]
    C --> I[Supprimer membre]
    H --> J[POST /admin/members/role]
    I --> K[POST /admin/members/delete]
```

## Flux technique simplifie MVC

```mermaid
flowchart LR
    A[Requete navigateur] --> B[public/index.php]
    B --> C[App]
    C --> D[Router]
    D --> E[Controller]
    E --> F[Facade de modele]
    F --> G[Manager]
    G --> H[(MySQL)]
    G --> I[Entite]
    E --> J[View]
    J --> K[HTML final]
```

## Pages et routes principales

- Accueil : `/`
- Inscription : `/register`
- Connexion : `/login`
- Catalogue : `/books/exchange`
- Fiche livre : `/books/show?id=...`
- Profil public : `/profiles/show?id=...`
- Mon compte : `/account`
- Edition profil : `/account/profile`
- Messagerie : `/messages`
- Redirection vers un fil : `/messages/thread?user=...&book=...`
- Administration livres : `/admin/books`
- Administration membres : `/admin/members`

## Points importants a dire a l'oral

- Le premier message vers un membre passe par un livre valide ou un fil deja existant.
- Les actions sensibles sont en `POST` avec protection CSRF.
- Les zones membre et admin sont protegees par controle d'acces.
- Le flux technique ne passe plus seulement par un `Model` generique : il inclut maintenant des entites et des managers.
