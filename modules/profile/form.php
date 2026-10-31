<?php
/**
 * Proyecto 5 - Formulario para modificar el propio perfil.
 * El usuario no puede cambiar su nombre de usuario ni su nivel de acceso (eso lo hace el Super Admin).
 */
$stmt = $mysqli->prepare("SELECT username, name_user, email, telefono, foto, permisos_acceso FROM usuarios WHERE id_user = ?");
$stmt->bind_param("i", $_SESSION["id_user"]);
$stmt->execute();
$perfil = $stmt->get_result()->fetch_assoc();
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> Modificar perfil</h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=profile">Perfil</a></li>
        <li class="active">Modificar</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <form role="form" class="form-horizontal" method="POST" action="modules/profile/proses.php" enctype="multipart/form-data">
                    <?= csrf_campo() ?>
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Nombre de usuario</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" value="<?= e($perfil["username"]) ?>" disabled>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Nombre</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="name_user" maxlength="150" value="<?= e($perfil["name_user"]) ?>" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Email</label>
                            <div class="col-sm-5">
                                <input type="email" class="form-control" name="email" maxlength="150" value="<?= e($perfil["email"]) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Teléfono</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="telefono" maxlength="13"
                                       onKeyPress="return goodchars(event,'0123456789',this)" value="<?= e($perfil["telefono"]) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Foto</label>
                            <div class="col-sm-5">
                                <input type="file" name="foto" accept=".jpg,.jpeg,.png">
                                <p class="help-block">JPG o PNG, máximo 1 MB.</p>
                                <img style="border:1px solid #eaeaea;border-radius:5px;object-fit:cover" src="<?= foto_usuario($perfil["foto"]) ?>" width="128" height="128">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Permisos de acceso</label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" value="<?= e($perfil["permisos_acceso"]) ?>" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="submit" class="btn btn-primary btn-submit" name="Guardar" value="Guardar">
                                <a href="?module=profile" class="btn btn-default btn-reset">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
