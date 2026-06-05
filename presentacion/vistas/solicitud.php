<?php
require_once __DIR__ . '/../../recursos/Auth.php';
require_once __DIR__ . '/../../capa_de_acceso/dao/SolicitudDAO.php';

Auth::requireLogin();

// -- Lógica para EDICIÓN --
$id = (int) ($_GET['id'] ?? 0);
$solicitud = null;
$isEdit = false;

if ($id > 0) {
    $solicitud = SolicitudDAO::obtenerPorId($id);
    if ($solicitud) {
        // SEGURIDAD: Solo el dueño o el admin pueden editar
        if (!Auth::isAdmin() && $solicitud['usuario_id'] != Auth::userId()) {
            header('Location: solicitudes.php?error=unauthorized');
            exit();
        }

        $estadoSolicitud = strtolower($solicitud['estado'] ?? '');

        if ($estadoSolicitud === 'revision' || $estadoSolicitud === 'en revisión') {
            $isEdit = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Editar Solicitud #' . $id : 'Nueva Solicitud'; ?> - CECAR</title>
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
    <!-- Tom Select for Searchable Dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        .ts-wrapper .ts-control {
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
            border-color: #d1d5db !important;                                                       
            font-size: 0.75rem !important;
            background-color: #f9fafb !important;
            min-height: 42px !important;
            height: auto !important; /* Allow expansion */
            display: flex !important;
            align-items: center !important;
            line-height: 1.2 !important;
        }
        .ts-wrapper.single .ts-control .item {
            white-space: normal !important; /* Allow text wrap in control */
            overflow: visible !important;
            word-break: break-word !important;
        }
        .ts-wrapper.focus .ts-control {
            border-color: #064c2b !important;
            box-shadow: 0 0 0 2px rgba(6, 76, 43, 0.1) !important;
            background-color: #fff !important;
        }
        /* Dropdown Optimization */
        .ts-dropdown {
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid #e5e7eb !important;
            margin-top: 5px !important;
            min-width: 300px !important; /* Prevent narrow dropdowns */
            z-index: 100 !important;
        }
        .ts-dropdown .option {
            padding: 10px 15px !important;
            font-size: 0.8rem !important;
            line-height: 1.4 !important;
            border-bottom: 1px solid #f3f4f6 !important;
        }
        .ts-dropdown .active {
            background-color: rgba(6, 76, 43, 0.05) !important;
            color: #064c2b !important;
        }
        .ts-dropdown .option:last-child { border-bottom: none !important; }
        
        /* Ocultar cursor de búsqueda si ya hay selección */
        .ts-control.has-items input {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800 pb-20">

    <!-- NAVIGATION -->
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3 text-sm font-medium text-primary">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    <span class="text-gray-500"><?php echo $isEdit ? 'Editar Solicitud' : 'Nueva Solicitud'; ?></span>
                </div>
                <div class="flex items-center">
                    <img src="../img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">
                <?php echo $isEdit ? 'Editar Solicitud #' . $id : 'Nueva Solicitud'; ?>
            </h1>
            <p class="text-gray-500 mt-1">
                <?php echo $isEdit ? 'Actualiza los campos de tu requerimiento institucional.' : 'Completa los campos para procesar tu requerimiento de servicio.'; ?>
            </p>
        </div>

        <div id="status-container" class="hidden mb-6 p-4 rounded-lg border animate-fade-in">
            <p id="status-message" class="font-medium"></p>
        </div>

        <form action="../../negocio/SolicitudController.php" method="POST" enctype="multipart/form-data"
            id="solicitudForm" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300">
            <input type="hidden" name="ajax" value="1">
            <input type="hidden" name="id_solicitud" value="<?php echo $id; ?>">
            
            <div class="p-8 space-y-10">
                <!-- SECCION 1 -->
                <section>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-primary/10 text-primary rounded-full flex items-center justify-center mr-4 font-bold">1</div>
                        <h2 class="text-xl font-bold text-gray-800">Información General</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Fecha Solicitud</label>
                            <input type="date" name="fecha_solicitud" id="fecha_solicitud"
                                value="<?php echo $isEdit ? $solicitud['fecha'] : date('Y-m-d'); ?>"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all bg-gray-50">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Dependencia</label>
                            <input type="text" name="dependencia" id="dependencia"
                                value="<?php echo htmlspecialchars(Auth::userDependencia()); ?>"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-50 text-gray-500 cursor-not-allowed" readonly>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Solicitante</label>
                            <input type="text" name="nombre_solicitante" id="nombre_solicitante"
                                value="<?php echo htmlspecialchars(Auth::userName()); ?>"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-50 text-gray-500 cursor-not-allowed" readonly>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Cargo</label>
                            <input type="text" name="cargo" id="cargo"
                                value="<?php echo htmlspecialchars(Auth::userCargo()); ?>"
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-50 text-gray-500 cursor-not-allowed" readonly>
                        </div>
                    </div>
                </section>

                <!-- SECCION 2 -->
                <section>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-secondary/10 text-secondary rounded-full flex items-center justify-center mr-4 font-bold">2</div>
                        <h2 class="text-xl font-bold text-gray-800">Detalle de Servicios</h2>
                    </div>
                    <div class="border border-gray-200 rounded-xl shadow-sm bg-white overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left table-fixed min-w-[1200px]">
                                <thead class="bg-primary text-white text-[10px] uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-3 py-4 border-r border-white/10 w-[24%]">Servicio / Descripci&oacute;n</th>
                                        <th class="px-2 py-4 border-r border-white/10 text-center w-16">Cant.</th>
                                        <th class="px-3 py-4 border-r border-white/10 w-[22%]">Centro de Costos (+C&oacute;d)</th>
                                        <th class="px-3 py-4 border-r border-white/10 w-[22%]">Rubro (+C&oacute;d)</th>
                                        <th class="px-3 py-4 border-r border-white/10 text-right w-28">Disp. ($)</th>
                                        <th class="px-3 py-4 border-r border-white/10 w-24">Fondo</th>
                                        <th class="px-3 py-4 border-r border-white/10 w-[22%]">Funci&oacute;n (+C&oacute;d)</th>
                                        <th class="px-2 py-4 text-center w-20">Acci&oacute;n</th>
                                    </tr>
                                </thead>
                                <tbody id="items" class="divide-y divide-gray-100 italic-inputs align-top">
                                    <!-- Dynamic rows will be injected here by solicitudes.js -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-start">
                        <button type="button" id="btnAgregarFila"
                            class="flex items-center text-secondary font-bold hover:text-secondaryDark transition-colors group text-sm">
                            <span class="w-6 h-6 rounded-full border-2 border-secondary flex items-center justify-center mr-2 group-hover:bg-secondary/10 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            </span>
                            Agregar nueva fila
                        </button>
                    </div>
                </section>

                <!-- SECCION 3 -->
                <section>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-tertiary/10 text-primary rounded-full flex items-center justify-center mr-4 font-bold">3</div>
                        <h2 class="text-xl font-bold text-gray-800">Justificaci&oacute;n y Soportes</h2>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Descripci&oacute;n detallada</label>
                            <textarea name="justificacion" id="justificacion" rows="4"
                                placeholder="Indica el motivo de esta solicitud..."
                                class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all bg-gray-50"><?php echo $isEdit ? htmlspecialchars($solicitud['justificacion']) : ''; ?></textarea>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Documentaci&oacute;n (PDF)</label>
                            <div onclick="document.getElementById('adjunto').click()"
                                class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-primary hover:bg-primary/5 transition-all group">
                                <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:bg-primary/20 transition-colors">
                                    <svg class="w-6 h-6 text-gray-400 group-hover:text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600">Haz clic para subir soporte</p>
                                <p class="text-xs text-gray-400 mt-1">S&oacute;lo archivos PDF hasta 5MB</p>
                                <input type="file" name="adjunto" id="adjunto" class="sr-only" accept=".pdf"
                                    onchange="mostrarNombre()">
                                <p id="nombre-elegido" class="mt-4 text-xs font-bold text-secondary"></p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex justify-end items-center space-x-6">
                <a href="dashboard.php" class="text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors">Cancelar</a>
                <button type="submit" class="bg-primary hover:bg-primaryDark text-white font-bold py-3 px-10 rounded-lg transition-colors shadow-lg shadow-primary/20">
                    <?php echo $isEdit ? 'Actualizar Solicitud' : 'Enviar Solicitud'; ?>
                </button>
            </div>
        </form>
    </main>

    <script>
        window.IS_EDIT = <?php echo $isEdit ? 'true' : 'false'; ?>;
        window.SOLICITUD_EDIT = {};
        window.ITEMS_EDIT = <?php echo json_encode($solicitud['servicios_list'] ?? []); ?>;
    </script>

    <script src="../js/solicitudes.js?v=<?php echo time(); ?>"></script>
    <script src="../js/ajax-solicitud.js?v=<?php echo time(); ?>"></script>
    <script src="../js/validaciones.js?v=<?php echo time(); ?>"></script>
    <script>
        // Función para mostrar el nombre del archivo (Módulo 3)
        function mostrarNombre() {
            const input = document.getElementById('adjunto');
            const etiqueta = document.getElementById('nombre-elegido');
            if (input.files.length > 0) {
                etiqueta.innerText = "📄 Archivo seleccionado: " + input.files[0].name;
                etiqueta.classList.remove('hidden');
            }
        }

        // Auto-ajuste para Justificación
        const justRaw = document.getElementById('justificacion');
        if (justRaw) {
            justRaw.style.overflow = 'hidden';
            justRaw.oninput = function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            };
            // Ajuste inicial
            justRaw.style.height = 'auto';
            justRaw.style.height = (justRaw.scrollHeight) + 'px';
        }
    </script>
</body>

</html>