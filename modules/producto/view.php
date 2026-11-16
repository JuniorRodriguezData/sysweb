<?php
/**
 * Examen final - Referencial Producto: listado (view) con tipo, unidad, precio y stock total.
 */
$alertas = [
    1 => ["success", "Éxito!", "El producto se registró correctamente."],
    2 => ["success", "Éxito!", "El producto se modificó correctamente."],
    3 => ["success", "Éxito!", "El producto se eliminó correctamente."],
    4 => ["danger",  "Error!", "No se puede eliminar: el producto tiene stock o movimientos de compras/ventas."],
    5 => ["danger",  "Error!", "Ya existe un producto con esa descripción."],
    6 => ["danger",  "Error!", "El producto que intenta eliminar no existe."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$productos = $mysqli->query("SELECT p.cod_producto, p.p_descrip, t.t_p_descrip, u.u_descrip, p.precio,
                                    COALESCE((SELECT SUM(s.cantidad) FROM stock s WHERE s.cod_producto = p.cod_producto), 0) AS stock
                             FROM producto p
                             JOIN tipo_producto t ON t.cod_tipo_prod = p.cod_tipo_prod
                             JOIN u_medida u      ON u.id_u_medida   = p.id_u_medida
                             ORDER BY p.cod_producto");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-cube icon-title"></i> Productos
        <a class="btn btn-primary btn-social pull-right" href="?module=form_producto&form=add" title="Agregar" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
        <a class="btn btn-default btn-social pull-right" style="margin-right:6px" href="modules/producto/print.php" target="_blank" title="Imprimir listado" data-toggle="tooltip">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Referenciales de compras</li>
        <li class="active">Producto</li>
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
                                <th class="center">Código</th>
                                <th class="center">Producto</th>
                                <th class="center">Tipo</th>
                                <th class="center">Unidad</th>
                                <th class="center">Precio (Gs.)</th>
                                <th class="center">Stock</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($p = $productos->fetch_assoc()): ?>
                            <tr>
                                <td width="70" class="center"><?= (int) $p["cod_producto"] ?></td>
                                <td><?= e($p["p_descrip"]) ?></td>
                                <td><?= e($p["t_p_descrip"]) ?></td>
                                <td><?= e($p["u_descrip"]) ?></td>
                                <td class="text-right"><?= gs($p["precio"]) ?></td>
                                <td width="80" class="center">
                                    <span class="label label-<?= (int) $p["stock"] > 0 ? "success" : "danger" ?>"><?= gs($p["stock"]) ?></span>
                                </td>
                                <td width="150" class="center">
                                    <a data-toggle="tooltip" title="Imprimir ficha" class="btn btn-default btn-sm" target="_blank"
                                       href="modules/producto/print_view.php?id=<?= (int) $p["cod_producto"] ?>">
                                        <i class="glyphicon glyphicon-print"></i>
                                    </a>
                                    <a data-toggle="tooltip" title="Modificar" class="btn btn-primary btn-sm"
                                       href="?module=form_producto&form=edit&id=<?= (int) $p["cod_producto"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-edit"></i>
                                    </a>
                                    <form method="POST" action="modules/producto/proses.php?act=delete" style="display:inline"
                                          onsubmit="return confirm('¿Eliminar el producto <?= e(addslashes($p["p_descrip"])) ?>?');">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $p["cod_producto"] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar">
                                            <i style="color:#fff" class="glyphicon glyphicon-trash"></i>
                                        </button>
                                    </form>
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
