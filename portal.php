<?php
session_start();

// Ha nincs bejelentkezve, visszadobjuk a login oldalra
if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Felhasználó';
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balajti Energetika Kft. - Portál</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f0f4f1;
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Zöld fejléc bar elrendezés */
        .header-bar {
            background-color: #1b5e20;
            color: #ffffff;
            padding: 18px 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-bar h1 {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .user-info {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-link {
            color: #a5d6a7;
            text-decoration: none;
            font-weight: 600;
        }

        .logout-link:hover {
            text-decoration: underline;
        }

        /* Középső elosztó kártyák */
        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .portal-grid {
            display: flex;
            gap: 30px;
            width: 100%;
            max-width: 700px;
        }

        .portal-card {
            flex: 1;
            background: #ffffff;
            border-radius: 12px;
            padding: 40px 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border: 2px solid #e0e0e0;
            text-decoration: none;
            color: #333;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 180px;
        }

        .portal-card:hover {
            border-color: #2e7d32;
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.15);
        }

        .portal-card h2 {
            font-size: 22px;
            color: #1b5e20;
            margin-bottom: 12px;
        }

        .portal-card p {
            font-size: 14px;
            color: #666;
            line-height: 1.4;
        }

        @media (max-width: 600px) {
            .portal-grid {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

    <div class="header-bar">
        <h1>Balajti Energetika Kft.</h1>
        <div class="user-info">
            <span>Üdv, <strong><?= htmlspecialchars($username) ?></strong>!</span>
            <a href="logout.php" class="logout-link">Kijelentkezés</a>
        </div>
    </div>

    <div class="main-container">
        <div class="portal-grid">
            <a href="/anemos_v2/public/index.php" class="portal-card">
                <h2>Felülvizsgálat</h2>
            </a>

            <a href="http://localhost:5173/" class="portal-card">
                <h2>Audit</h2>
            </a>
        </div>
    </div>

</body>

</html>