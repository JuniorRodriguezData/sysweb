<?php
/**
 * Proyecto 5 - Procesa la modificación del propio perfil (nombre, email, teléfono y foto).
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=profile&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header($volver . "11");
    exit;
}

$id_user  = $_SESSION["id_user"];
$name     = trim($_POST["name_user"] ?? "");
$email    = trim($_POST["email"] ?? "");
$telefono = preg_replace("/\D/", "", $_POST["telefono"] ?? "");

if ($name === "" || ($email !== "" && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
    header($volver . "10");
    exit;
}

$stmt = $mysqli->prepare("SELECT foto FROM usuarios WHERE id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$foto = $stmt->get_result()->fetch_assoc()["foto"];

// Foto nueva (opcional)
if (!empty($_FILES["foto"]["name"])) {
    [$nueva, $error] = guardar_foto($_FILES["foto"], $id_user, __DIR__ . "/../../images/user");
    if ($error) {
        header($volver . $error);
        exit;
    }
    borrar_foto($foto, __DIR__ . "/../../images/user");
    $foto = $nueva;
}

$stmt = $mysqli->prepare("UPDATE usuarios SET name_user = ?, email = ?, telefono = ?, foto = ? WHERE id_user = ?");
$stmt->bind_param("ssssi", $name, $email, $telefono, $foto, $id_user);
$stmt->execute();

$_SESSION["name_user"] = $name;   // el menú y el saludo muestran el nombre nuevo
header($volver . "1");
exit;
