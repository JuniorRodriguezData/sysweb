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
    cli_telefono  INT         NULL,
    cod_ciudad    INT         NULL,
    PRIMARY KEY (id_cliente),
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
    PRIMARY KEY (id_tmp)
) ENGINE = InnoDB;

-- ---------------------------------------------------------------------
-- Datos de referencia mínimos
-- ---------------------------------------------------------------------
INSERT INTO departamento VALUES (0, 'Asunción (Capital)'), (11, 'Central'), (7, 'Itapúa'), (10, 'Alto Paraná');
INSERT INTO ciudad VALUES (1, 'Asunción', 0), (2, 'San Lorenzo', 11), (3, 'Luque', 11), (4, 'Encarnación', 7), (5, 'Ciudad del Este', 10);
INSERT INTO deposito VALUES (1, 'Depósito Central');
INSERT INTO u_medida VALUES (1, 'Unidad'), (2, 'Caja'), (3, 'Kilogramo');
INSERT INTO tipo_producto VALUES (1, 'Informática'), (2, 'Librería');
