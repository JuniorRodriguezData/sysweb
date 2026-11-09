-- =====================================================================
-- SysWeb - Sistema Web de Compras, Ventas y Stock
-- Lenguaje de Programación III - UTIC | Junior Rodriguez
--
-- Basado en el script de la cátedra (backp.sql), con estas mejoras:
--   * Motor InnoDB (claves foráneas y transacciones) y codificación utf8mb4.
--   * usuarios.id_user como clave primaria AUTO_INCREMENT y username único.
--   * password VARCHAR(255) para guardar hashes de password_hash() (no MD5).
--
-- Ejecutar:  C:\xampp\mysql\bin\mysql.exe -u root --default-character-set=utf8mb4 < sql\sysweb.sql
-- =====================================================================

DROP DATABASE IF EXISTS sysweb;
CREATE DATABASE sysweb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sysweb;

-- ---------------------------------------------------------------------
-- Usuarios del sistema
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id_user         INT          NOT NULL AUTO_INCREMENT,
    username        VARCHAR(50)  NOT NULL,
    name_user       VARCHAR(150) NOT NULL,
    password        VARCHAR(255) NOT NULL,
    email           VARCHAR(150) NULL,
    telefono        VARCHAR(20)  NULL,
    foto            VARCHAR(300) NOT NULL DEFAULT '',
    permisos_acceso VARCHAR(30)  NOT NULL,          -- Super Admin | Compras | Ventas
    status          VARCHAR(15)  NOT NULL DEFAULT 'activo',   -- activo | bloqueado
    PRIMARY KEY (id_user),
    UNIQUE KEY uq_usuarios_username (username)
) ENGINE = InnoDB;

-- Usuarios de prueba (contraseñas: Admin2026, Compras2026, Ventas2026)
INSERT INTO usuarios (username, name_user, password, email, telefono, foto, permisos_acceso, status) VALUES
('admin',   'Junior Rodriguez', '$2y$10$SjeQiW6z9UQAJ6WI9YREX.6mxVmIj17V8CsKaHcuQIPoL0GH2S2iG', 'admin@sysweb.com.py',   '0981000001', '', 'Super Admin', 'activo'),
('compras', 'Usuario Compras',  '$2y$10$iNpdHV4aDCj8cQ0piFG9Wu7kJisEeMlIFm3MQ0AMaZIz1ztUk3wWC', 'compras@sysweb.com.py', '0981000002', '', 'Compras',     'activo'),
('ventas',  'Usuario Ventas',   '$2y$10$/y4bTYGL3yhpghz4jD2E/eDsWNNhWD.fWrT9QgIbIW7wmxfoCvt8m', 'ventas@sysweb.com.py',  '0981000003', '', 'Ventas',      'activo');

-- ---------------------------------------------------------------------
-- Referenciales generales
-- ---------------------------------------------------------------------
CREATE TABLE departamento (
    id_departamento INT         NOT NULL,
    dep_descripcion VARCHAR(35) NULL,
    PRIMARY KEY (id_departamento)
) ENGINE = InnoDB;

CREATE TABLE ciudad (
    cod_ciudad      INT         NOT NULL,
    descrip_ciudad  VARCHAR(25) NULL,
    id_departamento INT         NOT NULL,
    PRIMARY KEY (cod_ciudad),
    FOREIGN KEY (id_departamento) REFERENCES departamento (id_departamento)
        ON UPDATE CASCADE ON DELETE NO ACTION
) ENGINE = InnoDB;

