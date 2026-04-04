<?php
namespace App\Core;

// Service CSRF minimal :
// génère un token de session, fournit le champ caché et valide les soumissions POST.
class Csrf
{
  private const SESSION_KEY = '_csrf_token';

  // Génère le token une seule fois par session puis le réutilise.
  public static function token(): string
  {
    if (empty($_SESSION[self::SESSION_KEY])) {
      $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION[self::SESSION_KEY];
  }

  // Rend directement l'input caché attendu dans les formulaires.
  public static function input(): string
  {
    $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf" value="' . $token . '">';
  }

  // Compare le token soumis à celui de la session sans fuite de timing.
  public static function verify(?string $token): bool
  {
    $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;
    if (!is_string($sessionToken) || !is_string($token)) {
      return false;
    }

    return hash_equals($sessionToken, $token);
  }
}
