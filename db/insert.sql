SET
    FOREIGN_KEY_CHECKS = 0;

TRUNCATE roles;

TRUNCATE usuarios;

TRUNCATE zonas;

TRUNCATE rutas;

TRUNCATE rutas_dias;

TRUNCATE camiones;

TRUNCATE tipos_material;

TRUNCATE puntos_verdes;

TRUNCATE contenedores;

TRUNCATE cuadrillas;

TRUNCATE cuadrilla_integrantes;

TRUNCATE denuncias;

SET
    FOREIGN_KEY_CHECKS = 1;

START TRANSACTION;

-- ROLES
INSERT INTO
    roles (nombre)
VALUES
    ('Administrador Municipal'),
    ('Coordinador de Rutas'),
    ('Operador Punto Verde'),
    ('Ciudadano'),
    ('Auditor');

-- USUARIOS (3 por rol)
INSERT INTO
    usuarios (nombre, email, telefono, password_hash, id_role)
VALUES
    (
        'Admin 1',
        'admin1@munixela.gt',
        '50100001',
        'hash',
        1
    ),
    (
        'Admin 2',
        'admin2@munixela.gt',
        '50100002',
        'hash',
        1
    ),
    (
        'Admin 3',
        'admin3@munixela.gt',
        '50100003',
        'hash',
        1
    ),
    (
        'Coord 1',
        'coord1@munixela.gt',
        '50200001',
        'hash',
        2
    ),
    (
        'Coord 2',
        'coord2@munixela.gt',
        '50200002',
        'hash',
        2
    ),
    (
        'Coord 3',
        'coord3@munixela.gt',
        '50200003',
        'hash',
        2
    ),
    (
        'Operador 1',
        'op1@munixela.gt',
        '50300001',
        'hash',
        3
    ),
    (
        'Operador 2',
        'op2@munixela.gt',
        '50300002',
        'hash',
        3
    ),
    (
        'Operador 3',
        'op3@munixela.gt',
        '50300003',
        'hash',
        3
    ),
    (
        'Ciudadano 1',
        'c1@email.com',
        '50400001',
        'hash',
        4
    ),
    (
        'Ciudadano 2',
        'c2@email.com',
        '50400002',
        'hash',
        4
    ),
    (
        'Ciudadano 3',
        'c3@email.com',
        '50400003',
        'hash',
        4
    ),
    (
        'Auditor 1',
        'aud1@munixela.gt',
        '50500001',
        'hash',
        5
    ),
    (
        'Auditor 2',
        'aud2@munixela.gt',
        '50500002',
        'hash',
        5
    ),
    (
        'Auditor 3',
        'aud3@munixela.gt',
        '50500003',
        'hash',
        5
    );

-- ZONAS (Quetzaltenango)
INSERT INTO
    zonas (nombre, tipo, latitud, longitud)
VALUES
    (
        'Zona 1 Centro Histórico',
        'comercial',
        14.8347,
        -91.5180
    ),
    (
        'Zona 3 Minerva',
        'residencial',
        14.8458,
        -91.5239
    ),
    (
        'Zona 5 La Democracia',
        'residencial',
        14.8298,
        -91.5200
    ),
    (
        'Zona 7 Las Rosas',
        'residencial',
        14.8225,
        -91.5128
    ),
    (
        'Zona 9 Parque Industrial',
        'industrial',
        14.8550,
        -91.5350
    );

-- RUTAS
INSERT INTO
    rutas (
        nombre,
        id_zona,
        lat_inicio,
        lon_inicio,
        lat_fin,
        lon_fin,
        distancia_km,
        horario_inicio,
        horario_fin,
        tipo_residuo
    )
VALUES
    (
        'Ruta Centro Norte',
        1,
        14.8347,
        -91.5180,
        14.8450,
        -91.5185,
        5.1,
        '06:00',
        '12:00',
        'mixto'
    ),
    (
        'Ruta Centro Sur',
        1,
        14.8347,
        -91.5180,
        14.8230,
        -91.5182,
        4.8,
        '06:00',
        '12:00',
        'mixto'
    ),
    (
        'Ruta Minerva Este',
        2,
        14.8458,
        -91.5239,
        14.8500,
        -91.5150,
        4.2,
        '07:00',
        '13:00',
        'organico'
    ),
    (
        'Ruta Minerva Oeste',
        2,
        14.8458,
        -91.5239,
        14.8500,
        -91.5300,
        4.5,
        '07:00',
        '13:00',
        'inorganico'
    ),
    (
        'Ruta Democracia Norte',
        3,
        14.8298,
        -91.5200,
        14.8350,
        -91.5250,
        3.9,
        '06:30',
        '12:30',
        'mixto'
    ),
    (
        'Ruta Democracia Sur',
        3,
        14.8298,
        -91.5200,
        14.8220,
        -91.5220,
        4.0,
        '06:30',
        '12:30',
        'organico'
    ),
    (
        'Ruta Las Rosas Este',
        4,
        14.8225,
        -91.5128,
        14.8200,
        -91.5050,
        3.8,
        '07:00',
        '12:00',
        'mixto'
    ),
    (
        'Ruta Las Rosas Oeste',
        4,
        14.8225,
        -91.5128,
        14.8200,
        -91.5200,
        3.6,
        '07:00',
        '12:00',
        'mixto'
    ),
    (
        'Ruta Industrial',
        5,
        14.8550,
        -91.5350,
        14.8600,
        -91.5400,
        6.2,
        '05:30',
        '11:30',
        'inorganico'
    );

