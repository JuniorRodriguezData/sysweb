<?php
/**
 * Proyecto 1 - Proceso del login de acceso.
 * Verifica usuario y contraseña con una sentencia preparada y password_verify().
 */
require_once "config/session.php";
require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit;
}

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if ($username === "" || $password === "") {
    header("Location: index.php?alert=1");
    exit;
}

$stmt = $mysqli->prepare("SELECT id_user, username, name_user, password, permisos_acceso, status
                          FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$data = $stmt->get_result()->fetch_assoc();

// Mismo mensaje si no existe el usuario o si la contraseña es incorrecta (no se revela cuál falló)
if (!$data || !password_verify($password, $data["password"])) {
    header("Location: index.php?alert=1");
    exit;
}

if ($data["status"] !== "activo") {
    header("Location: index.php?alert=4");
    exit;
}

// Nuevo ID de sesión después del login (evita la fijación de sesión)
session_regenerate_id(true);

$_SESSION["id_user"]         = (int) $data["id_user"];
$_SESSION["username"]        = $data["username"];
$_SESSION["name_user"]       = $data["name_user"];
$_SESSION["permisos_acceso"] = $data["permisos_acceso"];

header("Location: main.php?module=start");
exit;
