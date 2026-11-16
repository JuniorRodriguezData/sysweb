<?php
/**
 * Examen final - Procesa Depósito: agregar, modificar y eliminar.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=deposito&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("deposito")) {
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
    $stmt = $mysqli->prepare("SELECT 1 FROM deposito WHERE cod_deposito = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        header($volver . "6");
        exit;
    }
    try {
        $stmt = $mysqli->prepare("DELETE FROM deposito WHERE cod_deposito = ?");
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
$id          = (int) ($_POST["cod_deposito"] ?? 0);
$descripcion = preg_replace('/\s+/u', " ", trim($_POST["descrip"] ?? ""));

if (mb_strlen($descripcion) < 2 || mb_strlen($descripcion) > 50 || !texto_valido($descripcion)) {
    header($volver . "10");
    exit;
}

// No repetir la descripción
$stmt = $mysqli->prepare("SELECT 1 FROM deposito WHERE descrip = ? AND cod_deposito <> ?");
$stmt->bind_param("si", $descripcion, $id);
$stmt->execute();
if ($stmt->get_result()->fetch_row()) {
    header($volver . "5");
    exit;
}

if ($act === "insert") {
    $id   = siguiente_codigo($mysqli, "deposito");
    $stmt = $mysqli->prepare("INSERT INTO deposito (cod_deposito, descrip) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $descripcion);
    $stmt->execute();
    header($volver . "1");
} elseif ($act === "update") {
    $stmt = $mysqli->prepare("UPDATE deposito SET descrip = ? WHERE cod_deposito = ?");
    $stmt->bind_param("si", $descripcion, $id);
    $stmt->execute();
    header($volver . "2");
} else {
    header($volver . "11");
}
exit;
