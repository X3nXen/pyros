<?php
session_start();

$configPath = __DIR__ . '/anemos_v2/config.php';

if (!file_exists($configPath)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Konfigurációs fájl nem található!']);
    header('Location: index.php');
    exit;
}

$config = require $configPath;

try {
    $hostParts = explode(':', $config['host']);
    $host = $hostParts[0];
    $port = $hostParts[1] ?? '3306';

    $dsn = "mysql:host={$host};port={$port};dbname={$config['dbname']};charset={$config['charset']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Adatbázis csatlakozási hiba: ' . $e->getMessage()]);
    header('Location: index.php');
    exit;
}

$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

if (!$username || !$password) {
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);
    if ($jsonData) {
        $username = $jsonData['username'] ?? null;
        $password = $jsonData['password'] ?? null;
    }
}

if (!$username || !$password) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Felhasználónév és jelszó megadása kötelező!']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, password, cu_id FROM users WHERE username = :username LIMIT 1");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Hibás felhasználónév vagy jelszó!']);
        header('Location: index.php');
        exit;
    }

    $passwordValid = password_verify($password, $user['password']);

    if (!$passwordValid && $password === $user['password']) {
        $passwordValid = true;
    }

    if (!$passwordValid) {
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => 'Hibás felhasználónév vagy jelszó!']);
        header('Location: index.php');
        exit;
    }

    $_SESSION['userid'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['cu_id'] = $user['cu_id'];
    $_SESSION['logged_in'] = true;

    header('Location: portal.php');
    exit;

} catch (PDOException $e) {
    header('Location: index.php?error=1');
    exit;
}