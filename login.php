<?php
require_once 'config.php';
$error = '';

if (checkLogin($pdo)) { header('Location: dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { die('CSRF-Token ungültig.'); }
    
    // Einfaches Rate-Limiting gegen Brute-Force via Session
    if (isset($_SESSION['last_login_attempt']) && (time() - $_SESSION['last_login_attempt'] < 2)) {
        $error = 'Bitte warte einen Moment vor dem nächsten Versuch.';
    } else {
        $_SESSION['last_login_attempt'] = time();
        $username = trim($_POST['username']);
        $code = trim($_POST['code']);

        $stmt = $pdo->prepare("SELECT * FROM link_codes WHERE username = ? AND code = ? AND created_at > NOW() - INTERVAL 10 MINUTE");
        $stmt->execute([$username, $code]);
        $link = $stmt->fetch();

        if ($link) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE uuid = ?");
            $stmt->execute([$link['uuid']]);
            $user = $stmt->fetch();

            if (!$user) {
                $stmt = $pdo->prepare("INSERT INTO users (uuid, username) VALUES (?, ?)");
                $stmt->execute([$link['uuid'], $link['username']]);
            }

            $rawToken = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $rawToken);
            $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));

            $stmt = $pdo->prepare("UPDATE users SET session_token = ?, session_expiry = ? WHERE uuid = ?");
            $stmt->execute([$tokenHash, $expiry, $link['uuid']]);

            // Cookie setzen (7 Tage persistent)
            setcookie('remember_token', $rawToken, time() + (86400 * 7), "/", "", true, true);
            $pdo->prepare("DELETE FROM link_codes WHERE id = ?")->execute([$link['id']]);

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Der Code ist ungültig oder abgelaufen.';
        }
    }
}
$csrf = generateCSRF();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FightSMP | Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; height: 90vh; padding: 0; }
        .login-box { padding: 45px; width: 100%; max-width: 380px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        input { width: 100%; padding: 14px; margin: 12px 0; background: #0f0f12; border: 1px solid var(--card-border); color: white; border-radius: 6px; font-size: 14px; }
        input:focus { border-color: var(--accent); outline: none; }
        button { width: 100%; padding: 14px; background: var(--accent); border: none; color: white; font-weight: 700; border-radius: 6px; cursor: pointer; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 10px; }
        button:hover { background: var(--accent-hover); }
        .error-msg { color: #f87171; font-size: 13px; background: rgba(248, 113, 113, 0.08); padding: 10px; border-radius: 4px; border: 1px solid rgba(248, 113, 113, 0.2); margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="glass-card login-box">
        <h2 style="text-align: center; margin-bottom: 5px; font-weight: 900;">PORTAL-<span>LOGIN</span></h2>
        <p style="text-align:center; font-size:13px; color:var(--text-muted); margin-bottom:25px;">Verifiziere dich über dein Minecraft-Konto.</p>
        <?php if($error): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="text" name="username" placeholder="Minecraft Name" required autocomplete="off">
            <input type="text" name="code" placeholder="6-stelliger Ingame-Code" maxlength="6" required autocomplete="off">
            <button type="submit">Authentifizieren</button>
        </form>
    </div>
    <footer>
        <div class="footer-content">
            <div class="logo" style="font-size: 20px;">Fight<span>SMP</span></div>
            <div class="footer-links">
                <a href="index.html">Home</a>
                <a href="support.html">Support</a>
                <a href="https://discord.gg/https://discord.gg/X53qbwatNs" target="_blank" style="color: #5865F2; font-weight: bold;">Discord</a>
            </div>
            <div class="copyright">
                &copy; 2026 FightSMP. Alle Rechte vorbehalten. Created by Martin (Mqrtn_) & Max (xam__).
            </div>
        </div>
    </footer>
</body>
</html>
