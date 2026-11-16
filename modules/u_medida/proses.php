<?php
/**
 * Examen final - Procesa Unidad de medida: agregar, modificar y eliminar.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=u_medida&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("u_medida")) {
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
    $stmt = $mysqli->prepare("SELECT 1 FROM u_medida WHERE id_u_medida = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        header($volver . "6");
        exit;
    }
    try {
        $stmt = $mysqli->prepare("DELETE FROM u_medida WHERE id_u_medida = ?");
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
$id          = (int) ($_POST["id_u_medida"] ?? 0);
$descripcion = preg_replace('/\s+/u', " ", trim($_POST["u_descrip"] ?? ""));

if (mb_strlen($descripcion) < 2 || mb_strlen($descripcion) > 20 || !texto_valido($descripcion)) {
    header($volver . "10");
    exit;
}

// No repetir la descripción
$stmt = $mysqli->prepare("SELECT 1 FROM u_medida WHERE u_descrip = ? AND id_u_medida <> ?");
$stmt->bind_param("si", $descripcion, $id);
$stmt->execute();
if ($stmt->get_result()->fetch_row()) {
    header($volver . "5");
    exit;
}

if ($act === "insert") {
    $id   = siguiente_codigo($mysqli, "u_medida");
    $stmt = $mysqli->prepare("INSERT INTO u_medida (id_u_medida, u_descrip) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $descripcion);
    $stmt->execute();
    header($volver . "1");
} elseif ($act === "update") {
    $stmt = $mysqli->prepare("UPDATE u_medida SET u_descrip = ? WHERE id_u_medida = ?");
    $stmt->bind_param("si", $descripcion, $id);
    $stmt->execute();
    header($volver . "2");
} else {
    header($volver . "11");
}
exit;
