<?php
namespace App\Core;

// Helper d'URL : calcule l'URL de base du projet
// pour éviter les liens cassés sous /tomtroc.
class Url
{
  public static function baseUrl(): string
  {
    $conf = require __DIR__ . '/../../config/config.php';
    $configured = rtrim($conf['app']['base_url'] ?? '', '/');
    if ($configured !== '') {
      return $configured;
    }

    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if ($scriptName === '') {
      return '';
    }

    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    return ($dir === '' || $dir === '.') ? '' : $dir;
  }

  public static function withBase(string $path): string
  {
    $normalized = '/' . ltrim($path, '/');
    return self::baseUrl() . $normalized;
  }

  // Retourne une URL d'asset avec version de cache si le fichier existe localement.
  public static function asset(string $path): string
  {
    if (preg_match('#^https?://#i', $path)) {
      return $path;
    }

    $normalized = '/' . ltrim($path, '/');
    $version = self::publicAssetVersion($normalized);

    return self::withBase($normalized) . '?v=' . $version;
  }

  // Vérifie si un chemin public local existe réellement dans /public.
  public static function publicFileExists(string $path): bool
  {
    $normalized = '/' . ltrim($path, '/');
    $publicPath = realpath(__DIR__ . '/../../public');
    if ($publicPath === false) {
      return false;
    }

    return is_file($publicPath . $normalized);
  }

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
