<?php
require_once 'config.php';

$playerFound = false;
$playerData = null;
$searchName = '';

// Prüfen, ob ein Spielername in der URL übergeben wurde (?user=Name)
if (isset($_GET['user']) && !empty($_GET['user'])) {
    $searchName = trim($_GET['user']);
    
    // Spieler in der Datenbank suchen
    $stmt = $pdo->prepare("SELECT uuid, username, rank FROM users WHERE username = ?");
    $stmt->execute([$searchName]);
    $playerData = $stmt->fetch();

    if ($playerData) {
        $playerFound = true;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FightSMP | Spieler-Profil</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .search-container { max-width: 600px; margin: 0 auto 40px auto; display: flex; gap: 10px; }
        .search-container input { flex-grow: 1; background: #111115; border: 1px solid rgba(255,255,255,0.05); padding: 16px; border-radius: 8px; color: white; font-size: 16px; transition: 0.2s; }
        .search-container input:focus { outline: none; border-color: #ff6600; box-shadow: 0 0 15px rgba(255,102,0,0.15); }
        .search-container button { background: #ff6600; border: none; color: white; padding: 0 30px; border-radius: 8px; font-weight: 800; cursor: pointer; text-transform: uppercase; transition: 0.2s; }
        .search-container button:hover { background: #ff8533; transform: scale(1.02); }

        .profile-card { max-width: 800px; margin: 0 auto; display: flex; flex-wrap: wrap; gap: 40px; padding: 40px; }
        .profile-left { flex: 1; min-width: 250px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .profile-right { flex: 2; min-width: 300px; display: flex; flex-direction: column; justify-content: center; }
        
        /* 3D Body Rendering */
        .skin-render { height: 280px; filter: drop-shadow(0 20px 20px rgba(0,0,0,0.5)); transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        .profile-card:hover .skin-render { transform: scale(1.05) translateY(-10px); }

        .username { font-size: 38px; font-weight: 900; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px; }
        .rank-badge { display: inline-block; background: var(--accent); color: white; padding: 6px 16px; border-radius: 6px; font-weight: 800; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 30px; box-shadow: 0 5px 15px rgba(255,102,0,0.2); }
        
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .stat-box { background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.04); padding: 20px; border-radius: 10px; text-align: center; }
        .stat-value { font-size: 28px; font-weight: 800; color: white; margin-bottom: 5px; }
        .stat-label { font-size: 13px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; }
        
        .not-found { text-align: center; padding: 60px 20px; }
        .not-found h2 { font-size: 28px; color: #f87171; margin-bottom: 10px; }
    </style>
</head>
<body>

    <nav>
        <div class="logo">Fight<span>SMP</span></div>
        <ul class="nav-links">
            <li><a href="index.html">Home</a></li>
            <li><a href="news.html">News</a></li>
            <li><a href="shop.html">Shop</a></li>
            <li><a href="team.html">Team</a></li>
            <li><a href="support.html">Support</a></li>
            <li><a href="login.php" class="btn-login">Login</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1 class="page-title" style="text-align: center; border: none; padding: 0;">Spieler-Suche</h1>
        <p class="subtitle" style="text-align: center;">Gib den Namen eines registrierten Kämpfers ein.</p>

        <!-- Suchfeld -->
        <form method="GET" action="profile.php" class="search-container">
            <input type="text" name="user" placeholder="Minecraft Name suchen..." value="<?= htmlspecialchars($searchName) ?>" required autocomplete="off">
            <button type="submit">Suchen</button>
        </form>

        <?php if (isset($_GET['user'])): ?>
            <?php if ($playerFound): ?>
                
                <!-- Das eigentliche Profil -->
                <div class="glass-card profile-card">
                    <div class="profile-left">
                        <!-- Zeigt den kompletten 3D-Körper an -->
                        <img src="https://mc-heads.net/body/<?= htmlspecialchars($playerData['username']) ?>/250" alt="Skin von <?= htmlspecialchars($playerData['username']) ?>" class="skin-render">
                    </div>
                    <div class="profile-right">
                        <div class="username"><?= htmlspecialchars($playerData['username']) ?></div>
                        <div><span class="rank-badge"><?= htmlspecialchars($playerData['rank']) ?></span></div>
                        
                        <!-- Statistik Platzhalter für Max -->
                        <div class="stats-grid">
                            <div class="stat-box">
                                <div class="stat-value" style="color: #10b981;">?</div>
                                <div class="stat-label">Kills</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value" style="color: #f87171;">?</div>
                                <div class="stat-label">Tode</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value" style="color: #3b82f6;">-</div>
                                <div class="stat-label">Spielzeit</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value" style="color: #eab308;">? $</div>
                                <div class="stat-label">Kontostand</div>
                            </div>
                        </div>
                        <p style="margin-top: 20px; font-size: 12px; color: var(--text-muted); text-align: center;">Statistiken werden bald ins Web-System integriert.</p>
                    </div>
                </div>

            <?php else: ?>
                <!-- Spieler nicht in der DB gefunden -->
                <div class="glass-card not-found">
                    <h2>Spieler nicht gefunden</h2>
                    <p style="color: var(--text-muted);">Der Spieler <b><?= htmlspecialchars($searchName) ?></b> existiert nicht in unserer Datenbank.<br>Er muss mindestens einmal auf dem Server <b>/link</b> eingegeben haben, um hier aufzutauchen.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

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
