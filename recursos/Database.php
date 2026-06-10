<?php
/**
 * Database.php — Capa de Recursos (Infraestructura)
 * 
 * Este archivo implementa el patrón Singleton para gestionar la conexión a la base de datos.
 * El objetivo es garantizar que toda la aplicación use una única instancia de PDO, ahorrando
 * recursos del servidor y evitando múltiples conexiones abiertas.
 * 
 * @author Antigravity (IA)
 * @project Proyecto CECAR - Gestión de Solicitudes
 */

class Database
{
    /** @var PDO|null Instancia única de la conexión */
    private static ?PDO $instance = null;

    /** 
     * Configuración de respaldo para entorno Local (XAMPP).
     * Estas variables se usan si NO se detectan variables de entorno del servidor.
     */
    private static string $host   = 'localhost';
    private static string $dbname = 'solicitud_final';
    private static string $user   = 'root';
    private static string $pass   = '';

    /**
     * Obtiene la conexión activa a la base de datos.
     * 
     * PRIORIDAD DE CONFIGURACIÓN:
     * 1. Variables de entorno (Railway): DB_HOST, DB_NAME, etc.
     * 2. Configuración estática (Local): Definida arriba.
     * 
     * @return PDO Objeto de conexión con atributos preconfigurados.
     * @throws PDOException Si la conexión falla, se captura y retorna un error JSON 500.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                // Intentar leer configuración del servidor (Producción)
                $host   = getenv('DB_HOST') ?: self::$host;
                $port   = getenv('DB_PORT') ?: '3306';
                $dbname = getenv('DB_NAME') ?: self::$dbname;
                $user   = getenv('DB_USER') ?: self::$user;

                // El password puede ser una cadena vacía en local, por eso validamos explícitamente
                $passwordFromEnv = getenv('DB_PASSWORD');
                $pass = ($passwordFromEnv !== false) ? $passwordFromEnv : self::$pass;

                // Creación de la instancia PDO con UTF-8 para evitar problemas de tildes
                self::$instance = new PDO(
                    "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        // Configuración de errores: lanza excepciones para ser capturadas
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        // El modo de obtención es asociativo (clave -> valor) por defecto
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        // Desactivar emulación de preparados para mayor seguridad SQL Injection
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                // Registro del error en el log del servidor y respuesta JSON controlada
                error_log('[Database Error] ' . $e->getMessage());
                http_response_code(500);
                die(json_encode([
                    'success' => false,
                    'error'   => 'Error crítico: No se pudo establecer conexión con el motor de base de datos.'
                ]));
            }
        }

        return self::$instance;
    }

    /** Constructor privado para impedir instanciación externa */
    private function __construct() {}
    /** Evitar clonación del objeto */
    private function __clone()    {}
}
