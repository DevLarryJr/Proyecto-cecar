<?php
/**
 * ViewHelper.php
 * Utilidades para la capa de presentación: centraliza lógica de UI, 
 * colores de estados y generación de URLs dinámicas.
 */

class ViewHelper {
    
    /**
     * Retorna la configuración de colores y texto para los estados de solicitud.
     */
    public static function getEstadoConfig($estado) {
        $estado = strtolower($estado ?? 'revision');
        $configs = [
            'revision'    => ['bg-amber-100 text-amber-700', 'En Revisión'],
            'en_transito' => ['bg-orange-100 text-orange-700', 'En Tránsito'],
            'pendiente'   => ['bg-blue-100 text-blue-700', 'Pendiente'],
            'entregado'   => ['bg-tertiary/20 text-primary', 'Entregado'],
            'aprobado'    => ['bg-secondary/10 text-secondaryDark', 'Aprobado'],
            'rechazado'   => ['bg-danger/10 text-danger', 'Rechazado'],
        ];
        
        return $configs[$estado] ?? ['bg-gray-100 text-gray-600', $estado];
    }

    /**
     * Genera la URL absoluta para visualizar un archivo PDF adjunto.
     */
    public static function getPdfUrl($archivo) {
        if (empty($archivo)) return '#';
        
        $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
        
        // Obtenemos la ruta base del proyecto de forma dinámica
        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        // Si estamos en /presentacion/vistas/, subimos dos niveles
        $projectRoot = $scriptPath;
        if (strpos($scriptPath, '/presentacion/vistas') !== false) {
            $projectRoot = dirname(dirname($scriptPath));
        }
        
        return rtrim($baseUrl, '/') . '/' . ltrim($projectRoot, '/') . '/uploads/' . $archivo;
    }

    /**
     * Prepara los datos del tracker visual (timeline) para detalle.php
     */
    public static function prepareTimeline($estado) {
        $data = [
            'step2Label' => 'En revisión',
            'step2Color' => 'bg-gray-200',
            'step2Active' => false,
            'step2Done' => in_array($estado, ['aprobado', 'rechazado']),
            'finalizado' => in_array($estado, ['aprobado', 'rechazado']),
            'finalLabel' => ($estado === 'rechazado') ? 'Rechazada' : 'Aceptada'
        ];

        if ($estado === 'revision' || $estado === 'en_transito') {
            $data['step2Label'] = ($estado === 'revision') ? 'En revisión' : 'En tránsito';
            $data['step2Color'] = 'bg-amber-100 border-2 border-amber-400 animate-glow-amber';
            $data['step2Active'] = true;
        } elseif (in_array($estado, ['pendiente', 'entregado'])) {
            $data['step2Label'] = 'Pendiente';
            $data['step2Color'] = 'bg-amber-100 border-2 border-amber-400 animate-glow-amber';
            $data['step2Active'] = true;
        } elseif ($data['step2Done']) {
            $data['step2Label'] = 'Pendiente';
            $data['step2Color'] = 'bg-primary text-white shadow-lg animate-glow-green';
        }

        return $data;
    }

    /**
     * Imprime el bloque de configuración estándar de Tailwind para el proyecto.
     */
    public static function renderTailwindConfig() {
        ?>
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
        <style>
            @keyframes fadeSlideUp {
                0% { opacity: 0; transform: translateY(30px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            .animate-card {
                opacity: 0;
                animation: fadeSlideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
            }
            /* Utilidades para delays escalonados */
            .delay-100 { animation-delay: 0.1s !important; }
            .delay-200 { animation-delay: 0.2s !important; }
            .delay-300 { animation-delay: 0.3s !important; }
            .delay-400 { animation-delay: 0.4s !important; }
            .delay-500 { animation-delay: 0.5s !important; }
        </style>
        <?php
    }
}
