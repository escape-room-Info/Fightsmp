<?php
require_once 'config.php';

$playerFound = false;
$playerData = null;
$searchName = '';

if (isset($_GET['user']) && !empty($_GET['user'])) {
    $searchName = trim($_GET['user']);
    $stmt = $pdo->prepare("SELECT uuid, username, rank FROM users WHERE username = ?");
    $stmt->execute([$searchName]);
    $playerData = $stmt->fetch();
    if ($playerData) $playerFound = true;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FightSMP | Spieler-Profil</title>
    <style>html,body{background-color:#09090b}</style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;800;900&display=swap">
    <link rel="stylesheet" href="style.css">
    <style>
        .search-container { max-width: 650px; margin: 0 auto 50px auto; display: flex; gap: 15px; }
        .search-container input { 
            flex-grow: 1; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); 
            padding: 18px 24px; border-radius: 8px; color: white; font-size: 16px; transition: var(--transition); 
        }
        .search-container input:focus { outline: none; border-color: var(--accent); background: rgba(255,255,255,0.06); }
        .search-container button { 
            background: var(--accent); border: none; color: white; padding: 0 35px; border-radius: 8px; 
            font-weight: 800; cursor: pointer; text-transform: uppercase; transition: var(--transition); 
        }
        .search-container button:hover { background: var(--accent-hover); transform: translateY(-2px); }

        .profile-card { 
            max-width: 850px; margin: 0 auto; display: flex; flex-wrap: wrap; gap: 50px; padding: 50px; 
            background: linear-gradient(135deg, rgba(24,24,27,0.8) 0%, rgba(9,9,11,0.9) 100%);
        }
        .profile-left { flex: 1; min-width: 250px; display: flex; justify-content: center; align-items: center; }
        .profile-right { flex: 2; min-width: 300px; display: flex; flex-direction: column; justify-content: center; }
        
        .skin-render { height: 320px; filter: drop-shadow(0 20px 30px rgba(0,0,0,0.6)); transition: var(--transition); }
        .profile-card:hover .skin-render { transform: scale(1.03) translateY(-10px); }

        .username { font-size: 42px; font-weight: 900; margin-bottom: 5px; text-transform: uppercase; letter-spacing: -1px; }
        .rank-badge { 
            display: inline-block; background: var(--accent); color: white; padding: 6px 18px; 
            border-radius: 6px; font-weight: 800; font-size: 13px; text-transform: uppercase; 
            letter-spacing: 1px; margin-bottom: 40px; 
        }
        
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
        .stat-box { 
            background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); 
            padding: 25px; border-radius: var(--radius); text-align: center; 
        }
        .stat-value { font-size: 32px; font-weight: 900; color: white; margin-bottom: 5px; }
        .stat-label { font-size: 12px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 1px; }
        
        .not-found { text-align: center; padding: 80px 20px; }
        .not-found h2 { font-size: 28px; color: #ef4444; margin-bottom: 15px; }
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
            <li><a href="profile.php" class="active">Spieler</a></li>
            <li><a href="login.php" class="btn-login">Login</a></li>
        </ul>
    </nav>

    <div class="container fade-in-scroll">
        <h1 class="page-title" style="text-align: center;">Kämpfer Akte</h1>
        <p class="subtitle" style="text-align: center;">Durchsuche die Datenbank nach registrierten Spielern.</p>

        <form method="GET" action="profile.php" class="search-container">
            <input type="text" name="user" placeholder="Minecraft Namen eingeben..." value="<?= htmlspecialchars($searchName) ?>" required autocomplete="off" aria-label="Spieler suchen">
            <button type="submit">Suchen</button>
        </form>

        <?php if (isset($_GET['user'])): ?>
            <?php if ($playerFound): ?>
                
                <div class="glass-card profile-card fade-in-scroll">
                    <div class="profile-left">
                        <img src="https://mc-heads.net/body/<?= htmlspecialchars($playerData['username']) ?>/320" alt="Skin von <?= htmlspecialchars($playerData['username']) ?>" class="skin-render" loading="lazy">
                    </div>
                    <div class="profile-right">
                        <div class="username"><?= htmlspecialchars($playerData['username']) ?></div>
                        <div><span class="rank-badge"><?= htmlspecialchars($playerData['rank']) ?></span></div>
                        
                        <div class="stats-grid">
                            <div class="stat-box">
                                <div class="stat-value" style="color: #10b981;">?</div>
                                <div class="stat-label">Kills</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value" style="color: #ef4444;">?</div>
                                <div class="stat-label">Tode</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value" style="color: #3b82f6;">-</div>
                                <div class="stat-label">Spielzeit</div>
                            </div>
                            <div class="stat-box">
                                <div class="stat-value" style="color: #f59e0b;">?</div>
                                <div class="stat-label">Kontostand ($)</div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="glass-card not-found fade-in-scroll">
                    <h2>Kein Eintrag gefunden</h2>
                    <p style="color: var(--text-muted);">Der Kämpfer <b><?= htmlspecialchars($searchName) ?></b> ist in der Datenbank nicht registriert.<br>Er muss mindestens einmal <code>/link</code> auf dem Server ausgeführt haben.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <footer>
        <div class="footer-content">
            <div class="logo" style="font-size: 22px;">Fight<span>SMP</span></div>
            <div class="footer-links">
                <a href="index.html">Home</a>
                <a href="support.html">Support</a>
                <a href="https://discord.gg/X53qbwatNs" target="_blank" rel="noopener noreferrer">Discord beitreten</a>
            </div>
            <div class="copyright">&copy; 2026 FightSMP. Created by Martin (Mqrtn_) & Max (xam__).</div>
        </div>
    </footer>

    <script src="main.js"></script>
</body>
</html>
