<?php
/**
 * RevisionController.php — Capa de Negocio
 * Gestión de auditoría y transiciones de estado para solicitudes.
 */

require_once __DIR__ . '/../recursos/Auth.php';
require_once __DIR__ . '/../capa_de_acceso/dao/SolicitudDAO.php';

class RevisionController {

    /**
     * Prepara los datos para la vista revision.php
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

// Lógica de procesamiento de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Asegurar limpieza de buffer para respuestas JSON
    $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
    
    if ($isAjax) {
        ob_start();
    }

    // ── 1. Verificar sesión y rol Admin ──────────────────────────
    Auth::requireLogin();

    if (!Auth::isAdmin()) {
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => ['Acceso denegado: Se requieren permisos de administrador.']]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/dashboard.php');
        exit();
    }

    // ── 3. Obtener y validar datos ────────────────────────────────
    $solicitudId = (int) ($_POST['id_solicitud'] ?? 0);
    $accion      = trim($_POST['accion']             ?? '');

    if ($solicitudId <= 0 || !in_array($accion, ['aprobar', 'rechazar', 'avanzar'], true)) {
        $msg = 'Datos técnicos faltantes para procesar la revisión.';
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => [$msg]]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?error=missing_data');
        exit();
    }

    // Validar regla de negocio básica
    $solicitudActual = SolicitudDAO::obtenerPorId($solicitudId);
    if (!$solicitudActual) {
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => ['Solicitud no encontrada']]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?error=not_found');
        exit();
    }

    // ── 4. Manejar ACCIÓN: AVANZAR (Hold-to-Advance) ───────────────
    if ($accion === 'avanzar') {
        $comentario = trim($_POST['comentario_revision'] ?? '');
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

    // ── 5. Manejar ACCIÓN: FINALIZAR (Aprobar/Rechazar) ─────────────
    $estadosNoPermitidos = ['aprobado', 'rechazado'];
    if (in_array($solicitudActual['estado'], $estadosNoPermitidos, true)) {
        $msg = 'Esta solicitud ya fue finalizada con estado: ' . $solicitudActual['estado'];
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => [$msg]]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?error=already_finalized');
        exit();
    }

    $nuevoEstado = ($accion === 'aprobar') ? 'aprobado' : 'rechazado';
    $rawComentario = trim($_POST['comentario_revision'] ?? '');
    $comentario    = !empty($rawComentario) ? $rawComentario : "Solicitud " . ($nuevoEstado === 'aprobado' ? 'aprobada' : 'rechazada') . " por auditoría técnica";

    $ok = SolicitudDAO::actualizarEstado(
        $solicitudId,
        $nuevoEstado,
        Auth::userId(),
        $comentario
    );

    if ($ok) {
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => true, 'message' => 'Revisión finalizada con éxito']);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?status=success');
    } else {
        if ($isAjax) {
            header('Content-Type: application/json');
            if (ob_get_length()) ob_clean();
            echo json_encode(['success' => false, 'errors' => ['Error en la base de datos']]);
            exit();
        }
        header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/revision.php?error=db_error');
    }
    exit();
}
