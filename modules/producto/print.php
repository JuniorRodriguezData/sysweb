<?php
/**
 * Examen final - Imprimir (PDF) el listado de productos.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("producto");

$filas = $mysqli->query("SELECT p.cod_producto, p.p_descrip, t.t_p_descrip, u.u_descrip, p.precio,
                                COALESCE((SELECT SUM(s.cantidad) FROM stock s WHERE s.cod_producto = p.cod_producto), 0)
                         FROM producto p
                         JOIN tipo_producto t ON t.cod_tipo_prod = p.cod_tipo_prod
                         JOIN u_medida u      ON u.id_u_medida   = p.id_u_medida
                         ORDER BY t.t_p_descrip, p.p_descrip")->fetch_all(MYSQLI_NUM);
$filas = array_map(fn($f) => [$f[0], $f[1], $f[2], $f[3], gs($f[4]), gs($f[5])], $filas);

generar_reporte("Reporte de Productos",
    ["Código" => 9, "Producto" => 37, "Tipo" => 18, "Unidad" => 13, "Precio (Gs.)" => 13, "Stock" => 10],
    $filas, "productos.pdf", "P", "", "", [4, 5]);
