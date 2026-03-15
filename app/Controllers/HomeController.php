<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Book;

class HomeController extends Controller
{
  public function index(): void
  {
    $latest = Book::latest(4);
    $this->render('home/index', ['latest' => $latest]);
  }
}
