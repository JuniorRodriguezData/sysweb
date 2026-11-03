<?php
/**
 * Proyecto 6 - Procesa los datos de departamentos: insert, update y delete.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=departamento&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("departamento")) {
    header("Location: ../../main.php?module=departamento");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header($volver . "11");
    exit;
}

$act = $_GET["act"] ?? "";

// ---------- Eliminar ----------
if ($act === "delete") {
    $id = (int) ($_POST["id"] ?? 0);
    try {
        $stmt = $mysqli->prepare("DELETE FROM departamento WHERE id_departamento = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header($volver . "3");
    } catch (mysqli_sql_exception $e) {
        if (!es_registro_en_uso($e)) {
            throw $e;
        }
        header($volver . "4");   // tiene ciudades asociadas (clave foránea)
    }
    exit;
}

// ---------- Datos de insert y update ----------
$id          = (int) ($_POST["id_departamento"] ?? 0);
$descripcion = trim($_POST["dep_descripcion"] ?? "");

if ($descripcion === "" || mb_strlen($descripcion) > 35) {
    header($volver . "10");
    exit;
}

// La descripción no se puede repetir
$stmt = $mysqli->prepare("SELECT 1 FROM departamento WHERE dep_descripcion = ? AND id_departamento <> ?");
$stmt->bind_param("si", $descripcion, $id);
$stmt->execute();
if ($stmt->get_result()->fetch_row()) {
    header($volver . "5");
    exit;
}

if ($act === "insert") {
    $id   = siguiente_codigo($mysqli, "departamento");   // se recalcula en el servidor
    $stmt = $mysqli->prepare("INSERT INTO departamento (id_departamento, dep_descripcion) VALUES (?, ?)");
    $stmt->bind_param("is", $id, $descripcion);
    $stmt->execute();
    header($volver . "1");
} elseif ($act === "update") {
    $stmt = $mysqli->prepare("UPDATE departamento SET dep_descripcion = ? WHERE id_departamento = ?");
    $stmt->bind_param("si", $descripcion, $id);
    $stmt->execute();
    header($volver . "2");
} else {
    header($volver . "11");
}
exit;
