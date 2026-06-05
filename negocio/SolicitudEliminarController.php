<?php
/**
 * SolicitudEliminarController.php — Capa de Negocio
 * Procesa la eliminación de una solicitud.
 */

require_once __DIR__ . '/../recursos/Auth.php';
require_once __DIR__ . '/../capa_de_acceso/dao/SolicitudDAO.php';

Auth::requireLogin();

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $solicitud = SolicitudDAO::obtenerPorId($id);
    if ($solicitud) {
        // SEGURIDAD: Solo el dueño o el admin pueden eliminar
        if (!Auth::isAdmin() && $solicitud['usuario_id'] != Auth::userId()) {
            header('Location: ../presentacion/vistas/solicitudes.php?error=unauthorized');
            exit();
        }

        // REGLA DE NEGOCIO: Solo se puede eliminar si ya está aprobada (verde)
        if ($solicitud['estado'] !== 'aprobado') {
            header('Location: ../presentacion/vistas/solicitudes.php?error=not_approved');
            exit();
        }

        if (SolicitudDAO::eliminar($id)) {
            header('Location: ../presentacion/vistas/solicitudes.php?delete=ok');
        } else {
            header('Location: ../presentacion/vistas/solicitudes.php?delete=error');
        }
    } else {
        header('Location: ../presentacion/vistas/solicitudes.php');
    }
} else {
    header('Location: ../presentacion/vistas/solicitudes.php');
}
exit();
