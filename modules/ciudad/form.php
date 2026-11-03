<?php
/**
 * Proyecto 7 - Formulario Ciudad (agregar y editar).
 */
$es_edicion = ($_GET["form"] ?? "") === "edit";

if ($es_edicion) {
    $id   = (int) ($_GET["id"] ?? 0);
    $stmt = $mysqli->prepare("SELECT cod_ciudad, descrip_ciudad, id_departamento FROM ciudad WHERE cod_ciudad = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $c = $stmt->get_result()->fetch_assoc();
    if (!$c) { ?>
        <section class="content">
            <div class="alert alert-danger"><h4><i class="icon fa fa-times-circle"></i> Ciudad no encontrada</h4></div>
            <a href="?module=ciudad" class="btn btn-default">Volver</a>
        </section>
        <?php return;
    }
} else {
    $c = ["cod_ciudad" => siguiente_codigo($mysqli, "ciudad"), "descrip_ciudad" => "", "id_departamento" => null];
}

$departamentos = $mysqli->query("SELECT id_departamento, dep_descripcion FROM departamento ORDER BY dep_descripcion");
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> <?= $es_edicion ? "Modificar ciudad" : "Agregar ciudad" ?></h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=ciudad">Ciudad</a></li>
        <li class="active"><?= $es_edicion ? "Modificar" : "Agregar" ?></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <form role="form" class="form-horizontal" method="POST"
                      action="modules/ciudad/proses.php?act=<?= $es_edicion ? "update" : "insert" ?>">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="cod_ciudad" value="<?= (int) $c["cod_ciudad"] ?>">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Código</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" value="<?= (int) $c["cod_ciudad"] ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ciudad</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="descrip_ciudad" maxlength="25" autocomplete="off"
                                       value="<?= e($c["descrip_ciudad"]) ?>" required autofocus>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Departamento</label>
                            <div class="col-sm-5">
                                <select class="form-control" name="id_departamento" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php while ($d = $departamentos->fetch_assoc()): ?>
                                        <option value="<?= (int) $d["id_departamento"] ?>"
                                            <?= $c["id_departamento"] !== null && (int) $c["id_departamento"] === (int) $d["id_departamento"] ? "selected" : "" ?>>
                                            <?= e($d["dep_descripcion"]) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="submit" class="btn btn-primary btn-submit" name="Guardar" value="Guardar">
                                <a href="?module=ciudad" class="btn btn-default btn-reset">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
