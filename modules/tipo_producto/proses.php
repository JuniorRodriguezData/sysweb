<?php
/**
 * Examen final - Procesa Tipo de producto: agregar, modificar y eliminar.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=tipo_producto&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("tipo_producto")) {
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
    $stmt = $mysqli->prepare("SELECT 1 FROM tipo_producto WHERE cod_tipo_prod = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        header($volver . "6");
        exit;
    }
    try {
        $stmt = $mysqli->prepare("DELETE FROM tipo_producto WHERE cod_tipo_prod = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header($volver . "3");
    } catch (mysqli_sql_exception $e) {
        if (!es_registro_en_uso($e)) {
            throw $e;
        }
        header($volver . "4");   // está referenciado por otra tabla
    }
    exit;
}

// ---------- Datos de insert y update ----------
$id          = (int) ($_POST["cod_tipo_prod"] ?? 0);
$descripcion = preg_replace('/\s+/u', " ", trim($_POST["t_p_descrip"] ?? ""));

if (mb_strlen($descripcion) < 2 || mb_strlen($descripcion) > 50 || !texto_valido($descripcion)) {
    header($volver . "10");
    exit;
}

// No repetir la descripción
$stmt = $mysqli->prepare("SELECT 1 FROM tipo_producto WHERE t_p_descrip = ? AND cod_tipo_prod <> ?");
$stmt->bind_param("si", $descripcion, $id);
$stmt->execute();
if ($stmt->get_result()->fetch_row()) {
    header($volver . "5");
    exit;
}

if ($act === "insert") {
    $id   = siguiente_codigo($mysqli, "tipo_producto");
    $stmt = $mysqli->prepare("INSERT INTO tipo_producto (cod_tipo_prod, t_p_descrip) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $descripcion);
    $stmt->execute();
    header($volver . "1");
} elseif ($act === "update") {
    $stmt = $mysqli->prepare("UPDATE tipo_producto SET t_p_descrip = ? WHERE cod_tipo_prod = ?");
    $stmt->bind_param("si", $descripcion, $id);
    $stmt->execute();
    header($volver . "2");
} else {
    header($volver . "11");
}
exit;
