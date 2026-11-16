<?php
/**
 * Examen final - Vista detalle de una venta (vistas v_ventas y v_det_venta).
 */
$id   = (int) ($_GET["id"] ?? 0);
$stmt = $mysqli->prepare("SELECT * FROM v_ventas WHERE cod_venta = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();

if (!$venta) { ?>
    <section class="content">
        <div class="alert alert-danger"><h4><i class="icon fa fa-times-circle"></i> Venta no encontrada</h4></div>
        <a href="?module=ventas" class="btn btn-default">Volver</a>
    </section>
    <?php return;
}

$stmt = $mysqli->prepare("SELECT cod_producto, p_descrip, t_p_descrip, u_descrip, deposito, det_precio_unit, det_cantidad
                          FROM v_det_venta WHERE cod_venta = ? ORDER BY p_descrip");
$stmt->bind_param("i", $id);
$stmt->execute();
$detalle = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$anulada = $venta["estado"] !== "activo";
?>
<section class="content-header">
    <h1>
        <i class="fa fa-file-text-o icon-title"></i> Venta Nro. <?= (int) $venta["cod_venta"] ?>
        <span class="label label-<?= $anulada ? "danger" : "success" ?>" style="font-size:12px;vertical-align:middle"><?= e($venta["estado"]) ?></span>
        <a class="btn btn-default btn-social pull-right" href="modules/ventas/imprimir.php?id=<?= (int) $venta["cod_venta"] ?>" target="_blank">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=ventas">Ventas</a></li>
        <li class="active">Detalle</li>
    </ol>
</section>

<section class="content">
    <?php if ($anulada): ?>
        <div class="alert alert-danger"><i class="icon fa fa-ban"></i> Esta venta está <b>anulada</b>: sus cantidades ya fueron devueltas al stock.</div>
    <?php endif; ?>
    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Datos de la venta</h3></div>
        <div class="box-body">
            <div class="row">
                <div class="col-sm-4"><strong>Cliente:</strong> <?= e($venta["cliente"]) ?> (<?= e($venta["ci_ruc"]) ?>)</div>
                <div class="col-sm-4"><strong>Factura Nro.:</strong> <?= (int) $venta["nro_factura"] ?></div>
                <div class="col-sm-4"><strong>Fecha y hora:</strong> <?= date("d/m/Y", strtotime($venta["fecha"])) ?> <?= substr($venta["hora"], 0, 5) ?></div>
            </div>
            <div class="row" style="margin-top:8px">
                <div class="col-sm-4"><strong>Registrada por:</strong> <?= e($venta["name_user"] ?? "-") ?></div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Productos</h3></div>
        <div class="box-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Código</th><th>Producto</th><th>Tipo</th><th>Unidad</th><th>Depósito</th>
                        <th class="text-right">Cantidad</th><th class="text-right">Precio (Gs.)</th><th class="text-right">Subtotal (Gs.)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($detalle as $d): ?>
                    <tr>
                        <td><?= (int) $d["cod_producto"] ?></td>
                        <td><?= e($d["p_descrip"]) ?></td>
                        <td><?= e($d["t_p_descrip"]) ?></td>
                        <td><?= e($d["u_descrip"]) ?></td>
                        <td><?= e($d["deposito"]) ?></td>
                        <td class="text-right"><?= (int) $d["det_cantidad"] ?></td>
                        <td class="text-right"><?= gs($d["det_precio_unit"]) ?></td>
                        <td class="text-right"><?= gs($d["det_precio_unit"] * $d["det_cantidad"]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="7" class="text-right">TOTAL</th>
                        <th class="text-right" style="font-size:16px"><?= gs($venta["total_venta"]) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="box-footer">
            <a href="?module=ventas" class="btn btn-default"><i class="fa fa-arrow-left"></i> Volver a ventas</a>
        </div>
    </div>
</section>
