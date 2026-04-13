<?php
declare(strict_types=1);

namespace App\Services;

// Gère l'upload des images du projet.
// Le service centralise la validation MIME, le nommage et l'écriture disque.
final class ImageUploadService
{
  private const ALLOWED_MIME_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
  ];

  public function uploadOptional(array $file, string $targetDirectory, string $publicPrefix, string $filenamePrefix, string $label): array
  {
    if (empty($file['name'])) {
      return ['path' => null, 'error' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
      return ['path' => null, 'error' => "L'{$label} n'a pas pu être envoyé."];
    }

    $tmpName = (string)($file['tmp_name'] ?? '');
    $mime = $tmpName !== '' ? mime_content_type($tmpName) : false;
    if (!is_string($mime) || !isset(self::ALLOWED_MIME_TYPES[$mime])) {
      return ['path' => null, 'error' => "Le format de l'{$label} doit être JPG, PNG ou WebP."];
    }

    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
      return ['path' => null, 'error' => "Impossible d'enregistrer l'{$label} pour le moment."];
    }

    $extension = self::ALLOWED_MIME_TYPES[$mime];
    $filename = $filenamePrefix . '-' . bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = rtrim($targetDirectory, '/') . '/' . $filename;

    if (!move_uploaded_file($tmpName, $destination)) {
      return ['path' => null, 'error' => "Impossible d'enregistrer l'{$label} pour le moment."];
    }

    return [
      'path' => rtrim($publicPrefix, '/') . '/' . $filename,
      'error' => null,
    ];
  }
}
