<?php
/**
 * Examen final - Tabla temporal de la venta (tmp_venta, por sesión).
 *   POST accion=agregar  (id, deposito, cantidad, precio) agrega o suma un producto, sin superar el stock.
 *   POST accion=eliminar (id_tmp)                          quita un renglón.
 *   GET                                                    solo dibuja la tabla.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

if (!sesion_activa() || !puede("form_ventas")) {
    http_response_code(403);
    exit("<div class='alert alert-danger'>Sesión vencida o sin permiso. Vuelva a iniciar sesión.</div>");
}

$sesion = session_id();
$aviso  = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!csrf_valido()) {
        http_response_code(400);
        exit("<div class='alert alert-danger'>Solicitud no válida. Recargue la página.</div>");
    }
    $accion = $_POST["accion"] ?? "";

    if ($accion === "agregar") {
        $id       = filter_var($_POST["id"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $deposito = filter_var($_POST["deposito"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
        $cantidad = filter_var($_POST["cantidad"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 100000]]);
        $precio   = filter_var($_POST["precio"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 999999999]]);

        if ($id === false || $deposito === false || $cantidad === false || $precio === false) {
            $aviso = "<div class='alert alert-warning'>Cantidad o precio inválidos.</div>";
        } else {
            // Stock del depósito y lo que ya está cargado de ese producto en esta venta
            $stmt = $mysqli->prepare("SELECT p.p_descrip, COALESCE(s.cantidad, 0) AS stock,
                                             (SELECT id_tmp FROM tmp_venta WHERE session_id = ? AND id_producto = p.cod_producto LIMIT 1) AS id_tmp,
                                             (SELECT cod_deposito FROM tmp_venta WHERE session_id = ? AND id_producto = p.cod_producto LIMIT 1) AS dep_tmp,
                                             COALESCE((SELECT cantidad_tmp FROM tmp_venta WHERE session_id = ? AND id_producto = p.cod_producto LIMIT 1), 0) AS en_venta
                                      FROM producto p
                                      LEFT JOIN stock s ON s.cod_producto = p.cod_producto AND s.cod_deposito = ?
                                      WHERE p.cod_producto = ?");
            $stmt->bind_param("sssii", $sesion, $sesion, $sesion, $deposito, $id);
            $stmt->execute();
            $info = $stmt->get_result()->fetch_assoc();

            if (!$info) {
                $aviso = "<div class='alert alert-warning'>El producto no existe.</div>";
            } elseif ($info["id_tmp"] !== null && (int) $info["dep_tmp"] !== $deposito) {
                $aviso = "<div class='alert alert-warning'>«" . e($info["p_descrip"]) . "» ya está en la venta desde otro depósito.</div>";
            } elseif ($info["en_venta"] + $cantidad > $info["stock"]) {
                $aviso = "<div class='alert alert-warning'>Stock insuficiente de «" . e($info["p_descrip"]) . "»: disponible "
                       . max(0, $info["stock"] - $info["en_venta"]) . ".</div>";
            } elseif ($info["id_tmp"] !== null) {
                $stmt = $mysqli->prepare("UPDATE tmp_venta SET cantidad_tmp = cantidad_tmp + ?, precio_tmp = ? WHERE id_tmp = ?");
                $stmt->bind_param("iii", $cantidad, $precio, $info["id_tmp"]);
                $stmt->execute();
            } else {
                $stmt = $mysqli->prepare("INSERT INTO tmp_venta (id_producto, cod_deposito, cantidad_tmp, precio_tmp, session_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iiiis", $id, $deposito, $cantidad, $precio, $sesion);
                $stmt->execute();
            }
        }
    } elseif ($accion === "eliminar") {
        $id_tmp = (int) ($_POST["id_tmp"] ?? 0);
        $stmt   = $mysqli->prepare("DELETE FROM tmp_venta WHERE id_tmp = ? AND session_id = ?");   // solo renglones propios
        $stmt->bind_param("is", $id_tmp, $sesion);
        $stmt->execute();
    }
}

// Renglones de la venta de esta sesión
$stmt = $mysqli->prepare("SELECT t.id_tmp, p.cod_producto, p.p_descrip, u.u_descrip, d.descrip AS deposito, t.cantidad_tmp, t.precio_tmp
                          FROM tmp_venta t
                          JOIN producto p ON p.cod_producto = t.id_producto
                          JOIN u_medida u ON u.id_u_medida  = p.id_u_medida
                          JOIN deposito d ON d.cod_deposito = t.cod_deposito
                          WHERE t.session_id = ? ORDER BY t.id_tmp");
$stmt->bind_param("s", $sesion);
$stmt->execute();
$renglones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo $aviso;
if (!$renglones) {
    exit("<p class='text-muted'><i class='fa fa-info-circle'></i> Todavía no hay productos. Use el botón <b>Agregar productos</b>.</p>");
}

$total = 0;
?>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Código</th><th>Producto</th><th>Unidad</th><th>Depósito</th>
            <th class="text-right">Cantidad</th><th class="text-right">Precio (Gs.)</th><th class="text-right">Importe (Gs.)</th><th width="50"></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($renglones as $r):
        $importe = $r["cantidad_tmp"] * $r["precio_tmp"];
        $total  += $importe; ?>
        <tr>
            <td><?= (int) $r["cod_producto"] ?></td>
            <td><?= e($r["p_descrip"]) ?></td>
            <td><?= e($r["u_descrip"]) ?></td>
            <td><?= e($r["deposito"]) ?></td>
            <td class="text-right"><?= (int) $r["cantidad_tmp"] ?></td>
            <td class="text-right"><?= gs($r["precio_tmp"]) ?></td>
            <td class="text-right"><?= gs($importe) ?></td>
            <td class="center">
                <button type="button" class="btn btn-danger btn-xs" title="Quitar" onclick="eliminar(<?= (int) $r["id_tmp"] ?>)">
                    <i class="glyphicon glyphicon-trash"></i>
                </button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="6" class="text-right">TOTAL DE LA VENTA</th>
            <th class="text-right" style="font-size:16px" id="total_venta"><?= gs($total) ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>
