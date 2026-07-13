<?php
require_once 'db.php';
$user = checkLogin($pdo);

if (!$user) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['logout'])) {
    $stmt = $pdo->prepare("UPDATE users SET session_token = NULL, session_expiry = NULL WHERE id = ?");
    $stmt->execute([$user['id']]);
    setcookie('remember_token', '', time() - 3600, "/");
    header('Location: login.php');
    exit;
}

// API-Endpunkt für die news.html-Datei bereithalten
if(isset($_GET['api']) && $_GET['api'] == 'news') {
    $newsStmt = $pdo->query("SELECT title, content, author, DATE_FORMAT(created_at, '%d.%m.%Y') as created_at FROM news ORDER BY id DESC LIMIT 5");
    header('Content-Type: application/json');
    echo json_encode($newsStmt->fetchAll());
    exit;
}

$isTeam = in_array($user['rank'], ['Admin', 'Owner']);
$msg = '';

if ($isTeam && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_news'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    if (!empty($title) && !empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO news (title, content, author) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $user['username']]);
        $msg = 'News erfolgreich live gestellt!';
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>FightSMP | Dashboard</title>
    <style>
        :root { --bg: #0b0b0c; --card-bg: #131316; --accent: #ff6600; --text: #f5f5f7; }
        body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', sans-serif; margin: 0; padding: 25px; }
        .top-nav { display: flex; justify-content: space-between; align-items: center; background: var(--card-bg); padding: 15px 35px; border-radius: 8px; border: 1px solid rgba(255,102,0,0.1); }
        .profile { display: flex; align-items: center; gap: 15px; }
        .profile img { width: 44px; height: 44px; border-radius: 6px; border: 1px solid var(--accent); }
        .badge-rank { background: var(--accent); color: white; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-left: 10px; }
        .btn-logout { color: #8a8a93; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .btn-logout:hover { color: #ff453a; }
        
        .main-layout { max-width: 900px; margin: 40px auto; display: flex; flex-direction: column; gap: 30px; }
        .box { background: var(--card-bg); padding: 30px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.02); }
        h2 { font-size: 20px; text-transform: uppercase; margin-top: 0; color: var(--accent); margin-bottom: 20px; }
        
        input[type="text"], textarea { width: 100%; padding: 12px; margin-bottom: 15px; background: #1c1c21; border: 1px solid rgba(255,255,255,0.05); color: white; border-radius: 6px; box-sizing: border-box; }
        input:focus, textarea:focus { border-color: var(--accent); outline: none; }
        .btn-send { background: var(--accent); border: none; color: white; padding: 12px 25px; font-weight: bold; border-radius: 6px; cursor: pointer; text-transform: uppercase; }
        .btn-send:hover { background: #e05500; }
    </style>
</head>
<body>

    <div class="top-nav">
        <div class="profile">
            <img src="https://crafatar.com/avatars/<?= $user['uuid'] ?>?size=44&overlay" alt="Skin">
            <div>
                <strong style="font-size: 16px;"><?= htmlspecialchars($user['username']) ?></strong>
                <span class="badge-rank"><?= $user['rank'] ?></span>
            </div>
        </div>
        <div>
            <a href="index.html" style="color: white; margin-right: 25px; text-decoration: none; font-size: 14px;">Zurück zur Homepage</a>
            <a href="?logout=1" class="btn-logout">Abmelden</a>
        </div>
    </div>

    <div class="main-layout">
        <div class="box">
            <h2>Willkommen im Spieler-Portal</h2>
            <p style="color: #8a8a93; line-height: 1.6;">Hier hast du Einsicht in deine Kontodetails. Dein aktuell hinterlegter Rang ist im System vermerkt. Wenn du deine Privilegien erweitern möchtest, besuche den Shop.</p>
        </div>

        <?php if ($isTeam): ?>
            <div class="box" style="border-left: 4px solid var(--accent);">
                <h2>News verfassen (Teamleitung)</h2>
                <?php if($msg): ?> <p style="color: #00ff66; font-size: 14px; margin-bottom: 15px;"><?= $msg ?></p> <?php endif; ?>
                <form method="POST">
                    <input type="text" name="title" placeholder="Überschrift der Ankündigung" required>
                    <textarea name="content" rows="5" placeholder="Inhalt der News..." required></textarea>
                    <button type="submit" name="post_news" class="btn-send">News veröffentlichen</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
