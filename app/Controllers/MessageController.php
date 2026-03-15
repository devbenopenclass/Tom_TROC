<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
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
    if ($other > 0) {
      foreach ($contacts as $contact) {
        if ((int)$contact['id'] === $other) {
          $otherUser = $contact;
          break;
        }
      }
      if ($otherUser) {
        Message::markThreadAsRead($me, $other);
        $messages = Message::thread($me, $other);
      }
    }

    $this->render('messages/inbox', [
      'items' => $items,
      'contacts' => $contacts,
      'other' => $otherUser,
      'messages' => $messages,
      'activeUserId' => $other,
    ]);
  }

  public function thread(): void
  {
    Auth::requireLogin();
    $other = (int)($_GET['user'] ?? 0);
    $this->redirect('/messages' . ($other > 0 ? '?user=' . $other : ''));
  }

  public function send(): void
  {
    Auth::requireLogin();

    $receiver = (int)($_POST['receiver_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if ($receiver <= 0 || $content === '' || $receiver === Auth::id()) {
      $this->redirect('/messages');
      return;
    }

    Message::send(Auth::id(), $receiver, $content);
    $this->redirect('/messages/thread?user=' . $receiver);
  }
}
