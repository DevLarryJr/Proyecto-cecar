<?php
/**
 * detalle.php — Capa de Presentación
 * Muestra el detalle completo de una solicitud leyendo desde MySQL.
 */
require_once __DIR__ . '/../../recursos/Auth.php';
require_once __DIR__ . '/../../capa_de_acceso/dao/SolicitudDAO.php';

Auth::requireLogin();

$id = (int) ($_GET['id'] ?? 0);

// Leer solicitud desde MySQL (migrado desde JSON)
$solicitud = $id > 0 ? SolicitudDAO::obtenerPorId($id) : null;

if (!$solicitud) {
    header('Location: solicitudes.php');
    exit();
}

// SEGURIDAD: Solo el dueño o el admin pueden ver el detalle
if (!Auth::isAdmin() && $solicitud['usuario_id'] != Auth::userId()) {
    header('Location: solicitudes.php?error=unauthorized');
    exit();
}

// Variables de estado para el tracker visual (Color-Flux 3-Step strictly as requested)
$estado = $solicitud['estado'] ?? 'revision';

// MAPPING DE ESTADOS PARA EL PUNTO CENTRAL (Sólo uno a la vez)
$step2Label = 'En revisión';
$step2Color = 'bg-gray-200';
$step2Active = false;
$step2Done = in_array($estado, ['aprobado', 'rechazado']);

if ($estado === 'revision') {
    $step2Label = 'En revisión';
    $step2Color = 'bg-amber-100 border-2 border-amber-400 animate-glow-amber';
    $step2Active = true;
} elseif ($estado === 'en_transito') {
    $step2Label = 'En tránsito';
    $step2Color = 'bg-amber-100 border-2 border-amber-400 animate-glow-amber';
    $step2Active = true;
} elseif (in_array($estado, ['pendiente', 'entregado'])) {
    $step2Label = 'Pendiente';
    $step2Color = 'bg-amber-100 border-2 border-amber-400 animate-glow-amber';
    $step2Active = true;
} elseif ($step2Done) {
    // Si ya finalizó, mostramos el último estado de trámite alcanzado (usualmente Pendiente)
    $step2Label = 'Pendiente';
    $step2Color = 'bg-brand-main text-white shadow-lg animate-glow-green';
}

// Extraer fechas de hitos desde el historial
$fechaTramite = null;
$fechaFinal = null;

if (!empty($solicitud['historial'])) {
    foreach (array_reverse($solicitud['historial']) as $h) {
        if (!$fechaTramite && in_array($h['estado'], ['revision', 'en_transito', 'pendiente', 'entregado'])) {
            $fechaTramite = $h['fecha'];
        }
        if (!$fechaFinal && in_array($h['estado'], ['aprobado', 'rechazado'])) {
            $fechaFinal = $h['fecha'];
        }
    }
}

