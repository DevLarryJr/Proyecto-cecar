<?php
require_once __DIR__ . '/../../recursos/Auth.php';
require_once __DIR__ . '/../../capa_de_acceso/dao/SolicitudDAO.php';

Auth::redirectIfLoggedIn();

$dependencias = SolicitudDAO::obtenerDependencias();
$cargos = SolicitudDAO::obtenerCargos();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Proyecto CECAR</title>
    <?php 
    require_once __DIR__ . '/../../recursos/ViewHelper.php';
    ViewHelper::renderTailwindConfig(); 
    ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100 relative overflow-hidden">
            <!-- Detalle decorativo superior -->
            <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
            
            <div class="text-center mb-10">
                <img src="../img/logo.png" alt="Logo CECAR" class="h-16 w-auto mx-auto mb-6">
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Crear Cuenta Institucional</h1>
                <p class="text-gray-500 mt-2">Completa los datos para unirte al sistema de gestión de servicios</p>
            </div>

            <form action="../../negocio/RegistroController.php" method="POST" class="space-y-6">
                <?php if (isset($_GET['error'])): ?>
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 font-medium italic">
                        &excl; <?php 
                        $msg = "Ocurrió un error al registrar el usuario.";
                        if($_GET['error'] === 'email_exists') $msg = "El correo electrónico ya está registrado.";
                        echo $msg;
                        ?>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Nombre</label>
                        <input type="text" name="nombre" required placeholder="Tu nombre"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Apellido</label>
                        <input type="text" name="apellido" required placeholder="Tu apellido"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Correo Electr&oacute;nico Institucional</label>
                    <input type="email" name="email" required placeholder="usuario@cecar.edu.co"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Dependencia</label>
                        <select name="id_dependencia" required class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all bg-white">
                            <option value="">Seleccione...</option>
                            <?php foreach($dependencias as $d): ?>
                                <option value="<?php echo $d['id']; ?>"><?php echo htmlspecialchars($d['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Cargo</label>
                        <select name="id_cargo" required class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all bg-white">
                            <option value="">Seleccione...</option>
                            <?php foreach($cargos as $c): ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Tel&eacute;fono</label>
                        <input type="text" name="telefono" placeholder="Opcional"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700">Contrase&ntilde;a</label>
                        <input type="password" name="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                            class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                    </div>
                </div>

                <button type="submit"
                    class="block w-full bg-primary hover:bg-primaryDark text-white font-semibold py-4 rounded-lg transition-colors shadow-lg shadow-primary/20 mt-4">
                    Registrarse
                </button>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-500">
                        &iquest;Ya tienes cuenta? 
                        <a href="../../index.php" class="text-secondary font-bold hover:underline">Inicia sesi&oacute;n aqu&iacute;</a>
                    </p>
                </div>
            </form>
        </div>
        <p class="mt-8 text-center text-xs text-gray-400">
            &copy; <?php echo date('Y'); ?> Corporaci&oacute;n Universitaria del Caribe - CECAR
        </p>
    </div>

</body>

</html>
