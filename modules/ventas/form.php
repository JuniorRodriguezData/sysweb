<?php
/**
 * Examen final - Formulario para registrar una venta.
 * Los productos se eligen en una ventana modal (productos_pedidos.php, por AJAX) que muestra el stock
 * del depósito elegido; el detalle se arma en la tabla temporal tmp_venta (tmp.php).
 */
$clientes  = $mysqli->query("SELECT id_cliente, ci_ruc, cli_nombre, cli_apellido FROM clientes ORDER BY cli_apellido, cli_nombre");
$depositos = $mysqli->query("SELECT cod_deposito, descrip FROM deposito ORDER BY cod_deposito");
$factura   = (int) $mysqli->query("SELECT COALESCE(MAX(nro_factura), 0) + 1 FROM venta")->fetch_row()[0];
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> Registrar venta</h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=ventas">Ventas</a></li>
        <li class="active">Agregar</li>
    </ol>
</section>

<section class="content">
    <form role="form" class="form-horizontal" method="POST" action="modules/ventas/proses.php?act=insert" id="form_venta">
        <?= csrf_campo() ?>
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">Datos de la venta</h3></div>
            <div class="box-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label">Nro. de venta</label>
                    <div class="col-sm-2">
                        <input type="text" class="form-control" value="<?= siguiente_codigo($mysqli, "venta") ?>" readonly>
                    </div>
                    <label class="col-sm-2 control-label">Fecha <span class="text-red">*</span></label>
                    <div class="col-sm-3">
                        <input type="date" class="form-control" name="fecha" id="fecha" value="<?= date("Y-m-d") ?>" max="<?= date("Y-m-d") ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Cliente <span class="text-red">*</span></label>
                    <div class="col-sm-4">
                        <select class="form-control chosen-select" name="id_cliente" id="id_cliente" data-placeholder="-- Buscar cliente --" required>
                            <option value=""></option>
                            <?php while ($c = $clientes->fetch_assoc()): ?>
                                <option value="<?= (int) $c["id_cliente"] ?>"><?= e($c["cli_apellido"] . ", " . $c["cli_nombre"]) ?> (<?= e($c["ci_ruc"]) ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <label class="col-sm-2 control-label">Factura Nro. <span class="text-red">*</span></label>
                    <div class="col-sm-2">
                        <input type="number" class="form-control" name="nro_factura" id="nro_factura" min="1" max="99999999" step="1"
                               value="<?= $factura ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">Depósito <span class="text-red">*</span></label>
                    <div class="col-sm-4">
                        <select class="form-control" id="cod_deposito">
                            <?php while ($d = $depositos->fetch_assoc()): ?>
                                <option value="<?= (int) $d["cod_deposito"] ?>"><?= e($d["descrip"]) ?></option>
                            <?php endwhile; ?>
                        </select>
                        <span class="help-block">Depósito del que salen los productos que agregue.</span>
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
                <!-- tmp.php dibuja acá la tabla de productos de la venta con los cálculos -->
                <div id="resultados"><img src="assets/img/ajax-loader.gif" alt="Cargando..."></div>
            </div>
            <div class="box-footer">
                <input type="submit" class="btn btn-primary" name="Guardar" value="Guardar venta">
                <a href="?module=ventas" class="btn btn-default">Cancelar</a>
            </div>
        </div>
    </form>
</section>

<!-- Ventana modal para buscar productos con stock en el depósito elegido -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-cubes"></i> Buscar productos <small id="modal_deposito"></small></h4>
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
    var csrf = document.querySelector('meta[name="csrf"]').content;
    var loader = '<img src="assets/img/ajax-loader.gif" alt="Cargando...">';

    function deposito() { return $("#cod_deposito").val(); }

    /** Carga la página "page" de productos del depósito elegido (productos_pedidos.php). */
    function load(page) {
        $("#loader").html(loader);
        $("#modal_deposito").text("— " + $("#cod_deposito option:selected").text());
        $.get("modules/ventas/productos_pedidos.php", { q: $("#q").val(), page: page, deposito: deposito() }, function (html) {
            $("#outer_div").html(html);
            $("#loader").html("");
        });
    }

    /** Agrega el producto con la cantidad y el precio escritos en su fila (tmp.php controla el stock). */
    function agregar(id) {
        var cantidad = parseInt($("#cantidad_" + id).val(), 10);
        var precio = parseInt($("#precio_" + id).val(), 10);
        var maximo = parseInt($("#cantidad_" + id).attr("max"), 10);
        if (!(cantidad > 0) || !(precio > 0)) {
            alert("Ingrese una cantidad y un precio mayores a 0.");
            return;
        }
        if (cantidad > maximo) {
            alert("No hay stock suficiente: disponible " + maximo + ".");
            return;
        }
        $.post("modules/ventas/tmp.php", { accion: "agregar", id: id, deposito: deposito(), cantidad: cantidad, precio: precio, csrf: csrf },
            function (html) { mostrar(html); load(1); });
    }

    /** Quita un renglón de la venta. */
    function eliminar(id_tmp) {
        $.post("modules/ventas/tmp.php", { accion: "eliminar", id_tmp: id_tmp, csrf: csrf }, mostrar);
    }

    function mostrar(html) { $("#resultados").html(html); }

    document.addEventListener("DOMContentLoaded", function () {
        $.get("modules/ventas/tmp.php", mostrar);         // productos que ya estaban cargados en esta sesión
        $("#myModal").on("shown.bs.modal", function () { load(1); $("#q").focus(); });
        $("#q").on("keyup", function () { load(1); })
              .on("keydown", function (ev) { if (ev.key === "Enter") { ev.preventDefault(); } });
        $("#form_venta").on("submit", function (ev) {
            var errores = [];
            if (!$("#id_cliente").val()) errores.push("Seleccione el cliente.");
            if (!(parseInt($("#nro_factura").val(), 10) > 0)) errores.push("Ingrese un número de factura válido.");
            if (!$("#fecha").val() || $("#fecha").val() > $("#fecha").attr("max")) errores.push("La fecha no puede ser futura.");
            if (!$("#total_venta").length) errores.push("Agregue al menos un producto a la venta.");
            if (errores.length) {
                ev.preventDefault();
                alert(errores.join("\n"));
            }
        });
    });
</script>
