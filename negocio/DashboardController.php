<?php
/**
 * DashboardController.php — Capa de Negocio
 * Provee los datos necesarios para el dashboard administrativo.
 */

require_once __DIR__ . '/../capa_de_acceso/dao/SolicitudDAO.php';
require_once __DIR__ . '/../recursos/Auth.php';

class DashboardController
{
    /**
     * Prepara los datos agregados para el dashboard administrativo.
     */
    public static function prepararVistaAdmin() {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            header('Location: dashboard.php');
            exit();
        }

        return self::obtenerDatos();
    }

    /**
     * Obtiene el set completo de estadísticas (KPIs, Charts, Recientes).
     * @return array
     */
    public static function obtenerDatos(): array
    {
        // Seguridad: Solo admin puede obtener estos datos estadísticos
        if (!Auth::isAdmin()) {
            return [];
        }

        return [
            'resumen'           => SolicitudDAO::obtenerResumenDashboard(),
            'porEstado'         => SolicitudDAO::contarSolicitudesPorEstado(),
            'porMes'            => SolicitudDAO::contarSolicitudesPorMes(),
            'porDependencia'    => SolicitudDAO::contarSolicitudesPorDependencia(10),
            'recientes'         => SolicitudDAO::obtenerSolicitudesRecientes(10),
            'dependencias'      => SolicitudDAO::obtenerDependencias() 
        ];
    }
}
