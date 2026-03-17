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

    $other = (int)($_GET['user'] ?? 0);
    $bookId = (int)($_GET['book'] ?? 0);
    $bookContext = null;

    if ($bookId > 0) {
      $bookContext = Book::find($bookId);
      if (
        !$bookContext ||
        (int)$bookContext['user_id'] === $me
      ) {
        $bookContext = null;
        $bookId = 0;
      } else {
        $other = (int)$bookContext['user_id'];
      }
    }

    if ($other <= 0 && !empty($items)) {
      $other = (int)$items[0]['other_id'];
    }

    $otherUser = null;
    $messages = [];
    $hasThread = false;
    if ($other > 0) {
      $otherUser = User::find($other);
      if ($otherUser) {
        $otherUser['books_count'] = count(Book::byUser($other));
        if ($bookContext && (int)$bookContext['user_id'] !== (int)$otherUser['id']) {
          $bookContext = null;
          $bookId = 0;
        }
      }

      if ($otherUser && (int)$otherUser['id'] !== $me) {
        Message::markThreadAsRead($me, $other);
        $messages = Message::thread($me, $other);
        $hasThread = !empty($messages);
      }
    }

    $this->render('messages/inbox', [
      'items' => $items,
      'other' => $otherUser,
      'messages' => $messages,
      'activeUserId' => $other,
      'bookContext' => $bookContext,
      'canCompose' => $otherUser !== null && ($bookContext !== null || $hasThread),
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

    $book = $bookId > 0 ? Book::find($bookId) : null;
    $hasThread = $receiver > 0 ? Message::hasThread((int)Auth::id(), $receiver) : false;

    if (
      $receiver <= 0 ||
      $content === '' ||
      $receiver === Auth::id() ||
      (
        !$hasThread &&
        (
          !$book ||
          (int)$book['user_id'] !== $receiver ||
          (int)$book['user_id'] === Auth::id()
        )
      )
    ) {
      $this->redirect('/messages');
      return;
    }

    Message::send(Auth::id(), $receiver, $content);
    $query = ['user' => $receiver];
    if ($book && (int)$book['user_id'] === $receiver) {
      $query['book'] = $bookId;
    }
    $this->redirect('/messages?' . http_build_query($query));
  }
}
