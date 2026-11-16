<?php
/**
 * Examen final - Movimiento de ventas: lista de ventas (vista v_ventas).
 */
$alertas = [
    1 => ["success", "Éxito!", "La venta se registró correctamente y se descontó del stock."],
    2 => ["success", "Éxito!", "La venta fue anulada: se devolvieron las cantidades al stock y la cabecera quedó anulada."],
    3 => ["danger",  "Error!", "No hay stock suficiente de algún producto. Revise las cantidades."],
    4 => ["danger",  "Error!", "La venta ya estaba anulada o no existe."],
    5 => ["danger",  "Error!", "La venta no tiene productos. Agregue al menos uno."],
    6 => ["danger",  "Error!", "Ya existe una venta activa con ese número de factura."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$ventas = $mysqli->query("SELECT v.cod_venta, v.nro_factura, v.fecha, v.hora, v.cliente, v.ci_ruc, v.total_venta, v.estado, v.name_user,
                                 (SELECT COUNT(*) FROM det_venta d WHERE d.cod_venta = v.cod_venta) AS items
                          FROM v_ventas v ORDER BY v.cod_venta DESC");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-cart-plus icon-title"></i> Ventas
        <a class="btn btn-primary btn-social pull-right" href="?module=form_ventas" title="Registrar venta" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Movimientos</li>
        <li class="active">Ventas</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if ($alerta): ?>
                <div class="alert alert-<?= $alerta[0] ?> alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <h4><i class="icon fa <?= $alerta[0] === "success" ? "fa-check-circle" : "fa-times-circle" ?>"></i> <?= $alerta[1] ?></h4>
                    <?= $alerta[2] ?>
                </div>
            <?php endif; ?>

            <div class="box box-primary">
                <div class="box-body">
                    <table id="dataTables1" class="table table-bordered table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="center">Nro.</th>
                                <th class="center">Fecha</th>
                                <th class="center">Factura</th>
                                <th class="center">Cliente</th>
                                <th class="center">Ítems</th>
                                <th class="center">Total (Gs.)</th>
                                <th class="center">Usuario</th>
                                <th class="center">Estado</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($v = $ventas->fetch_assoc()): ?>
                            <tr>
                                <td width="50" class="center"><?= (int) $v["cod_venta"] ?></td>
                                <td width="95"><?= date("d/m/Y", strtotime($v["fecha"])) ?> <small class="text-muted"><?= substr($v["hora"], 0, 5) ?></small></td>
                                <td><?= (int) $v["nro_factura"] ?></td>
                                <td><?= e($v["cliente"]) ?> <small class="text-muted">(<?= e($v["ci_ruc"]) ?>)</small></td>
                                <td class="center"><?= (int) $v["items"] ?></td>
                                <td class="text-right"><?= gs($v["total_venta"]) ?></td>
                                <td><?= e($v["name_user"] ?? "-") ?></td>
                                <td class="center">
                                    <span class="label label-<?= $v["estado"] === "activo" ? "success" : "danger" ?>"><?= e($v["estado"]) ?></span>
                                </td>
                                <td width="130" class="center">
                                    <a data-toggle="tooltip" title="Ver detalle" class="btn btn-info btn-sm"
                                       href="?module=detalle_venta&id=<?= (int) $v["cod_venta"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-eye-open"></i>
                                    </a>
                                    <a data-toggle="tooltip" title="Imprimir" class="btn btn-default btn-sm" target="_blank"
                                       href="modules/ventas/imprimir.php?id=<?= (int) $v["cod_venta"] ?>">
                                        <i class="glyphicon glyphicon-print"></i>
                                    </a>
                                    <?php if ($v["estado"] === "activo"): ?>
                                        <form method="POST" action="modules/ventas/proses.php?act=anular" style="display:inline"
                                              onsubmit="return confirm('¿Anular la venta Nro. <?= (int) $v["cod_venta"] ?>? Las cantidades vuelven al stock.');">
                                            <?= csrf_campo() ?>
                                            <input type="hidden" name="id" value="<?= (int) $v["cod_venta"] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Anular">
                                                <i style="color:#fff" class="glyphicon glyphicon-ban-circle"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
