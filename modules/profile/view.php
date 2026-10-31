<?php
/**
 * Proyecto 5 - Vista del perfil del usuario que inició sesión.
 */
$stmt = $mysqli->prepare("SELECT username, name_user, email, telefono, foto, permisos_acceso, status
                          FROM usuarios WHERE id_user = ?");
$stmt->bind_param("i", $_SESSION["id_user"]);
$stmt->execute();
$perfil = $stmt->get_result()->fetch_assoc();

$alertas = [
    1 => ["success", "Éxito!", "Tu perfil se actualizó correctamente."],
    5 => ["danger",  "Error!", "No se pudo subir la imagen. Intente nuevamente."],
    6 => ["danger",  "Error!", "La imagen no puede pesar más de 1 MB."],
    7 => ["danger",  "Error!", "La imagen debe ser de tipo JPG, JPEG o PNG."],
    10 => ["danger", "Error!", "Datos incompletos o inválidos. Revise el formulario."],
    11 => ["danger", "Error!", "La solicitud no es válida. Recargue la página e intente de nuevo."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;
?>
<section class="content-header">
    <h1><i class="fa fa-user icon-title"></i> Mi perfil</h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li class="active">Perfil</li>
    </ol>
</section>

<section class="content">
    <?php if ($alerta): ?>
        <div class="alert alert-<?= $alerta[0] ?> alert-dismissable">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h4><i class="icon fa <?= $alerta[0] === "success" ? "fa-check-circle" : "fa-times-circle" ?>"></i> <?= $alerta[1] ?></h4>
            <?= $alerta[2] ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="box box-primary">
                <div class="box-body box-profile">
                    <img class="profile-user-img img-responsive img-circle" style="width:128px;height:128px;object-fit:cover"
                         src="<?= foto_usuario($perfil["foto"]) ?>" alt="Foto de perfil">
                    <h3 class="profile-username text-center"><?= e($perfil["name_user"]) ?></h3>
                    <p class="text-muted text-center"><?= e($perfil["permisos_acceso"]) ?></p>
                    <a href="?module=form_profile" class="btn btn-primary btn-block"><i class="fa fa-edit"></i> Modificar perfil</a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="box box-primary">
                <div class="box-header with-border"><h3 class="box-title">Datos de la cuenta</h3></div>
                <div class="box-body">
                    <table class="table table-striped">
                        <tr><th width="35%">Nombre de usuario</th><td><?= e($perfil["username"]) ?></td></tr>
                        <tr><th>Nombre</th><td><?= e($perfil["name_user"]) ?></td></tr>
                        <tr><th>Email</th><td><?= e($perfil["email"]) ?: "<span class='text-muted'>Sin cargar</span>" ?></td></tr>
                        <tr><th>Teléfono</th><td><?= e($perfil["telefono"]) ?: "<span class='text-muted'>Sin cargar</span>" ?></td></tr>
                        <tr><th>Permisos de acceso</th><td><?= e($perfil["permisos_acceso"]) ?></td></tr>
                        <tr><th>Estado</th><td><span class="label label-success"><?= e($perfil["status"]) ?></span></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
