<?php
/**
 * Proyecto 7 - Referencial Ciudad: vista con el listado de ciudades.
 */
$alertas = [
    1 => ["success", "Éxito!", "La ciudad se registró correctamente."],
    2 => ["success", "Éxito!", "La ciudad se modificó correctamente."],
    3 => ["success", "Éxito!", "La ciudad se eliminó correctamente."],
    4 => ["danger",  "Error!", "No se puede eliminar: hay clientes registrados en esta ciudad."],
    5 => ["danger",  "Error!", "Ya existe esa ciudad en el departamento elegido."],
    6 => ["danger",  "Error!", "La ciudad que intenta eliminar no existe."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$ciudades = $mysqli->query("SELECT c.cod_ciudad, c.descrip_ciudad, d.dep_descripcion,
                                   (SELECT COUNT(*) FROM clientes cl WHERE cl.cod_ciudad = c.cod_ciudad) AS clientes
                            FROM ciudad c JOIN departamento d ON d.id_departamento = c.id_departamento
                            ORDER BY c.cod_ciudad");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-building icon-title"></i> Ciudades
        <a class="btn btn-primary btn-social pull-right" href="?module=form_ciudad&form=add" title="Agregar" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
        <a class="btn btn-default btn-social pull-right" style="margin-right:6px" href="modules/ciudad/reporte.php" target="_blank" title="Imprimir reporte" data-toggle="tooltip">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Referenciales generales</li>
        <li class="active">Ciudad</li>
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
                                <th class="center">Ciudad</th>
                                <th class="center">Departamento</th>
                                <th class="center">Clientes</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($c = $ciudades->fetch_assoc()): ?>
                            <tr>
                                <td width="80" class="center"><?= (int) $c["cod_ciudad"] ?></td>
                                <td><?= e($c["descrip_ciudad"]) ?></td>
                                <td><?= e($c["dep_descripcion"]) ?></td>
                                <td width="80" class="center"><?= (int) $c["clientes"] ?></td>
                                <td width="110" class="center">
                                    <a data-toggle="tooltip" title="Modificar" class="btn btn-primary btn-sm"
                                       href="?module=form_ciudad&form=edit&id=<?= (int) $c["cod_ciudad"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-edit"></i>
                                    </a>
                                    <form method="POST" action="modules/ciudad/proses.php?act=delete" style="display:inline"
                                          onsubmit="return confirm('¿Eliminar la ciudad <?= e(addslashes($c["descrip_ciudad"])) ?>?');">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $c["cod_ciudad"] ?>">
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
