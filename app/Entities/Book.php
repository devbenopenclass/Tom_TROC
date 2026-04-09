<?php
namespace App\Entities;

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
    private array $extra = []
  ) {
  }

  public static function fromArray(array $data): self
  {
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

  public function id(): int
  {
    return $this->id;
  }

  public function toArray(): array
  {
    $data = $this->extra;
    $data['id'] = $this->id;
    $data['user_id'] = $this->userId;
    $data['title'] = $this->title;
    $data['author'] = $this->author;

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
