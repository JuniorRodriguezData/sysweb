-- =====================================================================
--  SysWeb - Examen final: cambios en la base de datos
--  Ejecutar UNA vez sobre la base sysweb existente:
--     mysql -u root sysweb < sql/examen_final.sql
--  (sysweb.sql ya incluye estos cambios para una instalación nueva)
-- =====================================================================
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Teléfono del proveedor como texto: un INT pierde el 0 inicial (0981...)
ALTER TABLE proveedor MODIFY telefono VARCHAR(20) NOT NULL;

-- Usuario que registró la venta (igual que en compra)
ALTER TABLE venta ADD COLUMN id_user INT NULL AFTER nro_factura,
                  ADD CONSTRAINT fk_venta_usuario FOREIGN KEY (id_user) REFERENCES usuarios (id_user);

-- Tabla temporal para armar el detalle de una venta (separada de tmp, que usa compras)
CREATE TABLE IF NOT EXISTS tmp_venta (
    id_tmp       INT          NOT NULL AUTO_INCREMENT,
    id_producto  INT          NOT NULL,
    cod_deposito INT          NOT NULL,
    cantidad_tmp INT          NOT NULL,
    precio_tmp   INT          NOT NULL,
    session_id   VARCHAR(255) NOT NULL,
    PRIMARY KEY (id_tmp),
    KEY idx_tmp_venta_session (session_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Vistas de ventas (como v_compras y v_det_compra)
CREATE OR REPLACE VIEW v_ventas AS
SELECT ven.cod_venta, ven.nro_factura, ven.fecha, ven.hora, ven.total_venta, ven.estado,
       cli.id_cliente, cli.ci_ruc, CONCAT(cli.cli_nombre, ' ', cli.cli_apellido) AS cliente,
       usu.id_user, usu.name_user
FROM venta ven
JOIN clientes cli      ON ven.id_cliente = cli.id_cliente
LEFT JOIN usuarios usu ON ven.id_user    = usu.id_user;

CREATE OR REPLACE VIEW v_det_venta AS
SELECT det.cod_venta, det.cod_deposito, dep.descrip AS deposito,
       pro.cod_producto, pro.p_descrip, tpro.t_p_descrip, um.u_descrip,
       det.det_precio_unit, det.det_cantidad
FROM det_venta det
JOIN producto      pro  ON det.cod_producto  = pro.cod_producto
JOIN deposito      dep  ON det.cod_deposito  = dep.cod_deposito
JOIN tipo_producto tpro ON pro.cod_tipo_prod = tpro.cod_tipo_prod
JOIN u_medida      um   ON pro.id_u_medida   = um.id_u_medida;

-- Igual que borrar_temp en compras: al registrar la venta se vacía el tmp_venta de esa sesión
DROP TRIGGER IF EXISTS borrar_temp_venta;
DELIMITER $$
CREATE TRIGGER borrar_temp_venta AFTER INSERT ON venta
FOR EACH ROW
BEGIN
    DELETE FROM tmp_venta WHERE session_id = @tmp_session COLLATE utf8mb4_unicode_ci;
END$$
DELIMITER ;
