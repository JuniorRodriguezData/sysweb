<?php
/**
 * Proyecto 14 - Stock de productos: existencias por depósito.
 */
$depositos = $mysqli->query("SELECT cod_deposito, descrip FROM deposito ORDER BY cod_deposito")->fetch_all(MYSQLI_ASSOC);
$deposito  = (int) ($_GET["deposito"] ?? 0);   // 0 = todos los depósitos

$sql = "SELECT d.descrip AS deposito, p.cod_producto, p.p_descrip, t.t_p_descrip, u.u_descrip, p.precio,
               COALESCE(s.cantidad, 0) AS cantidad
        FROM producto p
        CROSS JOIN deposito d
        JOIN tipo_producto t ON t.cod_tipo_prod = p.cod_tipo_prod
        JOIN u_medida u      ON u.id_u_medida   = p.id_u_medida
        LEFT JOIN stock s    ON s.cod_producto  = p.cod_producto AND s.cod_deposito = d.cod_deposito
        WHERE (? = 0 OR d.cod_deposito = ?)
        ORDER BY d.cod_deposito, p.p_descrip";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ii", $deposito, $deposito);
$stmt->execute();
$filas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$valor_total = array_sum(array_map(fn($f) => $f["cantidad"] * $f["precio"], $filas));
$sin_stock   = count(array_filter($filas, fn($f) => (int) $f["cantidad"] === 0));
?>
<section class="content-header">
    <h1>
        <i class="fa fa-cubes icon-title"></i> Stock de productos
        <a class="btn btn-default btn-social pull-right" href="modules/stock/reporte.php?deposito=<?= $deposito ?>" target="_blank" title="Imprimir reporte" data-toggle="tooltip">
            <i class="fa fa-print"></i> Imprimir
        </a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="?module=start"><i class="fa fa-home"></i> Inicio</a></li>
        <li>Movimientos</li>
        <li class="active">Stock</li>
    </ol>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-4 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-cubes"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Unidades en stock</span>
                    <span class="info-box-number"><?= gs(array_sum(array_column($filas, "cantidad"))) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valor del inventario</span>
                    <span class="info-box-number">Gs. <?= gs($valor_total) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Productos sin stock</span>
                    <span class="info-box-number"><?= $sin_stock ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <form method="GET" class="form-inline">
                <input type="hidden" name="module" value="stock">
                <label>Depósito:&nbsp;</label>
                <select name="deposito" class="form-control" onchange="this.form.submit()">
                    <option value="0">Todos</option>
                    <?php foreach ($depositos as $d): ?>
                        <option value="<?= (int) $d["cod_deposito"] ?>" <?= $deposito === (int) $d["cod_deposito"] ? "selected" : "" ?>><?= e($d["descrip"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="box-body">
            <table id="dataTables1" class="table table-bordered table-striped table-hover">
                <thead>
                    <tr>
                        <th>Depósito</th><th>Código</th><th>Producto</th><th>Tipo</th><th>Unidad</th>
                        <th class="text-right">Existencia</th><th class="text-right">Precio (Gs.)</th><th class="text-right">Valor (Gs.)</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($filas as $f): ?>
                    <tr>
                        <td><?= e($f["deposito"]) ?></td>
                        <td><?= (int) $f["cod_producto"] ?></td>
                        <td><?= e($f["p_descrip"]) ?></td>
                        <td><?= e($f["t_p_descrip"]) ?></td>
                        <td><?= e($f["u_descrip"]) ?></td>
                        <td class="text-right">
                            <?php if ((int) $f["cantidad"] === 0): ?>
                                <span class="label label-danger">0</span>
                            <?php else: ?>
                                <strong><?= gs($f["cantidad"]) ?></strong>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= gs($f["precio"]) ?></td>
                        <td class="text-right"><?= gs($f["cantidad"] * $f["precio"]) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
