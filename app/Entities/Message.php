<?php
namespace App\Entities;

// Un message envoyé d'un membre à un autre.
// Une fois créé, on ne le modifie plus — tout est en lecture seule.
final class Message
{
  public function __construct(
    private int $id,
    private int $senderId,     // celui qui a envoyé
    private int $receiverId,   // celui qui reçoit
    private string $content,   // le texte du message
    private ?string $createdAt = null, // quand il a été envoyé
    private ?bool $isRead = null,      // le destinataire l'a-t-il lu ?
    private array $extra = []          // infos bonus ramenées par un JOIN (pseudo, avatar...)
  ) {
  }

  // Crée un Message à partir d'une ligne retournée par la base de données.
  // Les champs qu'on ne reconnaît pas (pseudo de l'expéditeur, avatar...)
  // sont gardés dans $extra pour ne pas les perdre.
  public static function fromArray(array $data): self
  {
    // On copie tout, puis on enlève ce qu'on gère déjà
    // pour que $extra ne contienne que le "reste" du JOIN.
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
      // isset() rate les valeurs "false", donc on utilise array_key_exists
      // pour détecter is_read = 0 (non lu) sans le confondre avec "absent".
      array_key_exists('is_read', $data) ? (bool)$data['is_read'] : null,
      $extra
    );
  }

  // Retourne le message sous forme de tableau simple,
  // pratique pour l'afficher dans une vue.
  // On part des infos bonus ($extra) et on ajoute par-dessus
  // les vraies valeurs du message — on n'inclut que ce qui existe.
  public function toArray(): array
  {
    $data = $this->extra; // pseudo, avatar... déjà là grâce au JOIN

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
