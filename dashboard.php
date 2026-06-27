<?php
session_start();

// 1. SICHERHEITS-CHECK: Ist der Spieler überhaupt eingeloggt?
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Wenn nicht, schmeiße ihn zurück zum Login!
    header("Location: login.php");
    exit;
}

// 2. Den Namen des eingeloggten Spielers aus dem Login-Speicher holen
$spieler_name = htmlspecialchars($_SESSION['spieler_name']);
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FightSMP | Dashboard</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --bg-dark: #05070c; --bg-sidebar: #0b0f19; --card-bg: rgba(17, 24, 39, 0.8); 
            --text-main: #f3f4f6; --text-muted: #9ca3af; --accent-orange: #f97316; 
            --accent-blue: #3b82f6; --accent-green: #10b981;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: var(--bg-dark); color: var(--text-main); font-family: 'Inter', sans-serif; display: flex; min-height: 100vh; }
        h1, h2, h3 { font-family: 'Rajdhani', sans-serif; font-weight: 700; }

        /* SIDEBAR */
        .sidebar { width: 280px; background: var(--bg-sidebar); border-right: 1px solid rgba(255,255,255,0.05); padding: 30px 20px; display: flex; flex-direction: column; }
        .logo { font-size: 28px; color: #fff; text-decoration: none; text-align: center; margin-bottom: 40px; display: block; }
        .logo i { color: var(--accent-orange); margin-right: 5px; }

        .user-profile { display: flex; align-items: center; gap: 15px; padding: 15px; background: rgba(0,0,0,0.3); border-radius: 12px; margin-bottom: 40px; border: 1px solid rgba(255,255,255,0.05); }
        .user-profile img { width: 45px; height: 45px; border-radius: 8px; image-rendering: pixelated; }
        .user-info h4 { font-size: 16px; margin-bottom: 3px; word-break: break-all; }
        .user-info span { font-size: 12px; color: var(--accent-orange); background: rgba(249,115,22,0.1); padding: 2px 8px; border-radius: 20px; font-weight: 600; }

        .nav-label { font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 1px; margin-bottom: 10px; padding-left: 10px; }
        
        .sidebar-menu { list-style: none; margin-bottom: 30px; }
        .sidebar-menu li { margin-bottom: 5px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 12px 15px; color: var(--text-muted); text-decoration: none; border-radius: 8px; font-weight: 500; font-size: 15px; transition: 0.2s; cursor: pointer; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: var(--accent-orange); color: #fff; }
        .sidebar-menu a i { font-size: 18px; width: 20px; text-align: center; }

        .logout-btn { margin-top: auto; color: #ef4444 !important; }
        .logout-btn:hover { background: rgba(239, 68, 68, 0.1) !important; color: #ef4444 !important; }

        /* MAIN CONTENT AREA */
        .main-content { flex: 1; padding: 40px 60px; overflow-y: auto; background-image: radial-gradient(circle at top right, rgba(59, 130, 246, 0.05), transparent 40%); }
        .header-title { font-size: 32px; margin-bottom: 10px; }
        .header-subtitle { color: var(--text-muted); margin-bottom: 40px; }

        .view-section { display: none; animation: fadeIn 0.4s ease; }
        .view-section.active { display: block; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card { background: var(--card-bg); border: 1px solid rgba(255,255,255,0.05); padding: 25px; border-radius: 16px; display: flex; align-items: center; gap: 20px; }
        .stat-icon { width: 60px; height: 60px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 24px; }
        .icon-blue { background: rgba(59, 130, 246, 0.1); color: var(--accent-blue); }
        .icon-green { background: rgba(16, 185, 129, 0.1); color: var(--accent-green); }
        .icon-red { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .icon-orange { background: rgba(249, 115, 22, 0.1); color: var(--accent-orange); }
        
        .stat-info p { color: var(--text-muted); font-size: 14px; font-weight: 600; text-transform: uppercase; margin-bottom: 5px; }
        .stat-info h3 { font-size: 28px; color: #fff; }

        @media (max-width: 768px) {
            body { flex-direction: column; }
            .sidebar { width: 100%; border-right: none; border-bottom: 1px solid rgba(255,255,255,0.05); padding: 20px; }
            .main-content { padding: 20px; }
        }
    </style>
</head>
<body>

    <nav class="sidebar">
        <a href="index.html" class="logo"><i class="fa-solid fa-shield-halved"></i> Fight<span>SMP</span></a>
        
        <div class="user-profile">
            <!-- HIER WIRD DER KOPF DES SPIELERS AUTOMATISCH GELADEN -->
            <img src="https://mc-heads.net/avatar/<?php echo $spieler_name; ?>/100" alt="Avatar">
            <div class="user-info">
                <!-- HIER WIRD DER NAME DES SPIELERS AUTOMATISCH EINGEFÜGT -->
                <h4><?php echo $spieler_name; ?></h4>
                <span><i class="fa-solid fa-user" style="font-size: 10px;"></i> Spieler</span>
            </div>
        </div>

        <div class="nav-label">Spieler Bereich</div>
        <ul class="sidebar-menu">
            <li><a class="nav-btn active" onclick="switchView('stats', this)"><i class="fa-solid fa-chart-simple"></i> Meine Stats</a></li>
            <li><a href="index.html"><i class="fa-solid fa-house"></i> Zurück zur Website</a></li>
        </ul>

        <ul class="sidebar-menu" style="margin-top: auto; margin-bottom: 0;">
            <!-- Logout leitet zurück zur login.php -->
            <li><a href="login.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Abmelden</a></li>
        </ul>
    </nav>

    <main class="main-content">
        
        <div id="view-stats" class="view-section active">
            <!-- HIER WIRD AUCH DER NAME ANGEPASST -->
            <h1 class="header-title">Willkommen zurück, <?php echo $spieler_name; ?>! 👋</h1>
            <p class="header-subtitle">Hier ist deine aktuelle Übersicht vom FightSMP Server.</p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-blue"><i class="fa-solid fa-clock"></i></div>
                    <div class="stat-info"><p>Spielzeit</p><h3>? Std.</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-green"><i class="fa-solid fa-coins"></i></div>
                    <div class="stat-info"><p>Kontostand</p><h3>? $</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-red"><i class="fa-solid fa-skull"></i></div>
                    <div class="stat-info"><p>Kills</p><h3>?</h3></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon icon-orange"><i class="fa-solid fa-crosshairs"></i></div>
                    <div class="stat-info"><p>K/D Ratio</p><h3>?</h3></div>
                </div>
            </div>
            
            <div class="admin-form" style="width: 100%; max-width: 100%;">
                <h3 style="margin-bottom: 20px;">Letzte Aktivitäten</h3>
                <p style="color: var(--text-muted); font-size: 14px;">+ Du hast deinen Account erfolgreich mit dem Web-Dashboard verknüpft!</p>
            </div>
        </div>

    </main>

    <script>
        function switchView(viewName, clickedBtn) {
            document.querySelectorAll('.nav-btn').forEach(btn => btn.classList.remove('active'));
            clickedBtn.classList.add('active');
            document.querySelectorAll('.view-section').forEach(sec => sec.classList.remove('active'));
            document.getElementById('view-' + viewName).classList.add('active');
        }
    </script>
</body>
</html>
