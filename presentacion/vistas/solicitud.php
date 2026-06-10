<?php
/**
 * solicitud.php — Capa de Presentación
 */
require_once __DIR__ . '/../../negocio/SolicitudController.php';

$data = SolicitudController::prepararFormulario($_GET['id'] ?? 0);
$id = $data['id'];
$solicitud = $data['solicitud'];
$isEdit = $data['isEdit'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Editar Solicitud #' . $id : 'Nueva Solicitud'; ?> - CECAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <?php ViewHelper::renderTailwindConfig(); ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .ts-wrapper .ts-control { border-radius: 0.5rem !important; padding: 0.5rem 0.75rem !important; font-size: 0.75rem !important; background-color: #f9fafb !important; min-height: 42px !important; }
        .ts-wrapper.focus .ts-control { border-color: #064c2b !important; box-shadow: 0 0 0 2px rgba(6, 76, 43, 0.1) !important; background-color: #fff !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800 pb-20">

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3 text-sm font-medium text-primary">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2"/></svg>
                    <span class="text-gray-500"><?php echo $isEdit ? 'Editar Solicitud' : 'Nueva Solicitud'; ?></span>
                </div>
                <img src="../img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800"><?php echo $isEdit ? 'Editar Solicitud #' . $id : 'Nueva Solicitud'; ?></h1>
            <p class="text-gray-500 mt-1"><?php echo $isEdit ? 'Actualiza los campos de tu requerimiento.' : 'Completa los campos para procesar tu requerimiento.'; ?></p>
        </div>

        <form action="../../negocio/SolicitudController.php" method="POST" enctype="multipart/form-data" id="solicitudForm" class="bg-white rounded-[2rem] shadow-xl shadow-gray-300/60 border border-gray-200 overflow-hidden">
            <input type="hidden" name="ajax" value="1">
            <input type="hidden" name="id_solicitud" value="<?php echo $id; ?>">
            
            <div class="p-8 space-y-10">
                <section>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-primary/10 text-primary rounded-full flex items-center justify-center mr-4 font-bold">1</div>
                        <h2 class="text-xl font-bold text-gray-800">Información General</h2>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Fecha Solicitud</label>
                            <input type="date" name="fecha_solicitud" value="<?php echo $isEdit ? $solicitud['fecha'] : date('Y-m-d'); ?>" class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-50 text-sm">
                        </div>
                        <div><label class="mb-2 block text-sm font-medium text-gray-700">Dependencia</label><input type="text" value="<?php echo Auth::userDependencia(); ?>" class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-100 text-gray-500 text-sm" readonly></div>
                        <div><label class="mb-2 block text-sm font-medium text-gray-700">Solicitante</label><input type="text" value="<?php echo Auth::userName(); ?>" class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-100 text-gray-500 text-sm" readonly></div>
                        <div><label class="mb-2 block text-sm font-medium text-gray-700">Cargo</label><input type="text" value="<?php echo Auth::userCargo(); ?>" class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-100 text-gray-500 text-sm" readonly></div>
                    </div>
                </section>

                <section>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-secondary/10 text-secondary rounded-full flex items-center justify-center mr-4 font-bold">2</div>
                        <h2 class="text-xl font-bold text-gray-800">Detalle de Servicios</h2>
                    </div>
                    <div class="border border-gray-200 rounded-xl overflow-hidden shadow-sm bg-white">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left table-fixed min-w-[1200px]">
                                <thead class="bg-primary text-white text-[10px] uppercase font-bold tracking-wider">
                                    <tr>
                                        <th class="px-3 py-4 w-[24%]">Servicio / Descripci&oacute;n</th>
                                        <th class="px-2 py-4 text-center w-16">Cant.</th>
                                        <th class="px-3 py-4 w-[22%]">Centro de Costos (+C&oacute;d)</th>
                                        <th class="px-3 py-4 w-[22%]">Rubro (+C&oacute;d)</th>
                                        <th class="px-3 py-4 text-right w-28">Disp. ($)</th>
                                        <th class="px-3 py-4 w-24">Fondo</th>
                                        <th class="px-3 py-4 w-[22%]">Funci&oacute;n (+C&oacute;d)</th>
                                        <th class="px-2 py-4 text-center w-20">Acci&oacute;n</th>
                                    </tr>
                                </thead>
                                <tbody id="items" class="divide-y divide-gray-100 align-top"></tbody>
                            </table>
                        </div>
                    </div>
                    <button type="button" id="btnAgregarFila" class="mt-6 flex items-center text-secondary font-bold text-sm hover:text-primary transition-colors">
                        <span class="w-6 h-6 rounded-full border-2 border-current flex items-center justify-center mr-2 text-xl">+</span>
                        Agregar nueva fila
                    </button>
                </section>

                <section>
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-tertiary/10 text-primary rounded-full flex items-center justify-center mr-4 font-bold">3</div>
                        <h2 class="text-xl font-bold text-gray-800">Justificaci&oacute;n y Soportes</h2>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Descripci&oacute;n detallada</label>
                            <textarea name="justificacion" id="justificacion" rows="4" placeholder="..." class="w-full rounded-lg border border-gray-300 px-4 py-3 bg-gray-50 text-sm focus:bg-white outline-none"><?php echo $isEdit ? htmlspecialchars($solicitud['justificacion']) : ''; ?></textarea>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700">Documentaci&oacute;n (PDF)</label>
                            <div onclick="document.getElementById('adjunto').click()" class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-primary transition-colors">
                                <p class="text-sm font-medium text-gray-600">Haz clic para subir soporte</p>
                                <input type="file" name="adjunto" id="adjunto" class="sr-only" accept=".pdf" onchange="mostrarNombre()">
                                <p id="nombre-elegido" class="mt-4 text-xs font-bold text-secondary"></p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t border-gray-100 flex justify-end items-center space-x-6">
                <a href="dashboard.php" class="text-xs font-bold text-red-500 hover:text-red-700 uppercase tracking-widest transition-colors mr-4">Cancelar</a>
                <button type="submit" class="bg-primary hover:bg-primaryDark text-white font-bold py-4 px-12 rounded-xl shadow-lg shadow-primary/20 transition-all hover:-translate-y-1">
                    <?php echo $isEdit ? 'Actualizar Solicitud' : 'Enviar Solicitud'; ?>
                </button>
            </div>
        </form>
    </main>

    <script>
        window.IS_EDIT = <?php echo $isEdit ? 'true' : 'false'; ?>;
        window.ITEMS_EDIT = <?php echo json_encode($solicitud['servicios_list'] ?? []); ?>;
        function mostrarNombre() {
            const input = document.getElementById('adjunto');
            const etiqueta = document.getElementById('nombre-elegido');
            if (input.files.length > 0) etiqueta.innerText = "📄 Archivo: " + input.files[0].name;
        }
    </script>
    <script src="../js/solicitudes.js?v=<?php echo time(); ?>"></script>
    <script src="../js/ajax-solicitud.js?v=<?php echo time(); ?>"></script>
    <script src="../js/validaciones.js?v=<?php echo time(); ?>"></script>
</body>
</html>