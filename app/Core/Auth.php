<?php
namespace App\Core;

use App\Models\User;

class Auth
{
  public static function check(): bool
  {
    $id = $_SESSION['user_id'] ?? null;
    return is_numeric($id) && User::find((int)$id) !== null;
  }

  public static function id(): ?int
  {
    return $_SESSION['user_id'] ?? null;
  }

  public static function requireLogin(): void
  {
    if (!self::check()) {
      $_SESSION = [];
      session_destroy();
      header('Location: ' . Url::withBase('/login'));
      exit;
    }
  }
}
