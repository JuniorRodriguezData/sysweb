<?php
/**
 * Proyecto 2 - Salir del sistema: vacía la sesión, borra la cookie y la destruye.
 */
require_once "config/session.php";

$_SESSION = [];

$p = session_get_cookie_params();
setcookie(session_name(), "", [
    "expires"  => time() - 3600,
    "path"     => $p["path"],
    "secure"   => $p["secure"],
    "httponly" => $p["httponly"],
    "samesite" => $p["samesite"],
]);

session_destroy();

header("Location: index.php?alert=2");
exit;
