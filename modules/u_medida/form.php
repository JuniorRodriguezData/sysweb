<?php
/**
 * Examen final - Formulario Unidad de medida (agregar y modificar).
 */
$es_edicion = ($_GET["form"] ?? "") === "edit";

if ($es_edicion) {
    $id   = (int) ($_GET["id"] ?? 0);
    $stmt = $mysqli->prepare("SELECT id_u_medida, u_descrip FROM u_medida WHERE id_u_medida = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if (!$r) { ?>
        <section class="content">
            <div class="alert alert-danger"><h4><i class="icon fa fa-times-circle"></i> Unidad de medida no encontrada</h4></div>
            <a href="?module=u_medida" class="btn btn-default">Volver</a>
        </section>
        <?php return;
    }
} else {
    $r = ["id_u_medida" => siguiente_codigo($mysqli, "u_medida"), "u_descrip" => ""];
}
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> <?= $es_edicion ? "Modificar unidad de medida" : "Agregar unidad de medida" ?></h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=u_medida">Unidad de medida</a></li>
        <li class="active"><?= $es_edicion ? "Modificar" : "Agregar" ?></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <form role="form" class="form-horizontal" method="POST" id="form_ref"
                      action="modules/u_medida/proses.php?act=<?= $es_edicion ? "update" : "insert" ?>">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id_u_medida" value="<?= (int) $r["id_u_medida"] ?>">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Código</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" value="<?= (int) $r["id_u_medida"] ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Descripción <span class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="u_descrip" id="u_descrip" maxlength="20" autocomplete="off"
                                       placeholder="Ej.: Unidad, Caja, Litro" value="<?= e($r["u_descrip"]) ?>" required autofocus>
                                <span class="help-block">Máximo 20 caracteres. Letras, números y signos básicos.</span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="submit" class="btn btn-primary btn-submit" name="Guardar" value="Guardar">
                                <a href="?module=u_medida" class="btn btn-default btn-reset">Cancelar</a>
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
    document.getElementById("form_ref").addEventListener("submit", function (ev) {
        var campo = document.getElementById("u_descrip");
        var valor = campo.value.trim();
        if (valor.length < 2 || !/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9 .,\-\/()°]+$/.test(valor)) {
            ev.preventDefault();
            campo.parentNode.parentNode.classList.add("has-error");
            alert("Ingrese una descripción válida (mínimo 2 caracteres, sin símbolos especiales).");
        }
    });
</script>
