<?php
/**
 * Proyecto 8 - Reporte PDF de ciudades.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("ciudad");

$filas = $mysqli->query("SELECT c.cod_ciudad, c.descrip_ciudad, d.dep_descripcion
                         FROM ciudad c JOIN departamento d ON d.id_departamento = c.id_departamento
                         ORDER BY d.dep_descripcion, c.descrip_ciudad")->fetch_all(MYSQLI_NUM);

generar_reporte("Reporte de Ciudades", ["Código" => 15, "Ciudad" => 45, "Departamento" => 40], $filas, "ciudades.pdf");
