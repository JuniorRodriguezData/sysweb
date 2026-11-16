<?php
/**
 * Examen final - Procesa Proveedor: agregar, modificar y eliminar.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=proveedor&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("proveedor")) {
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
    $stmt = $mysqli->prepare("SELECT 1 FROM proveedor WHERE cod_proveedor = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        header($volver . "6");
        exit;
    }
    try {
        $stmt = $mysqli->prepare("DELETE FROM proveedor WHERE cod_proveedor = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header($volver . "3");
    } catch (mysqli_sql_exception $e) {
        if (!es_registro_en_uso($e)) {
            throw $e;
        }
        header($volver . "4");   // tiene compras
    }
    exit;
}

// ---------- Datos de insert y update ----------
$id        = (int) ($_POST["cod_proveedor"] ?? 0);
$razon     = preg_replace('/\s+/u', " ", trim($_POST["razon_social"] ?? ""));
$ruc       = trim($_POST["ruc"] ?? "");
$direccion = preg_replace('/\s+/u', " ", trim($_POST["direccion"] ?? ""));
$telefono  = trim($_POST["telefono"] ?? "");

$valido = mb_strlen($razon) >= 3 && mb_strlen($razon) <= 75 && texto_valido($razon)
       && preg_match('/^[0-9]{5,8}(-[0-9])?$/', $ruc)
       && mb_strlen($direccion) <= 50 && ($direccion === "" || texto_valido($direccion))
       && preg_match('/^[0-9]{6,15}$/', $telefono);
if (!$valido) {
    header($volver . "10");
    exit;
}
$direccion = $direccion === "" ? null : $direccion;

// RUC y razón social no se repiten
$stmt = $mysqli->prepare("SELECT 1 FROM proveedor WHERE (ruc = ? OR razon_social = ?) AND cod_proveedor <> ?");
$stmt->bind_param("ssi", $ruc, $razon, $id);
$stmt->execute();
if ($stmt->get_result()->fetch_row()) {
    header($volver . "5");
    exit;
}

if ($act === "insert") {
    $id   = siguiente_codigo($mysqli, "proveedor");
    $stmt = $mysqli->prepare("INSERT INTO proveedor (cod_proveedor, razon_social, ruc, direccion, telefono) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $id, $razon, $ruc, $direccion, $telefono);
    $stmt->execute();
    header($volver . "1");
} elseif ($act === "update") {
    $stmt = $mysqli->prepare("UPDATE proveedor SET razon_social = ?, ruc = ?, direccion = ?, telefono = ? WHERE cod_proveedor = ?");
    $stmt->bind_param("ssssi", $razon, $ruc, $direccion, $telefono, $id);
    $stmt->execute();
    header($volver . "2");
} else {
    header($volver . "11");
}
exit;
