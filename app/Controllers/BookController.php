<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Book;
use App\Presenters\BookPresenter;
use App\Services\ImageUploadService;

// Contrôleur des livres : catalogue public, fiche détail,
// ajout, édition et suppression pour le membre connecté.
class BookController extends Controller
{
  // Chemin de retour après toute action sur un livre
  private const ACCOUNT_PATH = '/account';
  // Statuts valides pour un livre ; toute autre valeur sera ramenée à "available"
  private const AVAILABLE_STATUSES = ['available', 'unavailable', 'reserved'];

  private BookPresenter $bookPresenter;
  private ImageUploadService $imageUploadService;

  public function __construct()
  {
    parent::__construct();
    $this->bookPresenter = new BookPresenter();
    $this->imageUploadService = new ImageUploadService();
  }

  // Affiche le catalogue public des livres avec le moteur de recherche.
  public function exchange(): void
  {
    $q = trim($_GET['q'] ?? '');
    $books = Book::exchangeList($q !== '' ? $q : null);
    $this->render('books/exchange', [
      'books' => $this->bookPresenter->exchangeCards($books),
      'q' => $q,
    ]);
  }

  // Affiche une seule fiche livre à partir de son id dans l'URL.
  public function show(): void
  {
    $id = (int)($_GET['id'] ?? 0);
    $book = $id ? Book::find($id) : null;

    // Si le livre n'existe pas, on affiche un 404 clair
    if (!$book) {
      http_response_code(404);
      echo "Livre introuvable";
      return;
    }

    $this->render('books/show', ['bookView' => $this->bookPresenter->showView($book)]);
  }

  // Ouvre le formulaire d'ajout d'un livre pour le membre connecté.
  public function createForm(): void
  {
    $this->requireBookLogin();
    $this->render('books/form', ['mode' => 'create']);
  }

  // Valide les champs du formulaire puis crée le livre en base.
  public function create(): void
  {
    $this->requireBookLogin();
    $this->requireCsrf();

    $data = $this->bookPayloadForCreate();
    $error = $this->validateBookPayload($data);
    if ($error !== null) {
      $this->renderBookFormError('create', $error, $data);
      return;
    }

    // On tente d'uploader la couverture si le membre en a joint une
    $imageUpload = $this->uploadedBookImage();
    if ($imageUpload['error'] !== null) {
      $this->renderBookFormError('create', $imageUpload['error'], $data);
      return;
    }

    // On écrase l'image par le chemin réel si un fichier a bien été uploadé
    $data['image'] = $imageUpload['path'] ?? $data['image'];
    try {
      Book::create($data);
    } catch (\Throwable $e) {
      $this->renderBookFormError(
        'create',
        "Impossible d'ajouter le livre pour le moment.",
        $data
      );
      return;
    }

    $this->redirect(self::ACCOUNT_PATH);
  }

  // Charge le formulaire d'édition d'un livre existant.
  // On bloque l'accès si le livre n'appartient pas au membre connecté.
  public function editForm(): void
  {
    $this->requireBookLogin();
    $book = $this->findOwnedBook((int)($_GET['id'] ?? 0));
    $this->render('books/form', ['mode' => 'edit', 'book' => $book]);
  }

  // Enregistre les modifications d'un livre existant.
  public function update(): void
  {
    $this->requireBookLogin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    $book = $this->findOwnedBook($id);

    $data = $this->bookPayloadForUpdate();
    $error = $this->validateBookPayload($data);
    if ($error !== null) {
      // On fusionne les données existantes et les nouvelles pour repopuler le formulaire
      $this->renderBookFormError('edit', $error, array_merge($book, $data, ['id' => $id]));
      return;
    }

    $imageUpload = $this->uploadedBookImage();
    if ($imageUpload['error'] !== null) {
      $this->renderBookFormError('edit', $imageUpload['error'], array_merge($book, $data, ['id' => $id]));
      return;
    }

    $data['image'] = $imageUpload['path'] ?? $data['image'];

    try {
      Book::update($id, $this->currentUserId(), $data);
    } catch (\Throwable $e) {
      $this->renderBookFormError('edit', "Impossible d'enregistrer les modifications pour le moment.", array_merge($book, $data, ['id' => $id]));
      return;
    }

    $this->redirect(self::ACCOUNT_PATH);
  }

