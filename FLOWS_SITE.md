# Schéma Des Flux Du Site

Ce document montre simplement comment les principales pages du site s'enchaînent.

## Parcours utilisateur principal

```mermaid
flowchart TD
    A[Accueil] --> B[Nos livres à l'échange]
    B --> C[Fiche livre]
    C --> D{Utilisateur connecté ?}
    D -- Non --> E[Connexion / Inscription]
    D -- Oui --> F[Envoyer un message]
    E --> F
    F --> G[Messagerie]
    G --> H[Répondre au fil]
```

## Flux d'authentification

```mermaid
flowchart TD
    A[Page Connexion] --> B{Identifiants valides ?}
    B -- Non --> C[Retour au formulaire avec erreur]
    B -- Oui --> D[Session ouverte]
    D --> E[Mon compte]
    D --> F[Catalogue]
    D --> G[Messagerie]
```

## Flux livre côté membre

```mermaid
flowchart TD
    A[Mon compte] --> B[Ajouter un livre]
    A --> C[Modifier un livre]
    A --> D[Supprimer un livre]
    A --> H[Modifier le profil]
    B --> E[books/create]
    C --> F[books/edit]
    D --> G[books/delete]
    H --> I[account/profile]
    E --> A
    F --> A
    G --> A
    I --> A
```

## Flux messagerie

```mermaid
flowchart TD
    A[Fiche livre] --> B[Bouton Envoyer un message]
    B --> C[/messages/thread?user=...&book=...]
    C --> D[Fil de discussion]
    D --> E{Fil disponible ?}
    E -- Oui --> F[Réponse autorisée]
    E -- Non --> G[Retour vers la messagerie]
    F --> H[Message envoyé]
    H --> D
```

## Flux profil public

```mermaid
flowchart TD
    A[Catalogue] --> B[Fiche livre]
    B --> C[Profil du propriétaire]
    C --> D[Voir sa bibliothèque]
    D --> B
```

## Flux administration

```mermaid
flowchart TD
    A[Mon compte] --> B[Ouvrir l'espace admin]
    B --> C[Administration des livres]
    C --> D[Recherche / filtre / tri]
    C --> E[Modifier un livre]
    C --> F[Changer le statut]
    C --> G[Supprimer un livre]
    C --> H[Gestion des membres]
    H --> I[Supprimer un membre]
    H --> J[Restaurer un membre sous 30 jours]
```

## Flux technique simplifié MVC

```mermaid
flowchart LR
    A[Requête navigateur] --> B[public/index.php]
    B --> C[App]
    C --> D[Router]
    D --> E[Controller]
    E --> F[Model]
    E --> G[View]
    F --> H[(MySQL)]
    G --> I[HTML final]
    I --> A
```

## Pages principales concernées

- Accueil : [home/index.php](/opt/lampp/htdocs/tomtroc/app/Views/home/index.php)
- Catalogue : [books/exchange.php](/opt/lampp/htdocs/tomtroc/app/Views/books/exchange.php)
- Fiche livre : [books/show.php](/opt/lampp/htdocs/tomtroc/app/Views/books/show.php)
- Messagerie : [messages/inbox.php](/opt/lampp/htdocs/tomtroc/app/Views/messages/inbox.php)
- Mon compte : [account/index.php](/opt/lampp/htdocs/tomtroc/app/Views/account/index.php)
- Gestion du profil : [account/profile_edit.php](/opt/lampp/htdocs/tomtroc/app/Views/account/profile_edit.php)
- Admin livres : [admin/books.php](/opt/lampp/htdocs/tomtroc/app/Views/admin/books.php)
- Admin membres : [admin/members.php](/opt/lampp/htdocs/tomtroc/app/Views/admin/members.php)
