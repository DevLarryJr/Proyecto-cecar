<?php
/**
 * SolicitudController.php
 */

require_once __DIR__ . '/../recursos/Auth.php';
require_once __DIR__ . '/../recursos/FileUploader.php';
require_once __DIR__ . '/../recursos/ViewHelper.php';
require_once __DIR__ . '/../capa_de_acceso/dao/SolicitudDAO.php';

class SolicitudController {

    /**
     * Prepara los datos para la vista de detalle.php
     */
    public static function prepararDetalle($id) {
        Auth::requireLogin();
        
        $id = (int) $id;
        $solicitud = $id > 0 ? SolicitudDAO::obtenerPorId($id) : null;

        if (!$solicitud) {
            header('Location: solicitudes.php');
            exit();
        }

        if (!Auth::isAdmin() && $solicitud['usuario_id'] != Auth::userId()) {
            header('Location: solicitudes.php?error=unauthorized');
            exit();
        }

        $timeline = ViewHelper::prepareTimeline($solicitud['estado'] ?? 'revision');

        $fechaTramite = null;
        $fechaFinal = null;
        if (!empty($solicitud['historial'])) {
            foreach (array_reverse($solicitud['historial']) as $h) {
                if (!$fechaTramite && in_array($h['estado'], ['revision', 'en_transito', 'pendiente', 'entregado'])) {
                    $fechaTramite = $h['fecha'];
                }
                if (!$fechaFinal && in_array($h['estado'], ['aprobado', 'rechazado'])) {
                    $fechaFinal = $h['fecha'];
                }
            }
        }
        if (!$fechaTramite && ($solicitud['estado'] ?? 'revision') === 'revision') {
            $fechaTramite = $solicitud['fecha'];
        }

        return [
            'solicitud' => $solicitud,
            'timeline' => $timeline,
            'fechaTramite' => $fechaTramite,
            'fechaFinal' => $fechaFinal
        ];
    }

