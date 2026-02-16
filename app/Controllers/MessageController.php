<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Core\Csrf;
use App\Models\Message;
use App\Models\User;

final class MessageController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();

        $messageModel = new Message();
        $items = $messageModel->inbox((int)Auth::id());

        // On enrichit avec l'utilisateur "other"
        $userModel = new User();
        $conversations = [];
        foreach ($items as $m) {
            $otherId = ((int)$m['sender_id'] === (int)Auth::id()) ? (int)$m['receiver_id'] : (int)$m['sender_id'];
            $other = $userModel->findById($otherId);
            $conversations[] = [
                'other' => $other,
                'last' => $m,
            ];
        }

        $this->view('messages/index', [
            'conversations' => $conversations,
        ]);
    }

    public function thread(): void
    {
        $this->requireAuth();
        $otherId = (int)($_GET['user_id'] ?? 0);
        if ($otherId <= 0) $this->redirect('/messages');

        $userModel = new User();
        $other = $userModel->findById($otherId);
        if (!$other) {
            Session::flash('error', 'Interlocuteur introuvable.');
            $this->redirect('/messages');
        }

        $messageModel = new Message();
        $messages = $messageModel->thread((int)Auth::id(), $otherId);

        $this->view('messages/thread', [
            'other' => $other,
            'messages' => $messages,
        ]);
    }

    public function send(): void
    {
        $this->requireAuth();

        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'CSRF token invalide';
            return;
        }

        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $content = trim((string)($_POST['content'] ?? ''));

        if ($receiverId <= 0 || $content === '') {
            Session::flash('error', 'Message invalide.');
            $this->redirect('/messages');
        }

        $messageModel = new Message();
        $messageModel->send((int)Auth::id(), $receiverId, $content);

        $this->redirect('/messages/thread?user_id=' . $receiverId);
    }
}
