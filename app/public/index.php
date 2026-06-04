<?php
if (session_status() === PHP_SESSION_NONE) {
    date_default_timezone_set('Europe/Budapest');
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 3600);
}

session_start();
define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', ROOT_PATH . '/src');

require_once __DIR__ . '/../vendor/autoload.php';
use Controller;
use Service;

$router = new Controller\Router();
$pdoService = new Service\PDOService();

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpeg" href="logo.jpg">
    <title>Pyros</title>
    <link rel="stylesheet" href="index.css?<?=time()?>"/>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="header-content">
                <img src="logo.jpg" alt="BE Logo" class="main-logo">
                <h1>Pyros 1.0</h1>
            </div>

            <?php if (isset($_SESSION['userid'])): ?>
                <div class="header-actions">
                    <?php if (isset($_GET['view']) && $_GET['view'] !== 'home'): ?>
                        <a href="index.php?view=home" class="back-home-btn">
                            <i class="fas fa-home"></i> Kezdőlap
                        </a>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="logout-btn">
                            Kijelentkezés <span class="user-name">(<?= $_SESSION['username'] ?>)</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main>
        <?php if(isset($_SESSION['userid'])):?>
            <?= include($router->getView()) ?>
        <?php else:?>
            <?= include($router->view("login")) ?>
        <?php endif;?>
    </main>
</body>
</html>