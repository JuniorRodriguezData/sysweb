<?php
/**
 * Examen final - Imprimir (PDF) el listado de Tipos de producto.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("tipo_producto");

$filas = $mysqli->query("SELECT x.cod_tipo_prod, x.t_p_descrip, (SELECT COUNT(*) FROM producto p WHERE p.cod_tipo_prod = x.cod_tipo_prod)
                         FROM tipo_producto x ORDER BY x.cod_tipo_prod")->fetch_all(MYSQLI_NUM);
$filas = array_map(fn($f) => [$f[0], $f[1], gs($f[2])], $filas);

generar_reporte("Reporte de Tipos de producto", ["Código" => 15, "Descripción" => 60, "Productos" => 25], $filas, "tipo_producto.pdf",
    "P", "", "", [2]);
