<?php
namespace App\Entities;

// Entité immuable représentant un livre tel qu'il sort de la base de données.
// Sert de conteneur typé entre le manager SQL et les modèles de l'application.
final class Book
{
  public function __construct(
    private int $id,
    private int $userId,
    private string $title,
    private string $author,
    private ?string $image = null,
    private ?string $description = null,
    private ?string $status = null,
    private ?string $createdAt = null,
    // Les champs supplémentaires (ex: username du propriétaire) sont stockés ici
    private array $extra = []
  ) {
  }

  // Construit une entité Book à partir d'un tableau associatif venant de PDO.
  // Les colonnes connues sont typées, le reste part dans $extra (ex: username, email...).
  public static function fromArray(array $data): self
  {
    // On copie tout d'abord, puis on retire les champs gérés explicitement
    $extra = $data;
    unset(
      $extra['id'],
      $extra['user_id'],
      $extra['title'],
      $extra['author'],
      $extra['image'],
      $extra['description'],
      $extra['status'],
      $extra['created_at']
    );

    return new self(
      (int)($data['id'] ?? 0),
      (int)($data['user_id'] ?? 0),
      (string)($data['title'] ?? ''),
      (string)($data['author'] ?? ''),
      isset($data['image']) ? (string)$data['image'] : null,
      isset($data['description']) ? (string)$data['description'] : null,
      isset($data['status']) ? (string)$data['status'] : null,
      isset($data['created_at']) ? (string)$data['created_at'] : null,
      $extra
    );
  }

  // Retourne l'identifiant du livre.
  public function id(): int
  {
    return $this->id;
  }

  // Reconvertit l'entité en tableau associatif pour la couche vue ou modèle.
  // On n'inclut les champs optionnels que s'ils ont une valeur.
  public function toArray(): array
  {
    // On repart des champs extras (username, etc.) pour ne pas les perdre
    $data = $this->extra;
    $data['id'] = $this->id;
    $data['user_id'] = $this->userId;
    $data['title'] = $this->title;
    $data['author'] = $this->author;

    // Les champs optionnels ne sont ajoutés que s'ils ont été renseignés
    if ($this->image !== null) {
      $data['image'] = $this->image;
    }

    if ($this->description !== null) {
      $data['description'] = $this->description;
    }

    if ($this->status !== null) {
      $data['status'] = $this->status;
    }

    if ($this->createdAt !== null) {
      $data['created_at'] = $this->createdAt;
    }

    return $data;
  }
}
