<?php
/**
 * Examen final - Formulario Producto (agregar y modificar).
 */
$es_edicion = ($_GET["form"] ?? "") === "edit";

if ($es_edicion) {
    $id   = (int) ($_GET["id"] ?? 0);
    $stmt = $mysqli->prepare("SELECT cod_producto, cod_tipo_prod, id_u_medida, p_descrip, precio FROM producto WHERE cod_producto = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if (!$p) { ?>
        <section class="content">
            <div class="alert alert-danger"><h4><i class="icon fa fa-times-circle"></i> Producto no encontrado</h4></div>
            <a href="?module=producto" class="btn btn-default">Volver</a>
        </section>
        <?php return;
    }
} else {
    $p = ["cod_producto" => siguiente_codigo($mysqli, "producto"), "cod_tipo_prod" => null, "id_u_medida" => null,
          "p_descrip" => "", "precio" => ""];
}

$tipos    = $mysqli->query("SELECT cod_tipo_prod, t_p_descrip FROM tipo_producto ORDER BY t_p_descrip")->fetch_all(MYSQLI_ASSOC);
$unidades = $mysqli->query("SELECT id_u_medida, u_descrip FROM u_medida ORDER BY u_descrip")->fetch_all(MYSQLI_ASSOC);
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> <?= $es_edicion ? "Modificar producto" : "Agregar producto" ?></h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=producto">Producto</a></li>
        <li class="active"><?= $es_edicion ? "Modificar" : "Agregar" ?></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <form role="form" class="form-horizontal" method="POST" id="form_producto"
                      action="modules/producto/proses.php?act=<?= $es_edicion ? "update" : "insert" ?>">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="cod_producto" value="<?= (int) $p["cod_producto"] ?>">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Código</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" value="<?= (int) $p["cod_producto"] ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Descripción <span class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="p_descrip" id="p_descrip" maxlength="50" autocomplete="off"
                                       placeholder="Ej.: Mouse inalámbrico" value="<?= e($p["p_descrip"]) ?>" required autofocus>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Tipo de producto <span class="text-red">*</span></label>
                            <div class="col-sm-4">
                                <select class="form-control chosen-select" name="cod_tipo_prod" id="cod_tipo_prod" data-placeholder="-- Seleccionar --" required>
                                    <option value=""></option>
                                    <?php foreach ($tipos as $t): ?>
                                        <option value="<?= (int) $t["cod_tipo_prod"] ?>"
                                            <?= (int) $p["cod_tipo_prod"] === (int) $t["cod_tipo_prod"] ? "selected" : "" ?>><?= e($t["t_p_descrip"]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Unidad de medida <span class="text-red">*</span></label>
                            <div class="col-sm-4">
                                <select class="form-control chosen-select" name="id_u_medida" id="id_u_medida" data-placeholder="-- Seleccionar --" required>
                                    <option value=""></option>
                                    <?php foreach ($unidades as $u): ?>
                                        <option value="<?= (int) $u["id_u_medida"] ?>"
                                            <?= (int) $p["id_u_medida"] === (int) $u["id_u_medida"] ? "selected" : "" ?>><?= e($u["u_descrip"]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Precio (Gs.) <span class="text-red">*</span></label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <span class="input-group-addon">Gs.</span>
                                    <input type="number" class="form-control" name="precio" id="precio" min="1" max="999999999" step="1"
                                           value="<?= e((string) $p["precio"]) ?>" required>
                                </div>
                                <span class="help-block">Número entero, sin puntos ni decimales.</span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="submit" class="btn btn-primary btn-submit" name="Guardar" value="Guardar">
                                <a href="?module=producto" class="btn btn-default btn-reset">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    // Validación en el navegador (el servidor vuelve a validar en proses.php)
    document.getElementById("form_producto").addEventListener("submit", function (ev) {
        var errores = [];
        var descrip = document.getElementById("p_descrip").value.trim();
        var precio = document.getElementById("precio").value;
        if (descrip.length < 2 || !/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9 .,\-\/()°&]+$/.test(descrip)) errores.push("Descripción inválida (mínimo 2 caracteres).");
        if (!document.getElementById("cod_tipo_prod").value) errores.push("Seleccione el tipo de producto.");
        if (!document.getElementById("id_u_medida").value) errores.push("Seleccione la unidad de medida.");
        if (!/^[0-9]+$/.test(precio) || parseInt(precio, 10) < 1) errores.push("El precio debe ser un número entero mayor a 0.");
        if (errores.length) {
            ev.preventDefault();
            alert(errores.join("\n"));
        }
    });
</script>
