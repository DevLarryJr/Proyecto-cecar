-- Migración: agregar estados intermedios del flujo de gestión
-- Ejecutar una sola vez en la base de datos solicitud_final

INSERT IGNORE INTO estados_solicitud (nombre, descripcion) VALUES 
('en_transito', 'Solicitud en tránsito de gestión'),
('pendiente', 'Pendiente de entrega'),
('entregado', 'Servicio entregado, esperando aceptación final');
