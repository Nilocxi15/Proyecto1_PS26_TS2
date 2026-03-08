CREATE DATABASE IF NOT EXISTS gestion_residuos;

USE gestion_residuos;

-- Roles
CREATE TABLE
    roles (
        id_role INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50) NOT NULL
    );

INSERT INTO
    roles (nombre)
VALUES
    ('Administrador Municipal'),
    ('Coordinador de Rutas'),
    ('Operador Punto Verde'),
    ('Ciudadano'),
    ('Auditor');

-- Usuarios
CREATE TABLE
    usuarios (
        id_usuario INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE,
        telefono VARCHAR(20),
        password_hash VARCHAR(255),
        foto_perfil VARCHAR(255),
        id_role INT,
        estado ENUM ('activo', 'inactivo') DEFAULT 'activo',
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_role) REFERENCES roles (id_role)
    );

-- Auditoria de acciones
CREATE TABLE
    auditoria (
        id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT,
        accion VARCHAR(100),
        tabla_afectada VARCHAR(100),
        id_registro INT,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        detalle TEXT,
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    );

-- Notificaciones
CREATE TABLE
    notificaciones (
        id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT,
        mensaje TEXT,
        tipo VARCHAR(50),
        leida BOOLEAN DEFAULT FALSE,
        fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    );

-- Tabla auxiliar para los días de la semana
CREATE TABLE
    dias_semana (
        id_dia INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(15) UNIQUE
    );

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

-- Zonas
CREATE TABLE
    zonas (
        id_zona INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100),
        tipo ENUM ('residencial', 'comercial', 'industrial'),
        latitud DECIMAL(10, 7),
        longitud DECIMAL(10, 7)
    );

-- Rutas
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
        FOREIGN KEY (id_zona) REFERENCES zonas (id_zona)
    );

-- Tabla para vincular las rutas con los días de la semana
CREATE TABLE
    rutas_dias (
        id_ruta INT,
        id_dia INT,
        PRIMARY KEY (id_ruta, id_dia),
        FOREIGN KEY (id_ruta) REFERENCES rutas (id_ruta),
        FOREIGN KEY (id_dia) REFERENCES dias_semana (id_dia)
    );

-- Coordenadas de las rutas
CREATE TABLE
    coordenadas_ruta (
        id_coordenada INT AUTO_INCREMENT PRIMARY KEY,
        id_ruta INT,
        orden INT,
        latitud DECIMAL(10, 7),
        longitud DECIMAL(10, 7),
        FOREIGN KEY (id_ruta) REFERENCES rutas (id_ruta)
    );

-- Camiones
CREATE TABLE
    camiones (
        id_camion INT AUTO_INCREMENT PRIMARY KEY,
        placa VARCHAR(20) UNIQUE,
        capacidad_toneladas DECIMAL(6, 2),
        estado ENUM ('operativo', 'mantenimiento', 'fuera_servicio'),
        id_conductor INT,
        FOREIGN KEY (id_conductor) REFERENCES usuarios (id_usuario)
    );

-- Rutas programadas
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
        FOREIGN KEY (id_ruta) REFERENCES rutas (id_ruta),
        FOREIGN KEY (id_camion) REFERENCES camiones (id_camion)
    );

-- Incidencias de recolección
CREATE TABLE
    incidencias_recoleccion (
        id_incidencia INT AUTO_INCREMENT PRIMARY KEY,
        id_programacion INT,
        descripcion TEXT,
        fecha DATETIME,
        FOREIGN KEY (id_programacion) REFERENCES rutas_programadas (id_programacion)
    );

-- Puntos de recolección
CREATE TABLE
    puntos_recoleccion (
        id_punto INT AUTO_INCREMENT PRIMARY KEY,
        id_programacion INT,
        latitud DECIMAL(10, 7),
        longitud DECIMAL(10, 7),
        basura_estimada_kg DECIMAL(8, 2),
        basura_real_kg DECIMAL(8, 2),
        FOREIGN KEY (id_programacion) REFERENCES rutas_programadas (id_programacion)
    );

