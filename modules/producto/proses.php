<?php
/**
 * Examen final - Procesa Producto: agregar, modificar y eliminar.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=producto&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("producto")) {
    header("Location: ../../main.php?module=start");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header($volver . "11");
    exit;
}

$act = $_GET["act"] ?? "";

// ---------- Eliminar ----------
if ($act === "delete") {
    $id   = (int) ($_POST["id"] ?? 0);
    $stmt = $mysqli->prepare("SELECT 1 FROM producto WHERE cod_producto = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        header($volver . "6");
        exit;
    }
    try {
        $stmt = $mysqli->prepare("DELETE FROM producto WHERE cod_producto = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header($volver . "3");
    } catch (mysqli_sql_exception $e) {
        if (!es_registro_en_uso($e)) {
            throw $e;
        }
        header($volver . "4");   // tiene stock, compras o ventas
    }
    exit;
}

// ---------- Datos de insert y update ----------
$id      = (int) ($_POST["cod_producto"] ?? 0);
$descrip = preg_replace('/\s+/u', " ", trim($_POST["p_descrip"] ?? ""));
$tipo    = filter_var($_POST["cod_tipo_prod"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$unidad  = filter_var($_POST["id_u_medida"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
$precio  = filter_var($_POST["precio"] ?? "", FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 999999999]]);

if (mb_strlen($descrip) < 2 || mb_strlen($descrip) > 50 || !texto_valido($descrip)
    || $tipo === false || $unidad === false || $precio === false) {
    header($volver . "10");
    exit;
}

// El tipo y la unidad elegidos tienen que existir
$stmt = $mysqli->prepare("SELECT (SELECT COUNT(*) FROM tipo_producto WHERE cod_tipo_prod = ?) + (SELECT COUNT(*) FROM u_medida WHERE id_u_medida = ?)");
$stmt->bind_param("ii", $tipo, $unidad);
$stmt->execute();
if ((int) $stmt->get_result()->fetch_row()[0] !== 2) {
    header($volver . "10");
    exit;
}

// No repetir la descripción
$stmt = $mysqli->prepare("SELECT 1 FROM producto WHERE p_descrip = ? AND cod_producto <> ?");
$stmt->bind_param("si", $descrip, $id);
$stmt->execute();
if ($stmt->get_result()->fetch_row()) {
    header($volver . "5");
    exit;
}

if ($act === "insert") {
    $id   = siguiente_codigo($mysqli, "producto");
    $stmt = $mysqli->prepare("INSERT INTO producto (cod_producto, cod_tipo_prod, id_u_medida, p_descrip, precio) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisi", $id, $tipo, $unidad, $descrip, $precio);
    $stmt->execute();
    header($volver . "1");
} elseif ($act === "update") {
    $stmt = $mysqli->prepare("UPDATE producto SET cod_tipo_prod = ?, id_u_medida = ?, p_descrip = ?, precio = ? WHERE cod_producto = ?");
    $stmt->bind_param("iisii", $tipo, $unidad, $descrip, $precio, $id);
    $stmt->execute();
    header($volver . "2");
} else {
    header($volver . "11");
}
exit;