  // Supprime un livre de la bibliothèque du membre connecté.
  // Le user_id est passé à la requête pour s'assurer que personne ne peut supprimer le livre d'un autre.
  public function delete(): void
  {
    $this->requireBookLogin();
    $this->requireCsrf();

    $id = (int)($_POST['id'] ?? 0);
    Book::delete($id, $this->currentUserId());
    $this->redirect(self::ACCOUNT_PATH);
  }

  // Redirige vers la connexion si la session est vide.
  private function requireBookLogin(): void
  {
    Auth::requireLogin();
  }

  // Retourne l'id de l'utilisateur connecté en entier.
  private function currentUserId(): int
  {
    return (int) Auth::id();
  }

  // Rassemble les données POST pour la création d'un livre.
  private function bookPayloadForCreate(): array
  {
    return [
      'user_id' => $this->currentUserId(),
      'title' => trim($_POST['title'] ?? ''),
      'author' => trim($_POST['author'] ?? ''),
      'image' => null, // L'image sera remplie plus tard si un fichier est uploadé
      'description' => trim($_POST['description'] ?? ''),
      'status' => $this->normalizeStatus((string)($_POST['status'] ?? 'available')),
    ];
  }

  // Rassemble les données POST pour la mise à jour d'un livre existant.
  // On récupère l'image existante en cas d'absence de nouveau fichier.
  private function bookPayloadForUpdate(): array
  {
    return [
      'title' => trim($_POST['title'] ?? ''),
      'author' => trim($_POST['author'] ?? ''),
      'image' => $_POST['existing_image'] ?? null, // On conserve l'ancienne image si aucune nouvelle n'est envoyée
      'description' => trim($_POST['description'] ?? ''),
      'status' => $this->normalizeStatus((string)($_POST['status'] ?? 'available')),
    ];
  }

  // Tente d'uploader la couverture si le membre en a joint une.
  // Si aucun fichier n'est envoyé, retourne null sans erreur.
  private function uploadedBookImage(): array
  {
    return $this->imageUploadService->uploadOptional(
      $_FILES['image'] ?? [],
      __DIR__ . '/../../public/assets/uploads',
      '/assets/uploads',
      'book-cover',
      'image'
    );
  }

  // Vérifie que les champs obligatoires d'un livre sont bien remplis.
  private function validateBookPayload(array $data): ?string
  {
    if ($data['title'] === '' || $data['author'] === '') {
      return 'Titre et auteur sont obligatoires.';
    }

    return null;
  }

  // Accepte seulement les statuts connus, sinon on remet "available" par défaut.
  private function normalizeStatus(string $status): string
  {
    return in_array($status, self::AVAILABLE_STATUSES, true) ? $status : 'available';
  }

  // Charge un livre et s'assure qu'il appartient bien au membre connecté.
  // Si ce n'est pas le cas, on coupe court avec un 403.
  private function findOwnedBook(int $id): array
  {
    $book = $id > 0 ? Book::find($id) : null;
    if ($book && (int)($book['user_id'] ?? 0) === $this->currentUserId()) {
      return $book;
    }

    http_response_code(403);
    echo 'Accès interdit';
    exit;
  }

  // Réaffiche le formulaire livre avec le message d'erreur et les valeurs déjà saisies.
  private function renderBookFormError(string $mode, string $message, array $book = []): void
  {
    $this->render('books/form', [
      'mode' => $mode,
      'error' => $message,
      'book' => $book,
    ]);
  }
}
