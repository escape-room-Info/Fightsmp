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
            $msg = 'Die Ankündigung wurde erfolgreich synchronisiert!';
        }
    }
}
$csrf = generateCSRF();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FightSMP | Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dash-nav { padding: 20px 35px; display: flex; justify-content: space-between; align-items: center; }
        .u-info { display: flex; align-items: center; gap: 15px; }
        .u-info img { width: 44px; height: 44px; border-radius: 6px; border: 1px solid var(--accent); box-shadow: 0 0 10px var(--accent-glow); }
        .rb { background: var(--accent); font-size: 11px; padding: 3px 10px; border-radius: 4px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .dash-content { padding: 35px; margin-top: 30px; }
        textarea, input[type="text"] { width: 100%; padding: 14px; margin: 12px 0; background: #0f0f12; border: 1px solid var(--card-border); color: white; border-radius: 6px; font-size: 14px; }
        textarea:focus, input[type="text"]:focus { border-color: var(--accent); outline: none; }
        .btn { background: var(--accent); border: none; color: white; padding: 14px 30px; font-weight: 700; border-radius: 6px; cursor: pointer; text-transform: uppercase; font-size: 13px; letter-spacing: 0.5px; }
        .btn:hover { background: var(--accent-hover); }
    </style>
</head>
<body>
    <div class="container">
        <div class="glass-card dash-nav">
            <div class="u-info">
                <img src="https://crafatar.com/avatars/<?= urlencode($user['uuid']) ?>?size=44&overlay">
                <div><strong><?= htmlspecialchars($user['username']) ?></strong> <span class="rb"><?= htmlspecialchars($user['rank']) ?></span></div>
            </div>
            <form method="POST" style="display:inline;">
                <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                <button type="submit" name="logout" style="background:none; border:none; color:#f87171; font-weight:600; cursor:pointer; font-size:14px;">Session beenden</button>
            </form>
        </div>

        <div class="glass-card dash-content">
            <h2 style="font-weight:900; letter-spacing:-0.3px;">SPIELER-ZENTRALE</h2>
            <p style="color: var(--text-muted); margin-top: 10px; font-size:15px; line-height:1.6;">Willkommen im internen Web-Bereich. Dein Account ist vollständig verifiziert. Sitzungsdauer aktiv für 7 Tage.</p>
        </div>

        <?php if ($isTeam): ?>
            <div class="glass-card dash-content" style="border-left: 4px solid var(--accent);">
                <h2 style="font-weight:900; letter-spacing:-0.3px;">ADMINISTRATIVE ANKÜNDIGUNG ERSTELLEN</h2>
                <?php if($msg): ?><p style="color:#10b981; font-size:14px; margin-bottom:10px; font-weight:600;"><?= $msg ?></p><?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <input type="text" name="title" placeholder="Titel des Beitrags" required autocomplete="off">
                    <textarea name="content" rows="5" placeholder="Schreibe hier den offiziellen Text..." required></textarea>
                    <button type="submit" name="post_news" class="btn">Beitrag Veröffentlichen</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <footer>
        <div class="footer-content">
            <div class="logo" style="font-size: 20px;">Fight<span>SMP</span></div>
            <div class="footer-links">
                <a href="index.html">Home</a>
                <a href="support.html">Support</a>
                <a href="https://discord.gg/DEIN_DISCORD_LINK" target="_blank" style="color: #5865F2; font-weight: bold;">Discord</a>
            </div>
            <div class="copyright">
                &copy; 2026 FightSMP. Alle Rechte vorbehalten. Created by Martin (Mqrtn_) & Max (xam__).
            </div>
        </div>
    </footer>
</body>
</html>
