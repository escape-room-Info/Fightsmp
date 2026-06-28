<?php
session_start();
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['spieler_login'])) {
    // === DATENBANK-ZUGANGSDATEN ===
    $db_host = "localhost"; 
    $db_user = "root";       // Bei XAMPP "root"
    $db_pass = "";           // Bei XAMPP leer lassen
    $db_name = "fightsmp_db";
    
    // Unterdrückt Fehlermeldungen auf der Webseite (für mehr Sicherheit)
    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    
    if (!$conn->connect_error) {
        $name = trim($_POST['username']);
        $code = trim($_POST['logincode']);
        
        // HÖCHSTE SICHERHEIT: Prepared Statements verhindern Hacker-Angriffe (SQL-Injection)
        $stmt = $conn->prepare("SELECT * FROM website_logins WHERE spieler_name = ? AND login_code = ?");
        
        if ($stmt) {
            $stmt->bind_param("ss", $name, $code); // "ss" steht für zwei Strings (Text)
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($res->num_rows > 0) {
                // Login erfolgreich!
                $_SESSION['loggedin'] = true; 
                $_SESSION['spieler_name'] = $name;
                
                // Account als "verknüpft" markieren (OHNE den Code zu löschen!)
                $update_stmt = $conn->prepare("UPDATE website_logins SET verknuepft = 1 WHERE spieler_name = ?");
                $update_stmt->bind_param("s", $name);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Weiterleitung zum Dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $error_msg = "Falscher Minecraft-Name oder Code!";
            }
            $stmt->close();
        } else {
            $error_msg = "Datenbankfehler: Anfrage konnte nicht verarbeitet werden.";
        }
        $conn->close();
    } else {
        $error_msg = "Datenbankverbindung fehlgeschlagen! Läuft MySQL?";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FightSMP | Login</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --bg-color: #05070c; --card-bg: rgba(17, 24, 39, 0.7); --text-main: #f3f4f6; --text-muted: #9ca3af; --accent-orange: #f97316; --accent-blue: #3b82f6; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background-color: var(--bg-color); background-image: linear-gradient(rgba(5, 7, 12, 0.9), rgba(5, 7, 12, 0.95)), url('https://images.unsplash.com/photo-1605379399642-870262d3d051?q=80&w=2000&auto=format&fit=crop'); background-attachment: fixed; background-size: cover; color: var(--text-main); font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        
        .login-container { background: var(--card-bg); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 40px; width: 100%; max-width: 450px; text-align: center; box-shadow: 0 20px 40px rgba(0,0,0,0.5); }
        .logo { font-family: 'Rajdhani', sans-serif; font-size: 32px; font-weight: 700; text-decoration: none; color: #fff; margin-bottom: 30px; display: inline-block; transition: 0.2s; }
        .logo:hover { transform: scale(1.05); }
        .logo i { color: var(--accent-orange); margin-right: 5px; }
        
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; margin-bottom: 8px; font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .input-group input { width: 100%; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); padding: 14px; border-radius: 8px; color: #fff; outline: none; font-family: 'Inter', sans-serif; transition: 0.2s; }
        .input-group input:focus { border-color: var(--accent-orange); background: rgba(0,0,0,0.6); }
        
        .submit-btn { width: 100%; background: var(--accent-orange); color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 600; font-size: 16px; cursor: pointer; transition: 0.2s; display: flex; justify-content: center; align-items: center; gap: 8px; }
        .submit-btn:hover { background: #ea580c; transform: translateY(-2px); }
        
        .error-box { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px; border-radius: 8px; color: #fca5a5; margin-bottom: 20px; text-align: center; font-size: 14px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 8px; }
        
        .back-link { display: inline-block; margin-top: 25px; color: var(--text-muted); text-decoration: none; font-size: 14px; transition: 0.2s; }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>

    <div class="login-container">
        <a href="index.html" class="logo"><i class="fa-solid fa-shield-halved"></i> Fight<span style="color: var(--accent-blue);">SMP</span></a>
        
        <?php if($error_msg != ""): ?>
            <div class="error-box"><i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error_msg; ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
            <input type="hidden" name="spieler_login" value="1">
            
            <div class="input-group">
                <label>Minecraft Name</label>
                <input type="text" name="username" placeholder="Z.B. Notch" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <label>Login Code (via /link)</label>
                <input type="text" name="logincode" placeholder="XXXX-XXXX" required autocomplete="off">
            </div>
            
            <button type="submit" class="submit-btn"><i class="fa-solid fa-link"></i> Account Verknüpfen</button>
        </form>
        
        <a href="index.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Zurück zur Website</a>
    </div>

</body>
</html>
