<?php
require_once 'db.php';
$error = '';

if (checkLogin($pdo)) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $code = trim($_POST['code']);

    if (!empty($username) && !empty($code)) {
        $stmt = $pdo->prepare("SELECT * FROM link_codes WHERE username = ? AND code = ? AND created_at > NOW() - INTERVAL 10 MINUTE");
        $stmt->execute([$username, $code]);
        $linkData = $stmt->fetch();

        if ($linkData) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE uuid = ?");
            $stmt->execute([$linkData['uuid']]);
            $user = $stmt->fetch();

            if (!$user) {
                $stmt = $pdo->prepare("INSERT INTO users (uuid, username) VALUES (?, ?)");
                $stmt->execute([$linkData['uuid'], $linkData['username']]);
            }

            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));

            $stmt = $pdo->prepare("UPDATE users SET session_token = ?, session_expiry = ? WHERE uuid = ?");
            $stmt->execute([$token, $expiry, $linkData['uuid']]);

            // Cookie für genau 7 Tage setzen
            setcookie('remember_token', $token, time() + (86400 * 7), "/", "", false, true);

            $stmt = $pdo->prepare("DELETE FROM link_codes WHERE id = ?");
            $stmt->execute([$linkData['id']]);

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Falscher oder abgelaufener Verknüpfungscode! Hole einen neuen mit /link.';
        }
    } else {
        $error = 'Bitte fülle alle Pflichtfelder aus!';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FightSMP | Login Portal</title>
    <style>
        :root { --bg: #0b0b0c; --card-bg: #131316; --accent: #ff6600; --text: #f5f5f7; }
        body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-card { background: var(--card-bg); padding: 40px; border-radius: 10px; border: 1px solid rgba(255,102,0,0.15); width: 100%; max-width: 360px; box-shadow: 0 10px 30px rgba(0,0,0,0.4); text-align: center; }
        h2 { font-size: 26px; font-weight: 800; text-transform: uppercase; margin-bottom: 8px; }
        h2 span { color: var(--accent); }
        p { font-size: 13px; color: #8a8a93; margin-bottom: 30px; }
        input { width: 100%; padding: 12px 15px; margin-bottom: 15px; background: #1c1c21; border: 1px solid rgba(255,255,255,0.05); color: #fff; border-radius: 6px; font-size: 14px; box-sizing: border-box; }
        input:focus { border-color: var(--accent); outline: none; }
        button { width: 100%; padding: 14px; background: var(--accent); border: none; color: white; font-weight: 700; border-radius: 6px; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px; transition: 0.2s; }
        button:hover { background: #e05500; }
        .err { color: #ff453a; font-size: 13px; margin-bottom: 15px; text-align: left; background: rgba(255,69,58,0.1); padding: 10px; border-radius: 4px; border-left: 3px solid #ff453a; }
    </style>
</head>
<body>
    <div class="login-card">
        <h2>Fight<span>SMP</span></h2>
        <p>Nutze den Ingame-Code aus Minecraft zum Anmelden</p>
        <?php if($error): ?> <div class="err"><?= htmlspecialchars($error) ?></div> <?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Minecraft Accountname" required>
            <input type="text" name="code" placeholder="6-stelliger Ingame-Code" maxlength="6" required>
            <button type="submit">Dashboard öffnen</button>
        </form>
    </div>
</body>
</html>
