CREATE DATABASE IF NOT EXISTS gestion_residuos;

USE gestion_residuos;

-- ROLES
CREATE TABLE
    roles (
        id_role INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL
    ) ENGINE = InnoDB;

INSERT INTO
    roles (nombre)
VALUES
    ('Administrador Municipal'),
    ('Coordinador de Rutas'),
    ('Operador Punto Verde'),
    ('Ciudadano'),
    ('Auditor');

-- USUARIOS
CREATE TABLE
    usuarios (
        id_usuario INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE,
        telefono VARCHAR(20),
        password_hash VARCHAR(255),
        foto_perfil VARCHAR(255) DEFAULT 'https://res.cloudinary.com/dbsqzub25/image/upload/v1772951140/user-blue-gradient_78370-4692_othf7b.jpg',
        id_role INT,
        estado ENUM ('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_role) REFERENCES roles (id_role) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- AUDITORIA
CREATE TABLE
    auditoria (
        id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT,
        accion VARCHAR(100),
        tabla_afectada VARCHAR(100),
        id_registro INT,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        detalle TEXT,
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- NOTIFICACIONES
CREATE TABLE
    notificaciones (
        id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT,
        mensaje TEXT,
        tipo VARCHAR(50),
        leida BOOLEAN DEFAULT FALSE,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- DIAS SEMANA
CREATE TABLE
    dias_semana (
        id_dia INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(15) UNIQUE
    ) ENGINE = InnoDB;

INSERT INTO
    dias_semana (nombre)
VALUES
    ('Lunes'),
    ('Martes'),
    ('Miércoles'),
    ('Jueves'),
    ('Viernes'),
    ('Sábado'),
    ('Domingo');

-- ZONAS
CREATE TABLE
    zonas (
        id_zona INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100),
        tipo ENUM ('residencial', 'comercial', 'industrial'),
        latitud DECIMAL(10, 7),
        longitud DECIMAL(10, 7)
    ) ENGINE = InnoDB;

-- RUTAS
CREATE TABLE
    rutas (
        id_ruta INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100),
        id_zona INT,
        lat_inicio DECIMAL(10, 7),
        lon_inicio DECIMAL(10, 7),
        lat_fin DECIMAL(10, 7),
        lon_fin DECIMAL(10, 7),
        distancia_km DECIMAL(8, 2),
        horario_inicio TIME,
        horario_fin TIME,
        tipo_residuo ENUM ('organico', 'inorganico', 'mixto'),
        FOREIGN KEY (id_zona) REFERENCES zonas (id_zona) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- RUTAS DIAS
CREATE TABLE
    rutas_dias (
        id_ruta INT,
        id_dia INT,
        PRIMARY KEY (id_ruta, id_dia),
        FOREIGN KEY (id_ruta) REFERENCES rutas (id_ruta) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (id_dia) REFERENCES dias_semana (id_dia) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- COORDENADAS RUTA
CREATE TABLE
    coordenadas_ruta (
        id_coordenada INT AUTO_INCREMENT PRIMARY KEY,
        id_ruta INT,
        orden INT,
        latitud DECIMAL(10, 7),
        longitud DECIMAL(10, 7),
        FOREIGN KEY (id_ruta) REFERENCES rutas (id_ruta) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- CAMIONES
CREATE TABLE
    camiones (
        id_camion INT AUTO_INCREMENT PRIMARY KEY,
        placa VARCHAR(20) UNIQUE,
        capacidad_toneladas DECIMAL(6, 2),
        estado ENUM ('operativo', 'mantenimiento', 'fuera_servicio'),
        id_conductor INT NULL,
        FOREIGN KEY (id_conductor) REFERENCES usuarios (id_usuario) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- RUTAS PROGRAMADAS
CREATE TABLE
    rutas_programadas (
        id_programacion INT AUTO_INCREMENT PRIMARY KEY,
        id_ruta INT,
        id_camion INT,
        fecha DATE,
        estado ENUM (
            'programada',
            'en_proceso',
            'completada',
            'incompleta'
        ),
        hora_inicio DATETIME,
        hora_fin DATETIME,
        basura_recolectada_ton DECIMAL(8, 2),
        observaciones TEXT,
        FOREIGN KEY (id_ruta) REFERENCES rutas (id_ruta) ON DELETE RESTRICT ON UPDATE CASCADE,
        FOREIGN KEY (id_camion) REFERENCES camiones (id_camion) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- INCIDENCIAS
CREATE TABLE
    incidencias_recoleccion (
        id_incidencia INT AUTO_INCREMENT PRIMARY KEY,
        id_programacion INT,
        descripcion TEXT,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_programacion) REFERENCES rutas_programadas (id_programacion) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- PUNTOS RECOLECCION
CREATE TABLE
    puntos_recoleccion (
        id_punto INT AUTO_INCREMENT PRIMARY KEY,
        id_programacion INT,
        latitud DECIMAL(10, 7),
        longitud DECIMAL(10, 7),
        basura_estimada_kg DECIMAL(8, 2),
        basura_real_kg DECIMAL(8, 2),
        FOREIGN KEY (id_programacion) REFERENCES rutas_programadas (id_programacion) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- PUNTOS VERDES
CREATE TABLE
    puntos_verdes (
        id_punto_verde INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100),
        direccion TEXT,
        latitud DECIMAL(10, 7),
        longitud DECIMAL(10, 7),
        capacidad_m3 DECIMAL(8, 2),
        horario VARCHAR(100),
        id_encargado INT NULL,
        FOREIGN KEY (id_encargado) REFERENCES usuarios (id_usuario) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- TIPOS MATERIAL
CREATE TABLE
    tipos_material (
        id_material INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50)
    ) ENGINE = InnoDB;

-- CONTENEDORES
CREATE TABLE
    contenedores (
        id_contenedor INT AUTO_INCREMENT PRIMARY KEY,
        id_punto_verde INT,
        id_material INT,
        capacidad_kg DECIMAL(8, 2),
        porcentaje_llenado DECIMAL(5, 2),
        FOREIGN KEY (id_punto_verde) REFERENCES puntos_verdes (id_punto_verde) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (id_material) REFERENCES tipos_material (id_material) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- HISTORIAL LLENADO
CREATE TABLE
    historial_llenado_contenedor (
        id_historial INT AUTO_INCREMENT PRIMARY KEY,
        id_contenedor INT,
        porcentaje DECIMAL(5, 2),
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_contenedor) REFERENCES contenedores (id_contenedor) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- ENTREGAS RECICLAJE
CREATE TABLE
    entregas_reciclaje (
        id_entrega INT AUTO_INCREMENT PRIMARY KEY,
        id_contenedor INT,
        ciudadano_codigo VARCHAR(50),
        cantidad_kg DECIMAL(8, 2),
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_contenedor) REFERENCES contenedores (id_contenedor) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- VACIADO CONTENEDORES
CREATE TABLE
    vaciado_contenedores (
        id_vaciado INT AUTO_INCREMENT PRIMARY KEY,
        id_contenedor INT,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        cantidad_retirada_kg DECIMAL(8, 2),
        FOREIGN KEY (id_contenedor) REFERENCES contenedores (id_contenedor) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- DENUNCIAS
CREATE TABLE
    denuncias (
        id_denuncia INT AUTO_INCREMENT PRIMARY KEY,
        nombre_denunciante VARCHAR(100),
        telefono VARCHAR(20),
        email VARCHAR(100),
        descripcion TEXT,
        latitud DECIMAL(10, 7),
        longitud DECIMAL(10, 7),
        tamano ENUM ('pequeno', 'mediano', 'grande'),
        foto VARCHAR(255),
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        estado ENUM (
            'recibida',
            'en_revision',
            'asignada',
            'en_atencion',
            'atendida',
            'cerrada'
        )
    ) ENGINE = InnoDB;

-- HISTORIAL ESTADO DENUNCIA
CREATE TABLE
    historial_estado_denuncia (
        id_historial INT AUTO_INCREMENT PRIMARY KEY,
        id_denuncia INT,
        estado VARCHAR(50),
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        id_usuario INT,
        FOREIGN KEY (id_denuncia) REFERENCES denuncias (id_denuncia) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- FOTOS DENUNCIA
CREATE TABLE
    fotos_denuncia (
        id_foto INT AUTO_INCREMENT PRIMARY KEY,
        id_denuncia INT,
        tipo ENUM ('antes', 'despues'),
        ruta_archivo VARCHAR(255),
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_denuncia) REFERENCES denuncias (id_denuncia) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- CUADRILLAS
CREATE TABLE
    cuadrillas (
        id_cuadrilla INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100),
        estado ENUM ('disponible', 'ocupada', 'inactiva')
    ) ENGINE = InnoDB;

-- INTEGRANTES CUADRILLA
CREATE TABLE
    cuadrilla_integrantes (
        id_cuadrilla INT,
        id_usuario INT,
        rol VARCHAR(50),
        PRIMARY KEY (id_cuadrilla, id_usuario),
        FOREIGN KEY (id_cuadrilla) REFERENCES cuadrillas (id_cuadrilla) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB;

-- ASIGNACION DENUNCIA
CREATE TABLE
    asignacion_denuncia (
        id_asignacion INT AUTO_INCREMENT PRIMARY KEY,
        id_denuncia INT,
        id_cuadrilla INT,
        fecha_programada DATE,
        fecha_inicio DATETIME,
        fecha_fin DATETIME,
        estado ENUM ('pendiente', 'en_proceso', 'finalizado'),
        recursos_estimados TEXT,
        FOREIGN KEY (id_denuncia) REFERENCES denuncias (id_denuncia) ON DELETE CASCADE ON UPDATE CASCADE,
        FOREIGN KEY (id_cuadrilla) REFERENCES cuadrillas (id_cuadrilla) ON DELETE SET NULL ON UPDATE CASCADE
    ) ENGINE = InnoDB;