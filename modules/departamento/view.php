<?php
/**
 * Proyecto 6 - Referencial Departamento: tabla de datos.
 */
$alertas = [
    1 => ["success", "Éxito!", "El departamento se registró correctamente."],
    2 => ["success", "Éxito!", "El departamento se modificó correctamente."],
    3 => ["success", "Éxito!", "El departamento se eliminó correctamente."],
    4 => ["danger",  "Error!", "No se puede eliminar: hay ciudades asociadas a este departamento."],
    5 => ["danger",  "Error!", "Ya existe un departamento con esa descripción."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$departamentos = $mysqli->query("SELECT d.id_departamento, d.dep_descripcion, COUNT(c.cod_ciudad) AS ciudades
                                 FROM departamento d LEFT JOIN ciudad c ON c.id_departamento = d.id_departamento
                                 GROUP BY d.id_departamento, d.dep_descripcion ORDER BY d.id_departamento");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-map icon-title"></i> Departamentos
        <a class="btn btn-primary btn-social pull-right" href="?module=form_departamento&form=add" title="Agregar" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
        <a class="btn btn-default btn-social pull-right" style="margin-right:6px" href="modules/departamento/reporte.php" target="_blank" title="Imprimir reporte" data-toggle="tooltip">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Referenciales generales</li>
        <li class="active">Departamento</li>
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
                                <th class="center">Ciudades</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php while ($d = $departamentos->fetch_assoc()): ?>
                            <tr>
                                <td width="80" class="center"><?= (int) $d["id_departamento"] ?></td>
                                <td><?= e($d["dep_descripcion"]) ?></td>
                                <td width="90" class="center"><?= (int) $d["ciudades"] ?></td>
                                <td width="110" class="center">
                                    <a data-toggle="tooltip" title="Modificar" class="btn btn-primary btn-sm"
                                       href="?module=form_departamento&form=edit&id=<?= (int) $d["id_departamento"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-edit"></i>
                                    </a>
                                    <form method="POST" action="modules/departamento/proses.php?act=delete" style="display:inline"
                                          onsubmit="return confirm('¿Eliminar el departamento <?= e(addslashes($d["dep_descripcion"])) ?>?');">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $d["id_departamento"] ?>">
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
