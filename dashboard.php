<?php
require_once 'config.php';
$user = checkLogin($pdo);

if (!$user) { header('Location: login.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    if (verifyCSRF($_POST['csrf_token'] ?? '')) {
        $stmt = $pdo->prepare("UPDATE users SET session_token = NULL, session_expiry = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        setcookie('remember_token', '', time() - 3600, "/");
    }
    header('Location: login.php');
    exit;
}

$isTeam = in_array($user['rank'], ['Admin', 'Owner']);
$msg = '';

if ($isTeam && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_news'])) {
    if (verifyCSRF($_POST['csrf_token'] ?? '')) {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        if (!empty($title) && !empty($content)) {
            $stmt = $pdo->prepare("INSERT INTO news (title, content, author) VALUES (?, ?, ?)");
            $stmt->execute([$title, $content, $user['username']]);
            $msg = 'Die Ankündigung wurde erfolgreich veröffentlicht.';
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
    <title>FightSMP | Zentrale</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dash-nav { padding: 25px 40px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .u-info { display: flex; align-items: center; gap: 20px; }
        .u-info img { width: 56px; height: 56px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.5); }
        .rb { background: var(--accent); font-size: 11px; padding: 4px 12px; border-radius: 6px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; color: white; margin-left: 10px; }
        
        .dash-content { padding: 45px 40px; margin-bottom: 30px; }
        .dash-content h2 { font-weight: 900; letter-spacing: -0.5px; font-size: 24px; margin-bottom: 10px; }
        
        .form-group { margin-bottom: 20px; }
        textarea, input[type="text"] { 
            width: 100%; padding: 16px 20px; background: rgba(255,255,255,0.03); 
            border: 1px solid rgba(255,255,255,0.1); color: white; border-radius: 8px; 
            font-size: 15px; transition: var(--transition); 
        }
        textarea:focus, input[type="text"]:focus { border-color: var(--accent); outline: none; background: rgba(255,255,255,0.06); }
        
        .btn { 
            background: var(--accent); border: none; color: white; padding: 16px 35px; 
            font-weight: 800; border-radius: 8px; cursor: pointer; text-transform: uppercase; 
            font-size: 14px; letter-spacing: 1px; transition: var(--transition); 
        }
        .btn:hover { background: var(--accent-hover); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(255,102,0,0.3); }
        
        .btn-logout { background: none; border: 1px solid rgba(248, 113, 113, 0.3); color: #f87171; font-weight: 700; cursor: pointer; font-size: 13px; padding: 10px 20px; border-radius: 6px; transition: var(--transition); text-transform: uppercase; }
        .btn-logout:hover { background: rgba(248, 113, 113, 0.1); border-color: #f87171; }
        
        @media (max-width: 600px) {
            .dash-nav { flex-direction: column; gap: 20px; align-items: flex-start; }
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

    <div class="container fade-in-scroll">
        
        <div class="glass-card dash-nav">
            <div class="u-info">
                <img src="https://mc-heads.net/avatar/<?= htmlspecialchars($user['username']) ?>/56" alt="Dein Avatar" loading="lazy">
                <div>
                    <strong style="font-size: 20px;"><?= htmlspecialchars($user['username']) ?></strong> 
                    <span class="rb"><?= htmlspecialchars($user['rank']) ?></span>
                </div>
            </div>
            <form method="POST" style="margin:0;">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <button type="submit" name="logout" class="btn-logout">Sitzung beenden</button>
            </form>
        </div>

        <div class="glass-card dash-content">
            <h2>KONTO-ZENTRALE</h2>
            <p style="color: var(--text-muted); font-size:16px; line-height:1.7;">
                Willkommen im gesicherten Web-Bereich von FightSMP. Dein Account ist erfolgreich mit deinem Minecraft-Konto verknüpft.
            </p>
        </div>

        <?php if ($isTeam): ?>
            <div class="glass-card dash-content" style="border-left: 4px solid var(--accent);">
                <h2>ADMIN: NEUER BEITRAG</h2>
                <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 25px;">Dieser Text erscheint sofort öffentlich in den Ankündigungen.</p>
                
                <?php if($msg): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; padding: 15px; border-radius: 8px; font-weight: 600; margin-bottom: 25px;">
                        ✓ <?= $msg ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <div class="form-group">
                        <input type="text" name="title" placeholder="Titel (z.B. Wartungsarbeiten 20:00 Uhr)" required autocomplete="off">
                    </div>
                    <div class="form-group">
                        <textarea name="content" rows="6" placeholder="Inhalt des Beitrags..." required></textarea>
                    </div>
                    <button type="submit" name="post_news" class="btn">Beitrag Veröffentlichen</button>
                </form>
            </div>
        <?php endif; ?>

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
