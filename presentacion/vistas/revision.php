<?php
/**
 * revision.php — Capa de Presentación
 */
require_once __DIR__ . '/../../negocio/RevisionController.php';
require_once __DIR__ . '/../../recursos/ViewHelper.php';

$data = RevisionController::prepararRevision();
$pendientes = $data['pendientes'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Revisor - CECAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php ViewHelper::renderTailwindConfig(); ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
    <script src="../js/hold-confirm.js?v=4" defer></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .progress-fill { position: absolute; top: 0; left: 0; height: 100%; background: rgba(0, 0, 0, 0.15); width: 0%; transition: width 0.1s linear; z-index: 1; }
        .hold-trigger span.relative { z-index: 10; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800">

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3 text-sm font-medium text-primary">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2"/></svg>
                    <span class="text-gray-500">Panel de Decisiones</span>
                </div>
                <div class="flex items-center gap-6">
                    <div class="hidden md:flex items-center px-4 py-1.5 bg-gray-50 border border-gray-100 rounded-lg text-xs font-bold text-primary">
                        <span class="text-gray-400 mr-2 uppercase">Admin:</span>
                        <?php echo htmlspecialchars(Auth::userName()); ?>
                    </div>
                    <a href="../../negocio/LogoutController.php" class="text-xs font-bold text-danger hover:underline uppercase tracking-widest">Salir</a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6 animate-card delay-100">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Gesti&oacute;n de Solicitudes Pendientes</h1>
                <p class="text-gray-500 mt-2">Revisa y procesa los requerimientos institucionales.</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-gray-400 font-bold uppercase">Pendientes hoy</p>
                <p class="text-4xl font-black text-primary" id="revCount"><?php echo count($pendientes); ?></p>
            </div>
        </div>

        <!-- BUSCADOR -->
        <div class="bg-white rounded-2xl shadow-xl shadow-gray-300/60 border border-gray-200 p-6 mb-8 animate-card delay-200">
            <div class="relative max-w-xl">
                <input type="text" id="revSearchInput" placeholder="Buscar por ID o solicitante..." class="w-full pl-12 pr-4 py-4 bg-gray-50 border border-gray-200 rounded-xl outline-none text-sm focus:ring-2 focus:ring-primary/20 transition-all">
                <svg class="w-5 h-5 absolute left-4 top-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke-width="2"/></svg>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const searchInput = document.getElementById('revSearchInput');
                const revCountDisplay = document.getElementById('revCount');
                const rows = document.querySelectorAll('tbody tr:not(.empty-row)');
                searchInput.addEventListener('input', function () {
                    const searchTerm = searchInput.value.toLowerCase();
                    let visibleCount = 0;
                    rows.forEach(row => {
                        const content = row.innerText.toLowerCase();
                        if (content.includes(searchTerm)) {
                            row.style.display = ''; visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });
                    revCountDisplay.textContent = visibleCount;
                });
            });
        </script>

        <div class="bg-white rounded-2xl shadow-xl shadow-gray-300/60 border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-primary text-white text-xs font-bold uppercase tracking-wider">
                            <th class="px-8 py-4 w-24">ID</th>
                            <th class="px-8 py-4">Solicitante</th>
                            <th class="px-8 py-4 text-right">Decisi&oacute;n</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (empty($pendientes)): ?>
                            <tr class="empty-row animate-card delay-300"><td colspan="3" class="px-8 py-16 text-center text-gray-400 italic">No hay solicitudes pendientes.</td></tr>
                        <?php else: ?>
                            <?php 
                            $delayCounter = 300;
                            foreach ($pendientes as $s): 
                                $badge = ViewHelper::getEstadoConfig($s['estado']);
                                $delayClass = "delay-" . $delayCounter;
                                if ($delayCounter < 500) $delayCounter += 100;
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors group animate-card <?php echo $delayClass; ?>">
                                    <td class="px-8 py-10 font-black text-2xl text-primary align-top">#<?php echo $s['id']; ?></td>
                                    <td class="px-8 py-10 align-top">
                                        <div class="flex items-start">
                                            <div class="w-12 h-12 rounded-xl bg-gray-50 flex items-center justify-center text-primary font-black text-xl mr-5 shadow-inner border border-gray-100">
                                                <?php echo strtoupper(substr($s['nombre'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <p class="font-bold text-gray-800 text-lg leading-tight"><?php echo htmlspecialchars($s['nombre']); ?></p>
                                                <p class="text-xs text-gray-400 font-bold uppercase mt-1"><?php echo htmlspecialchars($s['dependencia']); ?></p>
                                                <div class="flex items-center mt-3 space-x-3">
                                                    <span class="inline-flex items-center px-4 py-1 <?php echo $badge[0]; ?> text-[10px] font-black rounded-full uppercase tracking-wider border border-current/10">
                                                        <?php echo $badge[1]; ?>
                                                    </span>
                                                    <span class="text-[10px] text-gray-400 font-medium"><?php echo date('d M, Y', strtotime($s['fecha'])); ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-8 py-10 align-top">
                                        <div class="flex flex-col space-y-4 max-w-xs ml-auto">
                                            <a href="detalle.php?id=<?php echo $s['id']; ?>" target="_blank" class="flex items-center justify-center space-x-2 text-primary font-bold text-xs hover:text-primaryDark">
                                                <span>REVISAR DETALLES</span>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" stroke-width="2"/></svg>
                                            </a>

                                            <?php
                                            $est = $s['estado'];
                                            $flujo = [
                                                'revision'    => ['label' => 'Mover a En Tránsito', 'class' => 'bg-amber-500 shadow-amber-200/50'],
                                                'en_transito' => ['label' => 'Mover a Pendiente', 'class' => 'bg-orange-500 shadow-orange-200/50'],
                                                'pendiente'   => ['label' => 'Confirmar Entrega', 'class' => 'bg-secondary shadow-secondary/20'],
                                            ];
                                            ?>

                                            <?php if (isset($flujo[$est])): ?>
                                                <form id="form-avanzar-<?php echo $s['id']; ?>" action="../../negocio/RevisionController.php" method="POST" class="space-y-2">
                                                    <input type="hidden" name="id_solicitud" value="<?php echo $s['id']; ?>">
                                                    <input type="hidden" name="accion" value="avanzar">
                                                    <textarea name="comentario_revision" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none text-[10px] bg-white font-medium focus:border-primary/30 transition-all" rows="2" placeholder="Nota opcional..."></textarea>
                                                    <button type="button" class="hold-trigger w-full px-6 py-4 <?php echo $flujo[$est]['class']; ?> text-white text-[10px] font-black uppercase tracking-widest relative overflow-hidden rounded-xl shadow-lg transition-all active:scale-[0.98] hover:-translate-y-1 block" data-form="form-avanzar-<?php echo $s['id']; ?>">
                                                        <span class="relative z-10"><?php echo $flujo[$est]['label']; ?> (Mantener)</span>
                                                        <div class="progress-fill"></div>
                                                    </button>
                                                </form>
                                            <?php endif; ?>

                                            <?php if ($est === 'entregado'): ?>
                                                <div class="p-5 bg-gray-50 rounded-xl border border-gray-200 shadow-inner">
                                                    <p class="text-[10px] font-bold text-gray-400 uppercase mb-4 tracking-widest text-center">Decisi&oacute;n Final</p>
                                                    <form action="../../negocio/RevisionController.php" method="POST" class="space-y-4">
                                                        <input type="hidden" name="id_solicitud" value="<?php echo $s['id']; ?>">
                                                        <textarea name="comentario_revision" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 outline-none text-sm bg-white focus:border-primary/30 transition-all" placeholder="Nota de auditor&iacute;a..."></textarea>
                                                        <div class="flex space-x-3">
                                                            <button type="submit" name="accion" value="aprobar" class="flex-1 bg-secondary hover:bg-secondaryDark text-white text-[10px] font-black py-4 rounded-xl shadow-lg shadow-secondary/30 transition-all uppercase tracking-widest hover:-translate-y-1">Aprobar</button>
                                                            <button type="submit" name="accion" value="rechazar" class="flex-1 bg-white border-2 border-danger/20 text-danger hover:bg-danger/5 text-[10px] font-black py-4 rounded-xl transition-all uppercase tracking-widest hover:-translate-y-1">Rechazar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-8 py-5 bg-gray-50 border-t border-gray-100 flex justify-between items-center text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">
                <span>Auditor&iacute;a de Servicios CECAR</span>
                <span><?php echo date('Y'); ?></span>
            </div>
        </div>
    </main>
</body>
</html>