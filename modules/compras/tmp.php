<?php
/**
 * Proyecto 13 - Tabla temporal de la compra (tabla tmp, por sesión).
 *   POST accion=agregar  (id, cantidad, precio)  agrega o suma un producto.
 *   POST accion=eliminar (id_tmp)                quita un renglón.
 *   GET                                          solo dibuja la tabla.
 * Devuelve el HTML de la tabla con el importe de cada renglón (cantidad x precio) y el total.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

if (!sesion_activa() || !puede("form_compras")) {
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
        $cantidad = filter_var($_POST["cantidad"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 100000]]);
        $precio   = filter_var($_POST["precio"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 0, "max_range" => 999999999]]);

        $stmt = $mysqli->prepare("SELECT 1 FROM producto WHERE cod_producto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $existe_producto = (bool) $stmt->get_result()->fetch_row();   // se lee siempre: deja libre la conexión

        if ($id === false || $cantidad === false || $precio === false || !$existe_producto) {
            $aviso = "<div class='alert alert-warning'>Cantidad o precio inválidos.</div>";
        } else {
            // Si el producto ya estaba en la compra se suma la cantidad y se toma el último precio
            $stmt = $mysqli->prepare("SELECT id_tmp FROM tmp WHERE session_id = ? AND id_producto = ?");
            $stmt->bind_param("si", $sesion, $id);
            $stmt->execute();
            $existente = $stmt->get_result()->fetch_row();
            if ($existente) {
                $stmt = $mysqli->prepare("UPDATE tmp SET cantidad_tmp = cantidad_tmp + ?, precio_tmp = ? WHERE id_tmp = ?");
                $stmt->bind_param("iii", $cantidad, $precio, $existente[0]);
            } else {
                $stmt = $mysqli->prepare("INSERT INTO tmp (id_producto, cantidad_tmp, precio_tmp, session_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiis", $id, $cantidad, $precio, $sesion);
            }
            $stmt->execute();
        }
    } elseif ($accion === "eliminar") {
        $id_tmp = (int) ($_POST["id_tmp"] ?? 0);
        $stmt   = $mysqli->prepare("DELETE FROM tmp WHERE id_tmp = ? AND session_id = ?");   // solo renglones propios
        $stmt->bind_param("is", $id_tmp, $sesion);
        $stmt->execute();
    }
}

// Renglones de la compra de esta sesión
$stmt = $mysqli->prepare("SELECT t.id_tmp, p.cod_producto, p.p_descrip, u.u_descrip, t.cantidad_tmp, t.precio_tmp
                          FROM tmp t
                          JOIN producto p ON p.cod_producto = t.id_producto
                          JOIN u_medida u ON u.id_u_medida  = p.id_u_medida
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
            <th>Código</th><th>Producto</th><th>Unidad</th>
            <th class="text-right">Cantidad</th><th class="text-right">Precio (Gs.)</th><th class="text-right">Importe (Gs.)</th><th width="50"></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($renglones as $r):
        $importe = $r["cantidad_tmp"] * $r["precio_tmp"];   // cálculo: cantidad x precio
        $total  += $importe; ?>
        <tr>
            <td><?= (int) $r["cod_producto"] ?></td>
            <td><?= e($r["p_descrip"]) ?></td>
            <td><?= e($r["u_descrip"]) ?></td>
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
            <th colspan="5" class="text-right">TOTAL DE LA COMPRA</th>
            <th class="text-right" style="font-size:16px" id="total_compra"><?= gs($total) ?></th>
            <th></th>
        </tr>
    </tfoot>
</table>
