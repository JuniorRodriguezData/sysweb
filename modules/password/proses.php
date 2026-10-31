<?php
/**
 * Proyecto 3 - Procesa el cambio de contraseña del usuario que inició sesión.
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!isset($_POST["Guardar"])) {
    header("Location: ../../main.php?module=password");
    exit;
}
if (!csrf_valido()) {
    header("Location: ../../main.php?module=password&alert=5");
    exit;
}

$volver   = "Location: ../../main.php?module=password&alert=";
$old_pass = $_POST["old_pass"] ?? "";
$new_pass = $_POST["new_pass"] ?? "";
$retype   = $_POST["retype_pass"] ?? "";
$id_user  = $_SESSION["id_user"];

$stmt = $mysqli->prepare("SELECT password FROM usuarios WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

if (!$data || !password_verify($old_pass, $data["password"])) {
    header($volver . "1");
} elseif ($new_pass !== $retype) {
    header($volver . "2");
} elseif (strlen($new_pass) < 6) {
    header($volver . "4");
} else {
    $hash = password_hash($new_pass, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE id_user = ?");
    $stmt->bind_param("si", $hash, $id_user);
    $stmt->execute();
    header($volver . "3");
}
exit;
