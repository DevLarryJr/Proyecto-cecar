<?php
/**
 * Database.php — Capa de Recursos
 * Singleton PDO para la base de datos solicitud_final.
 * Toda la aplicación usa este único punto de conexión.
 */
class Database
{
    private static ?PDO $instance = null;

    // ── Configuración de conexión ─────────────────────────────
    private static string $host   = 'localhost';
    private static string $dbname = 'solicitud_final';
    private static string $user   = 'root';
    private static string $pass   = '';
    // ─────────────────────────────────────────────────────────

    /**
     * Devuelve la instancia única de PDO.
     * La crea la primera vez y la reutiliza en las siguientes.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                self::$instance = new PDO(
                    "mysql:host=" . self::$host .
                    ";dbname="    . self::$dbname .
                    ";charset=utf8mb4",
                    self::$user,
                    self::$pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]
                );
            } catch (PDOException $e) {
                error_log('[Database] Error de conexión: ' . $e->getMessage());
                http_response_code(500);
                die(json_encode([
                    'success' => false,
                    'error'   => 'No se pudo conectar a la base de datos.'
                ]));
            }
        }

        return self::$instance;
    }

    // Evitar instanciación y clonación directa (patrón Singleton)
    private function __construct() {}
    private function __clone()    {}
}
