<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Book;

final class HomeController extends Controller
{
    public function index(): void
    {
        $bookModel = new Book();
        $latest = $bookModel->latest(4);

        $this->view('home/index', [
            'latest' => $latest,
        ]);
    }
}
