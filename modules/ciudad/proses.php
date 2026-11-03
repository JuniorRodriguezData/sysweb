<?php
/**
 * Proyecto 7 - Procesa Ciudad: agregar, modificar y eliminar.
 * Antes de eliminar se verifica que el registro exista y que el usuario tenga permiso.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=ciudad&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("ciudad")) {
    header("Location: ../../main.php?module=ciudad");
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
    $stmt = $mysqli->prepare("SELECT 1 FROM ciudad WHERE cod_ciudad = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        header($volver . "6");
        exit;
    }
    try {
        $stmt = $mysqli->prepare("DELETE FROM ciudad WHERE cod_ciudad = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header($volver . "3");
    } catch (mysqli_sql_exception $e) {
        if (!es_registro_en_uso($e)) {
            throw $e;
        }
        header($volver . "4");   // hay clientes en esta ciudad
    }
    exit;
}

// ---------- Datos de insert y update ----------
$id           = (int) ($_POST["cod_ciudad"] ?? 0);
$descripcion  = trim($_POST["descrip_ciudad"] ?? "");
$departamento = filter_var($_POST["id_departamento"] ?? "", FILTER_VALIDATE_INT);

if ($descripcion === "" || mb_strlen($descripcion) > 25 || $departamento === false) {
    header($volver . "10");
    exit;
}

// El departamento elegido tiene que existir
$stmt = $mysqli->prepare("SELECT 1 FROM departamento WHERE id_departamento = ?");
$stmt->bind_param("i", $departamento);
$stmt->execute();
if (!$stmt->get_result()->fetch_row()) {
    header($volver . "10");
    exit;
}

// No repetir la misma ciudad dentro del mismo departamento
$stmt = $mysqli->prepare("SELECT 1 FROM ciudad WHERE descrip_ciudad = ? AND id_departamento = ? AND cod_ciudad <> ?");
$stmt->bind_param("sii", $descripcion, $departamento, $id);
$stmt->execute();
if ($stmt->get_result()->fetch_row()) {
    header($volver . "5");
    exit;
}

if ($act === "insert") {
    $id   = siguiente_codigo($mysqli, "ciudad");
    $stmt = $mysqli->prepare("INSERT INTO ciudad (cod_ciudad, descrip_ciudad, id_departamento) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $id, $descripcion, $departamento);
    $stmt->execute();
    header($volver . "1");
} elseif ($act === "update") {
    $stmt = $mysqli->prepare("UPDATE ciudad SET descrip_ciudad = ?, id_departamento = ? WHERE cod_ciudad = ?");
    $stmt->bind_param("sii", $descripcion, $departamento, $id);
    $stmt->execute();
    header($volver . "2");
} else {
    header($volver . "11");
}
exit;
