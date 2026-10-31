<?php
/**
 * Proyecto 3 - Contenido: carga el módulo pedido en ?module=...
 * Antes de mostrarlo verifica en el servidor que el nivel del usuario tenga permiso.
 */
$vistas = [
    "start"        => "modules/start/view.php",
    "password"     => "modules/password/view.php",
    "user"         => "modules/user/view.php",
    "form_user"    => "modules/user/form.php",
    "profile"      => "modules/profile/view.php",
    "form_profile" => "modules/profile/form.php",
];

if (!isset(PERMISOS_MODULOS[$module])) {
    $titulo_aviso = "Módulo no encontrado";
    $texto_aviso  = "La sección solicitada no existe.";
} elseif (!puede($module)) {
    $titulo_aviso = "Acceso denegado";
    $texto_aviso  = "Tu nivel de usuario (" . e($_SESSION["permisos_acceso"]) . ") no tiene permiso para esta sección.";
} elseif (!isset($vistas[$module])) {
    $titulo_aviso = "Módulo en desarrollo";
    $texto_aviso  = "Esta sección se incorporará en las próximas etapas del proyecto.";
} else {
    include $vistas[$module];
    return;
}
?>
<section class="content-header">
    <h1><i class="fa fa-info-circle icon-title"></i> <?= $titulo_aviso ?></h1>
</section>
<section class="content">
    <div class="alert alert-warning">
        <h4><i class="icon fa fa-warning"></i> <?= $titulo_aviso ?></h4>
        <?= $texto_aviso ?>
    </div>
    <a href="?module=start" class="btn btn-primary"><i class="fa fa-home"></i> Volver al inicio</a>
</section>
