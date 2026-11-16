<?php
/**
 * Examen final - Imprimir (PDF) la ficha de el tipo de producto con sus productos de este tipo.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("tipo_producto");

$id   = (int) ($_GET["id"] ?? 0);
$stmt = $mysqli->prepare("SELECT cod_tipo_prod, t_p_descrip FROM tipo_producto WHERE cod_tipo_prod = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
if (!$r) {
    http_response_code(404);
    exit("Tipo de producto no encontrado.");
}

$stmt = $mysqli->prepare("SELECT p.cod_producto, p.p_descrip, u.u_descrip, p.precio FROM producto p JOIN u_medida u ON u.id_u_medida = p.id_u_medida WHERE p.cod_tipo_prod = ? ORDER BY p.p_descrip");
$stmt->bind_param("i", $id);
$stmt->execute();
$filas = array_map(fn($f) => [$f[0], $f[1], $f[2], gs($f[3])], $stmt->get_result()->fetch_all(MYSQLI_NUM));

$ficha = '
<table style="width:100%; margin-bottom:4mm; font-size:11pt;">
    <tr><td style="width:25%;"><b>Código:</b></td><td style="width:75%;">' . (int) $r["cod_tipo_prod"] . '</td></tr>
    <tr><td><b>Descripción:</b></td><td>' . e($r["t_p_descrip"]) . '</td></tr>
</table>
<p style="font-size:11pt; color:#0b2a5b;"><b>Productos de este tipo</b></p>';

generar_reporte("Tipo de producto Nro. " . (int) $r["cod_tipo_prod"], ["Código" => 12, "Producto" => 48, "Unidad" => 25, "Precio (Gs.)" => 15], $filas, "tipo_producto_" . (int) $r["cod_tipo_prod"] . ".pdf",
    "P", $ficha, "", [3]);
