<?php
/**
 * Proyecto 9 - Procesa Clientes: agregar, modificar y eliminar.
 * Valida los datos personales, verifica permisos y usa consultas parametrizadas.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=clientes&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("clientes")) {
    header("Location: ../../main.php?module=clientes");
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
    $stmt = $mysqli->prepare("SELECT 1 FROM clientes WHERE id_cliente = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if (!$stmt->get_result()->fetch_row()) {
        header($volver . "6");
        exit;
    }
    try {
        $stmt = $mysqli->prepare("DELETE FROM clientes WHERE id_cliente = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header($volver . "3");
    } catch (mysqli_sql_exception $e) {
        if (!es_registro_en_uso($e)) {
            throw $e;
        }
        header($volver . "4");   // tiene ventas: se conserva para no romper el historial
    }
    exit;
}

// ---------- Datos de insert y update ----------
$id        = (int) ($_POST["id_cliente"] ?? 0);
$ci_ruc    = trim($_POST["ci_ruc"] ?? "");
$nombre    = trim($_POST["cli_nombre"] ?? "");
$apellido  = trim($_POST["cli_apellido"] ?? "");
$direccion = trim($_POST["cli_direccion"] ?? "");
$telefono  = preg_replace("/\D/", "", $_POST["cli_telefono"] ?? "");
$ciudad    = filter_var($_POST["cod_ciudad"] ?? "", FILTER_VALIDATE_INT);

$valido = preg_match("/^[0-9]{1,8}(-[0-9])?$/", $ci_ruc)
       && $nombre !== "" && mb_strlen($nombre) <= 30
       && $apellido !== "" && mb_strlen($apellido) <= 50
       && mb_strlen($direccion) <= 50 && strlen($telefono) <= 20
       && $ciudad !== false;
if (!$valido) {
    header($volver . "10");
    exit;
}

// La ciudad debe existir
$stmt = $mysqli->prepare("SELECT 1 FROM ciudad WHERE cod_ciudad = ?");
$stmt->bind_param("i", $ciudad);
$stmt->execute();
if (!$stmt->get_result()->fetch_row()) {
    header($volver . "10");
    exit;
}

// C.I. / RUC único
$stmt = $mysqli->prepare("SELECT 1 FROM clientes WHERE ci_ruc = ? AND id_cliente <> ?");
$stmt->bind_param("si", $ci_ruc, $id);
$stmt->execute();
if ($stmt->get_result()->fetch_row()) {
    header($volver . "5");
    exit;
}

$direccion = $direccion === "" ? null : $direccion;
$telefono  = $telefono === "" ? null : $telefono;

if ($act === "insert") {
    $id   = siguiente_codigo($mysqli, "clientes");
    $stmt = $mysqli->prepare("INSERT INTO clientes (id_cliente, ci_ruc, cli_nombre, cli_apellido, cli_direccion, cli_telefono, cod_ciudad)
                              VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssi", $id, $ci_ruc, $nombre, $apellido, $direccion, $telefono, $ciudad);
    $stmt->execute();
    header($volver . "1");
} elseif ($act === "update") {
    $stmt = $mysqli->prepare("UPDATE clientes SET ci_ruc = ?, cli_nombre = ?, cli_apellido = ?, cli_direccion = ?,
                              cli_telefono = ?, cod_ciudad = ? WHERE id_cliente = ?");
    $stmt->bind_param("sssssii", $ci_ruc, $nombre, $apellido, $direccion, $telefono, $ciudad, $id);
    $stmt->execute();
    header($volver . "2");
} else {
    header($volver . "11");
}
exit;
