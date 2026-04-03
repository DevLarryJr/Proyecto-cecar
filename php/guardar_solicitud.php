<?php
require_once 'conexion.php';
require_once 'Solicitud.php';

/**
 * Guarda la solicitud enviada desde el formulario.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    // 1. Mandatory General Fields
    $nombre = trim($_POST['nombre_solicitante'] ?? '');
    $dependencia = trim($_POST['dependencia'] ?? '');
    $fecha = trim($_POST['fecha_solicitud'] ?? '');
    $justificacion = trim($_POST['justificacion'] ?? '');
    $cargo = trim($_POST['cargo'] ?? ''); 
    $tipo = trim($_POST['tipo_solicitud'] ?? '');

    if (empty($nombre))
        $errors[] = "El nombre del solicitante es obligatorio.";
    if (empty($dependencia))
        $errors[] = "La dependencia es obligatoria.";
    if (empty($fecha))
        $errors[] = "La fecha de solicitud es obligatoria.";
    if (empty($justificacion))
        $errors[] = "La justificación es obligatoria.";
    if (empty($tipo))
        $errors[] = "Debes seleccionar un tipo de solicitud.";

    // 2. Services Table Validation
    $servicios = $_POST['servicios'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];
    $cc_nombres = $_POST['cc_nombres'] ?? [];
    $cc_codigos = $_POST['cc_codigos'] ?? [];
    $rubro_nombres = $_POST['rubro_nombres'] ?? [];
    $rubro_codigos = $_POST['rubro_codigos'] ?? [];
    $disponibilidades = $_POST['disponibilidades'] ?? [];
    $fondos = $_POST['fondos'] ?? [];
    $funcion_nombres = $_POST['funcion_nombres'] ?? [];
    $funcion_codigos = $_POST['funcion_codigos'] ?? [];

    $validRows = 0;
    foreach ($servicios as $i => $s) {
        $rowFields = [
            $servicios[$i] ?? '', $cantidades[$i] ?? '',
            $cc_nombres[$i] ?? '', $cc_codigos[$i] ?? '',
            $rubro_nombres[$i] ?? '', $rubro_codigos[$i] ?? '',
            $disponibilidades[$i] ?? '', $fondos[$i] ?? '',
            $funcion_nombres[$i] ?? '', $funcion_codigos[$i] ?? ''
        ];

        $isRowStarted = false;
        foreach ($rowFields as $val) {
            if (!empty(trim((string)$val))) {
                $isRowStarted = true;
                break;
            }
        }

        if ($isRowStarted) {
            $isRowComplete = true;
            foreach ($rowFields as $val) {
                if (empty(trim((string)$val))) {
                    $isRowComplete = false;
                    break;
                }
            }

            if (!$isRowComplete) {
                $errors[] = "La fila de servicio #" . ($i + 1) . " está incompleta. Todos los 10 campos son requeridos.";
            }
            else {
                $validRows++;
            }
        }
    }

    if ($validRows === 0) {
        $errors[] = "Debes completar al menos una fila de servicios íntegramente.";
    }

    // 3. Handle File Upload (Requirement 3)
    $uploadResult = Solicitud::uploadFile($_FILES['adjunto'] ?? null);
    if (!$uploadResult['success']) {
        $errors[] = $uploadResult['message'];
    }
    else {
        $archivo_nombre = $uploadResult['filename'];
    }

    // 4. Handle Errors or Proceed
    $isAjax = isset($_POST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

    if (!empty($errors)) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'errors' => $errors]);
            exit;
        }
        echo "<div style='font-family: sans-serif; max-width: 600px; margin: 40px auto; padding: 20px; border: 1px solid #fecaca; background: #fef2f2; border-radius: 12px; color: #991b1b;'>";
        echo "<h2 style='margin-top:0;'>⚠️ Errores de Validación</h2>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>" . htmlspecialchars($error) . "</li>";
        }
        echo "</ul>";
        echo "<br><a href='javascript:history.back()' style='display:inline-block; padding:10px 20px; background:#ef4444; color:white; text-decoration:none; border-radius:8px;'>Volver a corregir</a>";
        echo "</div>";
        exit;
    }

    // --- PROCEED WITH STORAGE LOGIC ---
    
    // Preparar datos para guardar
    $data = [
        'nombre' => $nombre,
        'dependencia' => $dependencia,
        'fecha' => $fecha,
        'tipo' => $tipo,
        'justificacion' => $justificacion,
        'cargo' => $cargo,
        'archivo' => $archivo_nombre ?? null,
        'servicios_count' => count($servicios)
    ];

    // Guardar en JSON (Requerimiento 5)
    Solicitud::saveToJson($data);

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Solicitud enviada correctamente']);
        exit;
    }

    echo "<!DOCTYPE html><html lang='es'><head><link href='https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css' rel='stylesheet'></head><body class='bg-gray-50 flex items-center justify-center min-h-screen'>";
    echo "<div class='bg-white p-10 rounded-3xl shadow-xl border border-gray-100 max-w-lg text-center'>";
    echo "<div class='w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6'><svg class='w-12 h-12' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path d='M5 13l4 4L19 7' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'/></svg></div>";
    echo "<h1 class='text-3xl font-bold text-gray-900 mb-2'>Solicitud Procesada</h1>";
    echo "<p class='text-gray-600 mb-8'>Gracias, <strong>" . htmlspecialchars($nombre) . "</strong>. Tu requerimiento ha sido validado y guardado correctamente en el sistema.</p>";
    echo "<a href='../dashboard.php' class='inline-block w-full py-4 bg-green-600 hover:bg-green-700 text-white font-bold rounded-2xl transition-all shadow-lg shadow-green-100'>Finalizar</a>";
    echo "</div></body></html>";
}
else {
    header("Location: ../solicitud.html");
}
?>
