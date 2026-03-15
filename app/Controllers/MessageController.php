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
    $items = Message::inbox(Auth::id());
    $this->render('messages/inbox', ['items' => $items]);
  }

  public function thread(): void
  {
    Auth::requireLogin();

    $other = (int)($_GET['user'] ?? 0);
    $otherUser = $other ? User::find($other) : null;

    if (!$otherUser) {
      http_response_code(404);
      echo "Conversation introuvable";
      return;
    }

    Message::markThreadAsRead(Auth::id(), $other);
    $messages = Message::thread(Auth::id(), $other);
    $this->render('messages/thread', ['other' => $otherUser, 'messages' => $messages]);
  }

  public function send(): void
  {
    Auth::requireLogin();

    $receiver = (int)($_POST['receiver_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');

    if ($receiver <= 0 || $content === '') {
      $this->redirect('/messages');
      return;
    }

    Message::send(Auth::id(), $receiver, $content);
    $this->redirect('/messages/thread?user=' . $receiver);
  }
}
