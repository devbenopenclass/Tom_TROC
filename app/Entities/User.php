<?php
namespace App\Entities;

final class User
{
  public function __construct(
    private int $id,
    private string $username,
    private string $email,
    private ?string $avatar = null,
    private ?string $bio = null,
    private ?string $createdAt = null,
    private ?string $passwordHash = null,
    private array $extra = []
  ) {
  }

  public static function fromArray(array $data): self
  {
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

  public function id(): int
  {
    return $this->id;
  }

  public function email(): string
  {
    return $this->email;
  }

  public function toArray(): array
  {
    $data = $this->extra;
    $data['id'] = $this->id;
    $data['username'] = $this->username;
    $data['email'] = $this->email;

    if ($this->avatar !== null) {
      $data['avatar'] = $this->avatar;
    }

    if ($this->bio !== null) {
      $data['bio'] = $this->bio;
    }

    if ($this->createdAt !== null) {
      $data['created_at'] = $this->createdAt;
    }

    if ($this->passwordHash !== null) {
      $data['password_hash'] = $this->passwordHash;
    }

    return $data;
  }
}
