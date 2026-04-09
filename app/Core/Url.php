<?php
namespace App\Core;

// Helper d'URL : calcule la base du projet et construit des liens fiables,
// que l'appli soit en racine ou dans un sous-dossier comme /tomtroc.
class Url
{
  // Retourne le préfixe de base du projet (ex: "/tomtroc") ou une chaîne vide si on est en racine.
  // On lit d'abord la config, puis on se rabat sur SCRIPT_NAME si rien n'est configuré.
  public static function baseUrl(): string
  {
    $conf = require __DIR__ . '/../../config/config.php';
    $configured = rtrim($conf['app']['base_url'] ?? '', '/');
    if ($configured !== '') {
      return $configured;
    }

    // Fallback automatique : on détermine la base à partir du chemin du script
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($scriptName === '') {
      return '';
    }

    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    return ($dir === '' || $dir === '.') ? '' : $dir;
  }

  // Préfixe un chemin interne avec la base du projet pour obtenir une URL complète.
  public static function withBase(string $path): string
  {
    $normalized = '/' . ltrim($path, '/');
    return self::baseUrl() . $normalized;
  }

  // Retourne l'URL publique d'un asset avec un paramètre de version pour casser le cache.
  // Si le fichier est une URL externe, on la retourne telle quelle.
  public static function asset(string $path): string
  {
    // Les URLs externes (http/https) passent sans modification
    if (preg_match('#^https?://#i', $path)) {
      return $path;
    }

    $normalized = '/' . ltrim($path, '/');
    $version = self::publicAssetVersion($normalized);

    return self::withBase($normalized) . '?v=' . $version;
  }

  // Vérifie qu'un fichier existe réellement dans le dossier public du projet.
  public static function publicFileExists(string $path): bool
  {
    $normalized = '/' . ltrim($path, '/');
    $publicPath = realpath(__DIR__ . '/../../public');
    if ($publicPath === false) {
      return false;
    }

    return is_file($publicPath . $normalized);
  }

  // Retourne la date de modification d'un asset pour le versionnage du cache.
  // Si le fichier n'existe pas, on renvoie "1" comme version neutre.
  private static function publicAssetVersion(string $path): string
  {
    if (!self::publicFileExists($path)) {
      return '1';
    }

    $publicPath = realpath(__DIR__ . '/../../public');
    if ($publicPath === false) {
      return '1';
    }

    return (string) filemtime($publicPath . $path);
  }
}
