<?php
require_once __DIR__ . '/../../recursos/Auth.php';
require_once __DIR__ . '/../../negocio/DashboardController.php';

Auth::requireLogin();

// Validación de seguridad: Solo administradores
if (!Auth::isAdmin()) {
    http_response_code(403);
    header('Location: dashboard.php');
    exit('Acceso denegado');
}

$datos = DashboardController::obtenerDatos();
$resumen = $datos['resumen'];
$recientes = $datos['recientes'];
$porEstado = $datos['porEstado'];
$porMes = $datos['porMes'];
$porDependencia = $datos['porDependencia'];
$dependencias = $datos['dependencias'];

function jsonData($data) {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estad&iacute;sticas y Reportes - CECAR</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen text-gray-800 pb-12">

    <!-- NAVIGATION -->
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3 text-sm font-medium text-primary">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    <span class="text-gray-500">Estad&iacute;sticas Administrativas</span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center bg-gray-50 rounded-lg px-4 py-2 border border-gray-100">
                        <div class="mr-3 text-right hidden sm:block">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Admin</p>
                            <p class="text-xs font-bold text-gray-700"><?php echo Auth::userName(); ?></p>
                        </div>
                        <img src="../img/logo.png" alt="CECAR" class="h-8 w-auto">
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

        <!-- HEADER & ACTIONS -->
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-10 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Dashboard y Reportes</h1>
                <p class="text-gray-500 mt-1">Gesti&oacute;n de indicadores y generaci&oacute;n de informes institucionales.</p>
            </div>
            <a href="#reporte-seccion" class="inline-flex items-center bg-secondary hover:bg-secondaryDark text-white px-6 py-3 rounded-lg font-semibold transition-colors shadow-lg shadow-secondary/20">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Generar Reporte PDF
            </a>
        </div>

        <!-- KPI CARDS -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- TOTAL -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 border-t-4 border-t-primary transition hover:shadow-md">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Solicitudes</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $resumen['total']; ?></h3>
            </div>
            <!-- REVISIÓN -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 border-t-4 border-t-quaternary transition hover:shadow-md">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">En Revisi&oacute;n</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $resumen['revision']; ?></h3>
            </div>
            <!-- ACEPTADAS -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 border-t-4 border-t-secondary transition hover:shadow-md">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Aceptadas</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $resumen['aprobado']; ?></h3>
            </div>
            <!-- RECHAZADAS -->
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-100 border-t-4 border-t-danger transition hover:shadow-md">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Rechazadas</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $resumen['rechazado']; ?></h3>
            </div>
        </div>

        <!-- CHARTS GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- POR ESTADO -->
            <div class="bg-white p-8 rounded-xl border border-gray-100 shadow-sm transition hover:shadow-md">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-sm font-bold text-gray-700 flex items-center">
                        <span class="w-3 h-3 bg-primary rounded-full mr-2"></span> Distribución por Estado
                    </h4>
                    <div id="chartSpinner" class="hidden">
                        <svg class="animate-spin w-4 h-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="chartEstado"></canvas>
                </div>
                <p id="chartNoData" class="hidden text-center text-gray-400 text-xs mt-4 italic">Sin datos para los filtros seleccionados.</p>
            </div>
            <!-- TENDENCIA -->
            <div class="bg-white p-8 rounded-xl border border-gray-100 shadow-sm transition hover:shadow-md lg:col-span-2">
                <h4 class="text-sm font-bold text-gray-700 mb-6 flex items-center">
                    <span class="w-3 h-3 bg-secondary rounded-full mr-2"></span> Tendencia Mensual <?php echo date('Y'); ?>
                </h4>
                <div class="chart-container">
                    <canvas id="chartMes"></canvas>
                </div>
            </div>
        </div>
              <!-- TABLES & REPORTS -->
        <div id="reporte-seccion" class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- FILTROS REPORTE -->
            <div class="bg-white p-8 rounded-xl border border-gray-100 shadow-sm transition hover:shadow-md lg:col-span-1">
                <h4 class="text-lg font-bold text-gray-800 mb-6">Filtros de Reporte</h4>
                <form id="reportForm" action="reporte_solicitudes_pdf.php" method="POST" target="_blank" class="space-y-4">
                    <!-- Campos ocultos para imágenes de gráficos -->
                    <input type="hidden" name="chart_estado_img" id="inputChartEstado">
                    <input type="hidden" name="chart_mes_img" id="inputChartMes">
                    
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Estado</label>
                        <select name="estado" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm bg-white">
                            <option value="todos">Todos los estados</option>
                            <option value="revision">En Revisi&oacute;n</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Dependencia</label>
                        <select name="dependencia_id" class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 transition-all text-sm bg-white">
                            <option value="">Todas las dependencias</option>
                            <?php foreach($dependencias as $dep): ?>
                                <option value="<?php echo $dep['id']; ?>"><?php echo htmlspecialchars($dep['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-primary hover:bg-primaryDark text-white font-semibold py-4 rounded-lg transition-colors shadow-lg shadow-primary/20 mt-4 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        Descargar Reporte
                    </button>
                </form>
            </div>

            <!-- RECIENTES -->
            <div class="bg-white p-8 rounded-xl border border-gray-100 shadow-sm transition hover:shadow-md lg:col-span-3 overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <h4 class="text-xl font-bold text-gray-800">U&acute;ltimas Solicitudes</h4>
                    <span class="text-xs font-bold bg-primary/5 text-primary px-3 py-1 rounded-full uppercase">Top 10</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="px-4 py-3 border-b-0 rounded-tl-lg">ID</th>
                                <th class="px-4 py-3 border-b-0">Fecha</th>
                                <th class="px-4 py-3 border-b-0">Solicitante</th>
                                <th class="px-4 py-3 border-b-0">Dependencia</th>
                                <th class="px-4 py-3 border-b-0 text-center">Estado</th>
                                <th class="px-4 py-3 border-b-0 text-right rounded-tr-lg">Acci&oacute;n</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach($recientes as $s): 
                                $badgeClass = 'bg-gray-100 text-gray-600';
                                if($s['estado'] === 'aprobado') $badgeClass = 'bg-primary/10 text-primary';
                                if($s['estado'] === 'rechazado') $badgeClass = 'bg-danger/10 text-danger';
                                if($s['estado'] === 'revision') $badgeClass = 'bg-quaternary/15 text-quaternary';
                                if($s['estado'] === 'en_transito') $badgeClass = 'bg-secondary/10 text-secondary';
                            ?>
                            <tr class="hover:bg-primary/5 transition-colors">
                                <td class="px-4 py-4 font-bold text-primary">#<?php echo $s['id']; ?></td>
                                <td class="px-4 py-4 text-gray-600"><?php echo date('d/m/Y', strtotime($s['fecha_solicitud'])); ?></td>
                                <td class="px-4 py-4 font-semibold text-gray-800"><?php echo htmlspecialchars($s['solicitante']); ?></td>
                                <td class="px-4 py-4 text-gray-500"><?php echo htmlspecialchars($s['dependencia']); ?></td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-[11px] font-bold uppercase <?php echo $badgeClass; ?>">
                                        <?php echo $s['estado']; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="detalle.php?id=<?php echo $s['id']; ?>" class="text-secondary font-bold hover:underline inline-flex items-center">
                                        Ver <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        const porEstado = <?php echo jsonData($porEstado); ?>;
        const porMes    = <?php echo jsonData($porMes); ?>;
        const porDep    = <?php echo jsonData($porDependencia); ?>;

        // Mapeo de colores por estado
        const statusColorMap = {
            'REVISIÓN':    '#ffa400', // Quaternary (Orange)
            'REVISION':    '#ffa400', 
            'EN TRÁNSITO': '#61a60e', // Secondary (Green)
            'EN_TRANSITO': '#61a60e',
            'PENDIENTE':   '#f59e0b', // Amber
            'ENTREGADO':   '#3b82f6', // Blue
            'APROBADO':    '#064c2b', // Primary (Dark Green)
            'RECHAZADO':   '#e12d2e'  // Danger (Red)
        };
        const defaultColor = '#94a3b8';

        function getStatusColors(labels) {
            return labels.map(label => statusColorMap[label.toUpperCase()] || defaultColor);
        }

        // ── Chart 1: Por Estado (Dona) — actualizable por filtro ──────────────
        const chartLabelsInitial = porEstado.map(x => x.estado.toUpperCase());
        const donutChart = new Chart(document.getElementById('chartEstado'), {
            type: 'doughnut',
            data: {
                labels: chartLabelsInitial,
                datasets: [{
                    data: porEstado.map(x => x.total),
                    backgroundColor: getStatusColors(chartLabelsInitial),
                    borderWidth: 0,
                    hoverOffset: 15
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { weight: 'bold', size: 10 } } }
                },
                cutout: '70%',
                animation: { animateRotate: true, duration: 600 }
            }
        });

        function actualizarGrafico() {
            const estado       = document.querySelector('[name="estado"]').value;
            const fechaInicio  = document.querySelector('[name="fecha_inicio"]').value;
            const fechaFin     = document.querySelector('[name="fecha_fin"]').value;
            const dependencia  = document.querySelector('[name="dependencia_id"]').value;

            const spinner  = document.getElementById('chartSpinner');
            const noData   = document.getElementById('chartNoData');

            spinner.classList.remove('hidden');
            noData.classList.add('hidden');

            const params = new URLSearchParams({ estado, fecha_inicio: fechaInicio, fecha_fin: fechaFin, dependencia_id: dependencia });

            fetch('../../negocio/dashboard_chart_data.php?' + params.toString())
                .then(res => res.json())
                .then(data => {
                    spinner.classList.add('hidden');

                    if (!Array.isArray(data) || data.length === 0) {
                        donutChart.data.labels   = [];
                        donutChart.data.datasets[0].data = [];
                        donutChart.update();
                        noData.classList.remove('hidden');
                        return;
                    }

                    const newLabels = data.map(x => x.estado.toUpperCase());
                    donutChart.data.labels              = newLabels;
                    donutChart.data.datasets[0].data    = data.map(x => x.total);
                    donutChart.data.datasets[0].backgroundColor = getStatusColors(newLabels);
                    donutChart.update();
                })
                .catch(err => {
                    spinner.classList.add('hidden');
                    console.error('Error al cargar datos del gráfico:', err);
                });
        }

        // Escuchar cambios en todos los campos del filtro
        document.querySelectorAll('[name="estado"], [name="fecha_inicio"], [name="fecha_fin"], [name="dependencia_id"]')
            .forEach(el => el.addEventListener('change', actualizarGrafico));

        // ── Chart 2: Por Mes (Línea) ─────────────────────────────────────────
        const lineChart = new Chart(document.getElementById('chartMes'), {
            type: 'line',
            data: {
                labels: porMes.map(x => x.mes),
                datasets: [{
                    label: 'Solicitudes',
                    data: porMes.map(x => x.total),
                    borderColor: '#064c2b',
                    backgroundColor: 'rgba(6, 76, 43, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#064c2b',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { borderDash: [2, 2] } },
                    x: { grid: { display: false } }
                },
                plugins: { legend: { display: false } }
            }
        });

        // ── Captura de gráficos para el reporte PDF ──────────────────────────
        document.getElementById('reportForm').addEventListener('submit', function(e) {
            // Asignar base64 de los gráficos a los campos ocultos
            // toBase64Image() captura lo que está actualmente en el canvas
            document.getElementById('inputChartEstado').value = donutChart.toBase64Image();
            document.getElementById('inputChartMes').value    = lineChart.toBase64Image();
        });
    </script>
</body>
</html>
</html>
