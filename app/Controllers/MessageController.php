<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Book;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
  private const MESSAGES_PATH = '/messages';

  public function inbox(): void
  {
    Auth::requireLogin();
    $me = Auth::id();
    $items = Message::inbox($me);
    $contacts = Message::contacts($me);

    $other = (int)($_GET['user'] ?? 0);
    if ($other <= 0 && !empty($items)) {
      $other = (int)$items[0]['other_id'];
    }

    $otherUser = null;
    $messages = [];
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
