<?php
/**
 * Examen final - Imprimir (PDF) la ficha de el depósito con sus existencias en este depósito.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("deposito");

$id   = (int) ($_GET["id"] ?? 0);
$stmt = $mysqli->prepare("SELECT cod_deposito, descrip FROM deposito WHERE cod_deposito = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$r = $stmt->get_result()->fetch_assoc();
if (!$r) {
    http_response_code(404);
    exit("Depósito no encontrado.");
}

$stmt = $mysqli->prepare("SELECT p.cod_producto, p.p_descrip, u.u_descrip, s.cantidad FROM stock s JOIN producto p ON p.cod_producto = s.cod_producto JOIN u_medida u ON u.id_u_medida = p.id_u_medida WHERE s.cod_deposito = ? ORDER BY p.p_descrip");
$stmt->bind_param("i", $id);
$stmt->execute();
$filas = array_map(fn($f) => [$f[0], $f[1], $f[2], gs($f[3])], $stmt->get_result()->fetch_all(MYSQLI_NUM));

$ficha = '
<table style="width:100%; margin-bottom:4mm; font-size:11pt;">
    <tr><td style="width:25%;"><b>Código:</b></td><td style="width:75%;">' . (int) $r["cod_deposito"] . '</td></tr>
    <tr><td><b>Descripción:</b></td><td>' . e($r["descrip"]) . '</td></tr>
</table>
<p style="font-size:11pt; color:#0b2a5b;"><b>Existencias del depósito</b></p>';

generar_reporte("Depósito Nro. " . (int) $r["cod_deposito"], ["Código" => 12, "Producto" => 50, "Unidad" => 20, "Existencia" => 18], $filas, "deposito_" . (int) $r["cod_deposito"] . ".pdf",
    "P", $ficha, "", [3]);
