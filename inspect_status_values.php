<?php
require_once 'ams/config/config.php';
try {
    $stmt = $pdo->query('SELECT verification, COUNT(*) as cnt FROM enrollment2 GROUP BY verification ORDER BY cnt DESC');
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        echo '[' . ($row['verification'] === null ? 'NULL' : $row['verification']) . '] => ' . $row['cnt'] . "\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
?>
