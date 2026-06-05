<?php
/**
 * Migración: Añadir columna 'activo' para borrado lógico.
 */
require_once __DIR__ . '/recursos/Database.php';

try {
    $pdo = Database::getConnection();
    
    // 1. Agregar columna activo si no existe
    $pdo->exec("ALTER TABLE solicitudes ADD COLUMN activo TINYINT(1) DEFAULT 1 AFTER justificacion");
    
    echo "¡Columna 'activo' añadida exitosamente a la tabla 'solicitudes'!";
    echo "<br><br><a href='index.php'>Volver al inicio</a>";

} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "La columna 'activo' ya existe.";
    } else {
        echo "Error: " . $e->getMessage();
    }
}
