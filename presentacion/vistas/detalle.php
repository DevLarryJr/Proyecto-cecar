<?php
/**
 * detalle.php — Capa de Presentación
 */
require_once __DIR__ . '/../../negocio/SolicitudController.php';
require_once __DIR__ . '/../../recursos/ViewHelper.php';

// Los datos vienen preparados desde el controlador
$data = SolicitudController::prepararDetalle($_GET['id'] ?? 0);
$solicitud = $data['solicitud'];
$timeline = $data['timeline'];
$fechaTramite = $data['fechaTramite'];
$fechaFinal = $data['fechaFinal'];

// Variables de estado mapeadas
$estado = $solicitud['estado'] ?? 'revision';
$currentStatus = ViewHelper::getEstadoConfig($estado);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Solicitud #<?php echo $solicitud['id']; ?> - CECAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php ViewHelper::renderTailwindConfig(); ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes glow-primary { 0% { box-shadow: 0 0 0 0 rgba(6, 76, 43, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(6, 76, 43, 0); } 100% { box-shadow: 0 0 0 0 rgba(6, 76, 43, 0); } }
        @keyframes glow-secondary { 0% { box-shadow: 0 0 0 0 rgba(97, 166, 14, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(97, 166, 14, 0); } 100% { box-shadow: 0 0 0 0 rgba(97, 166, 14, 0); } }
        @keyframes glow-amber { 0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); } 70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); } 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); } }
        .animate-glow-primary { animation: glow-primary 2s infinite; }
        .animate-glow-secondary { animation: glow-secondary 2s infinite; }
        .animate-glow-amber { animation: glow-amber 2s infinite; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3 text-sm font-medium text-primary">
                <a href="dashboard.php" class="hover:underline">Dashboard</a>
                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                <a href="solicitudes.php" class="hover:underline"><?php echo Auth::isAdmin() ? 'Mis Decisiones' : 'Mis Solicitudes'; ?></a>
                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                <span class="text-gray-500">Detalle #<?php echo $solicitud['id']; ?></span>
            </div>
            <img src="../img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-12">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6 animate-card delay-100">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <span class="px-3 py-1 bg-primary/5 text-primary text-[10px] font-black uppercase tracking-widest rounded-lg border border-primary/10">Expediente Digital</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">ID #<?php echo $solicitud['id']; ?></span>
                </div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">Detalle de Solicitud</h1>
            </div>
            <div class="flex items-center px-6 py-3 <?php echo $currentStatus[0]; ?> rounded-2xl border border-current/10 shadow-sm">
                <div class="w-2 h-2 rounded-full bg-current mr-3 animate-pulse"></div>
                <span class="text-sm font-black uppercase tracking-widest"><?php echo $currentStatus[1]; ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
            <div class="lg:col-span-8 space-y-10">
                <div class="bg-white rounded-3xl shadow-xl shadow-gray-300/60 border border-gray-200 overflow-hidden animate-card delay-200">
                    <div class="p-8 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">Informaci&oacute;n del Registro</h2>
                    </div>
                    <div class="p-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                            <div>
                                <label class="text-[10px] uppercase font-black text-gray-400 tracking-[0.2em] block mb-3">Solicitante</label>
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary font-black text-xl mr-4 shadow-inner border border-primary/10">
                                        <?php echo strtoupper(substr($solicitud['nombre'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-xl font-bold text-gray-900 leading-tight"><?php echo htmlspecialchars($solicitud['nombre']); ?></p>
                                        <p class="text-xs font-bold text-primary mt-1"><?php echo htmlspecialchars($solicitud['cargo'] ?? 'Personal Administrativo'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase font-black text-gray-400 tracking-[0.2em] block mb-3">Dependencia</label>
                                <p class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($solicitud['dependencia']); ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase font-black text-gray-400 tracking-[0.2em] block mb-3">ID de Control</label>
                                <p class="text-2xl font-black text-primary tracking-tighter">#<?php echo str_pad($solicitud['id'], 6, '0', STR_PAD_LEFT); ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase font-black text-gray-400 tracking-[0.2em] block mb-3">Fecha de Env&iacute;o</label>
                                <p class="text-lg font-bold text-gray-800"><?php echo date('d F, Y', strtotime($solicitud['fecha'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($solicitud['servicios_list'])): ?>
                <div class="bg-white rounded-3xl shadow-xl shadow-gray-300/60 border border-gray-200 overflow-hidden animate-card delay-300">
                    <div class="p-8 border-b border-gray-100 flex items-center space-x-3">
                        <h2 class="text-xl font-bold text-gray-800">Servicios Requeridos</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Descripci&oacute;n</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-center">Cant.</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400">Presupuesto</th>
                                    <th class="px-8 py-4 text-[10px] font-black uppercase tracking-widest text-gray-400 text-right">Disp.</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php foreach ($solicitud['servicios_list'] as $s): ?>
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-6">
                                        <p class="font-bold text-gray-800 break-words group-hover:text-primary"><?php echo htmlspecialchars($s['servicio']); ?></p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-wider"><?php echo htmlspecialchars($s['centro_costos'] ?? 'General'); ?></p>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="inline-block px-3 py-1 bg-gray-50 rounded-lg font-black text-gray-800 border border-gray-100"><?php echo $s['cantidad']; ?></span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-xs">
                                            <p class="font-bold text-gray-700"><?php echo htmlspecialchars($s['rubro']); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <span class="text-xs font-black text-secondaryDark italic tracking-tighter"><?php echo $s['disponibilidad']; ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <div class="bg-primary/5 rounded-3xl p-10 border border-primary/10 relative overflow-hidden animate-card delay-400">
                    <h2 class="text-xs font-black text-primary uppercase tracking-[0.3em] mb-6">Justificaci&oacute;n Institucional</h2>
                    <p class="text-xl font-medium text-primaryDark leading-relaxed italic relative z-10 break-words">"<?php echo htmlspecialchars($solicitud['justificacion']); ?>"</p>
                </div>

                <?php if (!empty($solicitud['archivo'])): ?>
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm flex items-center justify-between animate-card delay-500">
                    <div class="flex items-center space-x-5">
                        <div class="w-14 h-14 bg-danger/10 rounded-2xl flex items-center justify-center text-danger">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-800">Soporte T&eacute;cnico</p>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Documento PDF Adjunto</p>
                        </div>
                    </div>
                    <a href="<?php echo ViewHelper::getPdfUrl($solicitud['archivo']); ?>" target="_blank" class="px-8 py-3 bg-danger hover:bg-red-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-danger/20">Visualizar</a>
                </div>
                <?php endif; ?>

                <?php if ($estado !== 'revision'): ?>
                <div class="bg-gray-100 rounded-3xl p-8 border border-gray-200">
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Nota de Auditor&iacute;a Final</h2>
                    <p class="text-gray-600 italic leading-relaxed">"<?php echo htmlspecialchars($solicitud['comentario_revision'] ?? 'Sin observaciones adicionales.'); ?>"</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-4 space-y-10">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 animate-card delay-200">
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-10">Progreso del Tr&aacute;mite</h2>
                    <div class="space-y-12 relative before:absolute before:inset-0 before:ml-5 before:h-full before:w-[3px] before:bg-gray-200">
                        
                        <div class="relative flex items-start">
                            <div class="flex items-center justify-center flex-none rounded-2xl z-10 bg-secondary text-white shadow-xl animate-glow-secondary" style="width: 44px; height: 44px;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-black text-gray-900 uppercase tracking-tight">Radicaci&oacute;n</p>
                                <p class="text-[10px] text-gray-400 font-bold mt-1"><?php echo date('d M, Y', strtotime($solicitud['fecha'])); ?></p>
                            </div>
                        </div>

                        <div class="relative flex items-start">
                            <div class="flex items-center justify-center flex-none rounded-2xl z-10 <?php echo $timeline['step2Color']; ?> transition-all" style="width: 44px; height: 44px;">
                                <?php if ($timeline['step2Done']): ?><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <?php elseif ($timeline['step2Active']): ?><svg class="w-6 h-6 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                                <?php else: ?><div class="w-2 h-2 bg-gray-300 rounded-full"></div><?php endif; ?>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-black <?php echo ($timeline['step2Active'] || $timeline['step2Done']) ? 'text-gray-900' : 'text-gray-300'; ?> uppercase tracking-tight"><?php echo $timeline['step2Label']; ?></p>
                                <p class="text-[10px] text-gray-400 font-bold mt-1"><?php echo $fechaTramite ? date('d M, Y', strtotime($fechaTramite)) : 'En cola...'; ?></p>
                            </div>
                        </div>

                        <div class="relative flex items-start">
                            <?php
                            $finalizado = $timeline['finalizado'];
                            $finalClass = $finalizado ? ($estado === 'aprobado' ? 'bg-secondary animate-glow-secondary' : 'bg-danger animate-glow-danger') . ' text-white' : 'bg-gray-100';
                            ?>
                            <div class="flex items-center justify-center flex-none rounded-2xl z-10 <?php echo $finalClass; ?> transition-all" style="width: 44px; height: 44px;">
                                <?php if ($finalizado): ?>
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ($estado === 'rechazado'): ?><path d="M6 18L18 6M6 6l12 12" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                        <?php else: ?><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /><?php endif; ?>
                                    </svg>
                                <?php else: ?><div class="w-2 h-2 bg-gray-300 rounded-full"></div><?php endif; ?>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-black <?php echo $finalizado ? 'text-gray-900' : 'text-gray-300'; ?> uppercase tracking-tight"><?php echo $timeline['finalLabel']; ?></p>
                                <p class="text-[10px] text-gray-400 font-bold mt-1"><?php echo $fechaFinal ? date('d M, Y', strtotime($fechaFinal)) : 'Pendiente...'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <a href="solicitudes.php" class="flex items-center justify-center w-full px-8 py-5 bg-white border border-gray-100 text-gray-400 font-black text-[10px] uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-50 transition-all">Regresar al Listado</a>
                </div>

                <?php if (!empty($solicitud['historial'])): ?>
                <div class="bg-gray-50/50 rounded-3xl p-8 border border-gray-100 shadow-inner">
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Log de Actividad</h2>
                    <div class="space-y-6">
                        <?php foreach (array_reverse($solicitud['historial']) as $h): 
                            $badgeH = ViewHelper::getEstadoConfig($h['estado']);
                        ?>
                        <div class="flex items-start space-x-4">
                            <div class="w-2.5 h-2.5 rounded-full bg-primary mt-1 shrink-0"></div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <p class="text-[11px] font-black text-gray-900 uppercase tracking-tight"><?php echo $badgeH[1]; ?></p>
                                    <p class="text-[10px] text-primary font-black uppercase tracking-widest opacity-80"><?php echo htmlspecialchars($h['usuario']); ?></p>
                                </div>
                                <p class="text-[9px] text-gray-400 font-bold uppercase mb-2"><?php echo date('d M, Y — H:i', strtotime($h['fecha'])); ?></p>
                                <div class="relative bg-white border border-gray-100 p-4 rounded-2xl">
                                    <p class="text-xs text-gray-600 italic">"<?php echo htmlspecialchars($h['observacion']); ?>"</p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="max-w-6xl mx-auto px-6 py-12 border-t border-gray-100 mt-12">
        <div class="flex justify-between items-center text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em]">
            <span>Gesti&oacute;n de Servicios Institucionales</span>
            <span>CECAR &copy; <?php echo date('Y'); ?></span>
        </div>
    </footer>
</body>
</html>