<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$spieler_name = htmlspecialchars($_SESSION['spieler_name']);

// === STATS AUS DATENBANK LADEN ===
$spielzeit  = "0.0";
$kontostand = 0;
$kills      = 0;
$deaths     = 0;
$kd         = "0.00";
$db_error   = false;

// TODO: Echte Zugangsdaten vom Webhoster eintragen!
$db_host = "localhost"; $db_user = "root"; $db_pass = ""; $db_name = "fightsmp_db";
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if (!$conn->connect_error) {
    $stmt = $conn->prepare("SELECT playtime_hours, money, kills, deaths FROM player_stats WHERE spieler_name = ?");
    if ($stmt) {
        $stmt->bind_param("s", $_SESSION['spieler_name']);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $spielzeit  = number_format($row['playtime_hours'], 1);
            $kontostand = number_format($row['money'], 0, ',', '.');
            $kills      = (int) $row['kills'];
            $deaths     = (int) $row['deaths'];
            $kd         = number_format($kills / max($deaths, 1), 2);
        }
        $stmt->close();
    }
    $conn->close();
} else {
    $db_error = true;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FightSMP | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-dark: #05070c; --bg-sidebar: #0b0f19;
            --card-bg: rgba(17, 24, 39, 0.8);
            --text-main: #f3f4f6; --text-muted: #9ca3af;
            --accent-orange: #f97316; --accent-blue: #3b82f6; --accent-green: #10b981;
            --border: rgba(255,255,255,0.06);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        /* --- WEICHER ÜBERGANG --- */
        body { 
            background-color: var(--bg-dark); 
            color: var(--text-main); 
            font-family: 'Inter', sans-serif; 
            display: flex; 
            min-height: 100vh; 
            opacity: 0; 
            animation: fadeIn 0.4s ease-in-out forwards; 
        }
        body.fade-out { opacity: 0; transition: opacity 0.4s ease-in-out; }
        @keyframes fadeIn { to { opacity: 1; } }

        h1, h2, h3 { font-family: 'Rajdhani', sans-serif; font-weight: 700; }

        .sidebar { width: 280px; background: var(--bg-sidebar); border-right: 1px solid var(--border); padding: 30px 20px; display: flex; flex-direction: column; flex-shrink: 0; }
        .logo { font-size: 28px; color: #fff; text-decoration: none; text-align: center; margin-bottom: 35px; display: block; font-family: 'Rajdhani', sans-serif; font-weight: 700; }
        .logo i { color: var(--accent-orange); margin-right: 5px; }

        .user-profile { display: flex; align-items: center; gap: 12px; padding: 14px; background: rgba(0,0,0,0.3); border-radius: 12px; margin-bottom: 35px; border: 1px solid var(--border); }
        .user-profile img { width: 46px; height: 46px; border-radius: 8px; image-rendering: pixelated; }
        .user-info h4 { font-size: 15px; margin-bottom: 4px; word-break: break-all; }
        .user-info span { font-size: 11px; color: var(--accent-orange); background: rgba(249,115,22,0.12); padding: 2px 8px; border-radius: 20px; font-weight: 600; }

        .nav-label { font-size: 10px; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 1.2px; margin-bottom: 8px; padding-left: 10px; }
        .sidebar-menu { list-style: none; margin-bottom: 25px; }
        .sidebar-menu li { margin-bottom: 4px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 12px; padding: 11px 15px; color: var(--text-muted); text-decoration: none; border-radius: 8px; font-weight: 500; font-size: 14px; transition: 0.2s; cursor: pointer; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: var(--accent-orange); color: #fff; }
        .sidebar-menu a i { font-size: 16px; width: 18px; text-align: center; }
        .logout-btn { color: #ef4444 !important; }
        .logout-btn:hover { background: rgba(239,68,68,0.1) !important; color: #ef4444 !important; }

        .main-content { flex: 1; padding: 45px 55px; overflow-y: auto; background-image: radial-gradient(circle at top right, rgba(59,130,246,0.04), transparent 40%); }
        .page-header { margin-bottom: 40px; }
        .page-header h1 { font-size: 30px; margin-bottom: 6px; }
        .page-header p { color: var(--text-muted); font-size: 15px; }

        .db-error { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); padding: 14px 18px; border-radius: 10px; color: #fca5a5; font-size: 14px; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card-bg); border: 1px solid var(--border); padding: 22px 24px; border-radius: 16px; display: flex; align-items: center; gap: 18px; transition: border-color 0.2s; }
        .stat-card:hover { border-color: rgba(255,255,255,0.12); }
        .stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; justify-content: center; align-items: center; font-size: 22px; flex-shrink: 0; }
        .icon-blue   { background: rgba(59,130,246,0.12);  color: var(--accent-blue); }
        .icon-green  { background: rgba(16,185,129,0.12);  color: var(--accent-green); }
        .icon-red    { background: rgba(239,68,68,0.12);   color: #ef4444; }
        .icon-orange { background: rgba(249,115,22,0.12);  color: var(--accent-orange); }
        .icon-purple { background: rgba(168,85,247,0.12);  color: #a855f7; }
        .stat-info p  { color: var(--text-muted); font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .stat-info h3 { font-size: 26px; color: #fff; letter-spacing: 0.5px; }

        .info-box { background: var(--card-bg); border: 1px solid var(--border); padding: 28px; border-radius: 16px; }
        .info-box h3 { font-size: 20px; margin-bottom: 18px; color: #fff; }
        .activity-item { display: flex; align-items: flex-start; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--border); }
        .activity-item:last-child { border-bottom: none; padding-bottom: 0; }
        .activity-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 6px; }
        .dot-green  { background: var(--accent-green); box-shadow: 0 0 6px var(--accent-green); }
        .dot-orange { background: var(--accent-orange); }
        .dot-blue   { background: var(--accent-blue); }
        .activity-text { font-size: 14px; color: var(--text-muted); line-height: 1.5; }
        .activity-text strong { color: var(--text-main); }

        @media (max-width: 900px) { body { flex-direction: column; } .sidebar { width: 100%; } .main-content { padding: 25px 20px; } }
    </style>
</head>
<body>

<nav class="sidebar">
    <a href="index.html" class="logo"><i class="fa-solid fa-shield-halved"></i> Fight<span style="color:var(--accent-blue)">SMP</span></a>

    <div class="user-profile">
        <img src="https://mc-heads.net/avatar/<?php echo $spieler_name; ?>/100" alt="Avatar">
        <div class="user-info">
            <h4><?php echo $spieler_name; ?></h4>
            <span><i class="fa-solid fa-user" style="font-size:10px"></i> Spieler</span>
        </div>
    </div>

    <div class="nav-label">Spieler Bereich</div>
    <ul class="sidebar-menu">
        <li><a class="active"><i class="fa-solid fa-chart-simple"></i> Meine Stats</a></li>
        <li><a href="index.html"><i class="fa-solid fa-house"></i> Zur Website</a></li>
    </ul>

    <ul class="sidebar-menu" style="margin-top:auto;margin-bottom:0">
        <li><a href="logout.php" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Abmelden</a></li>
    </ul>
</nav>

<main class="main-content">
    <div class="page-header">
        <h1>Willkommen zurück, <?php echo $spieler_name; ?>! 👋</h1>
        <p>Deine aktuelle Übersicht vom FightSMP Server.</p>
    </div>

    <?php if ($db_error): ?>
    <div class="db-error"><i class="fa-solid fa-triangle-exclamation"></i> Datenbankverbindung fehlgeschlagen – Stats konnten nicht geladen werden.</div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card"><div class="stat-icon icon-blue"><i class="fa-solid fa-clock"></i></div><div class="stat-info"><p>Spielzeit</p><h3><?php echo $spielzeit; ?> Std.</h3></div></div>
        <div class="stat-card"><div class="stat-icon icon-green"><i class="fa-solid fa-coins"></i></div><div class="stat-info"><p>Kontostand</p><h3><?php echo $kontostand; ?> $</h3></div></div>
        <div class="stat-card"><div class="stat-icon icon-red"><i class="fa-solid fa-skull"></i></div><div class="stat-info"><p>Kills</p><h3><?php echo $kills; ?></h3></div></div>
        <div class="stat-card"><div class="stat-icon icon-purple"><i class="fa-solid fa-cross"></i></div><div class="stat-info"><p>Tode</p><h3><?php echo $deaths; ?></h3></div></div>
        <div class="stat-card"><div class="stat-icon icon-orange"><i class="fa-solid fa-crosshairs"></i></div><div class="stat-info"><p>K/D Ratio</p><h3><?php echo $kd; ?></h3></div></div>
    </div>

    <div class="info-box">
        <h3><i class="fa-solid fa-bolt" style="color:var(--accent-orange);margin-right:8px"></i>Letzte Aktivitäten</h3>
        <div class="activity-item"><div class="activity-dot dot-green"></div><div class="activity-text"><strong>Account verknüpft</strong> – Du hast dein Minecraft-Konto erfolgreich mit dem Dashboard verbunden.</div></div>
        <div class="activity-item"><div class="activity-dot dot-blue"></div><div class="activity-text"><strong><?php echo $kills; ?> Kills</strong> insgesamt erzielt – K/D: <strong><?php echo $kd; ?></strong></div></div>
        <div class="activity-item"><div class="activity-dot dot-orange"></div><div class="activity-text"><strong><?php echo $spielzeit; ?> Stunden</strong> Spielzeit auf FightSMP geloggt.</div></div>
    </div>
</main>

<!-- EXTERNES SKRIPT EINBINDEN -->
<script src="main.js"></script>
</body>
</html>
