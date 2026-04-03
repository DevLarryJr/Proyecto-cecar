<?php
require_once 'php/Auth.php';
Auth::redirectIfLoggedIn();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Proyecto Premium</title>
    <link href="css/output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gradient-to-br from-brand-bg to-brand-soft min-h-screen flex items-center justify-center p-4">

    <!-- CONTENEDOR -->
    <div class="w-full max-w-md">

        <!-- CARD LOGIN -->
        <div class="bg-white/80 backdrop-blur-lg shadow-2xl rounded-3xl p-8 md:p-12 border border-white/20">
            <div class="text-center mb-10">
                <img src="img/logo.png" alt="Logo CECAR" class="h-20 w-auto mx-auto mb-6">
                <h1 class="text-3xl font-extrabold text-brand-dark tracking-tight">Gestión de Servicios</h1>
                
                <?php if (isset($_COOKIE['pref_nombre'])): ?>
                    <p class="text-brand-main mt-2 font-bold animate-bounce">¡Bienvenido de nuevo, <?php echo htmlspecialchars($_COOKIE['pref_nombre']); ?>!</p>
                <?php else: ?>
                    <p class="text-gray-500 mt-2">Bienvenido de nuevo</p>
                <?php endif; ?>
            </div>

            <form action="php/login.php" method="POST" class="space-y-6">
                <?php if (isset($_GET['error'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm font-medium animate-shake">
                        ⚠️ Credenciales incorrectas. Intenta de nuevo.
                    </div>
                <?php endif; ?>

                <!-- INPUTS -->
                <div>
                    <label class="block text-sm font-medium text-brand-gray mb-1">Correo Electrónico</label>
                    <input type="email" name="email" id="email" required 
                           value="<?php echo $_COOKIE['pref_email'] ?? ''; ?>"
                           placeholder="ejemplo@correo.com" 
                           class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-brand-main focus:ring-2 focus:ring-brand-soft outline-none transition-all duration-200">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-brand-gray mb-1">Contraseña</label>
                    <input type="password" name="password" id="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-brand-main focus:ring-2 focus:ring-brand-soft outline-none transition-all duration-200">
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center text-brand-gray">
                        <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300 text-brand-main focus:ring-brand-main">
                        Recordarme
                    </label>
                    <a href="#" class="text-brand-main font-medium hover:underline text-xs">¿Olvidaste tu contraseña?</a>
                </div>

                <!-- BOTÓN -->
                <button type="submit" class="block w-full text-center bg-brand-main hover:bg-brand-hover text-white font-semibold py-4 rounded-xl shadow-lg shadow-brand-soft transition-all duration-200 transform hover:-translate-y-1 active:scale-95">
                    Ingresar
                </button>
            </form>

            <div class="mt-8 text-center text-sm text-brand-gray">
                ¿No tienes una cuenta? <a href="#" class="text-brand-main font-bold hover:underline">Regístrate</a>
            </div>
        </div>

    </div>

</body>

</html>