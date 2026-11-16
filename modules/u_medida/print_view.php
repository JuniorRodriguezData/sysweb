<?php
/**
 * Examen final - Imprimir (PDF) la ficha de la unidad de medida con sus productos que usan esta unidad.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("u_medida");

$id   = (int) ($_GET["id"] ?? 0);
$stmt = $mysqli->prepare("SELECT id_u_medida, u_descrip FROM u_medida WHERE id_u_medida = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
if (!$r) {
    http_response_code(404);
    exit("Unidad de medida no encontrada.");
}

$stmt = $mysqli->prepare("SELECT p.cod_producto, p.p_descrip, t.t_p_descrip, p.precio FROM producto p JOIN tipo_producto t ON t.cod_tipo_prod = p.cod_tipo_prod WHERE p.id_u_medida = ? ORDER BY p.p_descrip");
$stmt->bind_param("i", $id);
$stmt->execute();
$filas = array_map(fn($f) => [$f[0], $f[1], $f[2], gs($f[3])], $stmt->get_result()->fetch_all(MYSQLI_NUM));

$ficha = '
<table style="width:100%; margin-bottom:4mm; font-size:11pt;">
    <tr><td style="width:25%;"><b>Código:</b></td><td style="width:75%;">' . (int) $r["id_u_medida"] . '</td></tr>
    <tr><td><b>Descripción:</b></td><td>' . e($r["u_descrip"]) . '</td></tr>
</table>
<p style="font-size:11pt; color:#0b2a5b;"><b>Productos con esta unidad de medida</b></p>';

generar_reporte("Unidad de medida Nro. " . (int) $r["id_u_medida"], ["Código" => 12, "Producto" => 48, "Tipo" => 25, "Precio (Gs.)" => 15], $filas, "u_medida_" . (int) $r["id_u_medida"] . ".pdf",
    "P", $ficha, "", [3]);
