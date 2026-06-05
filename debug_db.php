<?php
require_once __DIR__ . '/recursos/Database.php';

try {
    $pdo = Database::getConnection();
    
    $tables = ['centros_costo', 'rubros', 'funcion', 'fondos'];
    $results = [];

    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
            $count = $stmt->fetchColumn();
            $results[$table] = "OK (Total: $count)";
        } catch (Exception $e) {
            $results[$table] = "ERROR: " . $e->getMessage();
        }
    }

    header('Content-Type: application/json');
    echo json_encode($results, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage();
}
