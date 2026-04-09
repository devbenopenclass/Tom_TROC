<?php
namespace App\Core;

// Protection CSRF minimale :
// génère un token par session, le glisse dans les formulaires et vérifie les soumissions POST.
class Csrf
{
  // Clé utilisée pour stocker le token en session
  private const SESSION_KEY = '_csrf_token';

  // Génère un token aléatoire à la première demande, puis le réutilise pendant toute la session.
  public static function token(): string
  {
    if (empty($_SESSION[self::SESSION_KEY])) {
      $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION[self::SESSION_KEY];
  }

  // Retourne directement le champ hidden à coller dans les formulaires HTML.
  public static function input(): string
  {
    $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf" value="' . $token . '">';
  }

  // Compare le token du formulaire avec celui de la session sans fuite de timing.
  // hash_equals est important ici pour résister aux attaques par comparaison de temps.
  public static function verify(?string $token): bool
  {
    $sessionToken = $_SESSION[self::SESSION_KEY] ?? null;

    // Si l'un des deux n'est pas une chaîne, c'est forcément invalide
    if (!is_string($sessionToken) || !is_string($token)) {
      return false;
    }

    return hash_equals($sessionToken, $token);
  }
}
