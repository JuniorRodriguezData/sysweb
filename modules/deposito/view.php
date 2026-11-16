<?php
/**
 * Examen final - Referencial Depósito: listado (view).
 */
$alertas = [
    1 => ["success", "Éxito!", "El depósito se registró correctamente."],
    2 => ["success", "Éxito!", "El depósito se modificó correctamente."],
    3 => ["success", "Éxito!", "El depósito se eliminó correctamente."],
    4 => ["danger",  "Error!", "No se puede eliminar: el depósito tiene stock o movimientos de compras/ventas registrados."],
    5 => ["danger",  "Error!", "Ya existe el depósito con esa descripción."],
    6 => ["danger",  "Error!", "El depósito que intenta eliminar no existe."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$registros = $mysqli->query("SELECT x.cod_deposito, x.descrip, (SELECT COALESCE(SUM(s.cantidad), 0) FROM stock s WHERE s.cod_deposito = x.cod_deposito) AS uso
                             FROM deposito x ORDER BY x.cod_deposito");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-archive icon-title"></i> Depósitos
        <a class="btn btn-primary btn-social pull-right" href="?module=form_deposito&form=add" title="Agregar" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
        <a class="btn btn-default btn-social pull-right" style="margin-right:6px" href="modules/deposito/print.php" target="_blank" title="Imprimir listado" data-toggle="tooltip">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Referenciales de compras</li>
        <li class="active">Depósito</li>
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
                                <th class="center">Unidades en stock</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($r = $registros->fetch_assoc()): ?>
                            <tr>
                                <td width="80" class="center"><?= (int) $r["cod_deposito"] ?></td>
                                <td><?= e($r["descrip"]) ?></td>
                                <td width="140" class="center"><?= gs($r["uso"]) ?></td>
                                <td width="150" class="center">
                                    <a data-toggle="tooltip" title="Imprimir ficha" class="btn btn-default btn-sm" target="_blank"
                                       href="modules/deposito/print_view.php?id=<?= (int) $r["cod_deposito"] ?>">
                                        <i class="glyphicon glyphicon-print"></i>
                                    </a>
                                    <a data-toggle="tooltip" title="Modificar" class="btn btn-primary btn-sm"
                                       href="?module=form_deposito&form=edit&id=<?= (int) $r["cod_deposito"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-edit"></i>
                                    </a>
                                    <form method="POST" action="modules/deposito/proses.php?act=delete" style="display:inline"
                                          onsubmit="return confirm('¿Eliminar el depósito <?= e(addslashes($r["descrip"])) ?>?');">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $r["cod_deposito"] ?>">
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
