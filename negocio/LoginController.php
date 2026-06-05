<?php
/**
 * LoginController.php — Capa de Negocio
 * Procesa el formulario de login y crea la sesión.
 * Reemplaza php/login.php.
 */

require_once __DIR__ . '/../recursos/Auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (Auth::login($email, $password)) {
    header('Location: ../presentacion/vistas/dashboard.php');
} else {
    header('Location: ../index.php?error=credentials');
}
exit();
