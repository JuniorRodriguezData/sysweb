<?php
/**
 * Proyecto 10 - Imprimir el formulario de una compra (PDF).
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("compras");

$id   = (int) ($_GET["id"] ?? 0);
$stmt = $mysqli->prepare("SELECT * FROM v_compras WHERE cod_compra = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$compra = $stmt->get_result()->fetch_assoc();
if (!$compra) {
    http_response_code(404);
    exit("Compra no encontrada.");
}

$stmt = $mysqli->prepare("SELECT cod_producto, p_descrip, u_descrip, cantidad, precio, precio * cantidad
                          FROM v_det_compra WHERE cod_compra = ? ORDER BY p_descrip");
$stmt->bind_param("i", $id);
$stmt->execute();
$filas = array_map(
    fn($f) => [$f[0], $f[1], $f[2], $f[3], gs($f[4]), gs($f[5])],
    $stmt->get_result()->fetch_all(MYSQLI_NUM)
);

$anulada = $compra["estado"] !== "activo"
    ? '<p style="color:#c62828; font-size:13pt; font-weight:bold;">COMPRA ANULADA</p>' : "";

$cabecera = $anulada . '
<table style="width:100%; margin-bottom:5mm; font-size:10pt;">
    <tr>
        <td style="width:50%;"><b>Proveedor:</b> ' . e($compra["razon_social"]) . '</td>
        <td style="width:50%;"><b>Factura Nro.:</b> ' . e($compra["nro_factura"]) . '</td>
    </tr>
    <tr>
        <td><b>Fecha:</b> ' . date("d/m/Y", strtotime($compra["fecha"])) . ' ' . substr($compra["hora"], 0, 5) . '</td>
        <td><b>Depósito:</b> ' . e($compra["descrip"]) . '</td>
    </tr>
    <tr>
        <td><b>Registrada por:</b> ' . e($compra["name_user"]) . '</td>
        <td><b>Estado:</b> ' . e($compra["estado"]) . '</td>
    </tr>
</table>';

$total = '<table style="width:100%; margin-top:3mm;"><tr>
    <td style="width:70%; text-align:right; font-size:12pt;"><b>TOTAL Gs.</b></td>
    <td style="width:30%; text-align:right; font-size:12pt;"><b>' . gs($compra["total_compra"]) . '</b></td>
</tr></table>';

generar_reporte("Compra Nro. " . (int) $compra["cod_compra"],
    ["Código" => 10, "Producto" => 38, "Unidad" => 12, "Cantidad" => 10, "Precio" => 14, "Subtotal" => 16],
    $filas, "compra_" . (int) $compra["cod_compra"] . ".pdf", "P", $cabecera, $total, [3, 4, 5]);
