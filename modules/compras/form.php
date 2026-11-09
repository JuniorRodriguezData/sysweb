<?php
/**
 * Proyecto 11 - Formulario para agregar una compra, con ventana modal para seleccionar productos.
 * Proyecto 12 - Los productos se cargan con JavaScript (AJAX) desde productos_pedidos.php con paginación.
 * Proyecto 13 - La tabla de la compra (tmp.php) calcula importes y total.
 */
$proveedores = $mysqli->query("SELECT cod_proveedor, razon_social, ruc FROM proveedor ORDER BY razon_social");
$depositos   = $mysqli->query("SELECT cod_deposito, descrip FROM deposito ORDER BY cod_deposito");
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> Registrar compra</h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=compras">Compras</a></li>
        <li class="active">Agregar</li>
    </ol>
</section>

<section class="content">
    <form role="form" class="form-horizontal" method="POST" action="modules/compras/proses.php?act=insert" id="form_compra">
        <?= csrf_campo() ?>
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Datos de la compra</h3></div>
            <div class="box-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Nro. de compra</label>
                    <div class="col-sm-2">
                        <input type="text" class="form-control" value="<?= siguiente_codigo($mysqli, "compra") ?>" readonly>
                    </div>
                    <label class="col-sm-2 control-label">Fecha</label>
                    <div class="col-sm-3">
                        <input type="date" class="form-control" name="fecha" value="<?= date("Y-m-d") ?>" max="<?= date("Y-m-d") ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Proveedor</label>
                    <div class="col-sm-4">
                        <select class="form-control" name="cod_proveedor" required>
                            <option value="">-- Seleccionar --</option>
                            <?php while ($p = $proveedores->fetch_assoc()): ?>
                                <option value="<?= (int) $p["cod_proveedor"] ?>"><?= e($p["razon_social"]) ?> (RUC <?= e($p["ruc"]) ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <label class="col-sm-1 control-label">Depósito</label>
                    <div class="col-sm-3">
                        <select class="form-control" name="cod_deposito" required>
                            <?php while ($d = $depositos->fetch_assoc()): ?>
                                <option value="<?= (int) $d["cod_deposito"] ?>"><?= e($d["descrip"]) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Factura Nro.</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control" name="nro_factura" maxlength="25" autocomplete="off"
                               placeholder="001-001-0001234" pattern="[0-9]{3}-[0-9]{3}-[0-9]{1,7}"
                               title="Formato: 001-001-0001234" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Productos</h3>
                <button type="button" class="btn btn-success btn-sm pull-right" data-toggle="modal" data-target="#myModal">
                    <i class="fa fa-search"></i> Agregar productos
                </button>
            </div>
            <div class="box-body">
                <!-- tmp.php dibuja acá la tabla de productos de la compra con los cálculos -->
                <div id="resultados"><img src="assets/img/ajax-loader.gif" alt="Cargando..."></div>
            </div>
            <div class="box-footer">
                <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar compra">
                <a href="?module=compras" class="btn btn-default">Cancelar</a>
            </div>
        </div>
    </form>
</section>

<!-- Proyecto 11 - Ventana modal para buscar y cargar productos -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-cubes"></i> Buscar productos</h4>
            </div>
            <div class="modal-body">
                <div class="input-group">
                    <input type="text" class="form-control" id="q" placeholder="Buscar por nombre o tipo de producto" autocomplete="off">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default" onclick="load(1)"><i class="fa fa-search"></i> Buscar</button>
                    </span>
                </div>
                <div id="loader" style="text-align:center; margin-top:10px"></div>
                <div id="outer_div" style="margin-top:10px"></div><!-- productos_pedidos.php -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Proyecto 12 - JavaScript que llama a los archivos PHP para cargar las tablas
    var csrf = document.querySelector('meta[name="csrf"]').content;
    var loader = '<img src="assets/img/ajax-loader.gif" alt="Cargando...">';

    /** Carga la página "page" de productos en la ventana modal (productos_pedidos.php). */
    function load(page) {
        $("#loader").html(loader);
        $.get("modules/compras/productos_pedidos.php", { q: $("#q").val(), page: page }, function (html) {
            $("#outer_div").html(html);
            $("#loader").html("");
        });
    }

    /** Agrega el producto con la cantidad y el precio escritos en su fila. */
    function agregar(id) {
        var cantidad = $("#cantidad_" + id).val();
        var precio = $("#precio_" + id).val();
        if (!(cantidad > 0) || precio === "" || precio < 0) {
            alert("Ingrese una cantidad mayor a 0 y un precio válido.");
            return;
        }
        $.post("modules/compras/tmp.php", { accion: "agregar", id: id, cantidad: cantidad, precio: precio, csrf: csrf }, mostrar);
    }

    /** Quita un renglón de la compra. */
    function eliminar(id_tmp) {
        $.post("modules/compras/tmp.php", { accion: "eliminar", id_tmp: id_tmp, csrf: csrf }, mostrar);
    }

    function mostrar(html) { $("#resultados").html(html); }

    document.addEventListener("DOMContentLoaded", function () {
        $.get("modules/compras/tmp.php", mostrar);         // productos que ya estaban cargados en esta sesión
        $("#myModal").on("shown.bs.modal", function () { load(1); $("#q").focus(); });
        $("#q").on("keyup", function (ev) { if (ev.key === "Enter") { ev.preventDefault(); } load(1); })
              .on("keydown", function (ev) { if (ev.key === "Enter") { ev.preventDefault(); } });
        $("#form_compra").on("submit", function (ev) {
            if (!$("#total_compra").length) {
                ev.preventDefault();
                alert("Agregue al menos un producto a la compra.");
            }
        });
    });
</script>
