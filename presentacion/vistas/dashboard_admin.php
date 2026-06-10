<?php
/**
 * dashboard_admin.php — Capa de Presentación
 */
require_once __DIR__ . '/../../negocio/DashboardController.php';

// Los datos ya vienen encapsulados desde DashboardController
$datos = DashboardController::prepararVistaAdmin();
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
    <?php 
    require_once __DIR__ . '/../../recursos/ViewHelper.php';
    ViewHelper::renderTailwindConfig(); 
    ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../img/logoIco.ico" type="image/x-icon">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen text-gray-800 pb-12">

    <nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-3 text-sm font-medium text-primary">
                    <a href="dashboard.php" class="hover:underline">Dashboard</a>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2"/></svg>
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

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <div class="bg-white rounded-[1.5rem] p-6 shadow-xl shadow-gray-200/60 border border-gray-200 border-t-4 border-t-primary transition hover:shadow-2xl hover:-translate-y-1 duration-300">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Solicitudes</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $resumen['total']; ?></h3>
            </div>
            <div class="bg-white rounded-[1.5rem] p-6 shadow-xl shadow-gray-200/60 border border-gray-200 border-t-4 border-t-quaternary transition hover:shadow-2xl hover:-translate-y-1 duration-300">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">En Revisi&oacute;n</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $resumen['revision']; ?></h3>
            </div>
            <div class="bg-white rounded-[1.5rem] p-6 shadow-xl shadow-gray-200/60 border border-gray-200 border-t-4 border-t-secondary transition hover:shadow-2xl hover:-translate-y-1 duration-300">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Aceptadas</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $resumen['aprobado']; ?></h3>
            </div>
            <div class="bg-white rounded-[1.5rem] p-6 shadow-xl shadow-gray-200/60 border border-gray-200 border-t-4 border-t-danger transition hover:shadow-2xl hover:-translate-y-1 duration-300">
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Rechazadas</p>
                <h3 class="text-3xl font-bold text-gray-800"><?php echo $resumen['rechazado']; ?></h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="bg-white p-8 rounded-[1.5rem] shadow-xl shadow-gray-200/60 border border-gray-200 transition hover:shadow-2xl duration-300">
                <div class="flex items-center justify-between mb-6">
                    <h4 class="text-sm font-bold text-gray-700 flex items-center">
                        <span class="w-3 h-3 bg-primary rounded-full mr-2"></span> Distribución por Estado
                    </h4>
                    <div id="chartSpinner" class="hidden">
                        <svg class="animate-spin w-4 h-4 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                    </div>
                </div>
                <div class="chart-container"><canvas id="chartEstado"></canvas></div>
                <p id="chartNoData" class="hidden text-center text-gray-400 text-xs mt-4 italic">Sin datos para los filtros seleccionados.</p>
            </div>
            <div class="bg-white p-8 rounded-[1.5rem] shadow-xl shadow-gray-200/60 border border-gray-200 transition hover:shadow-2xl duration-300 lg:col-span-2">
                <h4 class="text-sm font-bold text-gray-700 mb-6 flex items-center">
                    <span class="w-3 h-3 bg-secondary rounded-full mr-2"></span> Tendencia Mensual <?php echo date('Y'); ?>
                </h4>
                <div class="chart-container"><canvas id="chartMes"></canvas></div>
            </div>
        </div>

        <div id="reporte-seccion" class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="bg-white p-8 rounded-[1.5rem] shadow-xl shadow-gray-200/60 border border-gray-200 lg:col-span-1">
                <h4 class="text-lg font-bold text-gray-800 mb-6">Filtros de Reporte</h4>
                <form id="reportForm" action="reporte_solicitudes_pdf.php" method="POST" target="_blank" class="space-y-4">
                    <input type="hidden" name="chart_estado_img" id="inputChartEstado">
                    <input type="hidden" name="chart_mes_img" id="inputChartMes">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Estado</label>
                        <select name="estado" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <option value="todos">Todos los estados</option>
                            <option value="revision">En Revisi&oacute;n</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700">Dependencia</label>
                        <select name="dependencia_id" class="w-full rounded-lg border border-gray-300 px-4 py-3 text-sm bg-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-all">
                            <option value="">Todas las dependencias</option>
                            <?php foreach($dependencias as $dep): ?>
                                <option value="<?php echo $dep['id']; ?>"><?php echo htmlspecialchars($dep['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-primary hover:bg-primaryDark text-white font-semibold py-4 rounded-xl shadow-lg shadow-primary/20 mt-4 flex items-center justify-center transition-all hover:-translate-y-1 active:scale-95">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" stroke-width="2"/></svg>
                        Descargar Reporte
                    </button>
                </form>
            </div>

            <div class="bg-white p-8 rounded-[1.5rem] shadow-xl shadow-gray-200/60 border border-gray-200 lg:col-span-3 overflow-hidden">
                <div class="flex items-center justify-between mb-8">
                    <h4 class="text-xl font-bold text-gray-800">U&acute;ltimas Solicitudes</h4>
                    <span class="text-xs font-bold bg-primary/5 text-primary px-3 py-1 rounded-full uppercase">Top 10</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="px-4 py-3 rounded-tl-lg">ID</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Solicitante</th>
                                <th class="px-4 py-3">Dependencia</th>
                                <th class="px-4 py-3 text-center">Estado</th>
                                <th class="px-4 py-3 text-right rounded-tr-lg">Acci&oacute;n</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach($recientes as $s): 
                                $config = ViewHelper::getEstadoConfig($s['estado']);
                            ?>
                            <tr class="hover:bg-primary/5 transition-colors">
                                <td class="px-4 py-4 font-bold text-primary">#<?php echo $s['id']; ?></td>
                                <td class="px-4 py-4 text-gray-600"><?php echo date('d/m/Y', strtotime($s['fecha_solicitud'])); ?></td>
                                <td class="px-4 py-4 font-semibold text-gray-800"><?php echo htmlspecialchars($s['solicitante']); ?></td>
                                <td class="px-4 py-4 text-gray-500"><?php echo htmlspecialchars($s['dependencia']); ?></td>
                                <td class="px-4 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $config[0]; ?>">
                                        <?php echo $config[1]; ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="detalle.php?id=<?php echo $s['id']; ?>" class="text-secondary font-bold hover:underline inline-flex items-center">
                                        Ver <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" stroke-width="2"/></svg>
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
        const statusColorMap = {
            'REVISIÓN': '#ffa400', 'REVISION': '#ffa400', 'EN TRÁNSITO': '#61a60e', 'EN_TRANSITO': '#61a60e',
            'PENDIENTE': '#f59e0b', 'ENTREGADO': '#3b82f6', 'APROBADO': '#064c2b', 'RECHAZADO': '#e12d2e'
        };
        const defaultColor = '#94a3b8';
        function getStatusColors(labels) { return labels.map(label => statusColorMap[label.toUpperCase()] || defaultColor); }

        const donutChart = new Chart(document.getElementById('chartEstado'), {
            type: 'doughnut',
            data: {
                labels: porEstado.map(x => x.estado.toUpperCase()),
                datasets: [{ data: porEstado.map(x => x.total), backgroundColor: getStatusColors(porEstado.map(x => x.estado)), borderWidth: 0 }]
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, font: { weight: 'bold', size: 10 } } } }, cutout: '70%' }
        });

        function actualizarGrafico() {
            const params = new URLSearchParams({ 
                estado: document.querySelector('[name="estado"]').value, 
                fecha_inicio: document.querySelector('[name="fecha_inicio"]').value, 
                fecha_fin: document.querySelector('[name="fecha_fin"]').value, 
                dependencia_id: document.querySelector('[name="dependencia_id"]').value 
            });
            document.getElementById('chartSpinner').classList.remove('hidden');
            fetch('../../negocio/dashboard_chart_data.php?' + params.toString())
                .then(res => res.json())
                .then(data => {
                    document.getElementById('chartSpinner').classList.add('hidden');
                    const noData = document.getElementById('chartNoData');
                    if (!data || data.length === 0) {
                        donutChart.data.labels = []; donutChart.data.datasets[0].data = []; noData.classList.remove('hidden');
                    } else {
                        donutChart.data.labels = data.map(x => x.estado.toUpperCase());
                        donutChart.data.datasets[0].data = data.map(x => x.total);
                        donutChart.data.datasets[0].backgroundColor = getStatusColors(data.map(x => x.estado));
                        noData.classList.add('hidden');
                    }
                    donutChart.update();
                });
        }
        document.querySelectorAll('[name="estado"], [name="fecha_inicio"], [name="fecha_fin"], [name="dependencia_id"]').forEach(el => el.addEventListener('change', actualizarGrafico));

        const lineChart = new Chart(document.getElementById('chartMes'), {
            type: 'line',
            data: { labels: porMes.map(x => x.mes), datasets: [{ data: porMes.map(x => x.total), borderColor: '#064c2b', backgroundColor: 'rgba(6, 76, 43, 0.1)', fill: true, tension: 0.4 }] },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        document.getElementById('reportForm').addEventListener('submit', function(e) {
            document.getElementById('inputChartEstado').value = donutChart.toBase64Image();
            document.getElementById('inputChartMes').value = lineChart.toBase64Image();
        });
    </script>
</body>
</html>
