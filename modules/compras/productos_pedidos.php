<?php
/**
 * Proyecto 12 - Devuelve (por AJAX) la tabla de productos de la ventana modal, con búsqueda y paginación.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";
require_once "paginacion.php";

if (!sesion_activa() || !puede("form_compras")) {
    http_response_code(403);
    exit("<div class='alert alert-danger'>Sesión vencida o sin permiso. Vuelva a iniciar sesión.</div>");
}

const POR_PAGINA = 5;
$q      = trim($_GET["q"] ?? "");
$pagina = max(1, (int) ($_GET["page"] ?? 1));
$like   = "%" . $q . "%";

// Total de productos que coinciden con la búsqueda
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM producto p JOIN tipo_producto t ON t.cod_tipo_prod = p.cod_tipo_prod
                          WHERE p.p_descrip LIKE ? OR t.t_p_descrip LIKE ?");
$stmt->bind_param("ss", $like, $like);
$stmt->execute();
$total_filas   = (int) $stmt->get_result()->fetch_row()[0];
$total_paginas = max(1, (int) ceil($total_filas / POR_PAGINA));
$pagina        = min($pagina, $total_paginas);
$desde         = ($pagina - 1) * POR_PAGINA;
$por_pagina    = POR_PAGINA;

// Productos de la página actual, con su stock total
$stmt = $mysqli->prepare("SELECT p.cod_producto, p.p_descrip, t.t_p_descrip, u.u_descrip, p.precio,
                                 COALESCE((SELECT SUM(s.cantidad) FROM stock s WHERE s.cod_producto = p.cod_producto), 0) AS stock
                          FROM producto p
                          JOIN tipo_producto t ON t.cod_tipo_prod = p.cod_tipo_prod
                          JOIN u_medida u      ON u.id_u_medida   = p.id_u_medida
                          WHERE p.p_descrip LIKE ? OR t.t_p_descrip LIKE ?
                          ORDER BY p.p_descrip LIMIT ?, ?");
$stmt->bind_param("ssii", $like, $like, $desde, $por_pagina);
$stmt->execute();
$productos = $stmt->get_result();

if ($total_filas === 0) {
    exit("<div class='alert alert-warning'>No se encontraron productos" . ($q !== "" ? " para «" . e($q) . "»" : "") . ".</div>");
}
?>
<table class="table table-bordered table-hover table-condensed">
    <thead>
        <tr>
            <th>Código</th><th>Producto</th><th>Tipo</th><th>Unidad</th><th class="text-right">Stock</th>
            <th width="90">Cantidad</th><th width="130">Precio (Gs.)</th><th width="50"></th>
        </tr>
    </thead>
    <tbody>
    <?php while ($p = $productos->fetch_assoc()): $id = (int) $p["cod_producto"]; ?>
        <tr>
            <td><?= $id ?></td>
            <td><?= e($p["p_descrip"]) ?></td>
            <td><?= e($p["t_p_descrip"]) ?></td>
            <td><?= e($p["u_descrip"]) ?></td>
            <td class="text-right"><?= (int) $p["stock"] ?></td>
            <td><input type="number" class="form-control input-sm" id="cantidad_<?= $id ?>" value="1" min="1" max="100000"></td>
            <td><input type="number" class="form-control input-sm" id="precio_<?= $id ?>" value="<?= (int) $p["precio"] ?>" min="0" step="1"></td>
            <td class="center">
                <button type="button" class="btn btn-success btn-sm" title="Agregar" onclick="agregar(<?= $id ?>)">
                    <i class="glyphicon glyphicon-plus"></i>
                </button>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<div class="clearfix">
    <span class="pull-left text-muted" style="margin-top:10px">
        Mostrando <?= $desde + 1 ?> a <?= min($desde + POR_PAGINA, $total_filas) ?> de <?= $total_filas ?> productos
    </span>
    <span class="pull-right"><?= paginate($pagina, $total_paginas) ?></span>
</div>
