<?php
/**
 * Auth.php — Capa de Recursos (Seguridad)
 * 
 * Gestiona el ciclo de vida del usuario: Inicio de sesión, control de acceso por roles,
 * y persistencia de datos en $_SESSION. Es el guardián de las rutas protegidas.
 */

require_once __DIR__ . '/Database.php';

class Auth
{
    /**
     * Inicializa la sesión PHP con parámetros de seguridad modernos.
     * Se asegura de que la cookie de sesión sea inaccesible vía JS (HttpOnly)
     * y que use SameSite=Lax para prevenir ataques CSRF.
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Detectar si el tráfico es HTTPS (necesario para cookies seguras en Railway)
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            session_set_cookie_params([
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => $isHttps, // Solo se envía por HTTPS si está disponible
            ]);
            session_start();
        }
    }

    /**
     * Calcula la URL absoluta de la raíz del proyecto.
     * Útil para redirecciones que funcionen tanto en Localhost como en Servidor Real.
     */
    public static function baseUrl(): string
    {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || 
                     (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) 
                     ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        
        $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
        $projectDir = str_replace('\\', '/', dirname(__DIR__));
        
        $baseFolder = '';
        if (strpos($projectDir, $docRoot) === 0) {
            $baseFolder = str_replace($docRoot, '', $projectDir);
        }
        
        return rtrim($protocol . $host . $baseFolder, '/') . '/';
    }

    /** Protege una vista redirigiendo al index.php si no hay sesión. */
    public static function requireLogin(): void
    {
        self::init();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . self::baseUrl() . 'index.php');
            exit();
        }
    }

    /** Evita que un usuario ya logueado vea la pantalla de Login. */
    public static function redirectIfLoggedIn(): void
    {
        self::init();
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ' . self::baseUrl() . 'presentacion/vistas/dashboard.php');
            exit();
        }
    }

    /**
     * Valida email y password contra la tabla `usuarios`.
     * Realiza un JOIN con roles, dependencias y cargos para obtener el perfil completo.
     * 
     * @param string $email Email ingresado.
     * @param string $password Contraseña sin cifrar.
     * @return bool True si las credenciales son correctas.
     */
    public static function login(string $email, string $password): bool
    {
        self::init();
        $pdo = Database::getConnection();

        // Consulta preparada para evitar Inyecciones SQL
        $stmt = $pdo->prepare("
            SELECT u.id, u.nombre, u.apellido, u.email, u.password_hash, u.activo,
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

        // Validar: Existe usuario -> Está activo -> Password coincide (hash)
        if (!$user || !$user['activo']) return false;
        if (!password_verify($password, $user['password_hash'])) return false;

        // Inyectar datos en la Superglobal $_SESSION
        $_SESSION['usuario_id']  = $user['id'];
        $_SESSION['usuario']     = $user['nombre'] . ' ' . $user['apellido'];
        $_SESSION['email']       = $user['email'];
        $_SESSION['rol']         = $user['rol'] ?? 'Usuario';
        $_SESSION['dependencia'] = $user['dependencia'] ?? '';
        $_SESSION['cargo']       = $user['cargo'] ?? '';

        return true;
    }

    /** Destruye la sesión y limpia cookies del navegador. */
    public static function logout(): void
    {
        self::init();
        session_unset();
        session_destroy();
        header('Location: ' . self::rootPath() . 'index.php');
        exit();
    }

    /** @return int ID del usuario o 0 si no hay sesión */
    public static function userId(): int
    {
        self::init();
        return (int) ($_SESSION['usuario_id'] ?? 0);
    }

    /** @return string Nombre completo guardado en sesión */
    public static function userName(): string
    {
        self::init();
        return $_SESSION['usuario'] ?? 'Invitado';
    }

    /** @return string Rol del usuario (ej. Administrador, Usuario) */
    public static function userRol(): string
    {
        self::init();
        return $_SESSION['rol'] ?? 'usuario';
    }

    /** @return string Nombre de la dependencia asignada */
    public static function userDependencia(): string
    {
        self::init();
        return $_SESSION['dependencia'] ?? '';
    }

    /** @return string Cargo registrado del usuario */
    public static function userCargo(): string
    {
        self::init();
        return $_SESSION['cargo'] ?? '';
    }

    /**
     * Verifica si el usuario actual tiene rol de Administrador.
     * @return bool True si es administrador.
     */
    public static function isAdmin(): bool
    {
        self::init();
        $rol = strtolower(self::userRol());
        return ($rol === 'administrador' || $rol === 'admin');
    }

    /** Calcula la ruta de retroceso (../../) dinámicamente según la profundidad de la carpeta. */
    private static function rootPath(): string
    {
        $recursosDir = str_replace('\\', '/', realpath(__DIR__));
        $projectRoot = str_replace('\\', '/', dirname($recursosDir));
        $scriptDir   = str_replace('\\', '/', dirname(realpath($_SERVER['SCRIPT_FILENAME'])));
        
        if ($scriptDir === $projectRoot) return '';
        
        $relativeDirs = trim(str_replace($projectRoot, '', $scriptDir), '/');
        if (empty($relativeDirs)) return '';

        $levels = count(explode('/', $relativeDirs));
        return str_repeat('../', $levels);
    }
}
