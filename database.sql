-- 1. TABLAS DE CATÁLOGO (Alineadas con el diagrama)

CREATE TABLE IF NOT EXISTS dependencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(50),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS estados_solicitud (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS rubros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS fondos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS funcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS centros_costo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    codigo INT NOT NULL UNIQUE,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS cargo (
    idcargo INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cargo VARCHAR(45) NOT NULL,
    descripcion_cargo VARCHAR(45),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255),
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- 2. TABLAS DE ENTIDADES PRINCIPALES

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    apellido VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    id_dependecia INT,
    id_cargo INT,
    telefono VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuario_dependencia FOREIGN KEY (id_dependecia) REFERENCES dependencias(id),
    CONSTRAINT fk_usuario_cargo FOREIGN KEY (id_cargo) REFERENCES cargo(idcargo)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS usuario_rol (
    usuario_id INT NOT NULL,
    rol_id INT NOT NULL,
    asignado_desde TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (usuario_id, rol_id),
    CONSTRAINT fk_rol_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_rol_id FOREIGN KEY (rol_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    estado_id INT NOT NULL,
    fecha_solicitud DATE NOT NULL,
    justificacion TEXT NOT NULL,
    id_fondo INT,
    id_funcion INT,
    id_centro_costo INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitud_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    CONSTRAINT fk_solicitud_estado FOREIGN KEY (estado_id) REFERENCES estados_solicitud(id),
    CONSTRAINT fk_solicitud_fondo FOREIGN KEY (id_fondo) REFERENCES fondos(id),
    CONSTRAINT fk_solicitud_funcion FOREIGN KEY (id_funcion) REFERENCES funcion(id),
    CONSTRAINT fk_solicitud_cc FOREIGN KEY (id_centro_costo) REFERENCES centros_costo(id)
) ENGINE=InnoDB;

-- 3. TABLAS DE DETALLE Y RELACIONES

CREATE TABLE IF NOT EXISTS items_solicitud_servicio (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    servicio TEXT NOT NULL,
    cantidad INT NOT NULL,
    disponibilidad DECIMAL(18,2) NOT NULL,
    rubro_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_item_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
    CONSTRAINT fk_item_rubro FOREIGN KEY (rubro_id) REFERENCES rubros(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS items_solicitud_refrigerio_almuerzo (
    iditems_solicitud_refrigerio_almuerzo INT AUTO_INCREMENT PRIMARY KEY,
    dia ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'),
    hora VARCHAR(15),
    cantidad INT,
    alimentos VARCHAR(100),
    bebidas VARCHAR(100),
    tipo_solicitud ENUM('Refrigerio','Almuerzo','Cena'),
    requiere_mesero ENUM('Si','No'),
    lugar_entrega VARCHAR(100),
    id_solicitud INT,
    CONSTRAINT fk_refrigerio_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS lugar (
    idlugar INT AUTO_INCREMENT PRIMARY KEY,
    nombre_lugar VARCHAR(100),
    ubicacion VARCHAR(100)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS Eventos (
    idEventos INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100),
    lugar VARCHAR(45),
    dias_evento DECIMAL(1),
    fecha_inicio DATE,
    fecha_finalizacion DATE,
    objeto_evento VARCHAR(100),
    id_solicitud INT,
    id_lugar INT,
    CONSTRAINT fk_evento_solicitud FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id) ON DELETE CASCADE,
    CONSTRAINT fk_evento_lugar FOREIGN KEY (id_lugar) REFERENCES lugar(idlugar)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS historial_estados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    estado_nuevo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    observacion TEXT,
    notificado TINYINT(1) DEFAULT 0,
    fecha_notificado TIMESTAMP NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE,
    CONSTRAINT fk_historial_estado FOREIGN KEY (estado_nuevo_id) REFERENCES estados_solicitud(id),
    CONSTRAINT fk_historial_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS archivos_adjuntos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tipo_mime VARCHAR(100),
    tamano_bytes INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_archivo_solicitud FOREIGN KEY (solicitud_id) REFERENCES solicitudes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Datos iniciales (Capa de Datos)
INSERT IGNORE INTO roles (id, nombre, descripcion) VALUES 
(1, 'Administrador', 'Control total del sistema'),
(2, 'Usuario', 'Solicitante de servicios');

INSERT IGNORE INTO dependencias (id, nombre) VALUES (1, 'Tecnologías de Información');

INSERT IGNORE INTO cargo (idcargo, nombre_cargo) VALUES (1, 'Administrador de Sistema');

-- Usuario inicial: admin@cecar.edu.co / pass: admin123
INSERT IGNORE INTO usuarios (id, nombre, apellido, email, password_hash, id_dependecia, id_cargo) 
VALUES (1, 'Admin', 'CECAR', 'admin@cecar.edu.co', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, 1);

INSERT IGNORE INTO usuario_rol (usuario_id, rol_id) VALUES (1, 1);

INSERT IGNORE INTO estados_solicitud (nombre, descripcion) VALUES 
('revision', 'Esperando aprobación técnica'), 
('en_transito', 'Solicitud en tránsito de gestión'),
('pendiente', 'Pendiente de entrega'),
('entregado', 'Servicio entregado, esperando aceptación final'),
('aprobado', 'Solicitud aceptada'), 
('rechazado', 'Solicitud denegada');
