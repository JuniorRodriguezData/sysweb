<?php
/**
 * Examen final - Formulario Depósito (agregar y modificar).
 */
$es_edicion = ($_GET["form"] ?? "") === "edit";

if ($es_edicion) {
    $id   = (int) ($_GET["id"] ?? 0);
    $stmt = $mysqli->prepare("SELECT cod_deposito, descrip FROM deposito WHERE cod_deposito = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $r = $stmt->get_result()->fetch_assoc();
    if (!$r) { ?>
        <section class="content">
            <div class="alert alert-danger"><h4><i class="icon fa fa-times-circle"></i> Depósito no encontrado</h4></div>
            <a href="?module=deposito" class="btn btn-default">Volver</a>
        </section>
        <?php return;
    }
} else {
    $r = ["cod_deposito" => siguiente_codigo($mysqli, "deposito"), "descrip" => ""];
}
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> <?= $es_edicion ? "Modificar depósito" : "Agregar depósito" ?></h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=deposito">Depósito</a></li>
        <li class="active"><?= $es_edicion ? "Modificar" : "Agregar" ?></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <form role="form" class="form-horizontal" method="POST" id="form_ref"
                      action="modules/deposito/proses.php?act=<?= $es_edicion ? "update" : "insert" ?>">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="cod_deposito" value="<?= (int) $r["cod_deposito"] ?>">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Código</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" value="<?= (int) $r["cod_deposito"] ?>" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Descripción <span class="text-red">*</span></label>
                            <div class="col-sm-5">
                                <input type="text" class="form-control" name="descrip" id="descrip" maxlength="50" autocomplete="off"
                                       placeholder="Ej.: Depósito Central" value="<?= e($r["descrip"]) ?>" required autofocus>
                                <span class="help-block">Máximo 50 caracteres. Letras, números y signos básicos.</span>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="submit" class="btn btn-primary btn-submit" name="Guardar" value="Guardar">
                                <a href="?module=deposito" class="btn btn-default btn-reset">Cancelar</a>
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
        var campo = document.getElementById("descrip");
        var valor = campo.value.trim();
        if (valor.length < 2 || !/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9 .,\-\/()°]+$/.test(valor)) {
            ev.preventDefault();
            campo.parentNode.parentNode.classList.add("has-error");
            alert("Ingrese una descripción válida (mínimo 2 caracteres, sin símbolos especiales).");
        }
    });
</script>