-- Puntos verdes
CREATE TABLE
    puntos_verdes (
        id_punto_verde INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100),
        direccion TEXT,
        latitud DECIMAL(10, 7),
        longitud DECIMAL(10, 7),
        capacidad_m3 DECIMAL(8, 2),
        horario VARCHAR(100),
        id_encargado INT,
        FOREIGN KEY (id_encargado) REFERENCES usuarios (id_usuario)
    );

-- Materiales
CREATE TABLE
    tipos_material (
        id_material INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(50)
    );

-- Contenedores
CREATE TABLE
    contenedores (
        id_contenedor INT AUTO_INCREMENT PRIMARY KEY,
        id_punto_verde INT,
        id_material INT,
        capacidad_kg DECIMAL(8, 2),
        porcentaje_llenado DECIMAL(5, 2),
        FOREIGN KEY (id_punto_verde) REFERENCES puntos_verdes (id_punto_verde),
        FOREIGN KEY (id_material) REFERENCES tipos_material (id_material)
    );

-- Historial de llenado
CREATE TABLE
    historial_llenado_contenedor (
        id_historial INT AUTO_INCREMENT PRIMARY KEY,
        id_contenedor INT,
        porcentaje DECIMAL(5, 2),
        fecha DATETIME,
        FOREIGN KEY (id_contenedor) REFERENCES contenedores (id_contenedor)
    );

-- Entregas de reciclaje
CREATE TABLE
    entregas_reciclaje (
        id_entrega INT AUTO_INCREMENT PRIMARY KEY,
        id_contenedor INT,
        ciudadano_codigo VARCHAR(50),
        cantidad_kg DECIMAL(8, 2),
        fecha DATETIME,
        FOREIGN KEY (id_contenedor) REFERENCES contenedores (id_contenedor)
    );

-- Vaciado de contenedores
CREATE TABLE
    vaciado_contenedores (
        id_vaciado INT AUTO_INCREMENT PRIMARY KEY,
        id_contenedor INT,
        fecha DATETIME,
        cantidad_retirada_kg DECIMAL(8, 2),
        FOREIGN KEY (id_contenedor) REFERENCES contenedores (id_contenedor)
    );

-- Denuncias
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
        fecha DATETIME,
        estado ENUM (
            'recibida',
            'en_revision',
            'asignada',
            'en_atencion',
            'atendida',
            'cerrada'
        )
    );

-- Historial de estados de denuncias
CREATE TABLE
    historial_estado_denuncia (
        id_historial INT AUTO_INCREMENT PRIMARY KEY,
        id_denuncia INT,
        estado VARCHAR(50),
        fecha DATETIME,
        id_usuario INT,
        FOREIGN KEY (id_denuncia) REFERENCES denuncias (id_denuncia),
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    );

-- Fotos de denuncias
CREATE TABLE
    fotos_denuncia (
        id_foto INT AUTO_INCREMENT PRIMARY KEY,
        id_denuncia INT,
        tipo ENUM ('antes', 'despues'),
        ruta_archivo VARCHAR(255),
        fecha DATETIME,
        FOREIGN KEY (id_denuncia) REFERENCES denuncias (id_denuncia)
    );

-- Cuadrillas
CREATE TABLE
    cuadrillas (
        id_cuadrilla INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100),
        estado ENUM ('disponible', 'ocupada', 'inactiva')
    );

-- Integrantes de cuadrillas
CREATE TABLE
    cuadrilla_integrantes (
        id_cuadrilla INT,
        id_usuario INT,
        rol VARCHAR(50),
        PRIMARY KEY (id_cuadrilla, id_usuario),
        FOREIGN KEY (id_cuadrilla) REFERENCES cuadrillas (id_cuadrilla),
        FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
    );

-- Asignación de denuncias
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
        FOREIGN KEY (id_denuncia) REFERENCES denuncias (id_denuncia),
        FOREIGN KEY (id_cuadrilla) REFERENCES cuadrillas (id_cuadrilla)
    );