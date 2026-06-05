<?php
/**
 * reporte_solicitudes_pdf.php — Capa de Presentación
 * Genera un reporte detallado de solicitudes en formato PDF usando Dompdf.
 */

// 1. Carga de dependencias y validación
$autoload = __DIR__ . '/../../vendor/autoload.php';

if (!file_exists($autoload)) {
    die("Error: El archivo 'vendor/autoload.php' no existe. Por favor, realiza la instalación de dependencias.");
}

require_once $autoload;
require_once __DIR__ . '/../../recursos/Auth.php';
require_once __DIR__ . '/../../capa_de_acceso/dao/SolicitudDAO.php';

use Dompdf\Dompdf;
use Dompdf\Options;

Auth::requireLogin();

if (!Auth::isAdmin()) {
    http_response_code(403);
    exit('Acceso denegado.');
}

// Obtener logo en base64 si GD está disponible
$logoPath = __DIR__ . '/../img/logo.png';
$logoBase64 = '';
$gdEnabled = extension_loaded('gd');

if ($gdEnabled && file_exists($logoPath)) {
    $logoData = file_get_contents($logoPath);
    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
}

// 2. Obtener filtros y datos
$filtros = [
    'fecha_inicio'   => $_POST['fecha_inicio'] ?? '',
    'fecha_fin'      => $_POST['fecha_fin'] ?? '',
    'estado'         => $_POST['estado'] ?? 'todos',
    'dependencia_id' => $_POST['dependencia_id'] ?? ''
];

$chartEstadoImg = $_POST['chart_estado_img'] ?? '';
$chartMesImg    = $_POST['chart_mes_img'] ?? '';

$solicitudes = SolicitudDAO::obtenerSolicitudesParaReporte($filtros);
$fechaGeneracion = date('d/m/Y H:i:s');
$adminNombre = Auth::userName();