CREATE TABLE clientes (
    id_cliente    INT         NOT NULL,
    ci_ruc        VARCHAR(10) NOT NULL,
    cli_nombre    VARCHAR(30) NOT NULL,
    cli_apellido  VARCHAR(50) NOT NULL,
    cli_direccion VARCHAR(50) NULL,
    cli_telefono  VARCHAR(20) NULL,              -- texto: un INT pierde el 0 inicial (0981...)
    cod_ciudad    INT         NULL,
    PRIMARY KEY (id_cliente),
    UNIQUE KEY uq_clientes_ci_ruc (ci_ruc),
    FOREIGN KEY (cod_ciudad) REFERENCES ciudad (cod_ciudad)
        ON UPDATE CASCADE ON DELETE NO ACTION
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Referenciales de compras y stock
-- ---------------------------------------------------------------------
CREATE TABLE deposito (
    cod_deposito INT         NOT NULL,
    descrip      VARCHAR(50) NOT NULL,
    PRIMARY KEY (cod_deposito)
) ENGINE = InnoDB;

CREATE TABLE u_medida (
    id_u_medida INT         NOT NULL,
    u_descrip   VARCHAR(20) NOT NULL,
    PRIMARY KEY (id_u_medida)
) ENGINE = InnoDB;

CREATE TABLE tipo_producto (
    cod_tipo_prod INT         NOT NULL,
    t_p_descrip   VARCHAR(50) NOT NULL,
    PRIMARY KEY (cod_tipo_prod)
) ENGINE = InnoDB;

CREATE TABLE producto (
    cod_producto  INT         NOT NULL,
    cod_tipo_prod INT         NOT NULL,
    id_u_medida   INT         NOT NULL,
    p_descrip     VARCHAR(50) NOT NULL,
    precio        INT         NOT NULL,
    PRIMARY KEY (cod_producto),
    FOREIGN KEY (cod_tipo_prod) REFERENCES tipo_producto (cod_tipo_prod),
    FOREIGN KEY (id_u_medida)   REFERENCES u_medida (id_u_medida)
) ENGINE = InnoDB;

CREATE TABLE proveedor (
    cod_proveedor INT         NOT NULL,
    razon_social  VARCHAR(75) NOT NULL,
    ruc           VARCHAR(9)  NOT NULL,
    direccion     VARCHAR(50) NULL,
    telefono      INT         NOT NULL,
    PRIMARY KEY (cod_proveedor)
) ENGINE = InnoDB;

CREATE TABLE stock (
    cod_deposito INT NOT NULL,
    cod_producto INT NOT NULL,
    cantidad     INT NOT NULL,
    PRIMARY KEY (cod_deposito, cod_producto),
    FOREIGN KEY (cod_deposito) REFERENCES deposito (cod_deposito),
    FOREIGN KEY (cod_producto) REFERENCES producto (cod_producto)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Movimientos: compras y ventas
-- ---------------------------------------------------------------------
CREATE TABLE compra (
    cod_compra    INT         NOT NULL,
    cod_proveedor INT         NOT NULL,
    nro_factura   VARCHAR(25) NOT NULL,
    fecha         DATE        NOT NULL,
    estado        VARCHAR(15) NOT NULL,
    cod_deposito  INT         NOT NULL,
    hora          TIME        NOT NULL,
    total_compra  INT         NULL,
    id_user       INT         NULL,
    PRIMARY KEY (cod_compra),
    FOREIGN KEY (cod_deposito)  REFERENCES deposito (cod_deposito) ON UPDATE CASCADE,
    FOREIGN KEY (cod_proveedor) REFERENCES proveedor (cod_proveedor) ON UPDATE CASCADE,
    FOREIGN KEY (id_user)       REFERENCES usuarios (id_user)
) ENGINE = InnoDB;

CREATE TABLE detalle_compra (
    cod_producto INT NOT NULL,
    cod_compra   INT NOT NULL,
    cod_deposito INT NOT NULL,
    precio       INT NOT NULL,
    cantidad     INT NOT NULL,
    PRIMARY KEY (cod_producto, cod_compra),
    FOREIGN KEY (cod_compra)   REFERENCES compra (cod_compra),
    FOREIGN KEY (cod_deposito) REFERENCES deposito (cod_deposito),
    FOREIGN KEY (cod_producto) REFERENCES producto (cod_producto)
) ENGINE = InnoDB;

CREATE TABLE venta (
    cod_venta   INT         NOT NULL,
    id_cliente  INT         NOT NULL,
    fecha       DATE        NOT NULL,
    total_venta INT         NOT NULL,
    estado      VARCHAR(15) NOT NULL,
    hora        TIME        NOT NULL,
    nro_factura INT         NULL,
    PRIMARY KEY (cod_venta),
    FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente)
) ENGINE = InnoDB;

CREATE TABLE det_venta (
    cod_producto    INT NOT NULL,
    cod_venta       INT NOT NULL,
    cod_deposito    INT NOT NULL,
    det_precio_unit INT NOT NULL,
    det_cantidad    INT NOT NULL,
    PRIMARY KEY (cod_producto, cod_venta),
    FOREIGN KEY (cod_deposito) REFERENCES deposito (cod_deposito),
    FOREIGN KEY (cod_producto) REFERENCES producto (cod_producto),
    FOREIGN KEY (cod_venta)    REFERENCES venta (cod_venta)
) ENGINE = InnoDB;

-- Tabla temporal para armar el detalle de una compra/venta antes de guardarla
CREATE TABLE tmp (
    id_tmp       INT          NOT NULL AUTO_INCREMENT,
    id_producto  INT          NULL,
    cantidad_tmp INT          NULL,
    precio_tmp   INT          NULL,
    session_id   VARCHAR(255) NULL,
    PRIMARY KEY (id_tmp),
    KEY idx_tmp_session (session_id)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Proyecto 10 - Vistas de la cátedra (con estado agregado, para mostrar compras anuladas)
-- ---------------------------------------------------------------------
CREATE VIEW v_compras AS
SELECT comp.cod_compra,
       prov.cod_proveedor, prov.razon_social,
       dep.cod_deposito, dep.descrip,
       comp.nro_factura, comp.fecha, comp.hora, comp.total_compra, comp.estado,
       usu.id_user, usu.name_user
FROM compra comp
JOIN proveedor prov ON comp.cod_proveedor = prov.cod_proveedor
JOIN deposito  dep  ON comp.cod_deposito  = dep.cod_deposito
JOIN usuarios  usu  ON comp.id_user       = usu.id_user;

CREATE VIEW v_det_compra AS
SELECT comp.cod_compra,
       pro.cod_producto, tpro.t_p_descrip, um.u_descrip, pro.p_descrip,
       det.precio, det.cantidad
FROM detalle_compra det
JOIN compra        comp ON det.cod_compra    = comp.cod_compra
JOIN producto      pro  ON det.cod_producto  = pro.cod_producto
JOIN tipo_producto tpro ON pro.cod_tipo_prod = tpro.cod_tipo_prod
JOIN u_medida      um   ON pro.id_u_medida   = um.id_u_medida;

-- Proyecto 14 - Stock por depósito y producto
CREATE VIEW v_stock AS
SELECT dep.cod_deposito, dep.descrip AS deposito,
       pro.cod_producto, pro.p_descrip, tpro.t_p_descrip, um.u_descrip, pro.precio,
       st.cantidad
FROM stock st
JOIN deposito      dep  ON st.cod_deposito   = dep.cod_deposito
JOIN producto      pro  ON st.cod_producto   = pro.cod_producto
JOIN tipo_producto tpro ON pro.cod_tipo_prod = tpro.cod_tipo_prod
JOIN u_medida      um   ON pro.id_u_medida   = um.id_u_medida;

-- ---------------------------------------------------------------------
-- Proyecto 13 - Trigger de la cátedra: después de registrar una compra se vacía tmp.
-- Mejora: el original hacía "DELETE FROM tmp" y borraba también lo que estaban cargando
-- otros usuarios. PHP guarda el id de sesión en la variable @tmp_session antes del INSERT
-- y el trigger borra solo los productos temporales de esa sesión.
-- ---------------------------------------------------------------------
DELIMITER $$
CREATE TRIGGER borrar_temp AFTER INSERT ON compra
FOR EACH ROW
BEGIN
    -- COLLATE: la variable toma la intercalación de la conexión y la columna usa utf8mb4_unicode_ci
    DELETE FROM tmp WHERE session_id = @tmp_session COLLATE utf8mb4_unicode_ci;
END$$
DELIMITER ;

-- ---------------------------------------------------------------------
-- Datos de referencia mínimos
-- ---------------------------------------------------------------------
INSERT INTO departamento VALUES (0, 'Asunción (Capital)'), (11, 'Central'), (7, 'Itapúa'), (10, 'Alto Paraná');
INSERT INTO ciudad VALUES (1, 'Asunción', 0), (2, 'San Lorenzo', 11), (3, 'Luque', 11), (4, 'Encarnación', 7), (5, 'Ciudad del Este', 10);
INSERT INTO clientes VALUES
(1, '4567890', 'María', 'González', 'Avda. Mcal. López 1234', '0981123456', 1),
(2, '80012345-6', 'Comercial', 'El Sol S.A.', 'Ruta 2 km 15', '021555666', 2),
(3, '3214567', 'Carlos', 'Benítez', 'Calle Itá 456', '0971987654', 4);
INSERT INTO deposito VALUES (1, 'Depósito Central'), (2, 'Depósito Sucursal Luque');
INSERT INTO u_medida VALUES (1, 'Unidad'), (2, 'Caja'), (3, 'Kilogramo'), (4, 'Resma');
INSERT INTO tipo_producto VALUES (1, 'Informática'), (2, 'Librería'), (3, 'Limpieza');

INSERT INTO proveedor VALUES
(1, 'Distribuidora Tecno S.A.',    '80045678', 'Avda. Eusebio Ayala 2500', 21500100),
(2, 'Papelera Guaraní S.R.L.',     '80098765', 'Ruta Mcal. Estigarribia km 9', 21600200),
(3, 'Importadora del Este S.A.',   '80011223', 'Avda. San Blas 150, CDE', 61500300);

INSERT INTO producto (cod_producto, cod_tipo_prod, id_u_medida, p_descrip, precio) VALUES
(1, 1, 1, 'Mouse inalámbrico',          85000),
(2, 1, 1, 'Teclado USB',                65000),
(3, 1, 1, 'Monitor 24 pulgadas',      1200000),
(4, 1, 1, 'Pendrive 64 GB',             55000),
(5, 1, 1, 'Cable HDMI 2 m',             30000),
(6, 2, 4, 'Papel A4 75 g',              38000),
(7, 2, 2, 'Bolígrafos azules x 50',     45000),
(8, 2, 1, 'Cuaderno universitario',     18000),
(9, 2, 1, 'Carpeta archivadora',        22000),
(10, 3, 3, 'Detergente en polvo',       25000),
(11, 3, 1, 'Alcohol en gel 1 L',        28000),
(12, 1, 1, 'Auriculares con micrófono', 95000);
