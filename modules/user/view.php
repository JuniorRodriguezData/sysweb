<?php
/**
 * Proyecto 4 - Tabla de usuarios con las opciones agregar, editar, activar y bloquear.
 */
$alertas = [
    1 => ["success", "Éxito!", "El nuevo usuario se registró correctamente."],
    2 => ["success", "Éxito!", "Los datos del usuario se modificaron correctamente."],
    3 => ["success", "Éxito!", "El usuario fue activado correctamente."],
    4 => ["success", "Éxito!", "El usuario fue bloqueado correctamente."],
    5 => ["danger",  "Error!", "No se pudo subir la imagen. Intente nuevamente."],
    6 => ["danger",  "Error!", "La imagen no puede pesar más de 1 MB."],
    7 => ["danger",  "Error!", "La imagen debe ser de tipo JPG, JPEG o PNG."],
    8 => ["danger",  "Error!", "Ese nombre de usuario ya existe. Elija otro."],
    9 => ["danger",  "Error!", "No podés bloquear tu propio usuario."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;

$usuarios = $mysqli->query("SELECT id_user, username, name_user, email, telefono, foto, permisos_acceso, status
                            FROM usuarios ORDER BY id_user DESC");
?>
<section class="content-header">
    <h1>
        <i class="fa fa-users icon-title"></i> Gestión de usuarios
        <a class="btn btn-primary btn-social pull-right" href="?module=form_user&form=add" title="Agregar" data-toggle="tooltip">
            <i class="fa fa-plus"></i> Agregar
        </a>
    </h1>
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
                                <th class="center">No.</th>
                                <th class="center">Foto</th>
                                <th class="center">Usuario</th>
                                <th class="center">Nombre</th>
                                <th class="center">Email</th>
                                <th class="center">Permisos de acceso</th>
                                <th class="center">Estado</th>
                                <th class="center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $nro = 1; while ($u = $usuarios->fetch_assoc()): ?>
                            <tr>
                                <td width="40" class="center"><?= $nro++ ?></td>
                                <td class="center"><img class="img-user img-circle" src="<?= foto_usuario($u["foto"]) ?>" width="40" height="40" style="object-fit:cover"></td>
                                <td><?= e($u["username"]) ?></td>
                                <td><?= e($u["name_user"]) ?></td>
                                <td><?= e($u["email"]) ?></td>
                                <td><?= e($u["permisos_acceso"]) ?></td>
                                <td class="center">
                                    <span class="label label-<?= $u["status"] === "activo" ? "success" : "danger" ?>"><?= e($u["status"]) ?></span>
                                </td>
                                <td class="center" width="110">
                                    <!-- Activar / bloquear: formulario POST con token CSRF -->
                                    <form method="POST" action="modules/user/proses.php?act=<?= $u["status"] === "activo" ? "off" : "on" ?>" style="display:inline">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $u["id_user"] ?>">
                                        <?php if ($u["status"] === "activo"): ?>
                                            <button type="submit" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Bloquear"
                                                    <?= (int) $u["id_user"] === $_SESSION["id_user"] ? "disabled" : "" ?>>
                                                <i style="color:#fff" class="glyphicon glyphicon-off"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-success btn-sm" data-toggle="tooltip" title="Activar">
                                                <i style="color:#fff" class="glyphicon glyphicon-ok"></i>
                                            </button>
                                        <?php endif; ?>
                                    </form>
                                    <a data-toggle="tooltip" title="Modificar" class="btn btn-primary btn-sm"
                                       href="?module=form_user&form=edit&id=<?= (int) $u["id_user"] ?>">
                                        <i style="color:#fff" class="glyphicon glyphicon-edit"></i>
                                    </a>
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
