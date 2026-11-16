<?php
/**
 * Examen final - Procesar venta: agregar (insert) y anular.
 * Todo dentro de una transacción: si algo falla no queda nada a medias.
 *   - insert: guarda cabecera y detalle y DESCUENTA las cantidades del stock.
 *   - anular: DEVUELVE las cantidades al stock y deja la cabecera con estado 'anulado'.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=ventas&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("ventas")) {
    header("Location: ../../main.php?module=start");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header($volver . "11");
    exit;
}

$act = $_GET["act"] ?? "";

// =====================================================================
// AGREGAR VENTA
// =====================================================================
if ($act === "insert") {
    $cliente = filter_var($_POST["id_cliente"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
    $factura = filter_var($_POST["nro_factura"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 99999999]]);
    $fecha   = $_POST["fecha"] ?? "";
    $f       = DateTime::createFromFormat("!Y-m-d", $fecha);

    if ($cliente === false || $factura === false || !$f || $f->format("Y-m-d") !== $fecha || $fecha > date("Y-m-d")) {
        header($volver . "10");
        exit;
    }

    $stmt = $mysqli->prepare("SELECT 1 FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $cliente);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        header($volver . "10");
        exit;
    }

    // El número de factura no se repite entre ventas activas
    $stmt = $mysqli->prepare("SELECT 1 FROM venta WHERE nro_factura = ? AND estado = 'activo'");
    $stmt->bind_param("i", $factura);
    $stmt->execute();
    if ($stmt->get_result()->fetch_row()) {
        header($volver . "6");
        exit;
    }

    $sesion = session_id();
    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("SELECT id_producto, cod_deposito, cantidad_tmp, precio_tmp FROM tmp_venta WHERE session_id = ? FOR UPDATE");
        $stmt->bind_param("s", $sesion);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if (!$items) {
            $mysqli->rollback();
            header($volver . "5");
            exit;
        }

        // Se vuelve a verificar el stock con las filas bloqueadas (otro usuario pudo vender mientras tanto)
        $leer  = $mysqli->prepare("SELECT cantidad FROM stock WHERE cod_deposito = ? AND cod_producto = ? FOR UPDATE");
        $total = 0;
        foreach ($items as $it) {
            $leer->bind_param("ii", $it["cod_deposito"], $it["id_producto"]);
            $leer->execute();
            $fila = $leer->get_result()->fetch_row();
            if (!$fila || $fila[0] < $it["cantidad_tmp"]) {
                $mysqli->rollback();
                header($volver . "3");
                exit;
            }
            $total += $it["cantidad_tmp"] * $it["precio_tmp"];
        }

        $cod_venta = (int) $mysqli->query("SELECT COALESCE(MAX(cod_venta), 0) + 1 FROM venta FOR UPDATE")->fetch_row()[0];
        $hora      = date("H:i:s");
        $id_user   = $_SESSION["id_user"];

        // El trigger borrar_temp_venta usa esta variable para vaciar solo el tmp_venta de esta sesión
        $stmt = $mysqli->prepare("SET @tmp_session = ?");
        $stmt->bind_param("s", $sesion);
        $stmt->execute();

        $stmt = $mysqli->prepare("INSERT INTO venta (cod_venta, id_cliente, fecha, total_venta, estado, hora, nro_factura, id_user)
                                  VALUES (?, ?, ?, ?, 'activo', ?, ?, ?)");
        $stmt->bind_param("iisisii", $cod_venta, $cliente, $fecha, $total, $hora, $factura, $id_user);
        $stmt->execute();   // AFTER INSERT -> trigger borrar_temp_venta

        $det   = $mysqli->prepare("INSERT INTO det_venta (cod_producto, cod_venta, cod_deposito, det_precio_unit, det_cantidad) VALUES (?, ?, ?, ?, ?)");
        $stock = $mysqli->prepare("UPDATE stock SET cantidad = cantidad - ? WHERE cod_deposito = ? AND cod_producto = ?");
        foreach ($items as $it) {
            $det->bind_param("iiiii", $it["id_producto"], $cod_venta, $it["cod_deposito"], $it["precio_tmp"], $it["cantidad_tmp"]);
            $det->execute();
            $stock->bind_param("iii", $it["cantidad_tmp"], $it["cod_deposito"], $it["id_producto"]);   // la venta resta del stock
            $stock->execute();
        }

        $mysqli->commit();
        header($volver . "1");
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;   // la registra el manejador de errores (logs/error.log)
    }
    exit;
}

// =====================================================================
// ANULAR VENTA: devuelve las cantidades al stock y anula la cabecera
// =====================================================================
if ($act === "anular") {
    $id = (int) ($_POST["id"] ?? 0);

    $mysqli->begin_transaction();
    try {
        // FOR UPDATE: si dos usuarios anulan a la vez, el segundo espera y luego ve 'anulado'
        $stmt = $mysqli->prepare("SELECT estado FROM venta WHERE cod_venta = ? FOR UPDATE");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $venta = $stmt->get_result()->fetch_assoc();
        if (!$venta || $venta["estado"] !== "activo") {
            $mysqli->rollback();
            header($volver . "4");
            exit;
        }

        $stmt = $mysqli->prepare("SELECT cod_producto, cod_deposito, det_cantidad FROM det_venta WHERE cod_venta = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Se devuelve cada cantidad al depósito del que salió
        $stock = $mysqli->prepare("INSERT INTO stock (cod_deposito, cod_producto, cantidad) VALUES (?, ?, ?)
                                   ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)");
        foreach ($items as $it) {
            $stock->bind_param("iii", $it["cod_deposito"], $it["cod_producto"], $it["det_cantidad"]);
            $stock->execute();
        }

        // No se borra: se cambia el estado para conservar el historial
        $stmt = $mysqli->prepare("UPDATE venta SET estado = 'anulado' WHERE cod_venta = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();

        $mysqli->commit();
        header($volver . "2");
    } catch (Throwable $e) {
        $mysqli->rollback();
        throw $e;
    }
    exit;
}

header($volver . "11");
exit;
