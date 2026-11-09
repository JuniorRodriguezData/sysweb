<?php
/**
 * Proyecto 13 - Procesar compra: agregar (insert) y anular.
 * Todo se hace dentro de una transacción: si algo falla, no queda nada a medias.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=compras&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("compras")) {
    header("Location: ../../main.php?module=compras");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header($volver . "11");
    exit;
}

$act = $_GET["act"] ?? "";

// =====================================================================
// AGREGAR COMPRA
// =====================================================================
if ($act === "insert") {
    $proveedor = filter_var($_POST["cod_proveedor"] ?? "", FILTER_VALIDATE_INT);
    $deposito  = filter_var($_POST["cod_deposito"] ?? "", FILTER_VALIDATE_INT);
    $factura   = trim($_POST["nro_factura"] ?? "");
    $fecha     = $_POST["fecha"] ?? "";
    $f         = DateTime::createFromFormat("!Y-m-d", $fecha);

    $valido = $proveedor !== false && $deposito !== false
           && preg_match("/^[0-9]{3}-[0-9]{3}-[0-9]{1,7}$/", $factura)
           && $f && $f->format("Y-m-d") === $fecha && $fecha <= date("Y-m-d");
    if (!$valido) {
        header($volver . "10");
        exit;
    }

    // Proveedor y depósito deben existir
    $stmt = $mysqli->prepare("SELECT (SELECT COUNT(*) FROM proveedor WHERE cod_proveedor = ?) + (SELECT COUNT(*) FROM deposito WHERE cod_deposito = ?)");
    $stmt->bind_param("ii", $proveedor, $deposito);
    $stmt->execute();
    if ((int) $stmt->get_result()->fetch_row()[0] !== 2) {
        header($volver . "10");
        exit;
    }

    // La misma factura del mismo proveedor no se puede cargar dos veces (salvo que esté anulada)
    $stmt = $mysqli->prepare("SELECT 1 FROM compra WHERE cod_proveedor = ? AND nro_factura = ? AND estado = 'activo'");
    $stmt->bind_param("is", $proveedor, $factura);
    $stmt->execute();
    if ($stmt->get_result()->fetch_row()) {
        header($volver . "6");
        exit;
    }

    $sesion = session_id();
    $mysqli->begin_transaction();
    try {
        // Productos cargados en la tabla temporal de esta sesión
        $stmt = $mysqli->prepare("SELECT id_producto, cantidad_tmp, precio_tmp FROM tmp WHERE session_id = ? FOR UPDATE");
        $stmt->bind_param("s", $sesion);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if (!$items) {
            $mysqli->rollback();
            header($volver . "5");
            exit;
        }

        $total = 0;
        foreach ($items as $it) {
            $total += $it["cantidad_tmp"] * $it["precio_tmp"];
        }

        // Número de compra (se bloquea la tabla para que dos usuarios no tomen el mismo número)
        $cod_compra = (int) $mysqli->query("SELECT COALESCE(MAX(cod_compra), 0) + 1 FROM compra FOR UPDATE")->fetch_row()[0];
        $hora       = date("H:i:s");
        $id_user    = $_SESSION["id_user"];

        // El trigger borrar_temp usa esta variable para vaciar solo el tmp de esta sesión
        $stmt = $mysqli->prepare("SET @tmp_session = ?");
        $stmt->bind_param("s", $sesion);
        $stmt->execute();

        $stmt = $mysqli->prepare("INSERT INTO compra (cod_compra, cod_proveedor, nro_factura, fecha, estado, cod_deposito, hora, total_compra, id_user)
                                  VALUES (?, ?, ?, ?, 'activo', ?, ?, ?, ?)");
        $stmt->bind_param("iissisii", $cod_compra, $proveedor, $factura, $fecha, $deposito, $hora, $total, $id_user);
        $stmt->execute();   // AFTER INSERT -> trigger borrar_temp

        $det   = $mysqli->prepare("INSERT INTO detalle_compra (cod_producto, cod_compra, cod_deposito, precio, cantidad) VALUES (?, ?, ?, ?, ?)");
        $stock = $mysqli->prepare("INSERT INTO stock (cod_deposito, cod_producto, cantidad) VALUES (?, ?, ?)
                                   ON DUPLICATE KEY UPDATE cantidad = cantidad + VALUES(cantidad)");
        foreach ($items as $it) {
            $det->bind_param("iiiii", $it["id_producto"], $cod_compra, $deposito, $it["precio_tmp"], $it["cantidad_tmp"]);
            $det->execute();
            $stock->bind_param("iii", $deposito, $it["id_producto"], $it["cantidad_tmp"]);   // la compra suma al stock
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
// ANULAR COMPRA
// =====================================================================
if ($act === "anular") {
    $id = (int) ($_POST["id"] ?? 0);

    $mysqli->begin_transaction();
    try {
        $stmt = $mysqli->prepare("SELECT estado, cod_deposito FROM compra WHERE cod_compra = ? FOR UPDATE");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $compra = $stmt->get_result()->fetch_assoc();
        if (!$compra || $compra["estado"] !== "activo") {
            $mysqli->rollback();
            header($volver . "4");
            exit;
        }

        // Se verifica que haya stock suficiente para descontar lo que se había comprado
        $stmt = $mysqli->prepare("SELECT d.cod_producto, d.cantidad, COALESCE(s.cantidad, 0) AS disponible
                                  FROM detalle_compra d
                                  LEFT JOIN stock s ON s.cod_deposito = d.cod_deposito AND s.cod_producto = d.cod_producto
                                  WHERE d.cod_compra = ? FOR UPDATE");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($items as $it) {
            if ($it["disponible"] < $it["cantidad"]) {
                $mysqli->rollback();
                header($volver . "3");
                exit;
            }
        }

        $stock = $mysqli->prepare("UPDATE stock SET cantidad = cantidad - ? WHERE cod_deposito = ? AND cod_producto = ?");
        foreach ($items as $it) {
            $stock->bind_param("iii", $it["cantidad"], $compra["cod_deposito"], $it["cod_producto"]);
            $stock->execute();
        }

        // No se borra: se cambia el estado para conservar el historial
        $stmt = $mysqli->prepare("UPDATE compra SET estado = 'anulado' WHERE cod_compra = ?");
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
