<?php
/**
 * RevisionController.php — Capa de Negocio (Administración)
 * 
 * Este controlador es el responsable de la lógica de auditoría. Permite a los
 * administradores revisar las solicitudes pendientes, avanzar su estado en el flujo
 * de trabajo o finalizarlas (Aprobar/Rechazar).
 */

require_once __DIR__ . '/../recursos/Auth.php';
require_once __DIR__ . '/../capa_de_acceso/dao/SolicitudDAO.php';

class RevisionController {

    /**
     * Prepara la lista de solicitudes pendientes para el panel de revisión.
     * Solo permite el acceso si el usuario tiene rol de Administrador.
     * 
     * @return array ['pendientes' => Lista de registros]
     */
    public static function prepararRevision() {
        Auth::requireLogin();

        if (!Auth::isAdmin()) {
            header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/dashboard.php');
            exit();
        }

        return [
            'pendientes' => SolicitudDAO::obtenerPendientes()
        ];
    }
}

/**
 * LÓGICA DE PROCESAMIENTO DE ACCIONES (POST)
 * ----------------------------------------
 * Se ejecuta cuando el administrador presiona "Aprobar", "Rechazar" o usa el 
 * botón de "Mantener para Avanzar".
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Detectar si es una petición AJAX para manejar la respuesta correctamente
    $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    
    // Si es AJAX, iniciamos un buffer para limpiar posibles espacios en blanco de PHP que dañen el JSON
    if ($isAjax) {
        ob_start();
    }

    // 1. Verificación Obligatoria de Seguridad
    Auth::requireLogin();

    if (!Auth::isAdmin()) {
        $errorMsg = 'Acceso denegado: Se requieren permisos de auditoría.';
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => [$errorMsg]]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/dashboard.php');
        exit();
    }

    // 2. Extracción de parámetros de la acción
    $solicitudId = (int) ($_POST['id_solicitud'] ?? 0);
    $accion      = trim($_POST['accion']             ?? '');

    // Validar que los datos técnicos mínimos existan
    if ($solicitudId <= 0 || !in_array($accion, ['aprobar', 'rechazar', 'avanzar'], true)) {
        $msg = 'Parámetros de acción inválidos o faltantes.';
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => [$msg]]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?error=missing_data');
        exit();
    }

    // Comprobar existencia real de la solicitud antes de operar
    $solicitudActual = SolicitudDAO::obtenerPorId($solicitudId);
    if (!$solicitudActual) {
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => ['La solicitud ya no existe en el sistema.']]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?error=not_found');
        exit();
    }

    /**
     * CASO A: AVANZAR ESTADO (Flujo intermedio)
     * Utilizado por el componente visual "Hold-to-Confirm".
     */
    if ($accion === 'avanzar') {
        $comentario = trim($_POST['comentario_revision'] ?? '');
        // El DAO se encarga de determinar cuál es el SIGUIENTE estado lógico
        $res = SolicitudDAO::avanzarEstado($solicitudId, Auth::userId(), $comentario);
        
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode($res);
            exit();
        }
        
        $status = $res['success'] ? 'success' : 'error';
        header("Location: " . Auth::baseUrl() . "presentacion/vistas/revision.php?status=$status");
        exit();
    }

    /**
     * CASO B: FINALIZAR (Aprobar o Rechazar)
     * Estas acciones cierran el ciclo de vida de la solicitud.
     */
    
    // Regla: No se puede finalizar algo que ya está cerrado
    $estadosCerrados = ['aprobado', 'rechazado'];
    if (in_array($solicitudActual['estado'], $estadosCerrados, true)) {
        $msg = 'Operación inválida: La solicitud ya tiene un veredicto final: ' . $solicitudActual['estado'];
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => [$msg]]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?error=already_finalized');
        exit();
    }

    // Determinar nuevo estado y comentario por defecto si no se escribió uno
    $nuevoEstado = ($accion === 'aprobar') ? 'aprobado' : 'rechazado';
    $rawComentario = trim($_POST['comentario_revision'] ?? '');
    $comentario = !empty($rawComentario) ? $rawComentario : "Solicitud " . ($nuevoEstado === 'aprobado' ? 'aprobada' : 'rechazada') . " tras revisión técnica.";

    // Ejecutar actualización en la base de datos
    $ok = SolicitudDAO::actualizarEstado($solicitudId, $nuevoEstado, Auth::userId(), $comentario);

    if ($ok) {
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => true, 'message' => 'Veredicto registrado correctamente']);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?status=success');
    } else {
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => ['Error crítico al actualizar el registro en la BD.']]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?error=db_error');
    }
    exit();
}
