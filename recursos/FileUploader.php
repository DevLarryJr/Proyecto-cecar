<?php
/**
 * FileUploader.php — Capa de Recursos
 * Gestiona la subida y validación de archivos PDF adjuntos.
 * Extraído de php/Solicitud.php::uploadFile().
 */
class FileUploader
{
    private static string $uploadDir = __DIR__ . '/../uploads/';

    /**
     * Procesa y guarda el archivo adjunto enviado por el formulario.
     *
     * @param  array|null $file  Elemento de $_FILES (ej: $_FILES['adjunto'])
     * @return array ['success' => bool, 'message' => string, 'filename' => string|null]
     */
    public static function upload(?array $file): array
    {
        // Sin archivo adjunto
        if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['success' => true, 'message' => 'Sin adjunto', 'filename' => null];
        }

        // Error en la transmisión
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Error al recibir el archivo.', 'filename' => null];
        }

        // Validar extensión (solo PDF)
        $info      = pathinfo($file['name']);
        $extension = strtolower($info['extension'] ?? '');
        if ($extension !== 'pdf') {
            return ['success' => false, 'message' => 'Solo se permiten archivos PDF.', 'filename' => null];
        }

        // Validar tamaño máximo (5 MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            return ['success' => false, 'message' => 'El archivo excede el tamaño máximo de 5MB.', 'filename' => null];
        }

        // Crear directorio si no existe
        if (!is_dir(self::$uploadDir)) {
            mkdir(self::$uploadDir, 0777, true);
        }

        // Nombre único para evitar colisiones
        $filename    = uniqid('adj_', true) . '.pdf';
        $destination = self::$uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'message' => 'No se pudo guardar el archivo en el servidor.', 'filename' => null];
        }

        return [
            'success'  => true,
            'message'  => 'Archivo subido correctamente.',
            'filename' => $filename,
            'size'     => $file['size'],
            'mime'     => $file['type'] ?? 'application/pdf',
        ];
    }
}