// 3. Estilo Premium (Versión Anterior Mejorada)
$html = '
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 1cm; }
        body { font-family: sans-serif; font-size: 10px; color: #1f2937; line-height: 1.5; }
        
        /* Institutional Branding */
        .color-primary { color: #064c2b; }
        .bg-primary { background-color: #064c2b; }
        .color-secondary { color: #61a60e; }
        
        .header { 
            border-bottom: 4px solid #064c2b; 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }
        .logo { width: 180px; }
        .title { 
            font-size: 24px; 
            font-weight: bold; 
            color: #064c2b; 
            margin: 0; 
            text-transform: uppercase;
            letter-spacing: -0.5px;
        }
        .subtitle {
            font-size: 12px;
            font-weight: bold;
            color: #61a60e;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .meta { color: #9ca3af; font-size: 9px; margin-top: 5px; font-weight: bold; }
        
        .summary-box { 
            background: #f9fafb; 
            border-radius: 12px; 
            padding: 20px; 
            margin-bottom: 30px; 
            border: 1px solid #e5e7eb; 
        }
        .summary-grid { width: 100%; border: none; }
        .summary-label { font-size: 8px; color: #9ca3af; text-transform: uppercase; font-weight: bold; letter-spacing: 1px; }
        .summary-value { font-size: 12px; color: #111827; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { 
            background: #064c2b; 
            color: #ffffff; 
            padding: 12px 10px; 
            text-align: left; 
            text-transform: uppercase; 
            font-size: 8px; 
            font-weight: bold;
            letter-spacing: 1px; 
        }
        td { 
            border-bottom: 1px solid #f3f4f6; 
            padding: 12px 10px; 
            vertical-align: top; 
            font-size: 9px; 
            color: #374151;
            word-wrap: break-word;
            word-break: break-all;
        }
        .row-even { background-color: #fafafa; }
        
        .id-badge {
            font-weight: 900;
            color: #064c2b;
            font-size: 11px;
        }
        
        .status-badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border-radius: 6px; 
            font-weight: bold; 
            font-size: 7px; 
            text-transform: uppercase; 
            background: #f3f4f6;
            color: #4b5563;
        }
        .status-revision { background: #fef3c7; color: #92400e; }
        .status-en_transito { background: #ffedd5; color: #9a3412; }
        .status-aprobado { background: #dcfce7; color: #166534; }
        .status-rechazado { background: #fee2e2; color: #991b1b; }

        .footer { 
            position: fixed; 
            bottom: -30px; 
            left: 0; 
            right: 0; 
            text-align: center; 
            font-size: 8px; 
            color: #9ca3af; 
            border-top: 1px solid #f3f4f6; 
            padding-top: 15px; 
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .charts-section { margin-bottom: 30px; width: 100%; }
        .chart-box { border: 1px solid #f3f4f6; border-radius: 12px; padding: 15px; background: #ffffff; text-align: center; }
        .chart-img { max-width: 100%; height: 180px; }
        .section-title { font-size: 10px; font-weight: bold; color: #111827; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border:none;">
            <tr>
                <td style="border:none; width: 65%;">
                    <h1 class="title">Reporte de Gesti&oacute;n</h1>
                    <div class="subtitle">Servicios Institucionales</div>
                    <div class="meta">Corporaci&oacute;n Universitaria del Caribe - CECAR</div>
                </td>
                <td style="border:none; text-align: right; vertical-align: middle;">
                    '. ($logoBase64 ? '<img src="'.$logoBase64.'" class="logo">' : '') .'
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-label">Generado por</div>
                    <div class="summary-value">'.$adminNombre.'</div>
                </td>
                <td>
                    <div class="summary-label">Fecha del Reporte</div>
                    <div class="summary-value">'.$fechaGeneracion.'</div>
                </td>
                <td>
                    <div class="summary-label">Total Gestión</div>
                    <div class="summary-value">'. count($solicitudes) .' Registros</div>
                </td>
                <td>
                    <div class="summary-label">Filtro Estado</div>
                    <div class="summary-value">'. ($filtros['estado'] === 'todos' ? 'TODOS LOS ESTADOS' : strtoupper($filtros['estado'])) .'</div>
                </td>
            </tr>
        </table>
    </div>';

    // SECCIÓN DE GRÁFICOS (Solo si se enviaron desde el dashboard)
    if ($chartEstadoImg || $chartMesImg) {
        $html .= '
        <div class="charts-section">
            <table style="width: 100%; border: none; border-spacing: 15px 0;">
                <tr>
                    <td class="chart-box" style="width: 35%; border: 1px solid #f3f4f6;">
                        <div class="section-title">Distribución por Estado</div>
                        <img src="'.$chartEstadoImg.'" class="chart-img">
                    </td>
                    <td class="chart-box" style="width: 65%; border: 1px solid #f3f4f6;">
                        <div class="section-title">Tendencia de Solicitudes</div>
                        <img src="'.$chartMesImg.'" class="chart-img">
                    </td>
                </tr>
            </table>
        </div>';
    }

    $html .= '
    <div class="section-title" style="margin-top: 20px;">Listado Detallado de Solicitudes</div>

    <table style="margin-bottom: 40px;">
        <thead>
            <tr>
                <th width="45pt">C&oacute;digo</th>
                <th width="60pt">Fecha</th>
                <th width="140pt">Solicitante</th>
                <th width="140pt">Dependencia</th>
                <th width="70pt">Estado</th>
                <th width="auto">Justificaci&oacute;n</th>
            </tr>
        </thead>
        <tbody>';

$count = 0;
foreach ($solicitudes as $s) {
    $count++;
    $estado = strtolower($s['estado'] ?? 'revision');
    $trClass = ($count % 2 == 0) ? 'row-even' : '';
    $justificacion = mb_strimwidth($s['justificacion'], 0, 300, "...");
    
    $html .= '
            <tr class="'.$trClass.'">
                <td class="id-badge">#'.$s['id'].'</td>
                <td>'.date('d/m/Y', strtotime($s['fecha_solicitud'])).'</td>
                <td><b style="color:#111827">'.$s['solicitante'].'</b></td>
                <td>'.$s['dependencia'].'</td>
                <td><span class="status-badge status-'.$estado.'">'.$s['estado'].'</span></td>
                <td style="font-style:italic; color:#6b7280; font-size:8px;">"'.htmlspecialchars($justificacion).'"</td>
            </tr>';
}

if (empty($solicitudes)) {
    $html .= '<tr><td colspan="6" style="text-align:center; padding: 60px; color: #9ca3af; font-style: italic; font-size: 12px;">No se encontraron registros activos bajo los par&aacute;metros seleccionados.</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="footer">
        CECAR &copy; '.date('Y').' - Sistema de Control y Gesti&oacute;n de Servicios - Confidencial
    </div>
</body>
</html>';

// 4. Generar el PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Forzar descarga
$dompdf->stream('Reporte_'.date('d-m-Y').'.pdf', [
    'Attachment' => true
]);
?>
