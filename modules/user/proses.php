<?php
/**
 * Proyecto 4 - Procesa los formularios de usuarios: insert, update, on (activar) y off (bloquear).
 * Solo el Super Admin puede usarlo (se verifica acá, no solo ocultando el menú).
 */
require_once "../../config/session.php";
require_once "../../config/funciones.php";
require_once "../../config/database.php";

$volver = "Location: ../../main.php?module=user&alert=";

if (!sesion_activa()) {
    header("Location: ../../index.php?alert=3");
    exit;
}
if (!puede("user")) {
    header("Location: ../../main.php?module=user");   // content.php mostrará "Acceso denegado"
    exit;
}
if ($_SERVER["REQUEST_METHOD"] !== "POST" || !csrf_valido()) {
    header($volver . "11");
    exit;
}

$act = $_GET["act"] ?? "";

// ---------- Activar / bloquear ----------
if ($act === "on" || $act === "off") {
    $id = (int) ($_POST["id"] ?? 0);
    if ($act === "off" && $id === $_SESSION["id_user"]) {
        header($volver . "9");
        exit;
    }
    $status = $act === "on" ? "activo" : "bloqueado";
    $stmt = $mysqli->prepare("UPDATE usuarios SET status = ? WHERE id_user = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();
    header($volver . ($act === "on" ? "3" : "4"));
    exit;
}

// ---------- Datos comunes de insert y update ----------
$id_user  = (int) ($_POST["id_user"] ?? 0);
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";
$name     = trim($_POST["name_user"] ?? "");
$email    = trim($_POST["email"] ?? "");
$telefono = preg_replace("/\D/", "", $_POST["telefono"] ?? "");
$nivel    = $_POST["permisos_acceso"] ?? "";

$valido = preg_match("/^[A-Za-z0-9_.]{3,50}$/", $username) && $name !== ""
       && in_array($nivel, NIVELES, true)
       && ($email === "" || filter_var($email, FILTER_VALIDATE_EMAIL));
if (!$valido || ($act === "insert" && strlen($password) < 6) || ($password !== "" && strlen($password) < 6)) {
    header($volver . "10");
    exit;
}

// El nombre de usuario debe ser único
$stmt = $mysqli->prepare("SELECT 1 FROM usuarios WHERE username = ? AND id_user <> ?");
$stmt->bind_param("si", $username, $id_user);
$stmt->execute();
if ($stmt->get_result()->fetch_row()) {
    header($volver . "8");
    exit;
}

// ---------- Insertar ----------
if ($act === "insert") {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO usuarios (username, password, name_user, email, telefono, permisos_acceso, status)
                              VALUES (?, ?, ?, ?, ?, ?, 'activo')");
    $stmt->bind_param("ssssss", $username, $hash, $name, $email, $telefono, $nivel);
    $stmt->execute();
    header($volver . "1");
    exit;
}

// ---------- Modificar ----------
if ($act === "update") {
    $stmt = $mysqli->prepare("SELECT foto FROM usuarios WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $actual = $stmt->get_result()->fetch_assoc();
    if (!$actual) {
        header($volver . "10");
        exit;
    }

    // Foto nueva (opcional)
    $foto = $actual["foto"];
    if (!empty($_FILES["foto"]["name"])) {
        [$nueva, $error] = guardar_foto($_FILES["foto"], $id_user, __DIR__ . "/../../images/user");
        if ($error) {
            header($volver . $error);
            exit;
        }
        borrar_foto($actual["foto"], __DIR__ . "/../../images/user");
        $foto = $nueva;
    }

    $stmt = $mysqli->prepare("UPDATE usuarios SET username = ?, name_user = ?, email = ?, telefono = ?, foto = ?, permisos_acceso = ?
                              WHERE id_user = ?");
    $stmt->bind_param("ssssssi", $username, $name, $email, $telefono, $foto, $nivel, $id_user);
    $stmt->execute();

    // Contraseña nueva (opcional)
    if ($password !== "") {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("UPDATE usuarios SET password = ? WHERE id_user = ?");
        $stmt->bind_param("si", $hash, $id_user);
        $stmt->execute();
    }

    // Si el administrador se editó a sí mismo, se actualizan los datos de su sesión
    if ($id_user === $_SESSION["id_user"]) {
        $_SESSION["username"]        = $username;
        $_SESSION["name_user"]       = $name;
        $_SESSION["permisos_acceso"] = $nivel;
    }
    header($volver . "2");
    exit;
}

header($volver . "11");
exit;
