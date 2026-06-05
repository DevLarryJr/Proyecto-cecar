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
            // Asegurar que la cookie de sesión sea válida para todo el dominio
            session_set_cookie_params([
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start();
        }
    }

    /** Redirige al login si el usuario no ha iniciado sesión. */
    public static function requireLogin(): void
    {
        self::init();
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . self::rootPath() . 'index.php');
            exit();
        }
    }

    /** Redirige al dashboard si ya hay sesión activa. */
    public static function redirectIfLoggedIn(): void
    {
        self::init();
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ' . self::rootPath() . 'presentacion/vistas/dashboard.php');
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
     * Estructura esperada:
     * - raíz: index.php
     * - nivel 1: negocio/, recursos/
     * - nivel 2: presentacion/vistas/
     */
    private static function rootPath(): string
    {
        $scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
        
        if (strpos($scriptPath, '/presentacion/vistas/') !== false) {
            return '../../';
        }
        if (strpos($scriptPath, '/negocio/') !== false || strpos($scriptPath, '/recursos/') !== false) {
            return '../';
        }
        
        return '';
    }
}
