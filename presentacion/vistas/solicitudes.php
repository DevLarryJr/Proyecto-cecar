<?php
/**
 * solicitudes.php — Capa de Presentación
 */
require_once __DIR__ . '/../../negocio/SolicitudController.php';
require_once __DIR__ . '/../../recursos/ViewHelper.php';

$data = SolicitudController::prepararListado();
$solicitudes = $data['solicitudes'];
$tituloPagina = $data['tituloPagina'];
$subtituloPagina = $data['subtituloPagina'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tituloPagina; ?> - CECAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php ViewHelper::renderTailwindConfig(); ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3 text-sm font-medium text-primary">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    <span class="text-gray-500"><?php echo $tituloPagina; ?></span>
                </div>
                <img src="../img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6 animate-card delay-100">
            <div class="max-w-2xl">
                <h1 class="text-3xl font-bold text-gray-800"><?php echo $tituloPagina; ?></h1>
                <p class="text-gray-500 mt-2"><?php echo $subtituloPagina; ?></p>
            </div>
            <a href="solicitud.php" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primaryDark text-white font-bold rounded-xl shadow-lg transition-all transform hover:-translate-y-1">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg>
                Nueva Solicitud
            </a>
        </div>

        <!-- FILTROS -->
        <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/60 border border-gray-200 p-6 mb-8 animate-card delay-200">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6 items-end">
                <div class="md:col-span-2 lg:col-span-1">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">B&uacute;squeda</label>
                    <input type="text" id="searchInput" placeholder="Buscar ID o solicitante..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Estado</label>
                    <select id="statusFilter" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none text-sm font-medium">
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
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2">Fecha</label>
                    <input type="date" id="dateFilter" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl outline-none text-sm font-medium">
                </div>
                <div class="text-right">
                    <p class="text-[10px] text-gray-400 font-bold uppercase">Total registros</p>
                    <p class="text-2xl font-black text-primary" id="rowCount"><?php echo count($solicitudes); ?></p>
                </div>
            </div>
        </div>

        <script src="../js/ajax-solicitudes.js"></script>

        <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/60 border border-gray-200 overflow-hidden animate-card delay-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-primary text-white text-xs font-bold uppercase tracking-wider">
                            <th class="px-8 py-4 w-20">ID</th>
                            <th class="px-8 py-4">Solicitante</th>
                            <th class="px-8 py-4">Dependencia</th>
                            <th class="px-8 py-4">Fecha</th>
                            <th class="px-8 py-4">Estado</th>
                            <th class="px-8 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($solicitudes)): ?>
                            <tr><td colspan="6" class="px-8 py-16 text-center text-gray-400 italic">No se encontraron solicitudes registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach ($solicitudes as $s): 
                                $config = ViewHelper::getEstadoConfig($s['estado'] ?? 'revision');
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors group" data-id="<?php echo $s['id']; ?>" data-date="<?php echo date('Y-m-d', strtotime($s['fecha'])); ?>">
                                    <td class="px-8 py-6 font-bold text-primary">#<?php echo $s['id']; ?></td>
                                    <td class="px-8 py-6 font-semibold text-gray-800"><?php echo htmlspecialchars($s['nombre']); ?></td>
                                    <td class="px-8 py-6 text-sm">
                                        <span class="px-3 py-1 bg-primary/5 text-primary text-[10px] font-bold rounded-lg uppercase border border-primary/10">
                                            <?php echo htmlspecialchars($s['dependencia'] ?? '—'); ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($s['fecha'])); ?></td>
                                    <td class="px-8 py-6">
                                        <span class="inline-flex items-center px-4 py-1.5 <?php echo $config[0]; ?> text-[10px] font-black rounded-full uppercase tracking-wider">
                                            <?php echo $config[1]; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <div class="flex items-center justify-end space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="detalle.php?id=<?php echo $s['id']; ?>" class="p-2 text-primary hover:bg-primary/5 rounded-lg" title="Ver Detalle">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" stroke-width="2"/><path d="M24 12s-4-9-12-9-12 9-12 9 4 9 12 9 12-9 12-9z" stroke-width="2"/></svg>
                                            </a>
                                            <?php if (($s['estado'] ?? 'revision') === 'revision'): ?>
                                                <a href="solicitud.php?id=<?php echo $s['id']; ?>" class="p-2 text-quaternary hover:bg-quaternary/5 rounded-lg" title="Editar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2"/></svg>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>