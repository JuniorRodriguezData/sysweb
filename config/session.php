<?php
/**
 * Inicio de sesión seguro (Unidad IV): cookie httponly, samesite y modo estricto.
 * Se incluye en lugar de llamar directamente a session_start().
 */
if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.use_strict_mode", "1");
    ini_set("session.use_only_cookies", "1");
    session_name("SYSWEB");
    session_set_cookie_params([
        "lifetime" => 0,
        "path"     => "/",
        "secure"   => !empty($_SERVER["HTTPS"]),
        "httponly" => true,
        "samesite" => "Lax",
    ]);
    session_start();
}
