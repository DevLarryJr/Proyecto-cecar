<?php
/**
 * Clase para gestionar la autenticación y sesiones del sistema.
 */
class Auth {
    /**
     * Inicia la sesión de forma segura.
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Verifica si el usuario ha iniciado sesión.
     * Si no, lo redirige al login.
     */
    public static function requireLogin() {
        self::init();
        if (!isset($_SESSION['usuario'])) {
            header("Location: index.php");
            exit();
        }
    }

    /**
     * Redirige al dashboard si el usuario ya está autenticado.
     * Útil para la página de login.
     */
    public static function redirectIfLoggedIn() {
        self::init();
        if (isset($_SESSION['usuario'])) {
            header("Location: dashboard.php");
            exit();
        }
    }

    /**
     * Autentica un usuario.
     */
    public static function login($email, $password) {
        self::init();
        // Credenciales quemadas para el parcial
        $email_valido = "admin@cecar.edu.co";
        $password_valido = "123456";

        if ($email === $email_valido && $password === $password_valido) {
            $_SESSION['usuario'] = "Administrador";
            $_SESSION['email'] = $email;
            return true;
        }
        return false;
    }

    /**
     * Cierra la sesión.
     */
    public static function logout() {
        self::init();
        session_unset();
        session_destroy();
        header("Location: ../index.php");
        exit();
    }
}
?>
