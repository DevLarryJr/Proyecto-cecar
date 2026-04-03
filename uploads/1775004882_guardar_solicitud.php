<?php
require_once 'conexion.php';

/**
 * Guarda la solicitud enviada desde el formulario.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre_solicitante'] ?? '';
    $dependencia = $_POST['dependencia'] ?? '';
    $fecha = $_POST['fecha_solicitud'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $justificacion = $_POST['justificacion'] ?? '';

    // Procesar servicios (arrays)
    $servicios = $_POST['servicios'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];

    // Aquí iría la lógica de inserción en la base de datos usando $pdo
    // Por ahora, simulamos éxito y mostramos resumen

    echo "<h1>Solicitud Recibida Correctamente</h1>";
    echo "<p>Gracias, <strong>$nombre</strong>. Tu solicitud para <strong>$dependencia</strong> ha sido procesada.</p>";
    
    echo "<h2>Resumen de Servicios:</h2>";
    echo "<ul>";
    foreach ($servicios as $index => $serv) {
        if (!empty($serv)) {
            echo "<li>$serv (Cantidad: " . ($cantidades[$index] ?? 0) . ")</li>";
        }
    }
    echo "</ul>";

    if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] === UPLOAD_ERR_OK) {
        echo "<p>Archivo adjunto recibido: " . htmlspecialchars($_FILES['adjunto']['name']) . "</p>";
    }

    echo '<br><a href="../dashboard.html" style="display:inline-block; padding:10px 20px; background:#10b981; color:white; text-decoration:none; border-radius:8px;">Volver al Dashboard</a>';
} else {
    header("Location: ../solicitud.html");
}
?>
