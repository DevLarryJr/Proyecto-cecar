<?php
/**
 * SolicitudModelo.php — Capa de Acceso / Modelo
 * Define la estructura de datos de una Solicitud.
 * Actúa como contenedor de propiedades (entidad).
 */
class Solicitud
{
    public int     $id            = 0;
    public int     $usuarioId     = 0;
    public int     $estadoId      = 1;   // 1 = revision por defecto
    public string  $fechaSolicitud = '';
    public string  $justificacion = '';
    public string  $createdAt     = '';
    public string  $updatedAt     = '';

    // Datos JOIN desde otras tablas (para vistas)
    public string  $nombre        = '';  // usuarios.nombre + apellido
    public string  $dependencia   = '';  // dependencias.nombre
    public string  $cargo         = '';  // cargo.nombre_cargo
    public string  $estado        = 'revision'; // estados_solicitud.nombre
    public ?string $comentarioRevision = null;
    public ?string $decisionAt    = null;
    public ?string $archivo       = null; // nombre del PDF en archivos_adjuntos

    /** @var array Lista de ítems de servicio asociados */
    public array   $serviciosList = [];

    /** @var array Historial de estados */
    public array   $historial     = [];

    /**
     * Convierte el modelo a un array asociativo compatible
     * con el formato que usan las vistas (herencia del sistema JSON).
     */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'nombre'             => $this->nombre,
            'dependencia'        => $this->dependencia,
            'cargo'              => $this->cargo,
            'fecha'              => $this->fechaSolicitud,
            'justificacion'      => $this->justificacion,
            'estado'             => $this->estado,
            'registro_at'        => $this->createdAt,
            'decision_at'        => $this->decisionAt,
            'comentario_revision'=> $this->comentarioRevision,
            'archivo'            => $this->archivo,
            'servicios_count'    => count($this->serviciosList),
            'servicios_list'     => $this->serviciosList,
            'historial'          => $this->historial,
        ];
    }
}
