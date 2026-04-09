<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Book;
use App\Models\User;

// Contrôleur d'administration : gestion des livres et des membres
// depuis le panneau réservé aux admins.
final class AdminController extends \App\Core\Controller
{
  // Ancre HTML pour revenir directement au tableau admin après une action
  private const ADMIN_ANCHOR = '#admin-panel';
  // Chemin de redirection par défaut après une action sur un livre
  private const BOOKS_PATH = '/admin/books#admin-panel';
  // Statuts acceptés pour un livre : tout autre valeur est ignorée
  private const ALLOWED_BOOK_STATUSES = ['available', 'unavailable', 'reserved'];
  // Rôles disponibles pour un membre : on refuse tout autre valeur
  private const ALLOWED_MEMBER_ROLES = ['user', 'admin'];

  // Affiche la liste des livres avec la possibilité de filtrer par texte.
  public function books(): void
  {
    $this->requireAdmin();

    $query = trim((string)($_GET['q'] ?? ''));
    $this->render('admin/books', [
      'books' => Book::adminList($query),
      'query' => $query,
      'adminAnchor' => self::ADMIN_ANCHOR,
    ]);
  }

  // Change le statut d'un livre depuis le panneau admin.
  // Si le statut soumis n'est pas dans la liste autorisée, on remet "available" par défaut.
  public function updateBookStatus(): void
  {
    $this->requireAdmin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    $status = (string)($_POST['status'] ?? 'available');

    // Un statut inconnu ne doit pas passer : on le ramène à la valeur par défaut
    if (!in_array($status, self::ALLOWED_BOOK_STATUSES, true)) {
      $status = 'available';
    }

    // Un id invalide ne peut pas correspondre à un livre réel
    if ($id <= 0) {
      $this->redirect(self::BOOKS_PATH);
    }

    Book::adminUpdateStatus($id, $status);

    $this->redirect(self::BOOKS_PATH);
  }

  // Supprime un livre depuis le panneau admin, quel que soit le propriétaire.
  public function deleteBook(): void
  {
    $this->requireAdmin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      $this->redirect(self::BOOKS_PATH);
    }

    Book::adminDelete($id);

    $this->redirect(self::BOOKS_PATH);
  }

  // Affiche la liste des membres avec leur rôle et un indicateur
  // pour repérer l'admin connecté parmi eux.
  public function members(): void
  {
    $this->requireAdmin();

    $query = trim((string)($_GET['q'] ?? ''));
    $members = User::adminMembers($query);

    foreach ($members as &$member) {
      // On enrichit chaque ligne avec le libellé du rôle et un flag "c'est moi"
      $member['role_label'] = User::roleLabel((int)$member['id']);
      $member['is_current_admin'] = (int)($member['id'] ?? 0) === (int)(Auth::id() ?? 0);
    }
    unset($member); // On libère la référence après la boucle

    $this->render('admin/members', [
      'members' => $members,
      'query' => $query,
      'adminAnchor' => self::ADMIN_ANCHOR,
    ]);
  }

  // Change le rôle d'un membre.
  // Un admin ne peut pas se rétrograder lui-même pour éviter de se bloquer dehors.
  public function updateMemberRole(): void
  {
    $this->requireAdmin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    $role = (string)($_POST['role'] ?? 'user');

    // Id invalide ou rôle inconnu : on refuse silencieusement
    if ($id <= 0 || !in_array($role, self::ALLOWED_MEMBER_ROLES, true)) {
      $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
    }

    $currentUserId = (int)(\App\Core\Auth::id() ?? 0);

    // Un admin ne peut pas se retirer lui-même ses droits admin
    if ($currentUserId > 0 && $currentUserId === $id && $role !== 'admin') {
      $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
    }

    try {
      User::updateRole($id, $role);
    } catch (\RuntimeException $e) {
      // La table n'a pas les colonnes nécessaires pour gérer les rôles
      $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
    }

    // Si l'admin change son propre rôle, on met à jour la session en direct
    if ($currentUserId > 0 && $currentUserId === $id) {
      $_SESSION['is_admin'] = $role === 'admin';
      $_SESSION['user_role'] = $role;
    }

    $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
  }

  // Supprime un compte membre.
  // Si l'admin supprime son propre compte, la session est détruite et il est renvoyé à l'accueil.
  public function deleteMember(): void
  {
    $this->requireAdmin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
      $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
    }

    User::delete($id);

    // Cas particulier : l'admin vient de supprimer son propre compte
    $currentUserId = Auth::id();
    if ($currentUserId !== null && (int)$currentUserId === $id) {
      $_SESSION = [];
      session_destroy();
      $this->redirect('/');
    }

    $this->redirect('/admin/members' . self::ADMIN_ANCHOR);
  }

  // Vérifie que l'utilisateur connecté est bien un administrateur.
  // Si ce n'est pas le cas, on renvoie un 403 et on arrête tout.
  private function requireAdmin(): void
  {
    Auth::requireLogin();

    $userId = Auth::id();
    // On commence par regarder en session pour éviter une requête SQL inutile
    $isAdmin = !empty($_SESSION['is_admin']);
    if (!$isAdmin && $userId !== null) {
      // En dernier recours, on vérifie directement en base
      $isAdmin = User::isAdmin((int)$userId);
    }

    if (!$isAdmin) {
      http_response_code(403);
      echo 'Acces administrateur requis';
      exit;
    }
  }
}
