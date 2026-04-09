<?php
namespace App\Managers;

use App\Core\Model;
use App\Entities\Book;

// Accès SQL pour les livres : toutes les requêtes passent par ici
// et retournent des entités Book hydratées.
final class BookManager extends Model
{
  // Retourne les N derniers livres ajoutés avec le pseudo du propriétaire.
  // Utilisé pour la page d'accueil.
  public static function latest(int $limit = 4): array
  {
    $stmt = self::db()->prepare("
      SELECT b.*, u.username
      FROM books b
      JOIN users u ON u.id = b.user_id
      ORDER BY b.created_at DESC
      LIMIT :limit
    ");
    $stmt->bindValue('limit', max(1, $limit), \PDO::PARAM_INT);
    $stmt->execute();

    return self::hydrateMany($stmt->fetchAll());
  }

  // Retourne tous les livres du catalogue public, avec filtre optionnel
  // sur le titre, l'auteur ou le pseudo du propriétaire.
  public static function exchangeList(?string $query = null): array
  {
    if ($query !== null && $query !== '') {
      $stmt = self::db()->prepare("
        SELECT b.*, u.username
        FROM books b
        JOIN users u ON u.id = b.user_id
        WHERE b.title LIKE :query OR b.author LIKE :query OR u.username LIKE :query
        ORDER BY b.created_at DESC
      ");
      $stmt->execute(['query' => '%' . $query . '%']);
      return self::hydrateMany($stmt->fetchAll());
    }

    // Pas de filtre : on retourne tout le catalogue trié par date
    $stmt = self::db()->query("
      SELECT b.*, u.username
      FROM books b
      JOIN users u ON u.id = b.user_id
      ORDER BY b.created_at DESC
    ");

    return self::hydrateMany($stmt->fetchAll());
  }

  // Charge un livre par son identifiant avec le pseudo du propriétaire.
  // Retourne null si l'id n'existe pas.
  public static function find(int $id): ?Book
  {
    $stmt = self::db()->prepare("
      SELECT b.*, u.username
      FROM books b
      JOIN users u ON u.id = b.user_id
      WHERE b.id = :id
      LIMIT 1
    ");
    $stmt->execute(['id' => $id]);

    return self::hydrateOne($stmt->fetch() ?: null);
  }

  // Retourne tous les livres d'un membre, triés du plus récent au plus ancien.
  public static function byUser(int $userId): array
  {
    $stmt = self::db()->prepare('SELECT * FROM books WHERE user_id = :uid ORDER BY created_at DESC');
    $stmt->execute(['uid' => $userId]);

    return self::hydrateMany($stmt->fetchAll());
  }

  // Insère un nouveau livre et retourne son identifiant auto-incrémenté.
  public static function create(array $data): int
  {
    $stmt = self::db()->prepare("
      INSERT INTO books (user_id, title, author, image, description, status)
      VALUES (:user_id, :title, :author, :image, :description, :status)
    ");
    $stmt->execute($data);

    return (int)self::db()->lastInsertId();
  }

  // Met à jour un livre, en s'assurant que user_id correspond bien au propriétaire.
  // Cela empêche un membre de modifier le livre d'un autre via un POST forgé.
  public static function update(int $id, int $userId, array $data): void
  {
    $data['id'] = $id;
    $data['user_id'] = $userId;

    $stmt = self::db()->prepare("
      UPDATE books
      SET title=:title, author=:author, image=:image, description=:description, status=:status
      WHERE id=:id AND user_id=:user_id
    ");
    $stmt->execute($data);
  }

  // Supprime un livre uniquement si le user_id correspond.
  // Même protection que pour update : on ne peut pas supprimer le livre de quelqu'un d'autre.
  public static function delete(int $id, int $userId): void
  {
    $stmt = self::db()->prepare('DELETE FROM books WHERE id = :id AND user_id = :uid');
    $stmt->execute(['id' => $id, 'uid' => $userId]);
  }

  // Liste tous les livres pour l'administration avec une recherche optionnelle
  // sur le titre, l'auteur, le pseudo ou l'email du propriétaire.
  public static function adminList(string $query = ''): array
  {
    $sql = implode("\n", [
      'SELECT b.*, u.username, u.email',
      'FROM books b',
      'JOIN users u ON u.id = b.user_id',
    ]);
    $params = [];

    if ($query !== '') {
      $sql .= implode("\n", [
        '',
        'WHERE b.title LIKE :query',
        '   OR b.author LIKE :query',
        '   OR u.username LIKE :query',
        '   OR u.email LIKE :query',
      ]);
      $params['query'] = '%' . $query . '%';
    }

    $sql .= implode("\n", [
      '',
      'ORDER BY b.created_at DESC, b.id DESC',
    ]);

    $stmt = self::db()->prepare($sql);
    $stmt->execute($params);

    return self::hydrateMany($stmt->fetchAll());
  }

  // Met à jour le statut d'un livre depuis le panneau admin,
  // sans restriction sur le propriétaire.
  public static function adminUpdateStatus(int $id, string $status): void
  {
    $stmt = self::db()->prepare('UPDATE books SET status = :status WHERE id = :id');
    $stmt->execute([
      'id' => $id,
      'status' => $status,
    ]);
  }

  // Supprime un livre depuis le panneau admin, quel que soit le propriétaire.
  public static function adminDelete(int $id): void
  {
    $stmt = self::db()->prepare('DELETE FROM books WHERE id = :id');
    $stmt->execute(['id' => $id]);
  }

  // Hydrate une seule ligne en entité Book, ou retourne null si la ligne est vide.
  private static function hydrateOne(?array $row): ?Book
  {
    return $row !== null ? Book::fromArray($row) : null;
  }

  // Hydrate un tableau de lignes en tableau d'entités Book.
  private static function hydrateMany(array $rows): array
  {
    return array_map(static fn (array $row): Book => Book::fromArray($row), $rows);
  }
}
