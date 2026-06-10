<?php
/**
 * ViewHelper.php — Capa de Recursos (Presentación)
 * 
 * Centraliza toda la lógica de diseño e inyección de estilos. Garantiza que toda
 * la aplicación tenga la misma configuración de colores, animaciones y comportamiento
 * visual, eliminando la duplicidad de CSS en cada archivo PHP.
 */

class ViewHelper {
    
    /**
     * Retorna la configuración de diseño para los estados de una solicitud.
     * Facilita el mantenimiento de colores y etiquetas de estados en un solo lugar.
     * 
     * @param string|null $estado Slug del estado (ej. 'revision', 'aprobado').
     * @return array [Clases de Tailwind, Nombre legible].
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
     * Genera la URL absoluta para visualizar un archivo PDF en la carpeta /uploads/.
     * Resuelve dinámicamente la ruta del proyecto sin importar en qué subcarpeta estemos.
     * 
     * @param string $archivo Nombre del archivo en la base de datos.
     * @return string URL completa para href.
     */
    public static function getPdfUrl($archivo) {
        if (empty($archivo)) return '#';
        
        // Detectar protocolo para prevenir advertencias de contenido mixto
        $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $baseUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
        
        // Determinar la raíz del proyecto para localizar la carpeta /uploads/
        $scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $projectRoot = $scriptPath;
        if (strpos($scriptPath, '/presentacion/vistas') !== false) {
            $projectRoot = dirname(dirname($scriptPath));
        }
        
        return rtrim($baseUrl, '/') . '/' . ltrim($projectRoot, '/') . '/uploads/' . $archivo;
    }

    /**
     * Prepara el estado visual del Timeline (pasos de progreso).
     * Determina qué círculos están rellenos, cuáles brillan y qué etiquetas mostrar.
     * 
     * @param string $estado Estado actual de la solicitud.
     * @return array Datos de configuración para el dibujo de la línea de tiempo.
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

        // Lógica de "Brillo" para el paso actual
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
     * Inyecta la configuración global de Tailwind y los estilos CSS propios.
     * Esto incluye: Colores de marca CECAR y el Sistema de Animaciones (Fade & Slide).
     */
    public static function renderTailwindConfig() {
        ?>
        <!-- Inclusión de CDN de Tailwind (Optimizado para desarrollo rápido) -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            // Definición de la paleta oficial CECAR (Verdes, Amarillos, Naranjas)
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
            /* Keyframes para la animación de entrada suave (Premiumfeel) */
            @keyframes fadeSlideUp {
                0% { opacity: 0; transform: translateY(30px); }
                100% { opacity: 1; transform: translateY(0); }
            }
            /* Clase global para animar tarjetas y secciones */
            .animate-card {
                opacity: 0; /* Inicia invisible hasta que comienza la animación */
                animation: fadeSlideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
                will-change: opacity, transform;
            }
            /* Utilidad para lograr el efecto escalonado (cascada) en listas */
            .delay-100 { animation-delay: 0.1s !important; }
            .delay-200 { animation-delay: 0.2s !important; }
            .delay-300 { animation-delay: 0.3s !important; }
            .delay-400 { animation-delay: 0.4s !important; }
            .delay-500 { animation-delay: 0.5s !important; }
        </style>
        <?php
    }
}
