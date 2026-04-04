<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Book;

// Contrôleur de la page d'accueil :
// récupère les derniers livres et alimente le home.
class HomeController extends Controller
{
  public function index(): void
  {
    $latest = Book::latest(4);
    $this->render('home/index', ['latest' => $latest]);
  }

  public function legalNotice(): void
  {
    $this->render('home/legal_notice');
  }

  public function privacyPolicy(): void
  {
    $this->render('home/privacy_policy');
  }
}
