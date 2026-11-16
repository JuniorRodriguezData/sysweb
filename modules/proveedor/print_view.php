<?php
/**
 * Examen final - Imprimir (PDF) la ficha de un proveedor con sus compras.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("proveedor");

$id   = (int) ($_GET["id"] ?? 0);
$stmt = $mysqli->prepare("SELECT cod_proveedor, razon_social, ruc, direccion, telefono FROM proveedor WHERE cod_proveedor = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
if (!$p) {
    http_response_code(404);
    exit("Proveedor no encontrado.");
}

$stmt = $mysqli->prepare("SELECT cod_compra, fecha, nro_factura, descrip, estado, total_compra
                          FROM v_compras WHERE cod_proveedor = ? ORDER BY fecha, cod_compra");
$stmt->bind_param("i", $id);
$stmt->execute();
$total = 0;
$filas = [];
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $c) {
    if ($c["estado"] === "activo") {
        $total += $c["total_compra"];
    }
    $filas[] = [$c["cod_compra"], date("d/m/Y", strtotime($c["fecha"])), $c["nro_factura"], $c["descrip"], $c["estado"], gs($c["total_compra"])];
}

$ficha = '
<table style="width:100%; margin-bottom:4mm; font-size:11pt;">
    <tr><td style="width:25%;"><b>Código:</b></td><td style="width:75%;">' . (int) $p["cod_proveedor"] . '</td></tr>
    <tr><td><b>Razón social:</b></td><td>' . e($p["razon_social"]) . '</td></tr>
    <tr><td><b>RUC:</b></td><td>' . e($p["ruc"]) . '</td></tr>
    <tr><td><b>Dirección:</b></td><td>' . e($p["direccion"] ?? "-") . '</td></tr>
    <tr><td><b>Teléfono:</b></td><td>' . e((string) $p["telefono"]) . '</td></tr>
</table>
<p style="font-size:11pt; color:#0b2a5b;"><b>Compras al proveedor</b></p>';

$pie = '<p style="font-size:11pt;"><b>Total comprado (compras activas): Gs. ' . gs($total) . '</b></p>';

generar_reporte("Proveedor Nro. " . (int) $p["cod_proveedor"],
    ["Nro." => 9, "Fecha" => 14, "Factura" => 22, "Depósito" => 25, "Estado" => 12, "Total (Gs.)" => 18],
    $filas, "proveedor_" . (int) $p["cod_proveedor"] . ".pdf", "P", $ficha, $pie, [5]);
