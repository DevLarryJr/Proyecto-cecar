<?php
/**
 * SolicitudAjaxController.php — Capa de Negocio (AJAX)
 * Proporciona respuestas JSON para búsquedas y operaciones asíncronas.
 */

require_once __DIR__ . '/../recursos/Auth.php';
require_once __DIR__ . '/../capa_de_acceso/dao/SolicitudDAO.php';

header('Content-Type: application/json; charset=utf-8');

// 1. Validar Sesión
Auth::init();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Sesión no válida'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// Acepta action y también accion
$action = $_GET['action'] ?? $_GET['accion'] ?? '';

try {
    switch ($action) {
        case 'buscar':
            $termino = $_GET['q'] ?? '';
            $estado  = $_GET['estado'] ?? 'all';
            $fecha   = $_GET['fecha'] ?? '';

            $resultados = SolicitudDAO::buscarAvanzado($termino, $estado, $fecha);

            echo json_encode([
                'success' => true,
                'data' => $resultados
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'centros_costo':
            $resultados = SolicitudDAO::obtenerCentrosCosto();

            echo json_encode([
                'success' => true,
                'data' => $resultados
            ], JSON_UNESCAPED_UNICODE);
            break;

        case 'funciones':
            $resultados = SolicitudDAO::obtenerFunciones();
            echo json_encode(['success' => true, 'data' => $resultados], JSON_UNESCAPED_UNICODE);
            break;

        case 'rubros':
            $resultados = SolicitudDAO::obtenerRubros();
            echo json_encode(['success' => true, 'data' => $resultados], JSON_UNESCAPED_UNICODE);
            break;

        case 'fondos':
            $resultados = SolicitudDAO::obtenerFondos();
            echo json_encode(['success' => true, 'data' => $resultados], JSON_UNESCAPED_UNICODE);
            break;

        case 'eliminar':
            $id = (int) ($_POST['id'] ?? 0);

            if ($id > 0 && SolicitudDAO::eliminar($id)) {
                echo json_encode([
                    'success' => true
                ], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo eliminar el registro'
                ], JSON_UNESCAPED_UNICODE);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no permitida'
            ], JSON_UNESCAPED_UNICODE);
            break;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}