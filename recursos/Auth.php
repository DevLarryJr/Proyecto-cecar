<?php
/**
 * Auth.php — Capa de Recursos
 * Gestión de sesiones y autenticación contra la BD MySQL.
 * Reemplaza php/Auth.php (que usaba credenciales estáticas).
 */

require_once __DIR__ . '/Database.php';

class Auth
{
    // ── Sesión ────────────────────────────────────────────────

    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Detectar HTTPS real: Railway usa proxy inverso con X-Forwarded-Proto
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            // 'secure' => true es obligatorio en HTTPS para que la cookie se envíe correctamente
            session_set_cookie_params([
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => $isHttps,
            ]);
            session_start();
        }
    }

    /**
     * Devuelve la URL base absoluta de la aplicación.
     * Ejemplo: https://dominio.com/ o http://localhost/proyecto-cecar/
     */
    public static function baseUrl(): string
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || 
                     (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                     ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        
        // Encontrar la ruta base usando el SCRIPT_NAME de index.php si estamos en él,
        // o determinándola según document root.
        $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        $projectDir = str_replace('\\', '/', dirname(__DIR__));
        
        $baseFolder = '';
        if (strpos($projectDir, $docRoot) === 0) {
            $baseFolder = str_replace($docRoot, '', $projectDir);
        }
        
        return rtrim($protocol . $host . $baseFolder, '/') . '/';
    }

    /** Redirige al login si el usuario no ha iniciado sesión. */
    public static function requireLogin(): void
    {
        self::init();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . self::baseUrl() . 'index.php');
            exit();
        }
    }

    /** Redirige al dashboard si ya hay sesión activa. */
    public static function redirectIfLoggedIn(): void
    {
        self::init();
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ' . self::baseUrl() . 'presentacion/vistas/dashboard.php');
            exit();
        }
    }

    // ── Login / Logout ────────────────────────────────────────

    /**
     * Verifica credenciales contra la tabla `usuarios`.
     * Guarda datos del usuario en sesión si es correcto.
     *
     * @return bool True si el login fue exitoso.
     */
    public static function login(string $email, string $password): bool
    {
        self::init();

        $pdo = Database::getConnection();

        // v4: El esquema finalmente usa email y password_hash con tabla de roles separada
        $stmt = $pdo->prepare("
            SELECT u.id,
                   u.nombre,
                   u.apellido,
                   u.email,
                   u.password_hash,
                   u.activo,
                   r.nombre AS rol,
                   d.nombre AS dependencia,
                   c.nombre_cargo AS cargo
            FROM   usuarios u
            LEFT JOIN usuario_rol ur ON ur.usuario_id = u.id
            LEFT JOIN roles r        ON r.id = ur.rol_id
            LEFT JOIN dependencias d ON d.id = u.id_dependecia
            LEFT JOIN cargo c        ON c.idcargo = u.id_cargo
            WHERE  u.email = ?
            LIMIT  1
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Verificamos existencia, contraseña y que esté activo
        if (!$user || !$user['activo']) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        // Guardar datos en sesión
        $_SESSION['usuario_id']  = $user['id'];
        $_SESSION['usuario']     = $user['nombre'] . ' ' . $user['apellido'];
        $_SESSION['email']       = $user['email'];
        $_SESSION['rol']         = $user['rol'] ?? 'Usuario';
        $_SESSION['dependencia'] = $user['dependencia'] ?? '';
        $_SESSION['cargo']       = $user['cargo'] ?? '';

        return true;
    }

    /** Cierra la sesión y redirige al login. */
    public static function logout(): void
    {
        self::init();
        session_unset();
        session_destroy();
        header('Location: ' . self::rootPath() . 'index.php');
        exit();
    }

    // ── Helpers de sesión ─────────────────────────────────────

    /** Devuelve el ID del usuario en sesión. */
    public static function userId(): int
    {
        self::init();
        return (int) ($_SESSION['usuario_id'] ?? 0);
    }

    /** Devuelve el nombre del usuario en sesión. */
    public static function userName(): string
    {
        self::init();
        return $_SESSION['usuario'] ?? 'Invitado';
    }

    /** Devuelve el rol del usuario en sesión. */
    public static function userRol(): string
    {
        self::init();
        return $_SESSION['rol'] ?? 'usuario';
    }

    /** Devuelve la dependencia del usuario en sesión. */
    public static function userDependencia(): string
    {
        self::init();
        return $_SESSION['dependencia'] ?? '';
    }

    /** Devuelve el cargo del usuario en sesión. */
    public static function userCargo(): string
    {
        self::init();
        return $_SESSION['cargo'] ?? '';
    }

    /** Comprueba si el usuario actual es Administrador. */
    public static function isAdmin(): bool
    {
        return strtolower(self::userRol()) === 'administrador';
    }

    // ── Utilidad ──────────────────────────────────────────────

    /**
     * Devuelve la ruta raíz relativa desde cualquier carpeta.
     * Basado en la ubicación física real de los archivos.
     */
    private static function rootPath(): string
    {
        $recursosDir = str_replace('\\', '/', realpath(__DIR__));
        $projectRoot = str_replace('\\', '/', dirname($recursosDir));
        $scriptDir   = str_replace('\\', '/', dirname(realpath($_SERVER['SCRIPT_FILENAME'])));
        
        $logStr = "[DEBUG PATHS] Root: $projectRoot | ScriptDir: $scriptDir";
        error_log($logStr);

        if ($scriptDir === $projectRoot) {
            return '';
        }
        
        $relativeDirs = str_replace($projectRoot, '', $scriptDir);
        $relativeDirs = trim($relativeDirs, '/');
        
        if (empty($relativeDirs)) {
            return '';
        }

        $levels = count(explode('/', $relativeDirs));
        $result = str_repeat('../', $levels);
        
        error_log("[DEBUG RESULT] Relative: $relativeDirs | Levels: $levels | Final: $result");
        return $result;
    }
}
