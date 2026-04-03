<?php
require_once 'php/Auth.php';
Auth::requireLogin();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud - Proyecto Premium</title>
    <link href="css/output.css?v=<?php echo time(); ?>" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50 min-h-screen text-gray-800 pb-20">

    <nav class="bg-white border-b border-gray-200 py-4">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
            <div class="flex items-center text-sm">
                <a href="dashboard.php" class="hover:underline transition-colors text-brand-main">Dashboard</a>
                <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-gray-800 font-medium">Nueva Solicitud</span>
            </div>
            <img src="img/logo.png" alt="Logo CECAR" class="h-8 w-auto">
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-6 py-10">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Nueva Solicitud</h1>
            <p class="text-gray-500 mt-1">Completa los campos para procesar tu requerimiento.</p>
        </div>

        <div id="status-container" class="hidden mb-6 p-4 rounded-xl border animate-fade-in">
            <p id="status-message" class="font-medium"></p>
        </div>

        <form action="php/guardar_solicitud.php" method="POST" enctype="multipart/form-data" id="solicitudForm" class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <input type="hidden" name="ajax" value="1">
            <div class="p-8 space-y-8">
                
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-brand-soft text-brand-main rounded-full flex items-center justify-center mr-3 text-sm font-bold">1</span>
                        Información General
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Solicitud</label>
                            <input type="date" name="fecha_solicitud" id="fecha_solicitud" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dependencia</label>
                            <input type="text" name="dependencia" id="dependencia" 
                                   value="<?php echo $_COOKIE['pref_dep'] ?? ''; ?>" 
                                   placeholder="Ej. Finanzas" 
                                   class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 focus:border-brand-main outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Solicitud</label>
                            <select name="tipo_solicitud" id="tipo_solicitud" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-main transition-all">
                                <option value="">Seleccione...</option>
                                <option value="Contratación">Contratación de Servicios</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Suministros">Suministros</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del solicitante</label>
                            <input type="text" name="nombre_solicitante" id="nombre_solicitante" placeholder="Ej. Juan Pérez" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                            <input type="text" name="cargo" id="cargo" placeholder="Ej. Analista" class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none">
                        </div>
                    </div>
                </div>



                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-brand-soft text-brand-main rounded-full flex items-center justify-center mr-3 text-sm font-bold">2</span>
                        Detalle de Servicios y Presupuesto
                    </h2>

                    <div class="border border-gray-200 rounded-2xl shadow-sm bg-white overflow-hidden">
                        <table class="w-full border-collapse text-sm">
                            <thead>
                                <tr class="bg-white border-b border-gray-100 text-[13px] text-center tracking-tight">
                                    <th rowspan="2" class="px-6 py-5 text-left font-bold text-slate-700 align-middle">Servicio</th>
                                    <th rowspan="2" class="px-6 py-5 text-center font-bold text-slate-700 align-middle">Cantidad</th>
                                    <th colspan="2" class="px-6 py-3 text-center font-bold text-slate-700 border-b border-gray-100">Centro de costos</th>
                                    <th colspan="2" class="px-6 py-3 text-center font-bold text-slate-700 border-b border-gray-100">Rubro</th>
                                    <th rowspan="2" class="px-6 py-5 text-center font-bold text-slate-700 align-middle">Disponibilidad<br>presupuestal</th>
                                    <th rowspan="2" class="px-6 py-5 text-center font-bold text-slate-700 align-middle">Fondo</th>
                                    <th colspan="2" class="px-6 py-3 text-center font-bold text-slate-700 border-b border-gray-100">Funcion</th>
                                    <th rowspan="2" class="px-6 py-5 text-center font-bold text-slate-700 align-middle">Acción</th>
                                </tr>
                                <tr class="bg-white border-b border-gray-100 text-[11px] font-bold text-slate-500">
                                    <th class="px-6 py-3 text-left">Nombre</th>
                                    <th class="px-6 py-3 text-center">Código</th>
                                    <th class="px-6 py-3 text-left">Nombre</th>
                                    <th class="px-6 py-3 text-center">Código</th>
                                    <th class="px-6 py-3 text-left">Nombre</th>
                                    <th class="px-6 py-3 text-center">Código</th>
                                </tr>
                            </thead>
                            <tbody id="items" class="divide-y divide-gray-50/50">
                                <!-- Items dinámicos aquí -->
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-start">
                        <button type="button" id="btnAgregarFila"
                            class="flex items-center text-emerald-500 font-bold hover:text-emerald-600 transition-all text-sm group">
                            <span class="w-6 h-6 rounded-full border-2 border-emerald-500 flex items-center justify-center mr-2 group-hover:bg-emerald-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 4v16m8-8H4" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            Agregar fila
                        </button>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-brand-soft text-brand-main rounded-full flex items-center justify-center mr-3 text-sm font-bold">3</span>
                        Justificación de la Solicitud
                    </h2>
                    <div>
                        <textarea name="justificacion" id="justificacion" rows="3" placeholder="Describe brevemente por qué se requiere este servicio..." 
                                  class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 outline-none focus:border-brand-main focus:ring-2 focus:ring-brand-soft transition-all"></textarea>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <span class="w-8 h-8 bg-brand-soft text-brand-main rounded-full flex items-center justify-center mr-3 text-sm font-bold">4</span>
                        Documentación de Soporte
                    </h2>
                    <div onclick="document.getElementById('adjunto').click()" class="border-2 border-dashed border-gray-200 rounded-xl p-8 text-center cursor-pointer hover:border-brand-main transition-colors">
                        <p class="text-gray-600">Haz clic aquí para subir tu soporte</p>
                        <p class="text-xs text-gray-400">PDF, PNG, JPG hasta 10MB</p>
                        <input type="file" name="adjunto" id="adjunto" class="sr-only" onchange="mostrarNombre()">
                        <p id="nombre-elegido" class="mt-3 text-sm font-bold text-green-600"></p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-6 border-t flex justify-end space-x-4">
                <a href="dashboard.php" class="px-6 py-3 text-red-600 font-semibold">Cancelar</a>
                <button type="submit" class="px-8 py-3 bg-brand-main text-white font-bold rounded-xl shadow-lg">Guardar Solicitud</button>
            </div>
        </form>
    </main>

    <script src="js/solicitud-manager.js"></script>
    <script src="js/ajax-solicitud.js"></script>
    <script src="js/validaciones.js"></script>
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
    </script>
</body>
</html>