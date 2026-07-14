<?php
require_once 'config.php';

// Verhindert unnötiges Buffering und setzt den exakten Cache-Header für 30 Sekunden
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: max-age=30');

try {
    $stmt = $pdo->query("SELECT title, content, author, DATE_FORMAT(created_at, '%d.%m.%Y - %H:%i Uhr') as created_at FROM news ORDER BY id DESC LIMIT 10");
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode([]);
}
exit;
?>
