<?php
/**
 * Examen final - Imprimir (PDF) el listado de Depósitos.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("deposito");

$filas = $mysqli->query("SELECT x.cod_deposito, x.descrip, (SELECT COALESCE(SUM(s.cantidad), 0) FROM stock s WHERE s.cod_deposito = x.cod_deposito)
                         FROM deposito x ORDER BY x.cod_deposito")->fetch_all(MYSQLI_NUM);
$filas = array_map(fn($f) => [$f[0], $f[1], gs($f[2])], $filas);

generar_reporte("Reporte de Depósitos", ["Código" => 15, "Descripción" => 60, "Unidades en stock" => 25], $filas, "deposito.pdf",
    "P", "", "", [2]);
