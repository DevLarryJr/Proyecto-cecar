<?php
require_once __DIR__ . '/recursos/Database.php';
require_once __DIR__ . '/capa_de_acceso/dao/SolicitudDAO.php';

try {
    echo "--- CENTROS DE COSTO ---\n";
    $cc = SolicitudDAO::obtenerCentrosCosto();
    echo "Count: " . count($cc) . "\n";
    if (count($cc) > 0) print_r($cc[0]);

    echo "\n--- FUNCIONES ---\n";
    $f = SolicitudDAO::obtenerFunciones();
    echo "Count: " . count($f) . "\n";
    if (count($f) > 0) print_r($f[0]);

    echo "\n--- RUBROS ---\n";
    $r = SolicitudDAO::obtenerRubros();
    echo "Count: " . count($r) . "\n";
    if (count($r) > 0) print_r($r[0]);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
