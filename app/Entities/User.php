<?php
namespace App\Entities;

// Entité immuable représentant un utilisateur tel qu'il sort de la base de données.
// Sert de conteneur typé entre le manager SQL et les modèles de l'application.
final class User
{
  public function __construct(
    private int $id,
    private string $username,
    private string $email,
    private ?string $avatar = null,
    private ?string $bio = null,
    private ?string $createdAt = null,
    // Le hash du mot de passe est optionnel : les requêtes publiques ne le chargent pas
    private ?string $passwordHash = null,
    // Les champs supplémentaires (ex: books_count) sont conservés ici
    private array $extra = []
  ) {
  }

  // Construit une entité User à partir d'un tableau associatif venant de PDO.
  // Les colonnes connues sont typées, le reste part dans $extra.
  public static function fromArray(array $data): self
  {
    // On copie tout d'abord, puis on retire les champs gérés explicitement
    $extra = $data;
    unset(
      $extra['id'],
      $extra['username'],
      $extra['email'],
      $extra['avatar'],
      $extra['bio'],
      $extra['created_at'],
      $extra['password_hash']
    );

    return new self(
      (int)($data['id'] ?? 0),
      (string)($data['username'] ?? ''),
      (string)($data['email'] ?? ''),
      isset($data['avatar']) ? (string)$data['avatar'] : null,
      isset($data['bio']) ? (string)$data['bio'] : null,
      isset($data['created_at']) ? (string)$data['created_at'] : null,
      isset($data['password_hash']) ? (string)$data['password_hash'] : null,
      $extra
    );
  }

  // Retourne l'identifiant de l'utilisateur.
  public function id(): int
  {
    return $this->id;
  }

  // Retourne l'email, utile pour les vérifications d'admin par adresse.
  public function email(): string
  {
    return $this->email;
  }

  // Reconvertit l'entité en tableau associatif pour la couche vue ou modèle.
  // On n'inclut les champs optionnels que s'ils ont une valeur.
  public function toArray(): array
  {
    // On repart des champs extras (books_count, etc.) pour ne pas les perdre
    $data = $this->extra;
    $data['id'] = $this->id;
    $data['username'] = $this->username;
    $data['email'] = $this->email;

    // Les champs optionnels ne sont ajoutés que s'ils ont été renseignés
    if ($this->avatar !== null) {
      $data['avatar'] = $this->avatar;
    }

    if ($this->bio !== null) {
      $data['bio'] = $this->bio;
    }

    if ($this->createdAt !== null) {
      $data['created_at'] = $this->createdAt;
    }

    // Le hash du mot de passe n'est transmis que quand il a été explicitement chargé
    if ($this->passwordHash !== null) {
      $data['password_hash'] = $this->passwordHash;
    }

    return $data;
  }
}
