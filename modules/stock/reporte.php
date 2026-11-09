<?php
/**
 * Proyecto 14 - Reporte PDF del stock de productos.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "../../config/reporte.php";

exigir_permiso_reporte("stock");

$deposito = (int) ($_GET["deposito"] ?? 0);
$stmt = $mysqli->prepare("SELECT d.descrip, p.cod_producto, p.p_descrip, u.u_descrip, COALESCE(s.cantidad, 0), p.precio
                          FROM producto p
                          CROSS JOIN deposito d
                          JOIN u_medida u   ON u.id_u_medida  = p.id_u_medida
                          LEFT JOIN stock s ON s.cod_producto = p.cod_producto AND s.cod_deposito = d.cod_deposito
                          WHERE (? = 0 OR d.cod_deposito = ?)
                          ORDER BY d.cod_deposito, p.p_descrip");
$stmt->bind_param("ii", $deposito, $deposito);
$stmt->execute();

$total = 0;
$filas = [];
foreach ($stmt->get_result()->fetch_all(MYSQLI_NUM) as [$dep, $cod, $prod, $unidad, $cant, $precio]) {
    $total  += $cant * $precio;
    $filas[] = [$dep, $cod, $prod, $unidad, gs($cant), gs($precio), gs($cant * $precio)];
}

$pie = '<table style="width:100%; margin-top:3mm;"><tr>
    <td style="width:75%; text-align:right; font-size:11pt;"><b>VALOR TOTAL DEL INVENTARIO Gs.</b></td>
    <td style="width:25%; text-align:right; font-size:11pt;"><b>' . gs($total) . '</b></td>
</tr></table>';

generar_reporte("Stock de productos",
    ["Depósito" => 20, "Cód." => 7, "Producto" => 29, "Unidad" => 11, "Existencia" => 10, "Precio" => 11, "Valor" => 12],
    $filas, "stock.pdf", "L", "", $pie, [4, 5, 6]);
