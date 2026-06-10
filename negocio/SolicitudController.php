<?php
/**
 * SolicitudController.php — Capa de Negocio
 * 
 * Este controlador gestiona el ciclo de vida de las solicitudes desde el lado del usuario:
 * creación, edición, listado personal y visualización de detalles.
 */

require_once __DIR__ . '/../recursos/Auth.php';
require_once __DIR__ . '/../recursos/FileUploader.php';
require_once __DIR__ . '/../recursos/ViewHelper.php';
require_once __DIR__ . '/../capa_de_acceso/dao/SolicitudDAO.php';

class SolicitudController {

    /**
     * Prepara los datos necesarios para mostrar la vista de detalle.php.
     * Incluye validación de propiedad (un usuario solo ve sus propias solicitudes,
     * a menos que sea administrador) y cálculo de fechas del timeline.
     * 
     * @param int $id ID de la solicitud a consultar.
     * @return array Conjunto de datos para la vista.
     */
    public static function prepararDetalle($id) {
        Auth::requireLogin();
        
        $id = (int) $id;
        $solicitud = $id > 0 ? SolicitudDAO::obtenerPorId($id) : null;

        // Si la solicitud no existe, fuera.
        if (!$solicitud) {
            header('Location: solicitudes.php');
            exit();
        }

        // CONTROL DE SEGURIDAD: Solo el dueño o un Admin pueden ver el detalle.
        if (!Auth::isAdmin() && $solicitud['usuario_id'] != Auth::userId()) {
            header('Location: solicitudes.php?error=unauthorized');
            exit();
        }

        // Preparar configuración visual del proceso (pasos 1, 2, 3)
        $timeline = ViewHelper::prepareTimeline($solicitud['estado'] ?? 'revision');

        // Lógica para determinar fechas clave del progreso basándose en el historial
        $fechaTramite = null;
        $fechaFinal = null;
        if (!empty($solicitud['historial'])) {
            foreach (array_reverse($solicitud['historial']) as $h) {
                // Primera vez que se movió de revisión
                if (!$fechaTramite && in_array($h['estado'], ['revision', 'en_transito', 'pendiente', 'entregado'])) {
                    $fechaTramite = $h['fecha'];
                }
                // Momento de la aprobación o rechazo final
                if (!$fechaFinal && in_array($h['estado'], ['aprobado', 'rechazado'])) {
                    $fechaFinal = $h['fecha'];
                }
            }
        }
        
        // Fallback: si está en revisión y no hay historial, la fecha inicial es la de creación
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
     * Punto de entrada principal para procesar el envío del formulario (POST).
     * Soporta tanto envío tradicional (recarga) como AJAX (JSON).
     */
    public static function procesarGuardado() {
        try {
            Auth::requireLogin();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                header('Location: ' . Auth::baseUrl() . 'presentacion/vistas/solicitud.php');
                exit();
            }

            // Detectar si la petición viene de JS (Fetch/AJAX)
            $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');
            $errors = [];

            // 1. Captura y Limpieza de datos básicos
            $fecha = trim($_POST['fecha_solicitud'] ?? '');
            $justificacion = trim($_POST['justificacion'] ?? '');

            // 2. Validaciones de Negocio (Backend bypass prevention)
            if (empty($fecha)) $errors[] = 'La fecha de solicitud es obligatoria.';
            elseif (strtotime($fecha) > strtotime(date('Y-m-d'))) $errors[] = 'La fecha de solicitud no puede ser futura.';

            if (empty($justificacion)) $errors[] = 'La justificación es obligatoria.';
            elseif (strlen($justificacion) < 10) $errors[] = 'La justificación debe tener al menos 10 caracteres.';

            // Datos del perfil (se recuperan de POST pero el DAO usará los de sesión por seguridad)
            $dependencia = trim($_POST['dependencia'] ?? '');
            $tipo_solicitud = trim($_POST['tipo_solicitud'] ?? '');
            $nombre_solicitante = trim($_POST['nombre_solicitante'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');

            // 3. Procesamiento de Servicios Requeridos (Tabla dinámica)
            $servicios = $_POST['servicio'] ?? [];
            $cantidades = $_POST['cantidad'] ?? [];
            $itemsValidos = [];

            foreach ($servicios as $i => $srv) {
                // Ignorar filas totalmente vacías
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

            // 4. Gestión del archivo adjunto (PDF)
            $uploadResult = FileUploader::upload($_FILES['adjunto'] ?? null);
            if (!$uploadResult['success']) $errors[] = $uploadResult['message'];

            // 5. Retorno de errores si existen
            if (!empty($errors)) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'errors' => $errors]);
                    exit();
                }
                throw new Exception(implode(", ", $errors));
            }

            // 6. Persistencia en Base de Datos vía DAO
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
                // Actualización (Solo permitido si está en revisión)
                SolicitudDAO::actualizarCompleta($solicitudId, $data, $itemsValidos);
                $nuevoId = $solicitudId;
            } else {
                // Creación de nueva solicitud
                $nuevoId = SolicitudDAO::guardar($data, $itemsValidos, Auth::userId(), $uploadResult['filename'] ? $uploadResult : null);
            }

            if ($nuevoId === false) throw new Exception('Error interno al intentar persistir los datos.');

            // 7. Respuesta final exitosa
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
            die("Fallo en el controlador: " . $e->getMessage());
        }
    }

    /**
     * Prepara datos para el formulario de solicitud.php.
     * Soporta modo "Creación" (ID 0) y modo "Edición" (ID > 0).
     */
    public static function prepararFormulario($id) {
        Auth::requireLogin();
        
        $id = (int) $id;
        $solicitud = null;
        $isEdit = false;

        if ($id > 0) {
            $solicitud = SolicitudDAO::obtenerPorId($id);
            if ($solicitud) {
                // Seguridad: solo el dueño edita
                if (!Auth::isAdmin() && $solicitud['usuario_id'] != Auth::userId()) {
                    header('Location: solicitudes.php?error=unauthorized');
                    exit();
                }
                // Regla de negocio: solo se edita si está en revisión inicial
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
     * Prepara el listado de solicitudes para la vista solicitudes.php.
     * Diferencia entre "Dueño de las solicitudes" y "Administrador/Revisor".
     */
    public static function prepararListado() {
        Auth::requireLogin();
        
        if (Auth::isAdmin()) {
            // El admin ve las solicitudes que ya ha procesado
            $solicitudes = SolicitudDAO::obtenerDecididasPorAdmin(Auth::userId());
            $tituloPagina = "Mis Decisiones";
            $subtituloPagina = "Historial de requerimientos que has aprobado o rechazado.";
        } else {
            // El usuario ve todas sus solicitudes personales
            $solicitudes = SolicitudDAO::obtenerTodas(Auth::userId());
            $tituloPagina = "Mis Solicitudes";
            $subtituloPagina = "Seguimiento de tus requerimientos registrados.";
        }

        return [
            'solicitudes' => $solicitudes,
            'tituloPagina' => $tituloPagina,
            'subtituloPagina' => $subtituloPagina
        ];
    }
}

/** Lógica de ruteo para el envío directo del formulario */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    SolicitudController::procesarGuardado();
}
