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
    <?php ViewHelper::renderTailwindConfig(); ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-0">
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
        <div class="mb-12 text-center animate-card delay-100">
            <h1 class="text-5xl font-extrabold text-gray-900 tracking-tight mb-4 tracking-tighter">¿Qué deseas hacer hoy?</h1>
            <p class="text-xl text-gray-500 max-w-2xl mx-auto font-light">Gestiona tus procesos de forma rápida y eficiente.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?php echo Auth::isAdmin() ? '4' : '2'; ?> gap-8 max-w-<?php echo Auth::isAdmin() ? '7xl' : '4xl'; ?> mx-auto">
            
            <!-- Crear Solicitud: verde, ícono se mantiene en color de marca en hover -->
            <a href="solicitud.php" class="animate-card group bg-white p-8 rounded-[2rem] shadow-lg shadow-gray-300/60 border border-gray-200 hover:shadow-2xl hover:shadow-brand-main/30 hover:border-brand-main/30 hover:-translate-y-3 transition-all duration-300 delay-200">
                <div class="w-16 h-16 bg-green-50 text-brand-main rounded-2xl flex items-center justify-center mb-6 group-hover:bg-green-100 transition-all duration-300 shadow-md shadow-green-200/50 rotate-3 group-hover:rotate-0">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2.5" stroke-linecap="round"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3 group-hover:text-brand-main transition-colors duration-300">Crear Solicitud</h2>
                <p class="text-gray-500 text-sm leading-relaxed">Inicia un nuevo requerimiento de servicios institucionales.</p>
            </a>

            <!-- Mis Solicitudes: azul -->
            <a href="solicitudes.php" class="animate-card group bg-white p-8 rounded-[2rem] shadow-lg shadow-gray-300/60 border border-gray-200 hover:shadow-2xl hover:shadow-blue-500/30 hover:border-blue-200 hover:-translate-y-3 transition-all duration-300 delay-300">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all duration-300 shadow-md shadow-blue-200/50 -rotate-3 group-hover:rotate-0">
                    <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke-width="2"/></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3 group-hover:text-blue-600 transition-colors duration-300">Mis Solicitudes</h2>
                <p class="text-gray-500 text-sm leading-relaxed">Consulta el historial y estado de todos tus trámites.</p>
            </a>

            <?php if (Auth::isAdmin()): ?>
                <!-- Panel Revisor: ámbar -->
                <a href="revision.php" class="animate-card group bg-white p-8 rounded-[2rem] shadow-lg shadow-gray-300/60 border border-gray-200 hover:shadow-2xl hover:shadow-amber-500/30 hover:border-amber-200 hover:-translate-y-3 transition-all duration-300 delay-400">
                    <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300 shadow-md shadow-amber-200/50 rotate-3 group-hover:rotate-0">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3 group-hover:text-amber-600 transition-colors duration-300">Panel Revisor</h2>
                    <p class="text-gray-500 text-sm leading-relaxed">Gestiona, audita y aprueba solicitudes registradas.</p>
                </a>

                <!-- Estadísticas: índigo -->
                <a href="dashboard_admin.php" class="animate-card group bg-white p-8 rounded-[2rem] shadow-lg shadow-gray-300/60 border border-gray-200 hover:shadow-2xl hover:shadow-indigo-500/30 hover:border-indigo-200 hover:-translate-y-3 transition-all duration-300 delay-500">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-300 shadow-md shadow-indigo-200/50 -rotate-3 group-hover:rotate-0">
                        <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" stroke-width="2"/></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3 group-hover:text-indigo-600 transition-colors duration-300">Estadísticas</h2>
                    <p class="text-gray-500 text-sm leading-relaxed">Analiza métricas globales y genera reportes detallados.</p>
                </a>
            <?php endif; ?>

        </div>
    </main>
</body>
</html>