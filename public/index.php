<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . '/../app/Core/App.php';

$app = new \App\Core\App();
$app->run();
