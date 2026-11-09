<?php
/**
 * Proyecto 10 - Vista detalle de una compra (vistas v_compras y v_det_compra).
 */
$id   = (int) ($_GET["id"] ?? 0);
$stmt = $mysqli->prepare("SELECT * FROM v_compras WHERE cod_compra = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$compra = $stmt->get_result()->fetch_assoc();

if (!$compra) { ?>
    <section class="content">
        <div class="alert alert-danger"><h4><i class="icon fa fa-times-circle"></i> Compra no encontrada</h4></div>
        <a href="?module=compras" class="btn btn-default">Volver</a>
    </section>
    <?php return;
}

$stmt = $mysqli->prepare("SELECT cod_producto, p_descrip, t_p_descrip, u_descrip, precio, cantidad
                          FROM v_det_compra WHERE cod_compra = ? ORDER BY p_descrip");
$stmt->bind_param("i", $id);
$stmt->execute();
$detalle = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<section class="content-header">
    <h1>
        <i class="fa fa-file-text-o icon-title"></i> Compra Nro. <?= (int) $compra["cod_compra"] ?>
        <span class="label label-<?= $compra["estado"] === "activo" ? "success" : "danger" ?>" style="font-size:12px;vertical-align:middle"><?= e($compra["estado"]) ?></span>
        <a class="btn btn-default btn-social pull-right" href="modules/compras/imprimir.php?id=<?= (int) $compra["cod_compra"] ?>" target="_blank">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=compras">Compras</a></li>
        <li class="active">Detalle</li>
    </ol>
</section>

<section class="content">
    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Datos de la compra</h3></div>
        <div class="box-body">
            <div class="row">
                <div class="col-sm-4"><strong>Proveedor:</strong> <?= e($compra["razon_social"]) ?></div>
                <div class="col-sm-4"><strong>Factura Nro.:</strong> <?= e($compra["nro_factura"]) ?></div>
                <div class="col-sm-4"><strong>Fecha y hora:</strong> <?= date("d/m/Y", strtotime($compra["fecha"])) ?> <?= substr($compra["hora"], 0, 5) ?></div>
            </div>
            <div class="row" style="margin-top:8px">
                <div class="col-sm-4"><strong>Depósito:</strong> <?= e($compra["descrip"]) ?></div>
                <div class="col-sm-4"><strong>Registrada por:</strong> <?= e($compra["name_user"]) ?></div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border"><h3 class="box-title">Productos</h3></div>
        <div class="box-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Código</th><th>Producto</th><th>Tipo</th><th>Unidad</th>
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
                        <td class="text-right"><?= (int) $d["cantidad"] ?></td>
                        <td class="text-right"><?= gs($d["precio"]) ?></td>
                        <td class="text-right"><?= gs($d["precio"] * $d["cantidad"]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-right">TOTAL</th>
                        <th class="text-right" style="font-size:16px"><?= gs($compra["total_compra"]) ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="box-footer">
            <a href="?module=compras" class="btn btn-default"><i class="fa fa-arrow-left"></i> Volver a compras</a>
        </div>
    </div>
</section>
