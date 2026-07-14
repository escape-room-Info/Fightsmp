<?php
require_once 'config.php';
$error = '';

if (checkLogin($pdo)) { header('Location: dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) { die('CSRF-Token ungültig.'); }
    
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FightSMP | Portal Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; flex-direction: column; min-height: 100vh; }
        .login-wrapper { flex: 1; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .login-box { padding: 50px 40px; width: 100%; max-width: 420px; box-shadow: 0 25px 50px rgba(0,0,0,0.6); }
        
        .login-box h2 { text-align: center; margin-bottom: 10px; font-weight: 900; font-size: 28px; }
        .login-box h2 span { color: var(--accent); }
        
        .form-group { margin-bottom: 20px; text-align: left; }
        .form-group label { display: block; font-size: 13px; color: var(--text-muted); font-weight: 700; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        input { 
            width: 100%; padding: 16px 20px; background: rgba(255,255,255,0.03); 
            border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 8px; 
            font-size: 15px; transition: var(--transition); 
        }
        input:focus { border-color: var(--accent); outline: none; background: rgba(255,255,255,0.06); }
        
        button { 
            width: 100%; padding: 16px; background: var(--accent); border: none; 
            color: white; font-weight: 800; border-radius: 8px; cursor: pointer; 
            text-transform: uppercase; letter-spacing: 1px; margin-top: 10px; transition: var(--transition);
        }
        button:hover { background: var(--accent-hover); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(255,102,0,0.3); }
        
        .error-msg { 
            color: #f87171; font-size: 14px; font-weight: 600; background: rgba(248, 113, 113, 0.1); 
            padding: 15px; border-radius: 8px; border: 1px solid rgba(248, 113, 113, 0.2); 
            margin-bottom: 25px; text-align: center; 
        }
    </style>
</head>
<body>

    <nav>
        <div class="logo">Fight<span>SMP</span></div>
        <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Menü öffnen">☰</button>
        <ul class="nav-links" id="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="news.html">News</a></li>
            <li><a href="shop.html">Shop</a></li>
            <li><a href="team.html">Team</a></li>
            <li><a href="support.html">Support</a></li>
            <li><a href="profile.php">Spieler</a></li>
        </ul>
    </nav>

    <div class="login-wrapper fade-in-scroll">
        <div class="glass-card login-box">
            <h2>PORTAL<span>LOGIN</span></h2>
            <p style="text-align:center; font-size:14px; color:var(--text-muted); margin-bottom:35px;">Verifiziere dich über dein Minecraft-Konto.</p>
            
            <?php if($error): ?><div class="error-msg"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                
                <div class="form-group">
                    <label for="username">Minecraft Name</label>
                    <input type="text" id="username" name="username" placeholder="Dein Spielername" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label for="code">Verifizierungs-Code</label>
                    <input type="text" id="code" name="code" placeholder="6-stelliger Code via /link" maxlength="6" required autocomplete="off">
                </div>
                
                <button type="submit">Authentifizieren</button>
            </form>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="logo" style="font-size: 22px;">Fight<span>SMP</span></div>
            <div class="copyright">&copy; 2026 FightSMP. Created by Martin (Mqrtn_) & Max (xam__).</div>
        </div>
    </footer>
    <script src="main.js"></script>
</body>
</html>
