<?php
require_once __DIR__ . '/recursos/Database.php';
require_once __DIR__ . '/capa_de_acceso/dao/SolicitudDAO.php';

function test_action($name, $data) {
    echo "--- ACTION: $name ---\n";
    $json = json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
    echo $json . "\n\n";
}

try {
    test_action('centros_costo', SolicitudDAO::obtenerCentrosCosto());
    test_action('funciones', SolicitudDAO::obtenerFunciones());
    test_action('rubros', SolicitudDAO::obtenerRubros());
    test_action('fondos', SolicitudDAO::obtenerFondos());

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
