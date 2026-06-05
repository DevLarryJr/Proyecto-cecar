<?php
/**
 * SolicitudDAO.php — Capa de Acceso / DAO
 * Data Access Object: todas las consultas SQL relacionadas con solicitudes.
 * Trabaja directamente con la base de datos solicitud_final.
 */

require_once __DIR__ . '/../../recursos/Database.php';
require_once __DIR__ . '/../modelo/Solicitud.php';

class SolicitudDAO
{
    // ──────────────────────────────────────────────────────────
    // LECTURA
    // ──────────────────────────────────────────────────────────

    /**
     * Obtiene todas las solicitudes con datos básicos para el listado.
     * @return array Array de arrays asociativos listos para la vista.
     */
    public static function obtenerTodas(?int $usuarioId = null): array
    {
        $pdo = Database::getConnection();
        $params = [];
        $where = "";

        if ($usuarioId !== null) {
            $where = "WHERE s.usuario_id = ? AND s.activo = 1";
            $params[] = $usuarioId;
        } else {
            $where = "WHERE s.activo = 1";
        }

        $stmt = $pdo->prepare("
            SELECT
                s.id,
                s.fecha_solicitud                       AS fecha,
                s.justificacion,
                s.created_at                            AS registro_at,
                s.updated_at,
                CONCAT(u.nombre, ' ', u.apellido)       AS nombre,
                d.nombre                                AS dependencia,
                c.nombre_cargo                          AS cargo,
                e.nombre                                AS estado
            FROM   solicitudes s
            JOIN   usuarios            u  ON u.id           = s.usuario_id
            JOIN   dependencias        d  ON d.id           = u.id_dependecia
            JOIN   cargo          c  ON c.idcargo      = u.id_cargo
            JOIN   estados_solicitud   e  ON e.id           = s.estado_id
            $where
            ORDER  BY s.created_at DESC
        ");

        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene las solicitudes en estado 'revision' (para el panel revisor).
     * @return array
     */
    public static function obtenerPendientes(): array
    {
        $pdo = Database::getConnection();

        // Ahora incluimos todos los estados de la fase de gestión administrativa
        $stmt = $pdo->prepare("
            SELECT
                s.id,
                s.fecha_solicitud                       AS fecha,
                s.justificacion,
                s.created_at                            AS registro_at,
                CONCAT(u.nombre, ' ', u.apellido)       AS nombre,
                d.nombre                                AS dependencia,
                e.nombre                                AS estado
            FROM   solicitudes s
            JOIN   usuarios            u  ON u.id           = s.usuario_id
            JOIN   dependencias        d  ON d.id           = u.id_dependecia
            JOIN   estados_solicitud   e  ON e.id           = s.estado_id
            WHERE  e.nombre IN ('revision', 'en_transito', 'pendiente', 'entregado') 
                   AND s.activo = 1
            ORDER  BY s.created_at DESC
        ");
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Obtiene una solicitud completa por ID (con ítems e historial).
     * @return array|null  Array asociativo o null si no existe.
     */
    public static function obtenerPorId(int $id): ?array
    {
        $pdo = Database::getConnection();

        // 1. Datos principales de la solicitud
        $stmt = $pdo->prepare("
            SELECT 
                s.*,
                s.fecha_solicitud                       AS fecha,
                CONCAT(u.nombre, ' ', u.apellido)       AS nombre,
                c.nombre_cargo                          AS cargo,
                c.descripcion_cargo,
                d.nombre                                AS dependencia,
                e.nombre                                AS estado
            FROM solicitudes s
            JOIN usuarios u ON u.id = s.usuario_id
            JOIN dependencias d ON d.id = u.id_dependecia
            JOIN cargo c ON c.idcargo = u.id_cargo
            LEFT JOIN estados_solicitud e ON s.estado_id = e.id
            WHERE s.id = ? AND s.activo = 1
        ");
        $stmt->execute([$id]);
        $solicitud = $stmt->fetch();

        if (!$solicitud) {
            return null;
        }

        // 2. Ítems de servicio
        $stmtItems = $pdo->prepare("
            SELECT
                i.id,
                i.solicitud_id,
                i.servicio,
                i.cantidad,
                i.rubro_id,
                r.nombre                AS rubro,
                r.codigo                AS rubro_codigo,
                i.disponibilidad,
                i.fondos_id,
                f.nombre                AS fondo,
                f.codigo                AS fondo_codigo,
                i.funcion_id,
                fn.nombre               AS funcion,
                fn.codigo               AS funcion_codigo,
                i.centro_costo_id,
                cc.nombre               AS centro_costos,
                cc.codigo               AS cc_codigo
            FROM   items_solicitud_servicio i
            LEFT JOIN rubros r ON i.rubro_id = r.id
            LEFT JOIN fondos f ON i.fondos_id = f.id
            LEFT JOIN funcion fn ON i.funcion_id = fn.id
            LEFT JOIN centros_costo cc ON i.centro_costo_id = cc.id
            WHERE  i.solicitud_id = ?
            ORDER  BY i.id ASC
        ");
        $stmtItems->execute([$id]);
        $solicitud['servicios_list'] = $stmtItems->fetchAll();
        $solicitud['servicios_count'] = count($solicitud['servicios_list']);

        // 3. Historial de estados
        $stmtHist = $pdo->prepare("
            SELECT
                e.nombre                AS estado,
                h.fecha,
                CONCAT(u.nombre, ' ', u.apellido) AS usuario,
                h.observacion
            FROM   historial_estados   h
            JOIN   estados_solicitud   e  ON e.id = h.estado_nuevo_id
            JOIN   usuarios            u  ON u.id = h.usuario_id
            WHERE  h.solicitud_id = ?
            ORDER  BY h.fecha ASC
        ");
        $stmtHist->execute([$id]);
        $solicitud['historial'] = $stmtHist->fetchAll();

        // 4. Archivo adjunto (si existe, tomamos el último)
        $stmtArch = $pdo->prepare("
            SELECT nombre_archivo, ruta_archivo
            FROM   archivos_adjuntos
            WHERE  solicitud_id = ?
            ORDER  BY uploaded_at DESC
            LIMIT  1
        ");
        $stmtArch->execute([$id]);
        $arch = $stmtArch->fetch();
        $solicitud['archivo'] = $arch ? $arch['nombre_archivo'] : null;

        // 5. Comentario de revisión: último historial que no sea 'revision'
        $solicitud['comentario_revision'] = null;
        foreach (array_reverse($solicitud['historial']) as $h) {
            if ($h['estado'] !== 'revision') {
                $solicitud['comentario_revision'] = $h['observacion'];
                break;
            }
        }

        return $solicitud;
    }

    /**
     * Búsqueda avanzada con filtros de texto, estado y fecha.
     */
    public static function buscarAvanzado(string $termino, string $estado = 'all', string $fecha = '', ?int $usuarioId = null): array
    {
        $pdo = Database::getConnection();
        $params = [];
        $sql = "
            SELECT
                s.id,
                s.fecha_solicitud                       AS fecha,
                s.justificacion,
                s.created_at                            AS registro_at,
                CONCAT(u.nombre, ' ', u.apellido)       AS nombre,
                d.nombre                                AS dependencia,
                e.nombre                                AS estado
            FROM   solicitudes s
            JOIN   usuarios            u  ON u.id           = s.usuario_id
            JOIN   dependencias        d  ON d.id           = u.id_dependecia
            JOIN   estados_solicitud   e  ON e.id           = s.estado_id
            WHERE  s.activo = 1
        ";

        if ($usuarioId !== null) {
            $sql .= " AND s.usuario_id = ?";
            $params[] = $usuarioId;
        }

        if (!empty($termino)) {
            $sql .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR s.justificacion LIKE ?)";
            $t = "%$termino%";
            $params[] = $t;
            $params[] = $t;
            $params[] = $t;
        }

        if ($estado !== 'all') {
            $sql .= " AND e.nombre = ?";
            $params[] = $estado;
        }

        if (!empty($fecha)) {
            $sql .= " AND s.fecha_solicitud = ?";
            $params[] = $fecha;
        }

        $sql .= " ORDER BY s.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los centros de costo activos.
     * @return array
     */
    public static function obtenerCentrosCosto(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id, nombre, codigo
                FROM centros_costo
                ORDER BY nombre ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las funciones activas.
     * @return array
     */
    public static function obtenerFunciones(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id, nombre, codigo
                FROM funcion
                ORDER BY nombre ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los rubros activos.
     * @return array
     */
    public static function obtenerRubros(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id, nombre, codigo
                FROM rubros
                ORDER BY nombre ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los fondos activos.
     * @return array
     */
    public static function obtenerFondos(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id, nombre, codigo
                FROM fondos
                ORDER BY nombre ASC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las dependencias activas.
     * @return array
     */
    public static function obtenerDependencias(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT id, nombre FROM dependencias WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todos los cargos.
     * @return array
     */
    public static function obtenerCargos(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT idcargo AS id, nombre_cargo AS nombre, descripcion_cargo 
                FROM cargo 
                ORDER BY nombre_cargo ASC";
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ──────────────────────────────────────────────────────────
    // ESCRITURA
    // ──────────────────────────────────────────────────────────

    /**
     * Guarda una nueva solicitud completa en la base de datos.
     * Usa una transacción para garantizar consistencia.
     *
     * @param  array $data    Datos generales de la solicitud.
     * @param  array $items   Lista de ítems de servicio.
     * @param  int   $userId  ID del usuario que crea la solicitud.
     * @param  array|null $archivo  Resultado de FileUploader::upload()
     * @return int|false  ID de la solicitud creada o false si falla.
     */
    public static function guardar(array $data, array $items, int $userId, ?array $archivo = null)
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            // Obtener ID del estado 'revision'
            $estadoId = self::obtenerEstadoId('revision');

            // 1. Insertar cabecera de la solicitud (simplificada en v4)
            $stmt = $pdo->prepare("
                INSERT INTO solicitudes (
                    usuario_id, estado_id, fecha_solicitud, justificacion
                ) VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $estadoId,
                $data['fecha_solicitud'] ?? date('Y-m-d'),
                $data['justificacion']
            ]);
            $solicitudId = (int)$pdo->lastInsertId();

            // 2. Insertar cada ítem de servicio
            foreach ($items as $item) {
                // Buscamos o creamos los IDs correspondientes para CADA ítem
                $rubroId = (isset($item['rubro_id']) && $item['rubro_id'] > 0)
                    ? $item['rubro_id']
                    : self::findOrCreateRubro($item['rubro'], $item['rubro_codigo']);

                $fondoId = (isset($item['fondos_id']) && $item['fondos_id'] > 0)
                    ? $item['fondos_id']
                    : self::findOrCreateFondo($item['fondo'], $item['fondo_codigo'] ?? $item['fondo']);
                
                $funcionId = (isset($item['funcion_id']) && $item['funcion_id'] > 0)
                    ? $item['funcion_id']
                    : self::findOrCreateFuncion($item['funcion'], $item['funcion_codigo']);

                $centroCostoId = (isset($item['centro_costo_id']) && $item['centro_costo_id'] > 0)
                    ? $item['centro_costo_id']
                    : self::findOrCreateCentroCosto($item['centro_costos'], (int)($item['cc_codigo'] ?? 0));

                $stmtItem = $pdo->prepare("
                    INSERT INTO items_solicitud_servicio
                        (solicitud_id, servicio, cantidad, disponibilidad, rubro_id, fondos_id, funcion_id, centro_costo_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtItem->execute([
                    $solicitudId,
                    $item['servicio'],
                    (int) $item['cantidad'],
                    (float) $item['disponibilidad'],
                    $rubroId,
                    $fondoId,
                    $funcionId,
                    $centroCostoId
                ]);
            }

            // 3. Insertar en historial_estados (estado inicial: revision)
            $stmtHist = $pdo->prepare("
                INSERT INTO historial_estados
                    (solicitud_id, estado_nuevo_id, usuario_id, observacion)
                VALUES (?, ?, ?, ?)
            ");
            $stmtHist->execute([
                $solicitudId,
                $estadoId,
                $userId,
                'Solicitud creada y enviada a revisión',
            ]);

            // 4. Guardar archivo adjunto si existe
            if (!empty($archivo['filename'])) {
                $stmtArch = $pdo->prepare("
                    INSERT INTO archivos_adjuntos
                        (solicitud_id, nombre_archivo, ruta_archivo, tipo_mime, tamano_bytes)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmtArch->execute([
                    $solicitudId,
                    $archivo['filename'],
                    'uploads/' . $archivo['filename'],
                    $archivo['mime'] ?? 'application/pdf',
                    $archivo['size'] ?? 0,
                ]);
            }

            $pdo->commit();
            return $solicitudId;

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('[SolicitudDAO::guardar] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza el estado de una solicitud y agrega entrada al historial.
     *
     * @param  int    $solicitudId  ID de la solicitud.
     * @param  string $estadoNombre 'aprobado' | 'rechazado'
     * @param  int    $usuarioId    ID del revisor.
     * @param  string $comentario   Observación del revisor.
     * @return bool
     */
    public static function actualizarEstado(int $solicitudId, string $estadoNombre, int $usuarioId, string $comentario): bool
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            $estadoId = self::obtenerEstadoId($estadoNombre);

            // Actualizar estado en la solicitud
            $stmt = $pdo->prepare("
                UPDATE solicitudes
                SET    estado_id  = ?,
                       updated_at = NOW()
                WHERE  id = ?
            ");
            $stmt->execute([$estadoId, $solicitudId]);

            // Insertar en historial
            $stmtHist = $pdo->prepare("
                INSERT INTO historial_estados
                    (solicitud_id, estado_nuevo_id, usuario_id, observacion)
                VALUES (?, ?, ?, ?)
            ");
            $stmtHist->execute([$solicitudId, $estadoId, $usuarioId, $comentario]);

            $pdo->commit();
            return true;

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('[SolicitudDAO::actualizarEstado] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Avanza la solicitud al siguiente estado en la cadena lógica.
     */
    public static function avanzarEstado(int $solicitudId, int $usuarioId, ?string $comentario = null): array
    {
        $pdo = Database::getConnection();
        
        $flujo = [
            'revision'    => 'en_transito',
            'en_transito' => 'pendiente',
            'pendiente'   => 'entregado'
        ];
 
        try {
            $pdo->beginTransaction();
 
            // 1. Obtener estado actual
            $stmt = $pdo->prepare("SELECT e.nombre FROM solicitudes s JOIN estados_solicitud e ON e.id = s.estado_id WHERE s.id = ?");
            $stmt->execute([$solicitudId]);
            $actual = $stmt->fetchColumn();
 
            if (!isset($flujo[$actual])) {
                throw new Exception("No se puede avanzar desde el estado actual: $actual");
            }
 
            $nuevoEstado = $flujo[$actual];
            $nuevoEstadoId = self::obtenerEstadoId($nuevoEstado);
 
            // 2. Actualizar
            $stmtUpdate = $pdo->prepare("UPDATE solicitudes SET estado_id = ?, updated_at = NOW() WHERE id = ?");
            $stmtUpdate->execute([$nuevoEstadoId, $solicitudId]);
 
            // 3. Historial
            $obsFinal = !empty($comentario) ? $comentario : "Avance automático: Solicitud movida a " . str_replace('_', ' ', $nuevoEstado);
            $stmtHist = $pdo->prepare("INSERT INTO historial_estados (solicitud_id, estado_nuevo_id, usuario_id, observacion) VALUES (?, ?, ?, ?)");
            $stmtHist->execute([$solicitudId, $nuevoEstadoId, $usuarioId, $obsFinal]);
 
            $pdo->commit();
            return ['success' => true, 'nuevoEstado' => $nuevoEstado];
 
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ──────────────────────────────────────────────────────────
    // HELPERS PRIVADOS — Find or Create catálogos
    // ──────────────────────────────────────────────────────────

    /** Devuelve el ID del estado por nombre. */
    private static function obtenerEstadoId(string $nombre): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM estados_solicitud WHERE nombre = ? LIMIT 1");
        $stmt->execute([$nombre]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new RuntimeException("Estado '$nombre' no encontrado en la BD.");
        }

        return (int) $row['id'];
    }

    /** Busca un rubro por código; si no existe, lo crea. */
    private static function findOrCreateRubro(string $nombre, string $codigo): int
    {
        return self::findOrCreate('rubros', $nombre, $codigo);
    }

    /** Busca un fondo por código; si no existe, lo crea. */
    private static function findOrCreateFondo(string $nombre, string $codigo): int
    {
        return self::findOrCreate('fondos', $nombre, $codigo);
    }

    /** Busca una función por código; si no existe, la crea. */
    private static function findOrCreateFuncion(string $nombre, string $codigo): int
    {
        return self::findOrCreate('funcion', $nombre, $codigo);
    }

    /** Busca un centro de costo por código INT; si no existe, lo crea. */
    private static function findOrCreateCentroCosto(string $nombre, int $codigo): int
    {
        $pdo = Database::getConnection();

        // El código de centros_costo es INT, diferente a los otros catálogos
        $stmt = $pdo->prepare("SELECT id FROM centros_costo WHERE codigo = ? LIMIT 1");
        $stmt->execute([$codigo]);
        $row = $stmt->fetch();

        if ($row) {
            return (int) $row['id'];
        }

        $insert = $pdo->prepare("INSERT INTO centros_costo (nombre, codigo) VALUES (?, ?)");
        $insert->execute([$nombre, $codigo]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Helper genérico: busca por código en una tabla de catálogo.
     * Si no encuentra el registro, lo inserta y devuelve el nuevo ID.
     *
     * @param  string $table  Nombre de la tabla (rubros | fondos | funcion)
     * @param  string $nombre
     * @param  string $codigo
     * @return int
     */
    private static function findOrCreate(string $table, string $nombre, string $codigo): int
    {
        // Sanitizar nombre de tabla (whitelist)
        $allowed = ['rubros', 'fondos', 'funcion'];
        if (!in_array($table, $allowed, true)) {
            throw new InvalidArgumentException("Tabla '$table' no permitida.");
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id FROM `$table` WHERE codigo = ? LIMIT 1");
        $stmt->execute([$codigo]);
        $row = $stmt->fetch();

        if ($row) {
            return (int) $row['id'];
        }

        $insert = $pdo->prepare("INSERT INTO `$table` (nombre, codigo) VALUES (?, ?)");
        $insert->execute([$nombre, $codigo]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Actualiza una solicitud completa (campos de cabecera y reemplazo de ítems).
     */
    public static function actualizarCompleta(int $solicitudId, array $data, array $items): bool
    {
        $pdo = Database::getConnection();

        try {
            $pdo->beginTransaction();

            // 1. Actualizar cabecera (simplificada en v4)
            $stmt = $pdo->prepare("
                UPDATE solicitud_final.solicitudes 
                SET    fecha_solicitud = ?, 
                       justificacion   = ?,
                       updated_at      = NOW()
                WHERE  id = ?
            ");
            $stmt->execute([
                $data['fecha'] ?? date('Y-m-d'),
                $data['justificacion'],
                $solicitudId
            ]);

            // 2. Eliminar ítems anteriores y reemplazarlos
            $pdo->prepare("DELETE FROM items_solicitud_servicio WHERE solicitud_id = ?")->execute([$solicitudId]);

            foreach ($items as $item) {
                $rubroId = (isset($item['rubro_id']) && $item['rubro_id'] > 0)
                    ? $item['rubro_id']
                    : self::findOrCreateRubro($item['rubro'], $item['rubro_codigo']);

                $fondoId = (isset($item['fondos_id']) && $item['fondos_id'] > 0)
                    ? $item['fondos_id']
                    : self::findOrCreateFondo($item['fondo'], $item['fondo_codigo'] ?? $item['fondo']);

                $funcionId = (isset($item['funcion_id']) && $item['funcion_id'] > 0)
                    ? $item['funcion_id']
                    : self::findOrCreateFuncion($item['funcion'], $item['funcion_codigo']);

                $centroCostoId = (isset($item['centro_costo_id']) && $item['centro_costo_id'] > 0)
                    ? $item['centro_costo_id']
                    : self::findOrCreateCentroCosto($item['centro_costos'], (int) ($item['cc_codigo'] ?? 0));

                $stmtItem = $pdo->prepare("
                    INSERT INTO items_solicitud_servicio
                        (solicitud_id, servicio, cantidad, disponibilidad, rubro_id, fondos_id, funcion_id, centro_costo_id)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmtItem->execute([
                    $solicitudId,
                    $item['servicio'],
                    (int) $item['cantidad'],
                    (float) $item['disponibilidad'],
                    $rubroId,
                    $fondoId,
                    $funcionId,
                    $centroCostoId
                ]);
            }

            // 3. Registrar en historial
            $stmtHist = $pdo->prepare("
                INSERT INTO historial_estados (solicitud_id, estado_nuevo_id, usuario_id, observacion)
                VALUES (?, ?, ?, ?)
            ");
            // Se asume que el estado sigue siendo 'revision' o el actual
            $estadoActual = self::obtenerEstadoId($data['estado'] ?? 'revision');
            $stmtHist->execute([
                $solicitudId,
                $estadoActual,
                $data['usuario_id'],
                'Solicitud editada por el usuario'
            ]);

            $pdo->commit();
            return true;

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('[SolicitudDAO::actualizarCompleta] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una solicitud y todos sus registros relacionados (items, historial, archivos).
     * Usa una transacción para asegurar integridad referencial manual.
     *
     * @param  int $solicitudId
     * @return bool
     */
    public static function eliminar(int $solicitudId): bool
    {
        $pdo = Database::getConnection();

        try {
            // Borrado lógico: cambiamos activo a 0
            $stmt = $pdo->prepare("UPDATE solicitudes SET activo = 0, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$solicitudId]);
            return $stmt->rowCount() > 0;

        } catch (PDOException $e) {
            error_log('[SolicitudDAO::eliminar] ' . $e->getMessage());
            return false;
        }
    }

    // ──────────────────────────────────────────────────────────
    // METRICAS Y REPORTES (DASHBOARD)
    // ──────────────────────────────────────────────────────────

    /** Conteos rápidos para las tarjetas del Dashboard */
    public static function obtenerResumenDashboard(): array
    {
        $pdo = Database::getConnection();
        $res = [
            'total' => 0,
            'revision' => 0,
            'aprobado' => 0,
            'rechazado' => 0,
            'mes_actual' => 0
        ];

        // 1. Totales por estado
        $sql = "SELECT e.nombre, COUNT(s.id) as total 
                FROM solicitudes s 
                JOIN estados_solicitud e ON e.id = s.estado_id 
                WHERE s.activo = 1 
                GROUP BY e.nombre";
        foreach ($pdo->query($sql) as $row) {
            $res[$row['nombre']] = (int)$row['total'];
        }
        $res['total'] = array_sum($res) - $res['mes_actual'];

        // 2. Mes actual
        $startOfMonth = date('Y-m-01');
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM solicitudes WHERE activo = 1 AND fecha_solicitud >= ?");
        $stmt->execute([$startOfMonth]);
        $res['mes_actual'] = (int)$stmt->fetchColumn();

        return $res;
    }

    /** Distribución por estado (Gráfica de Dona) */
    public static function contarSolicitudesPorEstado(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT e.nombre as estado, COUNT(s.id) as total 
                FROM solicitudes s 
                JOIN estados_solicitud e ON e.id = s.estado_id 
                WHERE s.activo = 1 
                GROUP BY e.id";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Tendencia mensual del año actual (Gráfica de Líneas) */
    public static function contarSolicitudesPorMes(): array
    {
        $pdo = Database::getConnection();
        $anio = date('Y');
        $sql = "SELECT MONTH(fecha_solicitud) as mes_num, COUNT(*) as total 
                FROM solicitudes 
                WHERE activo = 1 AND YEAR(fecha_solicitud) = ? 
                GROUP BY MONTH(fecha_solicitud) 
                ORDER BY mes_num ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$anio]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mapear meses numéricos a nombres
        $mesesNombres = [
            1=>"Ene", 2=>"Feb", 3=>"Mar", 4=>"Abr", 5=>"May", 6=>"Jun",
            7=>"Jul", 8=>"Ago", 9=>"Sep", 10=>"Oct", 11=>"Nov", 12=>"Dic"
        ];
        
        $res = [];
        foreach($rows as $r) {
            $res[] = [
                'mes' => $mesesNombres[(int)$r['mes_num']],
                'total' => (int)$r['total']
            ];
        }
        return $res;
    }

    /** Top dependencias con más solicitudes (Gráfica de Barras) */
    public static function contarSolicitudesPorDependencia(int $limite = 10): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT d.nombre as dependencia, COUNT(s.id) as total 
                FROM solicitudes s 
                JOIN usuarios u ON u.id = s.usuario_id 
                JOIN dependencias d ON d.id = u.id_dependecia 
                WHERE s.activo = 1 
                GROUP BY d.id 
                ORDER BY total DESC 
                LIMIT $limite";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Tabla de solicitudes recientes */
    public static function obtenerSolicitudesRecientes(int $limite = 10): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT s.id, s.fecha_solicitud, CONCAT(u.nombre, ' ', u.apellido) as solicitante, 
                       d.nombre as dependencia, e.nombre as estado 
                FROM solicitudes s 
                JOIN usuarios u ON u.id = s.usuario_id 
                JOIN dependencias d ON d.id = u.id_dependecia 
                JOIN estados_solicitud e ON e.id = s.estado_id 
                WHERE s.activo = 1 
                ORDER BY s.created_at DESC 
                LIMIT $limite";
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Consulta filtrada para reportes PDF */
    public static function obtenerSolicitudesParaReporte(array $f): array
    {
        $pdo = Database::getConnection();
        $params = [];
        $sql = "SELECT s.id, s.fecha_solicitud, CONCAT(u.nombre, ' ', u.apellido) as solicitante, 
                       d.nombre as dependencia, e.nombre as estado, s.justificacion
                FROM solicitudes s 
                JOIN usuarios u ON u.id = s.usuario_id 
                JOIN dependencias d ON d.id = u.id_dependecia 
                JOIN estados_solicitud e ON e.id = s.estado_id 
                WHERE s.activo = 1";

        if (!empty($f['fecha_inicio'])) {
            $sql .= " AND s.fecha_solicitud >= ?";
            $params[] = $f['fecha_inicio'];
        }
        if (!empty($f['fecha_fin'])) {
            $sql .= " AND s.fecha_solicitud <= ?";
            $params[] = $f['fecha_fin'];
        }
        if (!empty($f['estado']) && $f['estado'] !== 'todos') {
            $sql .= " AND LOWER(TRIM(e.nombre)) = LOWER(?)";
            $params[] = trim($f['estado']);
        }
        if (!empty($f['dependencia_id'])) {
            $sql .= " AND d.id = ?";
            $params[] = $f['dependencia_id'];
        }

        $sql .= " ORDER BY s.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene solicitudes donde un administrador específico tomó una decisión (aprobó/rechazó).
     */
    public static function obtenerDecididasPorAdmin(int $adminId): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT 
                s.id,
                s.fecha_solicitud                       AS fecha,
                s.justificacion,
                s.created_at                            AS registro_at,
                CONCAT(u.nombre, ' ', u.apellido)       AS nombre,
                d.nombre                                AS dependencia,
                e.nombre                                AS estado
            FROM   solicitudes s
            JOIN   usuarios            u  ON u.id           = s.usuario_id
            JOIN   dependencias        d  ON d.id           = u.id_dependecia
            JOIN   estados_solicitud   e  ON e.id           = s.estado_id
            JOIN   historial_estados   h  ON h.solicitud_id = s.id
            JOIN   estados_solicitud   eh ON eh.id          = h.estado_nuevo_id
            WHERE  h.usuario_id = ? 
              AND  eh.nombre IN ('aprobado', 'rechazado')
              AND  s.activo = 1
            GROUP BY s.id
            ORDER BY s.id DESC
        ");
        $stmt->execute([$adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
