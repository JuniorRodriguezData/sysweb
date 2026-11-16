<?php
/**
 * Examen final - Referencial Unidad de medida: listado (view).
 */
$alertas = [
    1 => ["success", "Éxito!", "La unidad de medida se registró correctamente."],
    2 => ["success", "Éxito!", "La unidad de medida se modificó correctamente."],
    3 => ["success", "Éxito!", "La unidad de medida se eliminó correctamente."],
    4 => ["danger",  "Error!", "No se puede eliminar: hay productos que usan esta unidad de medida."],
    5 => ["danger",  "Error!", "Ya existe la unidad de medida con esa descripción."],
    6 => ["danger",  "Error!", "La unidad de medida que intenta eliminar no existe."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$registros = $mysqli->query("SELECT x.id_u_medida, x.u_descrip, (SELECT COUNT(*) FROM producto p WHERE p.id_u_medida = x.id_u_medida) AS uso
                             FROM u_medida x ORDER BY x.id_u_medida");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-balance-scale icon-title"></i> Unidades de medida
        <a class="btn btn-primary btn-social pull-right" href="?module=form_u_medida&form=add" title="Agregar" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
        <a class="btn btn-default btn-social pull-right" style="margin-right:6px" href="modules/u_medida/print.php" target="_blank" title="Imprimir listado" data-toggle="tooltip">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Referenciales de compras</li>
        <li class="active">Unidad de medida</li>
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
                                <th class="center">Descripción</th>
                                <th class="center">Productos</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($r = $registros->fetch_assoc()): ?>
                            <tr>
                                <td width="80" class="center"><?= (int) $r["id_u_medida"] ?></td>
                                <td><?= e($r["u_descrip"]) ?></td>
                                <td width="140" class="center"><?= gs($r["uso"]) ?></td>
                                <td width="150" class="center">
                                    <a data-toggle="tooltip" title="Imprimir ficha" class="btn btn-default btn-sm" target="_blank"
                                       href="modules/u_medida/print_view.php?id=<?= (int) $r["id_u_medida"] ?>">
                                        <i class="glyphicon glyphicon-print"></i>
                                    </a>
                                    <a data-toggle="tooltip" title="Modificar" class="btn btn-primary btn-sm"
                                       href="?module=form_u_medida&form=edit&id=<?= (int) $r["id_u_medida"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-edit"></i>
                                    </a>
                                    <form method="POST" action="modules/u_medida/proses.php?act=delete" style="display:inline"
                                          onsubmit="return confirm('¿Eliminar la unidad de medida <?= e(addslashes($r["u_descrip"])) ?>?');">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $r["id_u_medida"] ?>">
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
