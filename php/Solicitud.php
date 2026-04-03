<?php
/**
 * Clase para gestionar el procesamiento de solicitudes y archivos.
 */
class Solicitud
{
    private static $uploadDir = '../uploads/';

    /**
     * Procesa y guarda un archivo adjunto.
     * @return array [success => bool, message => string, filename => string|null]
     */
    public static function uploadFile($file)
    {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al subir el archivo o no se adjuntó ninguno.'];
        }

        $allowedExtensions = ['pdf'];
        $fileInfo = pathinfo($file['name']);
        $extension = strtolower($fileInfo['extension']);

        if (!in_array($extension, $allowedExtensions)) {
            return ['success' => false, 'message' => 'Solo se permiten archivos PDF.'];
        }

        // 5MB max
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'El archivo excede el tamaño máximo de 5MB.'];
        }

        if (!is_dir(self::$uploadDir)) {
            mkdir(self::$uploadDir, 0777, true);
        }

        $newFilename = time() . '_' . basename($file['name']);
        $targetPath = self::$uploadDir . $newFilename;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => true, 'message' => 'Archivo cargado correctamente.', 'filename' => $newFilename];
        }

        return ['success' => false, 'message' => 'No se pudo guardar el archivo en el servidor.'];
    }

    /**
     * Guarda la solicitud en un archivo JSON (Requerimiento 5).
     */
    public static function saveToJson($data)
    {
        $filePath = __DIR__ . '/data/solicitudes.json';

        if (!is_dir(__DIR__ . '/data')) {
            mkdir(__DIR__ . '/data', 0777, true);
        }

        $solicitudes = [];
        if (file_exists($filePath)) {
            $jsonContent = file_get_contents($filePath);
            $solicitudes = json_decode($jsonContent, true) ?? [];
        }

        // Añadir ID único, fecha de registro y estado inicial
        $data['id'] = str_pad(count($solicitudes) + 1, 3, '0', STR_PAD_LEFT);
        $data['registro_at'] = date('Y-m-d H:i:s');
        $data['estado'] = 'revision'; // Estados posibles: 'enviado', 'revision', 'aprobado'

        $solicitudes[] = $data;

        return file_put_contents($filePath, json_encode($solicitudes, JSON_PRETTY_PRINT));
    }
}
?>
