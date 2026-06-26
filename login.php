<?php
session_start();
$error_msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['spieler_login'])) {
    $db_host = "localhost"; $db_user = "DEIN_DB_BENUTZER"; $db_pass = "DEIN_PASSWORT"; $db_name = "fightsmp_db";
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if (!$conn->connect_error) {
        $name = $conn->real_escape_string(trim($_POST['username']));
        $code = $conn->real_escape_string(trim($_POST['logincode']));
        $res = $conn->query("SELECT * FROM website_logins WHERE spieler_name = '$name' AND login_code = '$code'");
        if ($res->num_rows > 0) {
            $_SESSION['loggedin'] = true; $_SESSION['spieler_name'] = $name;
            $conn->query("UPDATE website_logins SET login_code = NULL, verknuepft = 1 WHERE spieler_name = '$name'");
            header("Location: dashboard.html"); exit;
        } else { $error_msg = "Falscher Name oder Code!"; }
        $conn->close();
    }
}
?>
<!-- Hier folgt dein HTML-Design wie zuvor -->

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
        body { background-color: var(--bg-color); color: var(--text-main); font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-container { background: var(--card-bg); border: 1px solid rgba(255,255,255,0.05); border-radius: 20px; padding: 40px; width: 100%; max-width: 450px; text-align: center; }
        .logo { font-family: 'Rajdhani', sans-serif; font-size: 32px; font-weight: 700; text-decoration: none; color: #fff; margin-bottom: 30px; display: inline-block; }
        .logo i { color: var(--accent-orange); margin-right: 5px; }
        .input-group { margin-bottom: 20px; text-align: left; }
        .input-group label { display: block; margin-bottom: 8px; font-size: 13px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }
        .input-group input { width: 100%; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); padding: 14px; border-radius: 8px; color: #fff; outline: none; }
        .submit-btn { width: 100%; background: var(--accent-orange); color: white; border: none; padding: 14px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .error-box { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 12px; border-radius: 8px; color: #fca5a5; margin-bottom: 20px; text-align: center; }
        .back-link { display: inline-block; margin-top: 25px; color: var(--text-muted); text-decoration: none; font-size: 14px; }
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
                <input type="text" name="username" placeholder="Z.B. Notch" required>
            </div>
            <div class="input-group">
                <label>Login Code (via /link)</label>
                <input type="text" name="logincode" placeholder="XXXX-XXXX" required>
            </div>
            <button type="submit" class="submit-btn"><i class="fa-solid fa-link"></i> Account Verknüpfen</button>
        </form>
        <a href="index.html" class="back-link"><i class="fa-solid fa-arrow-left"></i> Zurück zur Website</a>
    </div>
</body>
</html>
