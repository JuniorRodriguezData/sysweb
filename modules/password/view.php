<?php
/**
 * Proyecto 3 - Formulario para cambiar la contraseña.
 */
$alertas = [
    1 => ["danger",  "Error!",   "La contraseña actual no es correcta."],
    2 => ["danger",  "Error!",   "La nueva contraseña y su repetición no coinciden."],
    3 => ["success", "Exitoso!", "La contraseña se cambió correctamente."],
    4 => ["danger",  "Error!",   "La nueva contraseña debe tener al menos 6 caracteres."],
    5 => ["danger",  "Error!",   "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;
?>
<section class="content-header">
    <h1><i class="fa fa-lock icon-title"></i> Modificar contraseña</h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li class="active">Contraseña</li>
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
                <form role="form" class="form-horizontal" method="POST" action="modules/password/proses.php">
                    <?= csrf_campo() ?>
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Contraseña actual</label>
                            <div class="col-sm-5">
                                <input type="password" class="form-control" name="old_pass" autocomplete="current-password" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Contraseña nueva</label>
                            <div class="col-sm-5">
                                <input type="password" class="form-control" name="new_pass" autocomplete="new-password" minlength="6" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Repetir nueva contraseña</label>
                            <div class="col-sm-5">
                                <input type="password" class="form-control" name="retype_pass" autocomplete="new-password" minlength="6" required>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="submit" class="btn btn-primary btn-submit" name="Guardar" value="Guardar">
                                <a href="?module=start" class="btn btn-default btn-reset">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
