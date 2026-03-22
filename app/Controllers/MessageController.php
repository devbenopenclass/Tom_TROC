<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Book;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
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
      foreach ($contacts as $contact) {
        if ((int)$contact['id'] === $other) {
          $otherUser = $contact;
          break;
        }
      }
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
        if ($bookId > 0) {
          $book = Book::find($bookId);
          if ($book && (int)$book['user_id'] === (int)$other) {
            $bookContext = $book;
          }
        }
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

    $this->redirect('/messages' . (!empty($query) ? '?' . http_build_query($query) : ''));
  }

  public function send(): void
  {
    Auth::requireLogin();

    $receiver = (int)($_POST['receiver_id'] ?? 0);
    $bookId = (int)($_POST['book_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if ($receiver <= 0 || $content === '') {
      $this->redirect('/messages');
      return;
    }

    $hasThread = Message::hasThread((int)Auth::id(), $receiver);
    if (!$hasThread) {
      if ($bookId <= 0) {
        $this->redirect('/messages');
        return;
      }

      $book = Book::find($bookId);
      if (!$book || (int)$book['user_id'] !== $receiver) {
        $this->redirect('/messages');
        return;
      }
    }

    Message::send(Auth::id(), $receiver, $content);
    $redirect = '/messages/thread?user=' . $receiver;
    if ($bookId > 0) {
      $redirect .= '&book=' . $bookId;
    }
    $this->redirect($redirect);
  }
}
