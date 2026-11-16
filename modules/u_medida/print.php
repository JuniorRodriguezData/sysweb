<?php
/**
 * Examen final - Imprimir (PDF) el listado de Unidades de medida.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("u_medida");

$filas = $mysqli->query("SELECT x.id_u_medida, x.u_descrip, (SELECT COUNT(*) FROM producto p WHERE p.id_u_medida = x.id_u_medida)
                         FROM u_medida x ORDER BY x.id_u_medida")->fetch_all(MYSQLI_NUM);
$filas = array_map(fn($f) => [$f[0], $f[1], gs($f[2])], $filas);

generar_reporte("Reporte de Unidades de medida", ["Código" => 15, "Descripción" => 60, "Productos" => 25], $filas, "u_medida.pdf",
    "P", "", "", [2]);
