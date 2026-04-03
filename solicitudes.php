<?php
require_once 'php/Auth.php';
Auth::requireLogin();

// Leer solicitudes desde JSON (Requerimiento 5)
$jsonPath = 'php/data/solicitudes.json';
$solicitudes = [];
if (file_exists($jsonPath)) {
    $solicitudes = json_decode(file_get_contents($jsonPath), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Solicitudes - Proyecto Premium</title>
    <link href="css/output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

    <!-- NAVIGATION (Breadcrumbs) -->
    <nav class="bg-white border-b border-gray-200 py-4">
        <div class="max-w-6xl mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center text-sm text-brand-main">
                <a href="dashboard.php" class="hover:underline transition-colors">Dashboard</a>
                <svg class="w-4 h-4 mx-2 text-brand-gray" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-brand-dark font-medium">Mis Solicitudes</span>
            </div>
            <img src="img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-10">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Mis Solicitudes</h1>
                <p class="text-gray-500 mt-1">Listado de requerimientos realizados y su estado actual.</p>
            </div>
            <a href="solicitud.php" class="inline-flex items-center px-6 py-3 bg-brand-main hover:bg-brand-hover text-white font-bold rounded-xl shadow-lg shadow-brand-soft transition-all transform hover:-translate-y-0.5 active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Nueva Solicitud
            </a>
        </div>

        <!-- TABLA -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-100">
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">Solicitante</th>
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">Tipo</th>
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider">Fecha</th>
                            <th class="px-8 py-5 text-sm font-semibold text-gray-600 uppercase tracking-wider text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($solicitudes)): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-10 text-center text-gray-400 italic">No hay solicitudes registradas aún.</td>
                            </tr>
                        <?php
else: ?>
                            <?php foreach ($solicitudes as $s): ?>
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="px-8 py-6 font-medium text-gray-900">#<?php echo $s['id']; ?></td>
                                    <td class="px-8 py-6 font-semibold text-brand-dark"><?php echo htmlspecialchars($s['nombre']); ?></td>
                                    <td class="px-8 py-6">
                                        <span class="px-3 py-1 bg-brand-soft text-brand-main text-xs font-bold rounded-full uppercase border border-brand-light">
                                            <?php echo htmlspecialchars($s['tipo']); ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-6 text-gray-600"><?php echo date('d M, Y', strtotime($s['fecha'])); ?></td>
                                    <td class="px-8 py-6 text-right">
                                        <a href="detalle.php?id=<?php echo $solicitud['id'] ?? $s['id']; ?>" class="inline-flex items-center text-brand-main font-bold hover:text-brand-hover group">
                                            Detalles
                                            <svg class="ml-1 w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            <?php
    endforeach; ?>
                        <?php
endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 text-sm text-gray-500">
                Mostrando <?php echo count($solicitudes); ?> solicitudes encontradas.
            </div>
        </div>

    </main>

</body>

</html>