-- DIAS DE RUTA
INSERT INTO
    rutas_dias
VALUES
    (1, 1),
    (1, 3),
    (1, 5),
    (2, 2),
    (2, 4),
    (3, 1),
    (3, 4),
    (4, 2),
    (4, 5),
    (5, 1),
    (5, 3),
    (6, 2),
    (6, 4),
    (7, 1),
    (7, 5),
    (8, 3),
    (8, 6),
    (9, 2),
    (9, 4);

-- CAMIONES
INSERT INTO
    camiones (placa, capacidad_toneladas, estado, id_conductor)
VALUES
    ('XELA-001', 10, 'operativo', 4),
    ('XELA-002', 12, 'operativo', 5),
    ('XELA-003', 9, 'operativo', 6),
    ('XELA-004', 11, 'mantenimiento', 4),
    ('XELA-005', 8, 'operativo', 5);

-- TIPOS MATERIAL
INSERT INTO
    tipos_material (nombre)
VALUES
    ('Papel y cartón'),
    ('Plástico'),
    ('Vidrio'),
    ('Metal'),
    ('Orgánico');

-- PUNTOS VERDES
INSERT INTO
    puntos_verdes (
        nombre,
        direccion,
        latitud,
        longitud,
        capacidad_m3,
        horario,
        id_encargado
    )
VALUES
    (
        'Punto Verde Parque Central',
        'Parque Central',
        14.8347,
        -91.5180,
        20,
        '08:00-17:00',
        7
    ),
    (
        'Punto Verde Minerva',
        'Templo a Minerva',
        14.8458,
        -91.5239,
        18,
        '08:00-17:00',
        7
    ),
    (
        'Punto Verde Democracia',
        'Parque Benito Juárez',
        14.8298,
        -91.5200,
        15,
        '08:00-17:00',
        8
    ),
    (
        'Punto Verde Las Rosas',
        'Zona 7',
        14.8225,
        -91.5128,
        15,
        '08:00-17:00',
        8
    ),
    (
        'Punto Verde Interplaza',
        'Centro Comercial Interplaza',
        14.8505,
        -91.5302,
        22,
        '08:00-18:00',
        9
    ),
    (
        'Punto Verde Universidad',
        'Zona universitaria',
        14.8482,
        -91.5245,
        20,
        '08:00-17:00',
        9
    ),
    (
        'Punto Verde Mercado',
        'Mercado La Democracia',
        14.8300,
        -91.5215,
        17,
        '08:00-17:00',
        7
    );

-- CONTENEDORES
INSERT INTO
    contenedores (
        id_punto_verde,
        id_material,
        capacidad_kg,
        porcentaje_llenado
    )
VALUES
    (1, 1, 500, 40),
    (1, 2, 500, 30),
    (2, 3, 400, 20),
    (3, 1, 450, 50),
    (4, 2, 600, 35),
    (5, 4, 300, 25),
    (6, 1, 400, 10),
    (7, 5, 500, 45);

-- CUADRILLAS
INSERT INTO
    cuadrillas (nombre, estado)
VALUES
    ('Cuadrilla Norte', 'disponible'),
    ('Cuadrilla Centro', 'disponible'),
    ('Cuadrilla Sur', 'ocupada');

-- INTEGRANTES
INSERT INTO
    cuadrilla_integrantes
VALUES
    (1, 4, 'jefe'),
    (1, 5, 'operario'),
    (2, 6, 'jefe'),
    (2, 7, 'operario'),
    (3, 8, 'jefe'),
    (3, 9, 'operario');

-- DENUNCIAS
INSERT INTO
    denuncias (
        nombre_denunciante,
        telefono,
        email,
        descripcion,
        latitud,
        longitud,
        tamano,
        foto,
        estado
    )
