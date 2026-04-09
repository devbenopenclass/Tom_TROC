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
  // Chemin de base de la messagerie, utilisé pour les redirections
  private const MESSAGES_PATH = '/messages';

  // Prépare toute la page de messagerie :
  // liste des conversations, fil actif, contexte livre et droit de réponse.
  public function inbox(): void
  {
    Auth::requireLogin();
    $me = Auth::id();
    $items = Message::inbox($me);
    $contacts = Message::contacts($me);

    // Si aucun destinataire n'est demandé, on ouvre la première conversation disponible
    $other = (int)($_GET['user'] ?? 0);
    if ($other <= 0 && !empty($items)) {
      $other = (int)$items[0]['other_id'];
    }

    $otherUser = null;
    $messages = [];

    // Le contexte livre sert uniquement à démarrer un premier échange depuis une fiche livre
    // et à relier la conversation au bon membre propriétaire du livre.
    $bookContext = null;
    $bookId = (int)($_GET['book'] ?? 0);

    if ($other > 0) {
      $otherUser = $this->findContact($contacts, $other);

      // Cas particulier : l'utilisateur s'écrit à lui-même (peu probable mais on le gère)
      if (!$otherUser && $other === (int)$me) {
        $self = User::find((int)$me);
        if ($self) {
          $self['books_count'] = count(Book::byUser((int)$me));
          $otherUser = $self;
        }
      }

      if ($otherUser) {
        // On marque les messages comme lus dès qu'on ouvre le fil
        Message::markThreadAsRead($me, $other);
        $messages = Message::thread($me, $other);
        $bookContext = $this->resolveBookContext($bookId, $other);
      }
    }

    // On peut composer un message si on arrive depuis un livre ou si un fil existe déjà
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

  // Redirige proprement vers /messages en gardant les paramètres utiles.
  // Cela centralise la messagerie sur une seule vraie URL.
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

  // Envoie un message après avoir vérifié que c'est autorisé.
  // Premier message : seulement depuis un livre valide.
  // Réponses : autorisées si un fil existe déjà entre les deux membres.
  public function send(): void
  {
    Auth::requireLogin();
    $this->requireCsrf();

    $receiver = (int)($_POST['receiver_id'] ?? 0);
    $bookId = (int)($_POST['book_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    // On refuse si le destinataire ou le contenu est absent
    if ($receiver <= 0 || $content === '') {
      $this->redirect(self::MESSAGES_PATH);
      return;
    }

    $hasThread = Message::hasThread((int)Auth::id(), $receiver);

    // Pas encore de fil : le premier message doit obligatoirement être lié à un livre
    if (!$hasThread) {
      if ($bookId <= 0) {
        $this->redirect(self::MESSAGES_PATH);
        return;
      }

      // On vérifie que le livre appartient bien au destinataire visé
      if ($this->resolveBookContext($bookId, $receiver) === null) {
        $this->redirect(self::MESSAGES_PATH);
        return;
      }
    }

    Message::send(Auth::id(), $receiver, $content);

    // On redirige vers le fil actif, en conservant le contexte livre si besoin
    $redirect = self::MESSAGES_PATH . '/thread?user=' . $receiver;
    if ($bookId > 0) {
      $redirect .= '&book=' . $bookId;
    }
    $this->redirect($redirect);
  }

  // Cherche un contact dans la liste des membres avec qui on a déjà échangé.
  private function findContact(array $contacts, int $otherId): ?array
  {
    foreach ($contacts as $contact) {
      if ((int)($contact['id'] ?? 0) === $otherId) {
        return $contact;
      }
    }

    return null;
  }

  // Vérifie qu'un livre existe et qu'il appartient bien au membre ciblé.
  // Retourne le livre si tout est bon, sinon null.
  private function resolveBookContext(int $bookId, int $ownerId): ?array
  {
    if ($bookId <= 0) {
      return null;
    }

    $book = Book::find($bookId);

    // Si le livre n'existe pas ou n'appartient pas au bon membre, on refuse
    if (!$book || (int)($book['user_id'] ?? 0) !== $ownerId) {
      return null;
    }

    return $book;
  }
}
