<?php
/**
 * Conexión a MySQL con mysqli (igual que en las clases), con dos mejoras:
 *   - los errores se lanzan como excepciones y se registran en logs/error.log;
 *   - el usuario solo ve un mensaje genérico, nunca el detalle técnico.
 */

$server   = "localhost";
$username = "root";
$password = "";
$database = "sysweb";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);   // errores de BD como excepciones

if (!function_exists("sysweb_error")) {
    /** Manejo centralizado de errores no capturados. */
    function sysweb_error(Throwable $e): void
    {
        error_log(sprintf("[%s] %s: %s en %s:%d\n", date("Y-m-d H:i:s"), get_class($e),
            $e->getMessage(), basename($e->getFile()), $e->getLine()), 3, __DIR__ . "/../logs/error.log");
        http_response_code(500);
        echo "<div style='font-family:Arial;margin:40px;color:#a94442'>Ocurrió un error en el sistema. "
           . "Intente nuevamente o contacte al administrador.</div>";
        exit;
    }
    set_exception_handler("sysweb_error");
    ini_set("display_errors", "0");
}

$mysqli = new mysqli($server, $username, $password, $database);
$mysqli->set_charset("utf8mb4");
