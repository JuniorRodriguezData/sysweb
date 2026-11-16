<?php
/**
 * Examen final - Imprimir (PDF) el listado de proveedores.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("proveedor");

$filas = $mysqli->query("SELECT cod_proveedor, razon_social, ruc, COALESCE(direccion, '-'), telefono
                         FROM proveedor ORDER BY razon_social")->fetch_all(MYSQLI_NUM);

generar_reporte("Reporte de Proveedores",
    ["Código" => 9, "Razón social" => 33, "RUC" => 13, "Dirección" => 30, "Teléfono" => 15],
    $filas, "proveedores.pdf");