// Si no hay trámite aún pero está en revisión, usamos la fecha de registro o la primera del historial
if (!$fechaTramite && $estado === 'revision') {
    $fechaTramite = $solicitud['fecha'];
}
?>
<!DOCTYPE html>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle Solicitud #<?php echo $solicitud['id']; ?> - CECAR</title>
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
        @keyframes glow-primary {
            0% { box-shadow: 0 0 0 0 rgba(6, 76, 43, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(6, 76, 43, 0); }
            100% { box-shadow: 0 0 0 0 rgba(6, 76, 43, 0); }
        }
        @keyframes glow-secondary {
            0% { box-shadow: 0 0 0 0 rgba(97, 166, 14, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(97, 166, 14, 0); }
            100% { box-shadow: 0 0 0 0 rgba(97, 166, 14, 0); }
        }
        @keyframes glow-amber {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }
        .animate-glow-primary { animation: glow-primary 2s infinite; }
        .animate-glow-secondary { animation: glow-secondary 2s infinite; }
        .animate-glow-amber { animation: glow-amber 2s infinite; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800">

    <!-- NAVIGATION -->
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3 text-sm font-medium text-primary">
                <a href="dashboard.php" class="hover:underline">Dashboard</a>
                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                <a href="solicitudes.php" class="hover:underline">
                    <?php echo Auth::isAdmin() ? 'Mis Decisiones' : 'Mis Solicitudes'; ?>
                </a>
                <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                <span class="text-gray-500">Detalle #<?php echo $solicitud['id']; ?></span>
            </div>
            <img src="../img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-6 py-12">

        <!-- HEADER SECTION -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-12 gap-6">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <span class="px-3 py-1 bg-primary/5 text-primary text-[10px] font-black uppercase tracking-widest rounded-lg border border-primary/10">Expediente Digital</span>
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-400 text-[10px] font-bold uppercase tracking-widest">ID #<?php echo $solicitud['id']; ?></span>
                </div>
                <h1 class="text-4xl font-black text-gray-900 tracking-tight">Detalle de Solicitud</h1>
                <p class="text-gray-500 mt-2 text-lg">Gesti&oacute;n y seguimiento de requerimientos institucionales.</p>
            </div>

            <?php
            $statusBadge = [
                'revision'    => ['bg-amber-100 text-amber-700', 'En Revisi&oacute;n'],
                'en_transito' => ['bg-orange-100 text-orange-700', 'En Tr&aacute;nsito'],
                'pendiente'   => ['bg-blue-100 text-blue-700', 'Pendiente'],
                'entregado'   => ['bg-tertiary/20 text-primary', 'Entregado'],
                'aprobado'    => ['bg-secondary/10 text-secondaryDark', 'Aprobado'],
                'rechazado'   => ['bg-danger/10 text-danger', 'Rechazado'],
            ];
            $currentStatus = $statusBadge[$estado] ?? ['bg-gray-100 text-gray-600', $estado];
            ?>
            <div class="flex items-center px-6 py-3 <?php echo $currentStatus[0]; ?> rounded-2xl border border-current/10 shadow-sm">
                <div class="w-2 h-2 rounded-full bg-current mr-3 animate-pulse"></div>
                <span class="text-sm font-black uppercase tracking-widest"><?php echo $currentStatus[1]; ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            <!-- LEFT COLUMN: MAIN CONTENT -->
            <div class="lg:col-span-8 space-y-10">

                <!-- INFO CARD -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-800">Informaci&oacute;n del Registro</h2>
                        <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                    </div>
                    <div class="p-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                            <div>
                                <label class="text-[10px] uppercase font-black text-gray-400 tracking-[0.2em] block mb-3">Solicitante</label>
                                <div class="flex items-center">
                                    <div class="w-12 h-12 rounded-xl bg-primary/5 flex items-center justify-center text-primary font-black text-xl mr-4 shadow-inner">
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
                                <p class="text-xs text-gray-400 font-medium mt-1">Sede Principal CECAR</p>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase font-black text-gray-400 tracking-[0.2em] block mb-3">ID de Control</label>
                                <p class="text-2xl font-black text-primary tracking-tighter">#<?php echo str_pad($solicitud['id'], 6, '0', STR_PAD_LEFT); ?></p>
                            </div>
                            <div>
                                <label class="text-[10px] uppercase font-black text-gray-400 tracking-[0.2em] block mb-3">Fecha de Env&iacute;o</label>
                                <p class="text-lg font-bold text-gray-800"><?php echo date('d F, Y', strtotime($solicitud['fecha'])); ?></p>
                                <p class="text-xs text-gray-400 font-medium mt-1">Registrado a las <?php echo date('H:i', strtotime($solicitud['fecha'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SERVICES TABLE -->
                <?php if (!empty($solicitud['servicios_list'])): ?>
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-8 border-b border-gray-50 flex items-center space-x-3">
                        <div class="w-10 h-10 bg-secondary/10 rounded-xl flex items-center justify-center text-secondaryDark">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">Servicios Requeridos</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50/50">
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
                                        <p class="font-bold text-gray-800 break-words"><?php echo htmlspecialchars($s['servicio']); ?></p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-1 tracking-wider break-words"><?php echo htmlspecialchars($s['centro_costos'] ?? 'General'); ?></p>
                                    </td>
                                    <td class="px-8 py-6 text-center">
                                        <span class="inline-block px-3 py-1 bg-gray-100 rounded-lg font-black text-gray-600"><?php echo $s['cantidad']; ?></span>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-xs">
                                            <p class="font-bold text-gray-700"><?php echo htmlspecialchars($s['rubro']); ?></p>
                                            <p class="text-gray-400 mt-0.5"><?php echo htmlspecialchars($s['fondo'] ?? 'Ordinario'); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-8 py-6 text-right">
                                        <span class="text-xs font-black text-secondaryDark italic"><?php echo $s['disponibilidad']; ?></span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- JUSTIFICATION -->
                <div class="bg-primary/5 rounded-3xl p-10 border border-primary/10 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-6 opacity-10">
                        <svg class="w-24 h-24 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.154c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                    </div>
                    <h2 class="text-xs font-black text-primary uppercase tracking-[0.3em] mb-6">Justificaci&oacute;n Institucional</h2>
                    <p class="text-xl font-medium text-primaryDark leading-relaxed italic relative z-10 break-words">
                        "<?php echo htmlspecialchars($solicitud['justificacion']); ?>"
                    </p>
                </div>

                <!-- ATTACHMENT -->
                <?php if (!empty($solicitud['archivo'])): ?>
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm flex items-center justify-between">
                    <div class="flex items-center space-x-5">
                        <div class="w-14 h-14 bg-danger/10 rounded-2xl flex items-center justify-center text-danger">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-gray-800">Soporte T&eacute;cnico</p>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Documento PDF Adjunto</p>
                        </div>
                    </div>
                    <?php
                        $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
                        // Obtener la ruta base del proyecto (todo antes de /presentacion/...)
                        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
                        // Subir dos niveles desde /presentacion/vistas → raíz del proyecto
                        $projectRoot = rtrim(dirname(dirname($scriptPath)), '/');
                        $pdfUrl = $baseUrl . $projectRoot . '/uploads/' . $solicitud['archivo'];
                    ?>
                    <a href="<?php echo htmlspecialchars($pdfUrl); ?>" target="_blank"
                       class="px-8 py-3 bg-danger hover:bg-red-700 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all shadow-lg shadow-danger/20">
                        Visualizar
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($estado !== 'revision'): ?>
                <div class="bg-gray-100 rounded-3xl p-8 border border-gray-200">
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Nota de Auditor&iacute;a Final</h2>
                    <p class="text-gray-600 italic leading-relaxed break-words">
                        "<?php echo htmlspecialchars($solicitud['comentario_revision'] ?? 'Sin observaciones adicionales por parte de revisores.'); ?>"
                    </p>
                </div>
                <?php endif; ?>

            </div>

            <!-- RIGHT COLUMN: SIDEBAR / TIMELINE -->
            <div class="lg:col-span-4 space-y-10">

                <!-- TIMELINE TRACKER -->
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-10">Progreso del Tr&aacute;mite</h2>

                    <div class="space-y-12 relative before:absolute before:inset-0 before:ml-5 before:h-full before:w-[3px] before:bg-gray-200">

                        <!-- PASO 1 -->
                        <div class="relative flex items-start">
                            <div class="flex items-center justify-center flex-none rounded-2xl z-10 bg-secondary text-white shadow-xl shadow-secondary/20 animate-glow-secondary" style="width: 44px; height: 44px;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-black text-gray-900 uppercase tracking-tight">Radicaci&oacute;n</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase mt-1"><?php echo date('d M, Y • H:i', strtotime($solicitud['fecha'])); ?></p>
                                <p class="text-xs text-secondaryDark font-medium mt-2">Enviado satisfactoriamente</p>
                            </div>
                        </div>

                        <!-- PASO 2 -->
                        <div class="relative flex items-start">
                            <div class="flex items-center justify-center flex-none rounded-2xl z-10 <?php echo $step2Color; ?> transition-all duration-500 <?php echo $step2Active ? 'animate-glow-amber' : ($step2Done ? 'animate-glow-primary bg-primary text-white' : ''); ?>" style="width: 44px; height: 44px;">
                                <?php if ($step2Done): ?>
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <?php elseif ($step2Active): ?>
                                    <svg class="w-6 h-6 text-amber-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"/></svg>
                                <?php else: ?>
                                    <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-black <?php echo ($step2Active || $step2Done) ? 'text-gray-900' : 'text-gray-300'; ?> uppercase tracking-tight"><?php echo $step2Label; ?></p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">
                                    <?php echo $fechaTramite ? date('d M, Y', strtotime($fechaTramite)) : 'En cola de procesos'; ?>
                                </p>
                                <?php if ($step2Active): ?>
                                <p class="text-xs text-amber-600 font-medium mt-2">Analizando requisitos...</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- PASO 3 -->
                        <div class="relative flex items-start">
                            <?php
                            $finalizado = in_array($estado, ['aprobado', 'rechazado']);
                            $finalLabel = ($estado === 'rechazado') ? 'Rechazada' : 'Aceptada';
                            $finalClass = $finalizado ? ($estado === 'aprobado' ? 'bg-secondary animate-glow-secondary' : 'bg-danger animate-glow-danger') . ' text-white shadow-xl' : 'bg-gray-100';
                            ?>
                            <div class="flex items-center justify-center flex-none rounded-2xl z-10 <?php echo $finalClass; ?> transition-all" style="width: 44px; height: 44px;">
                                <?php if ($finalizado): ?>
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if ($estado === 'rechazado'): ?>
                                            <path d="M6 18L18 6M6 6l12 12" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                        <?php else: ?>
                                            <path d="M5 13l4 4L19 7" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /><?php endif; ?>
                                    </svg>
                                <?php else: ?>
                                    <div class="w-2 h-2 bg-gray-300 rounded-full"></div>
                                <?php endif; ?>
                            </div>
                            <div class="ml-6">
                                <p class="text-sm font-black <?php echo $finalizado ? 'text-gray-900' : 'text-gray-300'; ?> uppercase tracking-tight"><?php echo $finalLabel; ?></p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase mt-1">
                                    <?php echo $fechaFinal ? date('d M, Y', strtotime($fechaFinal)) : 'Pendiente de resoluci&oacute;n'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="space-y-4">
                    <a href="solicitudes.php" class="flex items-center justify-center w-full px-8 py-5 bg-white border border-gray-100 text-gray-400 font-black text-[10px] uppercase tracking-[0.2em] rounded-2xl hover:bg-gray-50 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Regresar al Listado
                    </a>

                </div>

                <!-- HISTORIC FEED -->
                <?php if (!empty($solicitud['historial'])): ?>
                <div class="bg-gray-50/50 rounded-3xl p-8 border border-gray-100 shadow-inner">
                    <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Log de Actividad</h2>
                    <div class="space-y-6">
                        <?php foreach (array_reverse($solicitud['historial']) as $h): 
                            $badgeH = $statusBadge[$h['estado']] ?? [null, $h['estado']];
                        ?>
                        <div class="flex items-start space-x-4">
                            <div class="w-2.5 h-2.5 rounded-full bg-primary mt-1 shrink-0 shadow-[0_0_10px_rgba(6,76,43,0.3)] border-2 border-white"></div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-1">
                                    <p class="text-[11px] font-black text-gray-900 uppercase tracking-tight"><?php echo $badgeH[1]; ?></p>
                                    <span class="text-[10px] text-gray-300 font-bold">•</span>
                                    <p class="text-[10px] text-primary font-black uppercase tracking-widest opacity-80"><?php echo htmlspecialchars($h['usuario']); ?></p>
                                </div>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight mb-2"><?php echo date('d M, Y — H:i', strtotime($h['fecha'])); ?></p>
                                
                                <div class="relative group">
                                    <div class="absolute -left-2 top-3 w-4 h-4 bg-white border-l border-t border-gray-100 rotate-45 rounded-sm"></div>
                                    <div class="relative bg-white border border-gray-100 p-4 rounded-2xl shadow-sm group-hover:shadow-md transition-shadow">
                                        <p class="text-xs text-gray-600 leading-relaxed italic">
                                            "<?php echo htmlspecialchars($h['observacion']); ?>"
                                        </p>
                                    </div>
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
   </div>
    </main>
</body>

</html>