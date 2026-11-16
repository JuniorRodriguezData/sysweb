<?php
/**
 * Examen final - Formulario Proveedor (agregar y modificar).
 */
$es_edicion = ($_GET["form"] ?? "") === "edit";

if ($es_edicion) {
    $id   = (int) ($_GET["id"] ?? 0);
    $stmt = $mysqli->prepare("SELECT cod_proveedor, razon_social, ruc, direccion, telefono FROM proveedor WHERE cod_proveedor = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if (!$p) { ?>
        <section class="content">
            <div class="alert alert-danger"><h4><i class="icon fa fa-times-circle"></i> Proveedor no encontrado</h4></div>
            <a href="?module=proveedor" class="btn btn-default">Volver</a>
        </section>
        <?php return;
    }
} else {
    $p = ["cod_proveedor" => siguiente_codigo($mysqli, "proveedor"), "razon_social" => "", "ruc" => "", "direccion" => "", "telefono" => ""];
}
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> <?= $es_edicion ? "Modificar proveedor" : "Agregar proveedor" ?></h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=proveedor">Proveedor</a></li>
        <li class="active"><?= $es_edicion ? "Modificar" : "Agregar" ?></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <form role="form" class="form-horizontal" method="POST" id="form_proveedor"
                      action="modules/proveedor/proses.php?act=<?= $es_edicion ? "update" : "insert" ?>">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="cod_proveedor" value="<?= (int) $p["cod_proveedor"] ?>">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Código</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" value="<?= (int) $p["cod_proveedor"] ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Razón social <span class="text-red">*</span></label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="razon_social" id="razon_social" maxlength="75" autocomplete="off"
                                       placeholder="Ej.: Distribuidora Tecno S.A." value="<?= e($p["razon_social"]) ?>" required autofocus>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">RUC <span class="text-red">*</span></label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="ruc" id="ruc" maxlength="9" autocomplete="off"
                                       placeholder="80045678" pattern="[0-9]{5,8}(-[0-9])?" value="<?= e($p["ruc"]) ?>" required>
                                <span class="help-block">Solo números (5 a 8) o con dígito verificador: 1234567-8.</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Dirección</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="direccion" id="direccion" maxlength="50" autocomplete="off"
                                       placeholder="Ej.: Avda. Eusebio Ayala 2500" value="<?= e($p["direccion"]) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Teléfono <span class="text-red">*</span></label>
                            <div class="col-sm-3">
                                <input type="tel" class="form-control" name="telefono" id="telefono" maxlength="15" autocomplete="off"
                                       placeholder="021500100" pattern="[0-9]{6,15}" value="<?= e((string) $p["telefono"]) ?>" required>
                                <span class="help-block">Solo números, entre 6 y 15 dígitos.</span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="submit" class="btn btn-primary btn-submit" name="Guardar" value="Guardar">
                                <a href="?module=proveedor" class="btn btn-default btn-reset">Cancelar</a>
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
    document.getElementById("form_proveedor").addEventListener("submit", function (ev) {
        var errores = [];
        var razon = document.getElementById("razon_social").value.trim();
        var ruc = document.getElementById("ruc").value.trim();
        var dir = document.getElementById("direccion").value.trim();
        var tel = document.getElementById("telefono").value.trim();
        var texto = /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9 .,\-\/()°&]+$/;
        if (razon.length < 3 || !texto.test(razon)) errores.push("Razón social inválida (mínimo 3 caracteres).");
        if (!/^[0-9]{5,8}(-[0-9])?$/.test(ruc)) errores.push("RUC inválido. Ej.: 80045678 o 1234567-8.");
        if (dir !== "" && !texto.test(dir)) errores.push("La dirección contiene caracteres no permitidos.");
        if (!/^[0-9]{6,15}$/.test(tel)) errores.push("Teléfono inválido: solo números, 6 a 15 dígitos.");
        if (errores.length) {
            ev.preventDefault();
            alert(errores.join("\n"));
        }
    });
</script>
