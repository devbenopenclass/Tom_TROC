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
}