VALUES
    (
        'Juan Perez',
        '55590001',
        '[juan@email.com](mailto:juan@email.com)',
        'Basura cerca parque central',
        14.8350,
        -91.5175,
        'mediano',
        'foto1.jpg',
        'recibida'
    ),
    (
        'Ana Lopez',
        '55590002',
        '[ana@email.com](mailto:ana@email.com)',
        'Basurero clandestino',
        14.8460,
        -91.5235,
        'grande',
        'foto2.jpg',
        'en_revision'
    ),
    (
        'Carlos Diaz',
        '55590003',
        '[carlos@email.com](mailto:carlos@email.com)',
        'Desechos en terreno baldío',
        14.8290,
        -91.5200,
        'mediano',
        'foto3.jpg',
        'asignada'
    ),
    (
        'Maria Gomez',
        '55590004',
        '[maria@email.com](mailto:maria@email.com)',
        'Basura cerca escuela',
        14.8230,
        -91.5120,
        'mediano',
        'foto4.jpg',
        'recibida'
    ),
    (
        'Luis Morales',
        '55590005',
        '[luis@email.com](mailto:luis@email.com)',
        'Restos construcción',
        14.8280,
        -91.5190,
        'grande',
        'foto5.jpg',
        'en_atencion'
    ),
    (
        'Pedro Ramirez',
        '55590006',
        '[pedro@email.com](mailto:pedro@email.com)',
        'Bolsa basura abandonada',
        14.8340,
        -91.5205,
        'pequeno',
        'foto6.jpg',
        'recibida'
    ),
    (
        'Laura Castillo',
        '55590007',
        '[laura@email.com](mailto:laura@email.com)',
        'Basura en parque',
        14.8335,
        -91.5160,
        'mediano',
        'foto7.jpg',
        'recibida'
    ),
    (
        'Jose Mendez',
        '55590008',
        '[jose@email.com](mailto:jose@email.com)',
        'Desechos domésticos',
        14.8320,
        -91.5185,
        'pequeno',
        'foto8.jpg',
        'recibida'
    ),
    (
        'Sandra Ruiz',
        '55590009',
        '[sandra@email.com](mailto:sandra@email.com)',
        'Basura cerca escuela',
        14.8365,
        -91.5200,
        'mediano',
        'foto9.jpg',
        'en_revision'
    ),
    (
        'Miguel Torres',
        '55590010',
        '[miguel@email.com](mailto:miguel@email.com)',
        'Contaminacion barranco',
        14.8210,
        -91.5150,
        'grande',
        'foto10.jpg',
        'asignada'
    ),
    (
        'Carmen Soto',
        '55590011',
        '[carmen@email.com](mailto:carmen@email.com)',
        'Basura acumulada',
        14.8330,
        -91.5170,
        'mediano',
        'foto11.jpg',
        'recibida'
    ),
    (
        'David Rojas',
        '55590012',
        '[david@email.com](mailto:david@email.com)',
        'Desechos organicos',
        14.8355,
        -91.5195,
        'pequeno',
        'foto12.jpg',
        'recibida'
    ),
    (
        'Rosa Fuentes',
        '55590013',
        '[rosa@email.com](mailto:rosa@email.com)',
        'Basura calle principal',
        14.8370,
        -91.5185,
        'mediano',
        'foto13.jpg',
        'recibida'
    ),
    (
        'Jorge Leon',
        '55590014',
        '[jorge@email.com](mailto:jorge@email.com)',
        'Escombros abandonados',
        14.8205,
        -91.5110,
        'grande',
        'foto14.jpg',
        'en_revision'
    ),
    (
        'Patricia Vega',
        '55590015',
        '[patricia@email.com](mailto:patricia@email.com)',
        'Basura cerca mercado',
        14.8305,
        -91.5210,
        'mediano',
        'foto15.jpg',
        'recibida'
    ),
    (
        'Andres Cruz',
        '55590016',
        '[andres@email.com](mailto:andres@email.com)',
        'Residuos industriales',
        14.8560,
        -91.5340,
        'grande',
        'foto16.jpg',
        'asignada'
    ),
    (
        'Daniela Ortiz',
        '55590017',
        '[daniela@email.com](mailto:daniela@email.com)',
        'Basura parada bus',
        14.8330,
        -91.5165,
        'pequeno',
        'foto17.jpg',
        'recibida'
    ),
    (
        'Fernando Pineda',
        '55590018',
        '[fernando@email.com](mailto:fernando@email.com)',
        'Desechos parque infantil',
        14.8360,
        -91.5190,
        'mediano',
        'foto18.jpg',
        'recibida'
    );

COMMIT;