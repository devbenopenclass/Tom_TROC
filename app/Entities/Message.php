<?php
namespace App\Entities;

final class Message
{
  public function __construct(
    private int $id,
    private int $senderId,
    private int $receiverId,
    private string $content,
    private ?string $createdAt = null,
    private ?bool $isRead = null,
    private array $extra = []
  ) {
  }

  public static function fromArray(array $data): self
  {
    $extra = $data;
    unset(
      $extra['id'],
      $extra['sender_id'],
      $extra['receiver_id'],
      $extra['content'],
      $extra['created_at'],
      $extra['is_read']
    );

    return new self(
      (int)($data['id'] ?? 0),
      (int)($data['sender_id'] ?? 0),
      (int)($data['receiver_id'] ?? 0),
      (string)($data['content'] ?? ''),
      isset($data['created_at']) ? (string)$data['created_at'] : null,
      array_key_exists('is_read', $data) ? (bool)$data['is_read'] : null,
      $extra
    );
  }

  public function toArray(): array
  {
    $data = $this->extra;

    if ($this->id > 0) {
      $data['id'] = $this->id;
    }

    if ($this->senderId > 0) {
      $data['sender_id'] = $this->senderId;
    }

    if ($this->receiverId > 0) {
      $data['receiver_id'] = $this->receiverId;
    }

    if ($this->content !== '') {
      $data['content'] = $this->content;
    }

    if ($this->createdAt !== null) {
      $data['created_at'] = $this->createdAt;
    }

    if ($this->isRead !== null) {
      $data['is_read'] = $this->isRead;
    }

    return $data;
  }
}
