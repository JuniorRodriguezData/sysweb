<?php
/**
 * Proyecto 10 - Movimiento de compras: lista de compras (vista v_compras).
 */
$alertas = [
    1 => ["success", "Éxito!", "La compra se registró correctamente y se actualizó el stock."],
    2 => ["success", "Éxito!", "La compra fue anulada y se descontó el stock."],
    3 => ["danger",  "Error!", "No se puede anular: el stock actual de algún producto es menor a lo comprado."],
    4 => ["danger",  "Error!", "La compra ya estaba anulada o no existe."],
    5 => ["danger",  "Error!", "La compra no tiene productos. Agregue al menos uno."],
    6 => ["danger",  "Error!", "Ya existe una compra con ese número de factura para el proveedor."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$compras = $mysqli->query("SELECT cod_compra, razon_social, descrip, nro_factura, fecha, hora, total_compra, estado, name_user
                           FROM v_compras ORDER BY cod_compra DESC");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-shopping-cart icon-title"></i> Compras
        <a class="btn btn-primary btn-social pull-right" href="?module=form_compras" title="Registrar compra" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Movimientos</li>
        <li class="active">Compras</li>
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
                                <th class="center">Proveedor</th>
                                <th class="center">Factura</th>
                                <th class="center">Depósito</th>
                                <th class="center">Total (Gs.)</th>
                                <th class="center">Usuario</th>
                                <th class="center">Estado</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($c = $compras->fetch_assoc()): ?>
                            <tr>
                                <td width="50" class="center"><?= (int) $c["cod_compra"] ?></td>
                                <td width="90"><?= date("d/m/Y", strtotime($c["fecha"])) ?> <small class="text-muted"><?= substr($c["hora"], 0, 5) ?></small></td>
                                <td><?= e($c["razon_social"]) ?></td>
                                <td><?= e($c["nro_factura"]) ?></td>
                                <td><?= e($c["descrip"]) ?></td>
                                <td class="text-right"><?= gs($c["total_compra"]) ?></td>
                                <td><?= e($c["name_user"]) ?></td>
                                <td class="center">
                                    <span class="label label-<?= $c["estado"] === "activo" ? "success" : "danger" ?>"><?= e($c["estado"]) ?></span>
                                </td>
                                <td width="130" class="center">
                                    <a data-toggle="tooltip" title="Ver detalle" class="btn btn-info btn-sm"
                                       href="?module=detalle_compra&id=<?= (int) $c["cod_compra"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-eye-open"></i>
                                    </a>
                                    <a data-toggle="tooltip" title="Imprimir" class="btn btn-default btn-sm" target="_blank"
                                       href="modules/compras/imprimir.php?id=<?= (int) $c["cod_compra"] ?>">
                                        <i class="glyphicon glyphicon-print"></i>
                                    </a>
                                    <?php if ($c["estado"] === "activo"): ?>
                                        <form method="POST" action="modules/compras/proses.php?act=anular" style="display:inline"
                                              onsubmit="return confirm('¿Anular la compra Nro. <?= (int) $c["cod_compra"] ?>? Se descontará del stock.');">
                                            <?= csrf_campo() ?>
                                            <input type="hidden" name="id" value="<?= (int) $c["cod_compra"] ?>">
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
