<?php
/**
 * dashboard_chart_data.php — Capa de Negocio (AJAX Endpoint)
 * Devuelve datos filtrados para el gráfico de dona del dashboard.
 * Acepta: estado, fecha_inicio, fecha_fin, dependencia_id
 */
require_once __DIR__ . '/../recursos/Auth.php';
require_once __DIR__ . '/../recursos/Database.php';

ob_start();

Auth::requireLogin();
if (!Auth::isAdmin()) {
    ob_clean();
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Forbidden']);
    exit();
}

header('Content-Type: application/json');

$estado        = trim($_GET['estado']        ?? 'todos');
$fechaInicio   = trim($_GET['fecha_inicio']  ?? '');
$fechaFin      = trim($_GET['fecha_fin']     ?? '');
$dependenciaId = (int) ($_GET['dependencia_id'] ?? 0);

try {
    $pdo = Database::getConnection();
    $params = [];
    $where  = ['s.activo = 1'];

    if ($estado !== 'todos' && !empty($estado)) {
        $where[]  = 'e.nombre = ?';
        $params[] = $estado;
    }
    if (!empty($fechaInicio)) {
        $where[]  = 's.fecha_solicitud >= ?';
        $params[] = $fechaInicio;
    }
    if (!empty($fechaFin)) {
        $where[]  = 's.fecha_solicitud <= ?';
        $params[] = $fechaFin;
    }
    if ($dependenciaId > 0) {
        $where[]  = 'u.id_dependecia = ?';
        $params[] = $dependenciaId;
    }

    $whereClause = 'WHERE ' . implode(' AND ', $where);

    $sql = "
        SELECT e.nombre AS estado, COUNT(s.id) AS total
        FROM solicitudes s
        JOIN estados_solicitud e ON e.id = s.estado_id
        JOIN usuarios u ON u.id = s.usuario_id
        $whereClause
        GROUP BY e.id, e.nombre
        ORDER BY total DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ob_clean();
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
