<?php
/**
 * Proyecto 8 - Reporte PDF de departamentos.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("departamento");

$filas = $mysqli->query("SELECT d.id_departamento, d.dep_descripcion, COUNT(c.cod_ciudad) AS ciudades
                         FROM departamento d LEFT JOIN ciudad c ON c.id_departamento = d.id_departamento
                         GROUP BY d.id_departamento, d.dep_descripcion ORDER BY d.id_departamento")->fetch_all(MYSQLI_NUM);

generar_reporte("Reporte de Departamentos", ["Código" => 15, "Descripción" => 65, "Ciudades" => 20], $filas, "departamentos.pdf");
