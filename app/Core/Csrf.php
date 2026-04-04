<?php
namespace App\Core;

class Csrf
{
  private const SESSION_KEY = '_csrf_token';

  public static function token(): string
  {
    if (empty($_SESSION[self::SESSION_KEY])) {
      $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION[self::SESSION_KEY];
  }

  public static function input(): string
  {
    $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf" value="' . $token . '">';
  }

  public static function verify(?string $token): bool
  {
    $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;
    if (!is_string($sessionToken) || !is_string($token)) {
      return false;
    }

    return hash_equals($sessionToken, $token);
  }
}
