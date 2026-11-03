<?php
/**
 * Proyecto 9 - Referencial Clientes: lista de clientes.
 */
$alertas = [
    1 => ["success", "Éxito!", "El cliente se registró correctamente."],
    2 => ["success", "Éxito!", "Los datos del cliente se modificaron correctamente."],
    3 => ["success", "Éxito!", "El cliente se eliminó correctamente."],
    4 => ["danger",  "Error!", "No se puede eliminar: el cliente tiene ventas registradas."],
    5 => ["danger",  "Error!", "Ya existe un cliente con ese C.I. / RUC."],
    6 => ["danger",  "Error!", "El cliente que intenta eliminar no existe."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$clientes = $mysqli->query("SELECT cl.id_cliente, cl.ci_ruc, cl.cli_nombre, cl.cli_apellido, cl.cli_direccion,
                                   cl.cli_telefono, c.descrip_ciudad
                            FROM clientes cl LEFT JOIN ciudad c ON c.cod_ciudad = cl.cod_ciudad
                            ORDER BY cl.id_cliente");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-address-book icon-title"></i> Clientes
        <a class="btn btn-primary btn-social pull-right" href="?module=form_clientes&form=add" title="Agregar" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
        <a class="btn btn-default btn-social pull-right" style="margin-right:6px" href="modules/clientes/reporte.php" target="_blank" title="Imprimir reporte" data-toggle="tooltip">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Referenciales de ventas</li>
        <li class="active">Clientes</li>
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
                                <th class="center">C.I. / RUC</th>
                                <th class="center">Nombre</th>
                                <th class="center">Apellido</th>
                                <th class="center">Dirección</th>
                                <th class="center">Teléfono</th>
                                <th class="center">Ciudad</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($cl = $clientes->fetch_assoc()): ?>
                            <tr>
                                <td width="60" class="center"><?= (int) $cl["id_cliente"] ?></td>
                                <td><?= e($cl["ci_ruc"]) ?></td>
                                <td><?= e($cl["cli_nombre"]) ?></td>
                                <td><?= e($cl["cli_apellido"]) ?></td>
                                <td><?= e($cl["cli_direccion"]) ?></td>
                                <td><?= e($cl["cli_telefono"]) ?></td>
                                <td><?= e($cl["descrip_ciudad"]) ?></td>
                                <td width="110" class="center">
                                    <a data-toggle="tooltip" title="Modificar" class="btn btn-primary btn-sm"
                                       href="?module=form_clientes&form=edit&id=<?= (int) $cl["id_cliente"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-edit"></i>
                                    </a>
                                    <form method="POST" action="modules/clientes/proses.php?act=delete" style="display:inline"
                                          onsubmit="return confirm('¿Eliminar al cliente <?= e(addslashes($cl["cli_nombre"] . " " . $cl["cli_apellido"])) ?>?');">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $cl["id_cliente"] ?>">
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
