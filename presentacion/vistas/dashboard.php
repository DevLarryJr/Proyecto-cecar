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

    <!-- NAVIGATION -->
    <nav class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0 flex items-center">
                        <img src="../img/logo.png" alt="Logo CECAR" class="h-10 w-auto">
                    </div>
                </div>
                <div class="flex items-center">
                    <a href="../../negocio/LogoutController.php"
                        class="text-sm font-medium text-red-600 hover:text-red-800 mr-4">Cerrar Sesión</a>
                    <div
                        class="flex items-center bg-brand-bg rounded-full px-4 py-1.5 border border-brand-soft italic font-medium text-brand-main shadow-sm">
                        <span
                            class="mr-1 opacity-60 font-normal not-italic text-xs uppercase tracking-tighter">Hola,</span>
                        <?php echo Auth::userName(); ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- HEADER -->
        <div class="mb-12 text-center">
            <h1 class="text-5xl font-extrabold text-gray-900 tracking-tight mb-4">¿Qué deseas hacer hoy?</h1>
            <p class="text-xl text-gray-500 max-w-2xl mx-auto font-light">Gestiona tus procesos de forma rápida y
                eficiente desde tu panel centralizado.</p>
        </div>

        <!-- OPCIONES / GRID -->
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-<?php echo Auth::isAdmin() ? '4' : '2'; ?> gap-8 max-w-<?php echo Auth::isAdmin() ? '7xl' : '4xl'; ?> mx-auto">

            <!-- CARD 1: Crear Solicitud -->
            <a href="solicitud.php"
                class="group bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-brand-soft hover:border-brand-light transition-all duration-500 transform hover:-translate-y-2">
                <div class="w-16 h-16 bg-brand-soft text-brand-main rounded-2xl flex items-center justify-center mb-6 group-hover:bg-gray-900 group-hover:text-white transition-all duration-500 shadow-sm rotate-3 group-hover:rotate-0 group-hover:scale-110">
                    <svg class="w-9 h-9 transform group-hover:scale-110 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Crear Solicitud</h2>
                <p class="text-gray-500 leading-relaxed font-light text-sm">Inicia un nuevo requerimiento de servicios
                    de manera simplificada.</p>
                <div
                    class="mt-8 flex items-center text-primary font-bold text-sm tracking-wide uppercase group-hover:text-primaryDark">
                    Comenzar
                    <svg class="ml-2 w-5 h-5 opacity-100 transform group-hover:translate-x-2 transition-all duration-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M14 5l7 7-7 7M3 12h18" stroke-width="3" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
            </a>

            <!-- CARD 2: Mis Solicitudes -->
            <a href="solicitudes.php"
                class="group bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-blue-100 hover:border-blue-200 transition-all duration-500 transform hover:-translate-y-2">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 shadow-sm -rotate-3 group-hover:rotate-0 group-hover:scale-110">
                    <svg class="w-9 h-9 transform group-hover:scale-110 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800 mb-3">Mis Solicitudes</h2>
                <p class="text-gray-500 leading-relaxed font-light text-sm">Consulta el estado actual e historial de
                    todos tus trámites realizados.</p>
                <div
                    class="mt-8 flex items-center text-blue-600 font-bold text-sm tracking-wide uppercase group-hover:text-blue-800">
                    Ver historial
                    <svg class="ml-2 w-5 h-5 opacity-100 transform group-hover:translate-x-2 transition-all duration-300"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M14 5l7 7-7 7M3 12h18" stroke-width="3" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
            </a>

            <?php if (Auth::isAdmin()): ?>
                <!-- CARD 3: Panel Revisor (Admin) -->
                <a href="revision.php"
                    class="group bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-amber-100 hover:border-amber-200 transition-all duration-500 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-amber-600 group-hover:text-white transition-all duration-500 shadow-sm rotate-3 group-hover:rotate-0 group-hover:scale-110">
                        <svg class="w-9 h-9 transform group-hover:scale-110 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">Panel Revisor</h2>
                    <p class="text-gray-500 leading-relaxed font-light text-sm">Gestiona y aprueba las solicitudes
                        registradas por otros usuarios.</p>
                    <div
                        class="mt-8 flex items-center text-amber-600 font-bold text-sm tracking-wide uppercase group-hover:text-amber-800">
                        Revisar
                        <svg class="ml-2 w-5 h-5 opacity-100 transform group-hover:translate-x-2 transition-all duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M14 5l7 7-7 7M3 12h18" stroke-width="3" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                </a>

                <!-- CARD 4: Dashboard y Reportes (Admin) -->
                <a href="dashboard_admin.php"
                    class="group bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 hover:shadow-2xl hover:shadow-indigo-100 hover:border-indigo-200 transition-all duration-500 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-6 group-hover:bg-indigo-600 group-hover:text-white transition-all duration-500 shadow-sm -rotate-3 group-hover:rotate-0 group-hover:scale-110">
                        <svg class="w-9 h-9 transform group-hover:scale-110 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-3">Estadísticas</h2>
                    <p class="text-gray-500 leading-relaxed font-light text-sm">Analiza métricas generales y genera reportes
                        detallados en formato PDF.</p>
                    <div
                        class="mt-8 flex items-center text-indigo-600 font-bold text-sm tracking-wide uppercase group-hover:text-indigo-800">
                        Ver Dashboard
                        <svg class="ml-2 w-5 h-5 opacity-100 transform group-hover:translate-x-2 transition-all duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M14 5l7 7-7 7M3 12h18" stroke-width="3" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </div>
                </a>
            <?php endif; ?>

        </div>
    </main>

</body>

</html>