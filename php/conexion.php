<?php
/**
 * Configuración de la conexión a la base de datos.
 * Ajuste los valores según su entorno local.
 */
$host = "localhost";
$user = "root";
$pass = "";
$db   = "proyecto_cecar";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En producción, no mostrar el error detallado
    // die("Error de conexión");
    error_log($e->getMessage());
}
?>
