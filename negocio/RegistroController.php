<?php
/**
 * RegistroController.php — Capa de Negocio
 * Procesa el registro de nuevos usuarios.
 */

require_once __DIR__ . '/../recursos/Database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../presentacion/vistas/registro.php');
    exit();
}

$nombre    = trim($_POST['nombre'] ?? '');
$apellido  = trim($_POST['apellido'] ?? '');
$email     = trim($_POST['email'] ?? '');
$id_dep    = (int)($_POST['id_dependencia'] ?? 0);
$id_cargo  = (int)($_POST['id_cargo'] ?? 0);
$telefono  = trim($_POST['telefono'] ?? '');
$password  = $_POST['password'] ?? '';

if (empty($nombre) || empty($email) || empty($password)) {
    header('Location: ../presentacion/vistas/registro.php?error=empty_fields');
    exit();
}

$pdo = Database::getConnection();

try {
    // 1. Verificar si el email ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header('Location: ../presentacion/vistas/registro.php?error=email_exists');
        exit();
    }

    $pdo->beginTransaction();

    // 2. Insertar usuario
    $passHash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, apellido, email, password_hash, id_dependecia, id_cargo, telefono, activo)
        VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([$nombre, $apellido, $email, $passHash, $id_dep, $id_cargo, $telefono]);
    $userId = (int)$pdo->lastInsertId();

    // 3. Asignar rol de 'Usuario' (ID 2)
    $stmtRol = $pdo->prepare("INSERT INTO usuario_rol (usuario_id, rol_id) VALUES (?, 2)");
    $stmtRol->execute([$userId]);

    $pdo->commit();

    // Redirigir al login con éxito
    header('Location: ../index.php?registro=ok');

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[RegistroController] ' . $e->getMessage());
    header('Location: ../presentacion/vistas/registro.php?error=db');
}
exit();
