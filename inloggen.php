<?php
session_start();

// Bepaal de redirect-doelpagina
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'leden.php';

// Als de gebruiker al is ingelogd, stuur direct door
if (isset($_SESSION['user'])&& isset($_COOKIE['user'])) {
    $cookieData = json_decode($_COOKIE['user'], true);

    if (isset($cookieData['user']) && isset($cookieData['datetime'])) {
        $_SESSION['user'] = $cookieData['user'];
    header("Location: $redirect");
    exit();
}
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validatie van de vaste inloggegevens
    if ($username === 'LOIDocent' && $password === 'mysqlphp') {
        // Stel de sessievariabele in
        $_SESSION['user'] = $username;
        
        // Cookie wegschrijven met datum, tijd en gebruiker (geldig 1 uur)
        $cookieData = [
            'user'     => $username,
            'datetime' => date('Y-m-d H:i:s')
        ];
        $secure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        setcookie('user', json_encode($cookieData), time() + 3600, '/', '', $secure, true);
        
        // Redirect naar de gewenste pagina
        header("Location: $redirect");
        exit();
    } else {
        $error = "Ongeldige gebruikersnaam of wachtwoord.";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Inloggen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }
        form {
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            border: 2px solid #7b42f5;
            border-radius: 8px;
            background-color: #fff;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
        }
        .input-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #7b42f5;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }
        button:hover {
            background-color: #652dd0;
        }
        .error {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <h1>Inloggen</h1>
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <form method="post" action="inloggen.php?redirect=<?php echo urlencode($redirect); ?>">
        <!-- formulier-elementen -->
        <div class="input-group">
            <label for="username">Gebruikersnaam:</label>
            <input type="text" name="username" id="username" required>
        </div>
        <div class="input-group">
            <label for="password">Wachtwoord:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <button type="submit">Inloggen</button>
    </form>
</body>
</html>
