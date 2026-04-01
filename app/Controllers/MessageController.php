<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Book;
use App\Models\Message;
use App\Models\User;

// Contrôleur de messagerie : ouvre les conversations,
// prépare le contexte livre et gère l'envoi des messages.
class MessageController extends Controller
{
  private const MESSAGES_PATH = '/messages';

  // Prépare toute la page de messagerie :
  // conversations, fil actif, contexte livre et droit de réponse.
  public function inbox(): void
  {
    Auth::requireLogin();
    $me = Auth::id();
    $items = Message::inbox($me);
    $contacts = Message::contacts($me);

    // Si aucun destinataire n'est demandé, on ouvre la première conversation disponible.
    $other = (int)($_GET['user'] ?? 0);
    if ($other <= 0 && !empty($items)) {
      $other = (int)$items[0]['other_id'];
    }

    $otherUser = null;
    $messages = [];
    // Le contexte livre est utilisé seulement pour démarrer un premier message
    // depuis une fiche livre et garder l'échange rattaché au bon membre.
    $bookContext = null;
    $bookId = (int)($_GET['book'] ?? 0);
    if ($other > 0) {
      $otherUser = $this->findContact($contacts, $other);
      if (!$otherUser && $other === (int)$me) {
        $self = User::find((int)$me);
        if ($self) {
          $self['books_count'] = count(Book::byUser((int)$me));
          $otherUser = $self;
        }
      }
      if ($otherUser) {
        Message::markThreadAsRead($me, $other);
        $messages = Message::thread($me, $other);
        $bookContext = $this->resolveBookContext($bookId, $other);
      }
    }

    // On peut écrire soit parce qu'on arrive depuis un livre,
    // soit parce qu'un fil existe déjà entre les deux membres.
    $canCompose = $otherUser !== null && ($bookContext !== null || Message::hasThread((int)$me, (int)$other));

    $this->render('messages/inbox', [
      'items' => $items,
      'contacts' => $contacts,
      'other' => $otherUser,
      'messages' => $messages,
      'activeUserId' => $other,
      'bookContext' => $bookContext,
      'canCompose' => $canCompose,
    ]);
  }

  // Redirige vers /messages en gardant les paramètres utiles.
  // Cela permet d'avoir une seule vraie page de messagerie.
  public function thread(): void
  {
    Auth::requireLogin();
    $other = (int)($_GET['user'] ?? 0);
    $bookId = (int)($_GET['book'] ?? 0);
    $query = [];
    if ($other > 0) {
      $query['user'] = $other;
    }
    if ($bookId > 0) {
      $query['book'] = $bookId;
    }

    $this->redirect(self::MESSAGES_PATH . (!empty($query) ? '?' . http_build_query($query) : ''));
  }

  // Envoie un message :
  // premier message seulement via un livre valide, réponses autorisées si le fil existe déjà.
  public function send(): void
  {
    Auth::requireLogin();
    $this->requireCsrf();

    $receiver = (int)($_POST['receiver_id'] ?? 0);
    $bookId = (int)($_POST['book_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if ($receiver <= 0 || $content === '') {
      $this->redirect(self::MESSAGES_PATH);
      return;
    }

    $hasThread = Message::hasThread((int)Auth::id(), $receiver);
    if (!$hasThread) {
      if ($bookId <= 0) {
        $this->redirect(self::MESSAGES_PATH);
        return;
      }

      if ($this->resolveBookContext($bookId, $receiver) === null) {
        $this->redirect(self::MESSAGES_PATH);
        return;
      }
    }

    Message::send(Auth::id(), $receiver, $content);
    $redirect = self::MESSAGES_PATH . '/thread?user=' . $receiver;
    if ($bookId > 0) {
      $redirect .= '&book=' . $bookId;
    }
    $this->redirect($redirect);
  }

  private function findContact(array $contacts, int $otherId): ?array
  {
    foreach ($contacts as $contact) {
      if ((int)($contact['id'] ?? 0) === $otherId) {
        return $contact;
      }
    }

    return null;
  }

  private function resolveBookContext(int $bookId, int $ownerId): ?array
  {
    if ($bookId <= 0) {
      return null;
    }

    $book = Book::find($bookId);
    if (!$book || (int)($book['user_id'] ?? 0) !== $ownerId) {
      return null;
    }

    return $book;
  }
}
