<?php
require_once 'Auth.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (Auth::login($email, $password)) {
        if (isset($_POST['remember'])) {
            // Guardar por 30 días
            setcookie('pref_email', $email, time() + (30 * 24 * 60 * 60), "/");
            setcookie('pref_nombre', $_SESSION['usuario'], time() + (30 * 24 * 60 * 60), "/");
        }
        header("Location: ../dashboard.php");
        exit();
    } else {
        header("Location: ../index.php?error=1");
        exit();
    }
}
?>
