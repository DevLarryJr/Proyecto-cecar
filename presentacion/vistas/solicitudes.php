<?php
/**
 * solicitudes.php — Capa de Presentación
 * Lista todas las solicitudes leyendo desde MySQL vía SolicitudDAO.
 */
require_once __DIR__ . '/../../recursos/Auth.php';
require_once __DIR__ . '/../../capa_de_acceso/dao/SolicitudDAO.php';

Auth::requireLogin();

// Lógica de filtrado:
// Si es Admin, ve las que él mismo aprobó/rechazó (Historial de sus decisiones)
// Si es Usuario, ve las que él mismo creó
if (Auth::isAdmin()) {
    $solicitudes = SolicitudDAO::obtenerDecididasPorAdmin(Auth::userId());
    $tituloPagina = "Mis Decisiones";
    $subtituloPagina = "Historial de solicitudes procesadas (Aprobadas / Rechazadas) por ti.";
} else {
    $solicitudes = SolicitudDAO::obtenerTodas(Auth::userId());
    $tituloPagina = "Mis Solicitudes";
    $subtituloPagina = "Listado de requerimientos realizados y su estado actual.";
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tituloPagina; ?> - CECAR</title>
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
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

    <!-- NAVIGATION -->
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3 text-sm font-medium text-primary">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    <span class="text-gray-500"><?php echo $tituloPagina; ?></span>
                </div>
                <div class="flex items-center">
                    <img src="../img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold text-gray-800"><?php echo $tituloPagina; ?></h1>
                <p class="text-gray-500 mt-2"><?php echo $subtituloPagina; ?></p>
            </div>
            <a href="solicitud.php"
                class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primaryDark text-white font-bold rounded-xl shadow-lg transition-all transform hover:-translate-y-1 active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                Nueva Solicitud
            </a>
        </div>

        <!-- ALERTAS -->
        <?php if (isset($_GET['delete'])): ?>
            <div class="mb-8 p-4 rounded-xl border <?php echo $_GET['delete'] === 'ok' ? 'bg-emerald-50 border-emerald-100 text-emerald-800' : 'bg-red-50 border-red-100 text-red-800'; ?> flex items-center animate-fade-in shadow-sm">
                <span class="mr-3 text-xl"><?php echo $_GET['delete'] === 'ok' ? '✓' : '⚠'; ?></span>
                <p class="text-sm font-medium">
                    <?php echo $_GET['delete'] === 'ok' ? 'La solicitud ha sido eliminada del sistema.' : 'Error al eliminar la solicitud. Por favor verifique el estado actual.'; ?>
                </p>
            </div>
        <?php endif; ?>

        <!-- FILTROS -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 items-end">
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">B&uacute;squeda</label>
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Buscar ID o solicitante..." 
                            class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm">
                        <svg class="w-4 h-4 absolute left-3 top-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Estado</label>
                    <select id="statusFilter" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm font-medium appearance-none bg-no-repeat bg-[right_1rem_center] bg-[length:1em_1em]" style="background-image: url('data:image/svg+xml;charset=utf-8,%3Csvg xmlns=%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22 fill=%22none%22 stroke=%22%239ca3af%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 class=%22feather feather-chevron-down%22 viewBox=%220 0 24 24%22%3E%3Cpath d=%22m6 9 6 6 6-6%22%2F%3E%3C%2Fsvg%3E');">
                        <option value="all">Ver todos</option>
                        <option value="revision">En Revisi&oacute;n</option>
                        <option value="en_transito">En Tr&aacute;nsito</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="entregado">Entregado</option>
                        <option value="aprobado">Aprobado</option>
                        <option value="rechazado">Rechazado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Fecha</label>
                    <input type="date" id="dateFilter" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all text-sm font-medium">
                </div>
                <div class="flex items-center justify-end">
                    <div class="text-right">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest leading-tight">Total registros</p>
                        <p class="text-2xl font-black text-primary" id="rowCount"><?php echo count($solicitudes); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <script src="../js/ajax-solicitudes.js"></script>

        <!-- TABLA -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="px-8 py-4 text-xs font-bold uppercase tracking-wider w-20">ID</th>
                            <th class="px-8 py-4 text-xs font-bold uppercase tracking-wider">Solicitante</th>
                            <th class="px-8 py-4 text-xs font-bold uppercase tracking-wider">Dependencia</th>
                            <th class="px-8 py-4 text-xs font-bold uppercase tracking-wider">Fecha</th>
                            <th class="px-8 py-4 text-xs font-bold uppercase tracking-wider">Estado</th>
                            <th class="px-8 py-4 text-xs font-bold uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($solicitudes)): ?>
                            <tr>
                                <td colspan="6" class="px-8 py-16 text-center text-gray-400 italic">No se encontraron solicitudes registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($solicitudes as $s): ?>
                                <tr class="hover:bg-gray-50 transition-colors group" data-id="<?php echo $s['id']; ?>" data-date="<?php echo date('Y-m-d', strtotime($s['fecha'])); ?>">
                                    <td class="px-8 py-6 font-bold text-primary">#<?php echo $s['id']; ?></td>
                                    <td class="px-8 py-6 font-semibold text-gray-800 break-words"><?php echo htmlspecialchars($s['nombre']); ?></td>
                                    <td class="px-8 py-6 text-sm">
                                        <span class="px-3 py-1 bg-primary/5 text-primary text-[10px] font-bold rounded-lg uppercase border border-primary/10 break-words">
                                            <?php echo htmlspecialchars($s['dependencia'] ?? '—'); ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($s['fecha'])); ?></td>
                                    <td class="px-8 py-6">
                                        <?php
                                        $est = $s['estado'] ?? 'revision';
                                        $estadoConfigs = [
                                            'revision'    => ['bg-amber-100 text-amber-700', 'En Revisi&oacute;n'],
                                            'en_transito' => ['bg-orange-100 text-orange-700', 'En Tr&aacute;nsito'],
                                            'pendiente'   => ['bg-blue-100 text-blue-700', 'Pendiente'],
                                            'entregado'   => ['bg-tertiary/20 text-primary', 'Entregado'],
                                            'aprobado'    => ['bg-secondary/10 text-secondary', 'Aprobado'],
                                            'rechazado'   => ['bg-danger/10 text-danger', 'Rechazado'],
                                        ];
                                        $config = $estadoConfigs[$est] ?? ['bg-gray-100 text-gray-600', $est];
                                        ?>
                                        <span class="inline-flex items-center px-4 py-1.5 <?php echo $config[0]; ?> text-[10px] font-black rounded-full uppercase tracking-wider">
                                            <?php echo $config[1]; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="detalle.php?id=<?php echo $s['id']; ?>" class="p-2 text-primary hover:bg-primary/5 rounded-lg transition-colors" title="Ver Detalle">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 12s-4-9-12-9-12 9-12 9 4 9 12 9 12-9 12-9z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </a>
                                            <?php if ($est === 'revision'): ?>
                                                <a href="solicitud.php?id=<?php echo $s['id']; ?>" class="p-2 text-quaternary hover:bg-quaternary/5 rounded-lg transition-colors" title="Editar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($est === 'revision' || $est === 'aprobado'): ?>
                                                <button onclick="eliminarSolicitud(<?php echo $s['id']; ?>)" class="p-2 text-danger hover:bg-danger/5 rounded-lg transition-colors" title="Eliminar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-4v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex justify-between items-center text-xs font-bold text-gray-400 uppercase tracking-widest">
                <span>Gestión de Servicios CECAR</span>
                <span><?php echo date('Y'); ?></span>
            </div>
        </div>

    </main>

</body>

</html>
in>

</body>

</html>