    /**
     * Procesa el guardado/edición de una solicitud (POST)
     */
    public static function procesarGuardado() {
        try {
            Auth::requireLogin();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/solicitud.php');
                exit();
            }

            $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
            $errors = [];

            $fecha = trim($_POST['fecha_solicitud'] ?? '');
            $justificacion = trim($_POST['justificacion'] ?? '');

            if (empty($fecha)) $errors[] = 'La fecha de solicitud es obligatoria.';
            elseif (strtotime($fecha) > strtotime(date('Y-m-d'))) $errors[] = 'La fecha de solicitud no puede ser futura.';

            if (empty($justificacion)) $errors[] = 'La justificación es obligatoria.';
            elseif (strlen($justificacion) < 10) $errors[] = 'La justificación debe tener al menos 10 caracteres.';

            $dependencia = trim($_POST['dependencia'] ?? '');
            $tipo_solicitud = trim($_POST['tipo_solicitud'] ?? '');
            $nombre_solicitante = trim($_POST['nombre_solicitante'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');

            $servicios = $_POST['servicio'] ?? [];
            $cantidades = $_POST['cantidad'] ?? [];
            $itemsValidos = [];

            foreach ($servicios as $i => $srv) {
                if (empty(trim((string)$srv)) && empty(trim((string)($cantidades[$i] ?? '')))) continue;
                $itemsValidos[] = [
                    'servicio'           => trim((string)$srv),
                    'cantidad'           => trim((string)($cantidades[$i] ?? 0)),
                    'rubro_id'           => (int)($_POST['rubro_id'][$i] ?? 0),
                    'rubro'              => trim((string)($_POST['rubro'][$i] ?? '')),
                    'rubro_codigo'       => trim((string)($_POST['rubro_codigo'][$i] ?? '')),
                    'disponibilidad'     => trim((string)($_POST['disponibilidad'][$i] ?? 0)),
                    'centro_costo_id'    => (int)($_POST['centro_costo_id'][$i] ?? 0),
                    'centro_costos'      => trim((string)($_POST['centro_costos'][$i] ?? '')),
                    'cc_codigo'          => trim((string)($_POST['cc_codigo'][$i] ?? '')),
                    'fondos_id'          => (int)($_POST['fondos_id'][$i] ?? 0),
                    'fondo'              => trim((string)($_POST['fondo'][$i] ?? '')),
                    'funcion_id'         => (int)($_POST['funcion_id'][$i] ?? 0),
                    'funcion'            => trim((string)($_POST['funcion_nombre'][$i] ?? '')),
                    'funcion_codigo'     => trim((string)($_POST['funcion_codigo'][$i] ?? ''))
                ];
            }

            if (empty($itemsValidos)) $errors[] = 'Debes completar al menos una fila de servicios.';

            $uploadResult = FileUploader::upload($_FILES['adjunto'] ?? null);
            if (!$uploadResult['success']) $errors[] = $uploadResult['message'];

            if (!empty($errors)) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'errors' => $errors]);
                    exit();
                }
                throw new Exception(implode(", ", $errors));
            }

            $solicitudId = (int) ($_POST['id_solicitud'] ?? 0);
            $data = [
                'fecha' => $fecha,
                'justificacion' => $justificacion,
                'dependencia' => $dependencia,
                'tipo_solicitud' => $tipo_solicitud,
                'nombre_solicitante' => $nombre_solicitante,
                'cargo' => $cargo,
                'usuario_id' => Auth::userId(),
            ];

            if ($solicitudId > 0) {
                SolicitudDAO::actualizarCompleta($solicitudId, $data, $itemsValidos);
                $nuevoId = $solicitudId;
            } else {
                $nuevoId = SolicitudDAO::guardar($data, $itemsValidos, Auth::userId(), $uploadResult['filename'] ? $uploadResult : null);
            }

            if ($nuevoId === false) throw new Exception('Error al guardar la solicitud.');

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => true, 'message' => 'Solicitud enviada correctamente', 'id' => $nuevoId]);
                exit();
            }

            header('Location: ../presentacion/vistas/detalle.php?id=' . $nuevoId);
            exit();

        } catch (Exception $e) {
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'errors' => [$e->getMessage()]]);
                exit();
            }
            die("Error crítico: " . $e->getMessage());
        }
    }

    /**
     * Prepara los datos para la vista de solicitud.php (Formulario)
     */
    public static function prepararFormulario($id) {
        Auth::requireLogin();
        
        $id = (int) $id;
        $solicitud = null;
        $isEdit = false;

        if ($id > 0) {
            $solicitud = SolicitudDAO::obtenerPorId($id);
            if ($solicitud) {
                if (!Auth::isAdmin() && $solicitud['usuario_id'] != Auth::userId()) {
                    header('Location: solicitudes.php?error=unauthorized');
                    exit();
                }
                $estado = strtolower($solicitud['estado'] ?? '');
                if ($estado === 'revision') {
                    $isEdit = true;
                }
            }
        }

        return [
            'id' => $id,
            'isEdit' => $isEdit,
            'solicitud' => $solicitud
        ];
    }

    /**
     * Prepara los datos para la vista de solicitudes.php (Listado)
     */
    public static function prepararListado() {
        Auth::requireLogin();
        
        if (Auth::isAdmin()) {
            $solicitudes = SolicitudDAO::obtenerDecididasPorAdmin(Auth::userId());
            $tituloPagina = "Mis Decisiones";
            $subtituloPagina = "Historial de solicitudes procesadas (Aprobadas / Rechazadas) por ti.";
        } else {
            $solicitudes = SolicitudDAO::obtenerTodas(Auth::userId());
            $tituloPagina = "Mis Solicitudes";
            $subtituloPagina = "Listado de requerimientos realizados y su estado actual.";
        }

        return [
            'solicitudes' => $solicitudes,
            'tituloPagina' => $tituloPagina,
            'subtituloPagina' => $subtituloPagina
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    SolicitudController::procesarGuardado();
}
