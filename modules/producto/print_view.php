<?php
/**
 * Examen final - Imprimir (PDF) la ficha de un producto con su stock por depósito.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("producto");

$id   = (int) ($_GET["id"] ?? 0);
$stmt = $mysqli->prepare("SELECT p.cod_producto, p.p_descrip, t.t_p_descrip, u.u_descrip, p.precio
                          FROM producto p
                          JOIN tipo_producto t ON t.cod_tipo_prod = p.cod_tipo_prod
                          JOIN u_medida u      ON u.id_u_medida   = p.id_u_medida
                          WHERE p.cod_producto = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) {
    http_response_code(404);
    exit("Producto no encontrado.");
}

// Existencia en cada depósito (0 si nunca se compró en ese depósito)
$stmt = $mysqli->prepare("SELECT d.cod_deposito, d.descrip, COALESCE(s.cantidad, 0)
                          FROM deposito d
                          LEFT JOIN stock s ON s.cod_deposito = d.cod_deposito AND s.cod_producto = ?
                          ORDER BY d.cod_deposito");
$stmt->bind_param("i", $id);
$stmt->execute();
$total = 0;
$filas = [];
foreach ($stmt->get_result()->fetch_all(MYSQLI_NUM) as [$cod, $dep, $cant]) {
    $total  += $cant;
    $filas[] = [$cod, $dep, gs($cant), gs($cant * $p["precio"])];
}

$ficha = '
<table style="width:100%; margin-bottom:4mm; font-size:11pt;">
    <tr><td style="width:25%;"><b>Código:</b></td><td style="width:75%;">' . (int) $p["cod_producto"] . '</td></tr>
    <tr><td><b>Descripción:</b></td><td>' . e($p["p_descrip"]) . '</td></tr>
    <tr><td><b>Tipo de producto:</b></td><td>' . e($p["t_p_descrip"]) . '</td></tr>
    <tr><td><b>Unidad de medida:</b></td><td>' . e($p["u_descrip"]) . '</td></tr>
    <tr><td><b>Precio:</b></td><td>Gs. ' . gs($p["precio"]) . '</td></tr>
</table>
<p style="font-size:11pt; color:#0b2a5b;"><b>Stock por depósito</b></p>';

$pie = '<p style="font-size:11pt;"><b>Stock total: ' . gs($total) . ' · Valor: Gs. ' . gs($total * $p["precio"]) . '</b></p>';

generar_reporte("Producto Nro. " . (int) $p["cod_producto"],
    ["Cód." => 12, "Depósito" => 48, "Existencia" => 20, "Valor (Gs.)" => 20],
    $filas, "producto_" . (int) $p["cod_producto"] . ".pdf", "P", $ficha, $pie, [2, 3]);
