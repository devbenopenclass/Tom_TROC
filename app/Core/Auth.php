<?php
namespace App\Core;

class Auth
{
  public static function check(): bool
  {
    return isset($_SESSION['user_id']);
  }

  public static function id(): ?int
  {
    return $_SESSION['user_id'] ?? null;
  }

  public static function requireLogin(): void
  {
    if (!self::check()) {
      header('Location: ' . Url::withBase('/login'));
      exit;
    }
  }
}
