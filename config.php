<?php
// Erhöhter Session-Schutz vor Session-Hijacking und XSS
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Nur über HTTPS erlauben
ini_set('session.cookie_samesite', 'Strict');
session_start();

$host = 'localhost';
$db   = 'fightsmp_db';
$user = 'root';
$pass = 'DEIN_PASSWORT';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    die("Kritischer Systemfehler bei der Synchronisation.");
}

function checkLogin($pdo) {
    if (isset($_COOKIE['remember_token'])) {
        // Token hashen, um Timing-Attacks auf die DB zu unterbinden
        $tokenHash = hash('sha256', $_COOKIE['remember_token']);
        $stmt = $pdo->prepare("SELECT * FROM users WHERE session_token = ? AND session_expiry > NOW()");
        $stmt->execute([$tokenHash]);
        return $stmt->fetch();
    }
    return false;
}

function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
