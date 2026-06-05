<?php
require_once __DIR__ . '/recursos/Auth.php';
Auth::redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gesti&oacute;n de Servicios - Iniciar Sesi&oacute;n</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#064c2b',
                        secondary: '#61a60e',
                        tertiary: '#c2d500',
                        quaternary: '#ffa400',
                        danger: '#e12d2e',
                        primaryDark: '#043c22',
                        secondaryDark: '#4f890b'
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="presentacion/img/logoIco.ico" type="image/x-icon">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="w-full max-w-md">
        
        <!-- CARD LOGIN -->
        <div class="bg-white rounded-2xl p-8 shadow-xl border border-gray-100 backdrop-blur-sm relative overflow-hidden">
            <!-- Detalle decorativo superior -->
            <div class="absolute top-0 left-0 w-full h-2 bg-primary"></div>
            
            <div class="text-center mb-8">
                <img src="presentacion/img/logo.png" alt="Logo CECAR" class="h-16 w-auto mx-auto mb-6">
                <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Gesti&oacute;n de Servicios</h1>
                
                <?php if (isset($_COOKIE['pref_nombre'])): ?>
                    <p class="text-secondary mt-2 font-medium">¡Bienvenido de nuevo,
                        <?php echo htmlspecialchars($_COOKIE['pref_nombre']); ?>!</p>
                <?php else: ?>
                    <p class="text-gray-500 mt-2">Ingresa tus credenciales institucional</p>
                <?php endif; ?>
            </div>

            <form action="negocio/LoginController.php" method="POST" class="space-y-6">
                <?php if (isset($_GET['registro']) && $_GET['registro'] === 'ok'): ?>
                    <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        &check; Cuenta creada exitosamente. Ya puedes iniciar sesión.
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 font-medium">
                        &excl; Credenciales incorrectas. Intenta de nuevo.
                    </div>
                <?php endif; ?>

                <!-- INPUTS -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Correo Electr&oacute;nico</label>
                    <input type="email" name="email" id="email" required
                        value="<?php echo $_COOKIE['pref_email'] ?? ''; ?>" placeholder="usuario@cecar.edu.co"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Contrase&ntilde;a</label>
                    <input type="password" name="password" id="password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all">
                </div>

                <!-- BOT&Oacute;N -->
                <button type="submit"
                    class="block w-full bg-primary hover:bg-primaryDark text-white font-semibold py-3 rounded-lg transition-colors shadow-lg shadow-primary/20">
                    Iniciar Sesi&oacute;n
                </button>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-500">
                        &iquest;No tienes cuenta? 
                        <a href="presentacion/vistas/registro.php" class="text-secondary font-bold hover:underline">Reg&iacute;strate aqu&iacute;</a>
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