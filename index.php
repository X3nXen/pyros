<?php
session_start();

// Ha már be van jelentkezve, egyből a portal.php-re irányítjuk
if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: portal.php');
    exit;
}

$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balajti Energetika Kft. - Bejelentkezés</title>
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

        /* Zöld fejléc bar */
        .header-bar {
            background-color: #1b5e20;
            color: #ffffff;
            padding: 18px 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .header-bar h1 {
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Login kártya középre igazítva */
        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-top: 5px solid #2e7d32;
        }

        .login-card h2 {
            font-size: 20px;
            color: #1b5e20;
            margin-bottom: 24px;
            text-align: center;
        }

        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
        }

        .form-group input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.2s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2e7d32;
        }

        .btn-submit {
            width: 100%;
            background-color: #2e7d32;
            color: white;
            border: none;
            padding: 13px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #1b5e20;
        }
    </style>
</head>

<body>

    <div class="header-bar">
        <h1>Balajti Energetika Kft.</h1>
    </div>

    <div class="main-container">
        <div class="login-card">
            <h2>Rendszer Bejelentkezés</h2>

            <?php if ($error): ?>
                <div class="alert-error">
                    Hibás felhasználónév vagy jelszó!
                </div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <!-- Megjelöljük, hogy ez egy HTML form átirányítás -->
                <input type="hidden" name="redirect_html" value="1">

                <div class="form-group">
                    <label for="username">Felhasználónév</label>
                    <input type="text" id="username" name="username" required autofocus autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Jelszó</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" class="btn-submit">Bejelentkezés</button>
            </form>
        </div>
    </div>

</body>

</html>