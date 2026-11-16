<?php
/**
 * Examen final - Referencial Proveedor: listado (view) con cantidad de compras activas.
 */
$alertas = [
    1 => ["success", "Éxito!", "El proveedor se registró correctamente."],
    2 => ["success", "Éxito!", "El proveedor se modificó correctamente."],
    3 => ["success", "Éxito!", "El proveedor se eliminó correctamente."],
    4 => ["danger",  "Error!", "No se puede eliminar: el proveedor tiene compras registradas."],
    5 => ["danger",  "Error!", "Ya existe un proveedor con ese RUC o esa razón social."],
    6 => ["danger",  "Error!", "El proveedor que intenta eliminar no existe."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$proveedores = $mysqli->query("SELECT p.cod_proveedor, p.razon_social, p.ruc, p.direccion, p.telefono,
                                      (SELECT COUNT(*) FROM compra c WHERE c.cod_proveedor = p.cod_proveedor AND c.estado = 'activo') AS compras
                               FROM proveedor p ORDER BY p.cod_proveedor");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-truck icon-title"></i> Proveedores
        <a class="btn btn-primary btn-social pull-right" href="?module=form_proveedor&form=add" title="Agregar" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
        <a class="btn btn-default btn-social pull-right" style="margin-right:6px" href="modules/proveedor/print.php" target="_blank" title="Imprimir listado" data-toggle="tooltip">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Referenciales de compras</li>
        <li class="active">Proveedor</li>
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
                                <th class="center">Razón social</th>
                                <th class="center">RUC</th>
                                <th class="center">Dirección</th>
                                <th class="center">Teléfono</th>
                                <th class="center">Compras</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($p = $proveedores->fetch_assoc()): ?>
                            <tr>
                                <td width="70" class="center"><?= (int) $p["cod_proveedor"] ?></td>
                                <td><?= e($p["razon_social"]) ?></td>
                                <td><?= e($p["ruc"]) ?></td>
                                <td><?= e($p["direccion"]) ?></td>
                                <td><?= e($p["telefono"]) ?></td>
                                <td width="70" class="center"><?= (int) $p["compras"] ?></td>
                                <td width="150" class="center">
                                    <a data-toggle="tooltip" title="Imprimir ficha" class="btn btn-default btn-sm" target="_blank"
                                       href="modules/proveedor/print_view.php?id=<?= (int) $p["cod_proveedor"] ?>">
                                        <i class="glyphicon glyphicon-print"></i>
                                    </a>
                                    <a data-toggle="tooltip" title="Modificar" class="btn btn-primary btn-sm"
                                       href="?module=form_proveedor&form=edit&id=<?= (int) $p["cod_proveedor"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-edit"></i>
                                    </a>
                                    <form method="POST" action="modules/proveedor/proses.php?act=delete" style="display:inline"
                                          onsubmit="return confirm('¿Eliminar el proveedor <?= e(addslashes($p["razon_social"])) ?>?');">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $p["cod_proveedor"] ?>">
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
