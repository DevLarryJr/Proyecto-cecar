<?php
/**
 * SolicitudController.php — Capa de Negocio
 * Orquesta el proceso de guardar una nueva solicitud.
 * Reemplaza php/guardar_solicitud.php.
 *
 * Flujo:
 *  1. Validar sesión
 *  2. Validar campos POST
 *  3. Subir archivo PDF
 *  4. Guardar en MySQL vía SolicitudDAO
 *  5. Responder (JSON para AJAX | HTML para formulario normal)
 */

require_once __DIR__ . '/../recursos/Auth.php';
require_once __DIR__ . '/../recursos/FileUploader.php';
require_once __DIR__ . '/../capa_de_acceso/dao/SolicitudDAO.php';

try {
    // ── 1. Verificar sesión ───────────────────────────────────────
    Auth::requireLogin();

    // ── Solo aceptar POST ────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../presentacion/vistas/solicitud.php');
        exit();
    }

    $isAjax = isset($_POST['ajax'])
        || (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

    $errors = [];

    // ── 2. Validar campos generales ───────────────────────────────
    $fecha = trim($_POST['fecha_solicitud'] ?? '');
    $justificacion = trim($_POST['justificacion'] ?? '');

    if (empty($fecha)) {
        $errors[] = 'La fecha de solicitud es obligatoria.';
    } elseif (strtotime($fecha) > strtotime(date('Y-m-d'))) {
        $errors[] = 'La fecha de solicitud no puede ser futura.';
    }

    if (empty($justificacion)) {
        $errors[] = 'La justificación es obligatoria.';
    } elseif (strlen($justificacion) < 10) {
        $errors[] = 'La justificación debe tener al menos 10 caracteres.';
    }

    // NUEVOS CAMPOS GENERALES
    $dependencia = trim($_POST['dependencia'] ?? '');
    $tipo_solicitud = trim($_POST['tipo_solicitud'] ?? '');
    $nombre_solicitante = trim($_POST['nombre_solicitante'] ?? '');
    $cargo = trim($_POST['cargo'] ?? '');

    // ── 3. Validar tabla de servicios ─────────────────────────────
    // Datos dinámicos de los ítems (arrays)
    $servicios        = $_POST['servicio']               ?? [];
    $cantidades       = $_POST['cantidad']               ?? [];
    $rubro_ids        = $_POST['rubro_id']               ?? [];
    $rubro_nombres    = $_POST['rubro']                  ?? [];
    $rubro_codigos    = $_POST['rubro_codigo']           ?? [];
    $disponibilidades = $_POST['disponibilidad']         ?? [];

    // v4: Campos de presupuesto ahora vienen en arrays por ítem
    $cc_ids           = $_POST['centro_costo_id']        ?? [];
    $cc_nombres       = $_POST['centro_costos']           ?? [];
    $cc_codigos       = $_POST['cc_codigo']               ?? [];
    $fondo_ids        = $_POST['fondos_id']               ?? [];
    $fondo_nombres    = $_POST['fondo']                   ?? [];
    $func_ids         = $_POST['funcion_id']              ?? [];
    $func_nombres     = $_POST['funcion_nombre']          ?? [];
    $func_cods        = $_POST['funcion_codigo']          ?? [];

    $itemsValidos = [];

    foreach ($servicios as $i => $srv) {
        if (empty(trim((string)$srv)) && empty(trim((string)($cantidades[$i] ?? '')))) {
            continue;
        }

        $itemsValidos[] = [
            'servicio'           => trim((string)$srv),
            'cantidad'           => trim((string)($cantidades[$i] ?? 0)),
            'rubro_id'           => (int)($rubro_ids[$i] ?? 0),
            'rubro'              => trim((string)($rubro_nombres[$i] ?? '')),
            'rubro_codigo'       => trim((string)($rubro_codigos[$i] ?? '')),
            'disponibilidad'     => trim((string)($disponibilidades[$i] ?? 0)),
            
            // v4: Datos por ítem
            'centro_costo_id'    => (int)($cc_ids[$i] ?? 0),
            'centro_costos'      => trim((string)($cc_nombres[$i] ?? '')),
            'cc_codigo'          => trim((string)($cc_codigos[$i] ?? '')),
            
            'fondos_id'          => (int)($fondo_ids[$i] ?? 0),
            'fondo'              => trim((string)($fondo_nombres[$i] ?? '')),
            
            'funcion_id'         => (int)($func_ids[$i] ?? 0),
            'funcion'            => trim((string)($func_nombres[$i] ?? '')),
            'funcion_codigo'     => trim((string)($func_cods[$i] ?? ''))
        ];
    }

    if (empty($itemsValidos)) {
        $errors[] = 'Debes completar al menos una fila de servicios íntegramente.';
    }

    // ── 4. Subir archivo PDF ──────────────────────────────────────
    $uploadResult = FileUploader::upload($_FILES['adjunto'] ?? null);
    if (!$uploadResult['success']) {
        $errors[] = $uploadResult['message'];
    }

    // ── 5. Responder con errores si los hay ──────────────────────
    if (!empty($errors)) {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit();
        }

        // Respuesta HTML de error (formulario normal)
        echo "<!DOCTYPE html><html lang='es'><head>
            <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
            </head><body class='bg-gray-50 flex items-center justify-center min-h-screen'>";
        echo "<div class='bg-white p-10 rounded-3xl shadow-xl max-w-lg text-center'>";
        echo "<h2 class='text-2xl font-bold text-red-600 mb-4'>Errores de Validación</h2><ul class='text-left mb-6'>";
        foreach ($errors as $e) {
            echo "<li class='text-red-700 mb-1'>• " . htmlspecialchars($e) . "</li>";
        }
        echo "</ul><a href='javascript:history.back()' 
                  class='px-6 py-3 bg-red-500 text-white rounded-xl font-bold'>
                  Volver a corregir</a></div></body></html>";
        exit();
    }

    // ── 6. Guardar o Actualizar en MySQL ─────────────────────────
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
        // Es una ACTUALIZACIÓN
        $exito = SolicitudDAO::actualizarCompleta(
            $solicitudId,
            $data,
            $itemsValidos
        );
        $nuevoId = $exito ? $solicitudId : false;
    } else {
        // Es una CREACIÓN
        $nuevoId = SolicitudDAO::guardar(
            $data,
            $itemsValidos,
            Auth::userId(),
            $uploadResult['filename'] ? $uploadResult : null
        );
    }

    if ($nuevoId === false) {
        throw new Exception('Error al guardar la solicitud en la base de datos.');
    }

    // ── 7. Respuesta de éxito ─────────────────────────────────────
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'message' => 'Solicitud enviada correctamente',
            'id' => $nuevoId,
        ]);
        exit();
    }

    // HTML de éxito (formulario normal)
    echo "<!DOCTYPE html><html lang='es'><head>
        <link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'>
        </head><body class='bg-gray-50 flex items-center justify-center min-h-screen'>";
    echo "<div class='bg-white p-10 rounded-3xl shadow-xl border border-gray-100 max-w-lg text-center'>";
    echo "<div class='w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6'>
            <svg class='w-12 h-12' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
              <path d='M5 13l4 4L19 7' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/>
            </svg></div>";
    echo "<h1 class='text-3xl font-bold text-gray-900 mb-2'>Solicitud Procesada</h1>";
    echo "<p class='text-gray-600 mb-8'>Tu requerimiento ha sido guardado correctamente en el sistema.</p>";
    echo "<a href='../presentacion/vistas/detalle.php?id=$nuevoId'
             class='inline-block w-full py-4 bg-green-600 hover:bg-green-700 
                    text-white font-bold rounded-2xl transition-all'>
             Ver mi solicitud</a>";
    echo "</div></body></html>";

} catch (Exception $e) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'errors' => [$e->getMessage()]
        ]);
        exit();
    }
    die("Error crítico: " . $e->getMessage());
}

