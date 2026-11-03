<?php
/**
 * Proyecto 9 - Imprimir reporte de clientes (PDF horizontal).
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("clientes");

$filas = $mysqli->query("SELECT cl.id_cliente, cl.ci_ruc, CONCAT(cl.cli_nombre, ' ', cl.cli_apellido),
                                IFNULL(cl.cli_direccion, ''), IFNULL(cl.cli_telefono, ''), IFNULL(c.descrip_ciudad, '')
                         FROM clientes cl LEFT JOIN ciudad c ON c.cod_ciudad = cl.cod_ciudad
                         ORDER BY cl.cli_apellido, cl.cli_nombre")->fetch_all(MYSQLI_NUM);

generar_reporte("Reporte de Clientes",
    ["Código" => 9, "C.I. / RUC" => 14, "Nombre y apellido" => 27, "Dirección" => 24, "Teléfono" => 13, "Ciudad" => 13],
    $filas, "clientes.pdf", "L");
