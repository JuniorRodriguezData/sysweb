<?php
/**
 * Proyecto 2 y 5 - Menú lateral según el nivel de usuario.
 *   Super Admin: todo.  Compras: referenciales de compras.  Ventas: referenciales de ventas.
 */
$nivel = $_SESSION["permisos_acceso"];

/** Devuelve "active" si el módulo actual es uno de los indicados (resalta la opción del menú). */
$activo = fn(string ...$modulos) => in_array($module, $modulos, true) ? "active" : "";
?>
<ul class="sidebar-menu">
    <li class="header">MENÚ · <?= e(strtoupper($nivel)) ?></li>

    <li class="<?= $activo("start") ?>">
        <a href="?module=start"><i class="fa fa-home"></i> <span>Inicio</span></a>
    </li>

    <?php if ($nivel === "Super Admin"): ?>
        <li class="treeview <?= $activo("departamento", "form_departamento", "ciudad", "form_ciudad") ?>">
            <a href="javascript:void(0);">
                <i class="fa fa-file-text"></i><span>Referenciales Generales</span><i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li class="<?= $activo("departamento", "form_departamento") ?>"><a href="?module=departamento"><i class="fa fa-circle-o"></i> Departamento</a></li>
                <li class="<?= $activo("ciudad", "form_ciudad") ?>"><a href="?module=ciudad"><i class="fa fa-circle-o"></i> Ciudad</a></li>
            </ul>
        </li>
    <?php endif; ?>

    <?php if ($nivel === "Super Admin" || $nivel === "Compras"): ?>
        <li class="treeview <?= $activo("deposito", "form_deposito", "proveedor", "form_proveedor", "producto", "form_producto",
                                        "u_medida", "form_u_medida", "tipo_producto", "form_tipo_producto") ?>">
            <a href="javascript:void(0);">
                <i class="fa fa-truck"></i><span>Referenciales de Compras</span><i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li class="<?= $activo("deposito", "form_deposito") ?>"><a href="?module=deposito"><i class="fa fa-circle-o"></i> Depósito</a></li>
                <li class="<?= $activo("proveedor", "form_proveedor") ?>"><a href="?module=proveedor"><i class="fa fa-circle-o"></i> Proveedor</a></li>
                <li class="<?= $activo("producto", "form_producto") ?>"><a href="?module=producto"><i class="fa fa-circle-o"></i> Producto</a></li>
                <li class="<?= $activo("tipo_producto", "form_tipo_producto") ?>"><a href="?module=tipo_producto"><i class="fa fa-circle-o"></i> Tipo de producto</a></li>
                <li class="<?= $activo("u_medida", "form_u_medida") ?>"><a href="?module=u_medida"><i class="fa fa-circle-o"></i> Unidad de medida</a></li>
            </ul>
        </li>
    <?php endif; ?>

    <?php if ($nivel === "Super Admin" || $nivel === "Ventas"): ?>
        <li class="treeview <?= $activo("clientes", "form_clientes") ?>">
            <a href="javascript:void(0);">
                <i class="fa fa-certificate"></i><span>Referenciales de Ventas</span><i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
                <li class="<?= $activo("clientes", "form_clientes") ?>"><a href="?module=clientes"><i class="fa fa-circle-o"></i> Clientes</a></li>
            </ul>
        </li>
    <?php endif; ?>

    <li class="header">MOVIMIENTOS</li>
    <?php if (puede("compras")): ?>
        <li class="<?= $activo("compras", "form_compras", "detalle_compra") ?>">
            <a href="?module=compras"><i class="fa fa-shopping-cart"></i> <span>Compras</span></a>
        </li>
    <?php endif; ?>
    <?php if (puede("ventas")): ?>
        <li class="<?= $activo("ventas", "form_ventas", "detalle_venta") ?>">
            <a href="?module=ventas"><i class="fa fa-cart-plus"></i> <span>Ventas</span></a>
        </li>
    <?php endif; ?>
    <li class="<?= $activo("stock") ?>">
        <a href="?module=stock"><i class="fa fa-cubes"></i> <span>Stock de productos</span></a>
    </li>

    <?php if (puede("user")): ?>
        <li class="<?= $activo("user", "form_user") ?>">
            <a href="?module=user"><i class="fa fa-users"></i> <span>Administrar usuarios</span></a>
        </li>
    <?php endif; ?>

    <li class="header">MI CUENTA</li>
    <li class="<?= $activo("profile", "form_profile") ?>">
        <a href="?module=profile"><i class="fa fa-user"></i> <span>Mi perfil</span></a>
    </li>
    <li class="<?= $activo("password") ?>">
        <a href="?module=password"><i class="fa fa-lock"></i> <span>Cambiar contraseña</span></a>
    </li>
    <li>
        <a data-toggle="modal" href="#logout"><i class="fa fa-sign-out"></i> <span>Salir</span></a>
    </li>
</ul>
