<?php
require_once 'ams/config/config.php';

try {
    $stmt = $pdo->prepare('SELECT fk_full_name_bd, pi_first_name, pi_last_name, verification, LENGTH(verification) AS len, HEX(verification) AS hex FROM enrollment2 WHERE pi_first_name LIKE ? OR pi_last_name LIKE ? LIMIT 20');
    $stmt->execute(['%ABDUL%', '%ABDUL%']);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($rows) . " rows\n";
    foreach ($rows as $row) {
        echo $row['fk_full_name_bd'] . ' | ' . $row['pi_first_name'] . ' ' . $row['pi_last_name'] . ' | ' . var_export($row['verification'], true) . ' | len=' . $row['len'] . ' | hex=' . $row['hex'] . "\n";
    }
} catch (Exception $e) {
    echo 'ERROR: ' . $e->getMessage();
}
?>
