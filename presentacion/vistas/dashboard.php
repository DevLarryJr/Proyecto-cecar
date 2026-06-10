<?php
require_once __DIR__ . '/../../recursos/Auth.php';
Auth::requireLogin();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Selección - CECAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="../css/estilos.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <img src="../img/logo.png" alt="Logo CECAR" class="h-10 w-auto">
                </div>
                <div class="flex items-center">
                    <a href="../../negocio/LogoutController.php" class="text-sm font-medium text-red-600 hover:text-red-800 mr-4">Cerrar Sesión</a>
                    <div class="flex items-center bg-brand-bg rounded-full px-4 py-1.5 border border-brand-soft italic font-medium text-brand-main shadow-sm">
                        <span class="mr-1 opacity-60 font-normal not-italic text-xs uppercase tracking-tighter">Hola,</span>
                        <?php echo Auth::userName(); ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-12 text-center">
            <h1 class="text-5xl font-extrabold text-gray-900 tracking-tight mb-4">¿Qué deseas hacer hoy?</h1>
            <p class="text-xl text-gray-500 max-w-2xl mx-auto font-light">Gestiona tus procesos de forma rápida y eficiente.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?php echo Auth::isAdmin() ? '4' : '2'; ?> gap-8 max-w-<?php echo Auth::isAdmin() ? '7xl' : '4xl'; ?> mx-auto">
            
            <a href="solicitud.php" class="group bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 hover:shadow-2xl transition-all transform hover:-translate-y-2">
                <div class="w-16 h-16 bg-brand-soft text-brand-main rounded-2xl flex items-center justify-center mb-6 group-hover:bg-gray-900 group-hover:text-white transition-all duration-500 shadow-sm rotate-3 group-hover:rotate-0">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Crear Solicitud</h2>
                <p class="text-gray-500 text-sm">Inicia un nuevo requerimiento de servicios.</p>
            </a>

            <a href="solicitudes.php" class="group bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 hover:shadow-2xl transition-all transform hover:-translate-y-2">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-sm -rotate-3 group-hover:rotate-0">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Mis Solicitudes</h2>
                <p class="text-gray-500 text-sm">Consulta el estado actual de tus trámites.</p>
            </a>

            <?php if (Auth::isAdmin()): ?>
                <a href="revision.php" class="group bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 hover:shadow-2xl transition-all transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-amber-600 group-hover:text-white transition-all duration-500 shadow-sm rotate-3 group-hover:rotate-0">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">Panel Revisor</h2>
                    <p class="text-gray-500 text-sm">Gestiona y aprueba las solicitudes registradas.</p>
                </a>

                <a href="dashboard_admin.php" class="group bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 hover:shadow-2xl transition-all transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 shadow-sm -rotate-3 group-hover:rotate-0">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" stroke-width="2"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">Estadísticas</h2>
                    <p class="text-gray-500 text-sm">Analiza métricas y genera reportes detallados.</p>
                </a>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>