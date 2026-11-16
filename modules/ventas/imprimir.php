<?php
/**
 * Examen final - Imprimir el comprobante de una venta (PDF).
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("ventas");

$id   = (int) ($_GET["id"] ?? 0);
$stmt = $mysqli->prepare("SELECT * FROM v_ventas WHERE cod_venta = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();
if (!$venta) {
    http_response_code(404);
    exit("Venta no encontrada.");
}

$stmt = $mysqli->prepare("SELECT cod_producto, p_descrip, deposito, det_cantidad, det_precio_unit, det_precio_unit * det_cantidad
                          FROM v_det_venta WHERE cod_venta = ? ORDER BY p_descrip");
$stmt->bind_param("i", $id);
$stmt->execute();
$filas = array_map(
    fn($f) => [$f[0], $f[1], $f[2], $f[3], gs($f[4]), gs($f[5])],
    $stmt->get_result()->fetch_all(MYSQLI_NUM)
);

$anulada = $venta["estado"] !== "activo"
    ? '<p style="color:#c62828; font-size:13pt; font-weight:bold;">VENTA ANULADA</p>' : "";

$cabecera = $anulada . '
<table style="width:100%; margin-bottom:5mm; font-size:10pt;">
    <tr>
        <td style="width:50%;"><b>Cliente:</b> ' . e($venta["cliente"]) . '</td>
        <td style="width:50%;"><b>CI/RUC:</b> ' . e($venta["ci_ruc"]) . '</td>
    </tr>
    <tr>
        <td><b>Factura Nro.:</b> ' . (int) $venta["nro_factura"] . '</td>
        <td><b>Fecha:</b> ' . date("d/m/Y", strtotime($venta["fecha"])) . ' ' . substr($venta["hora"], 0, 5) . '</td>
    </tr>
    <tr>
        <td><b>Registrada por:</b> ' . e($venta["name_user"] ?? "-") . '</td>
        <td><b>Estado:</b> ' . e($venta["estado"]) . '</td>
    </tr>
</table>';

$total = '<table style="width:100%; margin-top:3mm;"><tr>
    <td style="width:70%; text-align:right; font-size:12pt;"><b>TOTAL Gs.</b></td>
    <td style="width:30%; text-align:right; font-size:12pt;"><b>' . gs($venta["total_venta"]) . '</b></td>
</tr></table>';

generar_reporte("Venta Nro. " . (int) $venta["cod_venta"],
    ["Código" => 9, "Producto" => 33, "Depósito" => 20, "Cantidad" => 10, "Precio" => 13, "Subtotal" => 15],
    $filas, "venta_" . (int) $venta["cod_venta"] . ".pdf", "P", $cabecera, $total, [3, 4, 5]);
