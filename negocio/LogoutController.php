<?php
/**
 * LogoutController.php — Capa de Negocio
 * Cierra la sesión del usuario.
 */
require_once __DIR__ . '/../recursos/Auth.php';
Auth::logout();
