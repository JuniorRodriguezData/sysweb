<?php
/**
 * Proyecto 9 - Formulario para agregar y modificar clientes.
 */
$es_edicion = ($_GET["form"] ?? "") === "edit";
$vacio = ["id_cliente" => siguiente_codigo($mysqli, "clientes"), "ci_ruc" => "", "cli_nombre" => "", "cli_apellido" => "",
          "cli_direccion" => "", "cli_telefono" => "", "cod_ciudad" => null];

if ($es_edicion) {
    $id   = (int) ($_GET["id"] ?? 0);
    $stmt = $mysqli->prepare("SELECT * FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $cl = $stmt->get_result()->fetch_assoc();
    if (!$cl) { ?>
        <section class="content">
            <div class="alert alert-danger"><h4><i class="icon fa fa-times-circle"></i> Cliente no encontrado</h4></div>
            <a href="?module=clientes" class="btn btn-default">Volver</a>
        </section>
        <?php return;
    }
} else {
    $cl = $vacio;
}

$ciudades = $mysqli->query("SELECT c.cod_ciudad, c.descrip_ciudad, d.dep_descripcion
                            FROM ciudad c JOIN departamento d ON d.id_departamento = c.id_departamento
                            ORDER BY c.descrip_ciudad");

/** Imprime un campo de texto del formulario. */
$campo = function (string $etiqueta, string $nombre, int $max, bool $requerido = true, string $extra = "") use ($cl) {
    printf('<div class="form-group"><label class="col-sm-2 control-label">%s</label><div class="col-sm-5">'
         . '<input type="text" class="form-control" name="%s" maxlength="%d" autocomplete="off" value="%s" %s %s></div></div>',
        $etiqueta, $nombre, $max, e($cl[$nombre]), $requerido ? "required" : "", $extra);
};
?>
<section class="content-header">
    <h1><i class="fa fa-edit icon-title"></i> <?= $es_edicion ? "Modificar cliente" : "Agregar cliente" ?></h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li><a href="?module=clientes">Clientes</a></li>
        <li class="active"><?= $es_edicion ? "Modificar" : "Agregar" ?></li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <form role="form" class="form-horizontal" method="POST"
                      action="modules/clientes/proses.php?act=<?= $es_edicion ? "update" : "insert" ?>">
                    <?= csrf_campo() ?>
                    <input type="hidden" name="id_cliente" value="<?= (int) $cl["id_cliente"] ?>">
                    <div class="box-body">
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Código</label>
                            <div class="col-sm-2">
                                <input type="text" class="form-control" value="<?= (int) $cl["id_cliente"] ?>" readonly>
                            </div>
                        </div>
                        <?php
                        $campo("C.I. / RUC", "ci_ruc", 10, true,
                            'pattern="[0-9]{1,8}(-[0-9])?" title="Solo números; RUC con guion y dígito verificador (ej.: 80012345-6)"');
                        $campo("Nombre", "cli_nombre", 30);
                        $campo("Apellido", "cli_apellido", 50);
                        $campo("Dirección", "cli_direccion", 50, false);
                        $campo("Teléfono", "cli_telefono", 20, false, "onKeyPress=\"return goodchars(event,'0123456789',this)\"");
                        ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Ciudad</label>
                            <div class="col-sm-5">
                                <select class="form-control" name="cod_ciudad" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php while ($c = $ciudades->fetch_assoc()): ?>
                                        <option value="<?= (int) $c["cod_ciudad"] ?>"
                                            <?= $cl["cod_ciudad"] !== null && (int) $cl["cod_ciudad"] === (int) $c["cod_ciudad"] ? "selected" : "" ?>>
                                            <?= e($c["descrip_ciudad"]) ?> (<?= e($c["dep_descripcion"]) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <div class="form-group">
                            <div class="col-sm-offset-2 col-sm-10">
                                <input type="submit" class="btn btn-primary btn-submit" name="Guardar" value="Guardar">
                                <a href="?module=clientes" class="btn btn-default btn-reset">Cancelar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
