<?php
/**
 * Proyecto 3 y 5 - Pantalla de inicio: bloques de acceso según el nivel del usuario.
 */
$nivel = $_SESSION["permisos_acceso"];

// Bloques del panel: [módulo, título, líneas, ícono, color]
$bloques = [
    ["compras", "Compras",            ["Registrar", "la compra", "de productos"], "glyphicon glyphicon-piggy-bank", "#00c0ef"],
    ["ventas",  "Ventas",             ["Registrar", "la venta", "de productos"],  "fa fa-cart-plus",                "#00a65a"],
    ["stock",   "Stock de productos", ["Visualizar", "el stock", "de productos"], "fa fa-area-chart",               "#f39c12"],
];

$total_usuarios = null;
if (puede("user")) {
    $total_usuarios = $mysqli->query("SELECT COUNT(*) AS total, SUM(status = 'activo') AS activos FROM usuarios")->fetch_assoc();
}
?>
<section class="content-header">
    <h1><i class="fa fa-home icon-title"></i> Inicio</h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i></a></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-lg-12 col-xs-12">
            <div class="alert alert-info alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <p style="font-size:15px">
                    <i class="icon fa fa-user"></i> Bienvenido/a <strong><?= e($_SESSION["name_user"]) ?></strong>
                    a la aplicación <strong>SysWeb</strong>. Nivel de acceso: <strong><?= e($nivel) ?></strong>.
                </p>
            </div>
        </div>
    </div>

    <h2>Formularios de movimiento</h2>
    <div class="row">
        <?php foreach ($bloques as [$modulo, $titulo, $lineas, $icono, $color]): ?>
            <?php if (puede($modulo)): ?>
                <div class="col-lg-4 col-xs-6">
                    <div style="background-color:<?= $color ?>; color:#fff" class="small-box">
                        <div class="inner">
                            <p><strong><?= $titulo ?></strong></p>
                            <ul><?php foreach ($lineas as $l): ?><li><?= $l ?></li><?php endforeach; ?></ul>
                        </div>
                        <div class="icon"><i class="<?= $icono ?>"></i></div>
                        <a href="?module=<?= $modulo ?>" class="small-box-footer" title="<?= $titulo ?>" data-toggle="tooltip">
                            <i class="fa fa-plus"></i></a>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($total_usuarios): ?>
            <div class="col-lg-4 col-xs-6">
                <div style="background-color:#605ca8; color:#fff" class="small-box">
                    <div class="inner">
                        <h3><?= (int) $total_usuarios["total"] ?></h3>
                        <p>Usuarios registrados (<?= (int) $total_usuarios["activos"] ?> activos)</p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                    <a href="?module=user" class="small-box-footer">Administrar usuarios <i class="fa fa-arrow-circle-right"></i></a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
