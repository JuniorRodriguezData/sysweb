<?php
/**
 * Proyecto 4 - Formulario para agregar (form=add) y editar (form=edit) usuarios.
 */
$es_edicion = ($_GET["form"] ?? "") === "edit";
$u = ["id_user" => "", "username" => "", "name_user" => "", "email" => "", "telefono" => "", "foto" => "", "permisos_acceso" => ""];

if ($es_edicion) {
    $id   = (int) ($_GET["id"] ?? 0);
    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE id_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();

    if (!$u) { ?>
        <section class="content">
            <div class="alert alert-danger"><h4><i class="icon fa fa-times-circle"></i> Usuario no encontrado</h4></div>
            <a href="?module=user" class="btn btn-default">Volver</a>
        </section>
        <?php return;
    }
}
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> <?= $es_edicion ? "Modificar datos de usuario" : "Agregar usuario" ?></h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=user">Usuarios</a></li>
        <li class="active"><?= $es_edicion ? "Modificar" : "Agregar" ?></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <form role="form" class="form-horizontal" method="POST" enctype="multipart/form-data"
                      action="modules/user/proses.php?act=<?= $es_edicion ? "update" : "insert" ?>">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id_user" value="<?= (int) $u["id_user"] ?>">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Nombre de usuario</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="username" autocomplete="off" maxlength="50"
                                       pattern="[A-Za-z0-9_.]{3,50}" title="De 3 a 50 letras, números, punto o guion bajo"
                                       value="<?= e($u["username"]) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Contraseña</label>
                            <div class="col-sm-5">
                                <input type="password" class="form-control" name="password" autocomplete="new-password" minlength="6"
                                       <?= $es_edicion ? 'placeholder="Dejar vacío para no cambiarla"' : "required" ?>>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Nombre</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="name_user" autocomplete="off" maxlength="150"
                                       value="<?= e($u["name_user"]) ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-5">
                                <input type="email" class="form-control" name="email" autocomplete="off" maxlength="150" value="<?= e($u["email"]) ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Teléfono</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="telefono" autocomplete="off" maxlength="13"
                                       onKeyPress="return goodchars(event,'0123456789',this)" value="<?= e($u["telefono"]) ?>">
                            </div>
                        </div>

                        <?php if ($es_edicion): ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">Foto</label>
                                <div class="col-sm-5">
                                    <input type="file" name="foto" accept=".jpg,.jpeg,.png">
                                    <p class="help-block">JPG o PNG, máximo 1 MB.</p>
                                    <img style="border:1px solid #eaeaea;border-radius:5px;object-fit:cover" src="<?= foto_usuario($u["foto"]) ?>" width="128" height="128">
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label class="col-sm-2 control-label">Permisos de acceso</label>
                            <div class="col-sm-5">
                                <select class="form-control" name="permisos_acceso" required>
                                    <option value=""></option>
                                    <?php foreach (NIVELES as $nivel): ?>
                                        <option value="<?= $nivel ?>" <?= $u["permisos_acceso"] === $nivel ? "selected" : "" ?>><?= $nivel ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="submit" class="btn btn-primary btn-submit" name="Guardar" value="Guardar">
                                <a href="?module=user" class="btn btn-default btn-reset">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
