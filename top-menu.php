<?php
/**
 * Proyecto 2 - Menú superior: foto, nombre y nivel del usuario, acceso al perfil y salir.
 */
$stmt = $mysqli->prepare("SELECT name_user, foto, permisos_acceso FROM usuarios WHERE id_user = ?");
$stmt->bind_param("i", $_SESSION["id_user"]);
$stmt->execute();
$usuario_top = $stmt->get_result()->fetch_assoc();
?>
<li class="dropdown user user-menu">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        <img src="<?= foto_usuario($usuario_top["foto"]) ?>" class="user-image" alt="Imagen de usuario">
        <span class="hidden-xs"><?= e($usuario_top["name_user"]) ?><i style="margin-left:5px" class="fa fa-angle-down"></i></span>
    </a>
    <ul class="dropdown-menu">
        <li class="user-header">
            <img src="<?= foto_usuario($usuario_top["foto"]) ?>" class="img-circle" alt="Imagen de usuario">
            <p>
                <?= e($usuario_top["name_user"]) ?>
                <small><?= e($usuario_top["permisos_acceso"]) ?></small>
            </p>
        </li>
        <li class="user-footer">
            <div class="pull-left">
                <a style="width:80px" href="?module=profile" class="btn btn-default btn-flat">Perfil</a>
            </div>
            <div class="pull-right">
                <a style="width:80px" data-toggle="modal" href="#logout" class="btn btn-default btn-flat">Salir</a>
            </div>
        </li>
    </ul>
</li>
