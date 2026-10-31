<?php
/**
 * Funciones comunes del sistema.
 */

/** Escapa un texto para mostrarlo en HTML (evita XSS). */
function e(?string $texto): string
{
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
}

/** ¿Hay un usuario con sesión iniciada? */
function sesion_activa(): bool
{
    return !empty($_SESSION["id_user"]) && !empty($_SESSION["permisos_acceso"]);
}

/**
 * Módulos y niveles que pueden usarlos. El menú se arma con esto y content.php
 * lo vuelve a verificar del lado del servidor (ocultar el menú no alcanza).
 */
const PERMISOS_MODULOS = [
    "start"        => ["Super Admin", "Compras", "Ventas"],
    "password"     => ["Super Admin", "Compras", "Ventas"],
    "profile"      => ["Super Admin", "Compras", "Ventas"],
    "form_profile" => ["Super Admin", "Compras", "Ventas"],
    "user"         => ["Super Admin"],
    "form_user"    => ["Super Admin"],
    "compras"      => ["Super Admin", "Compras"],
    "ventas"       => ["Super Admin", "Ventas"],
    "stock"        => ["Super Admin", "Compras", "Ventas"],
];

/** ¿El usuario actual puede entrar al módulo? */
function puede(string $modulo): bool
{
    return in_array($_SESSION["permisos_acceso"] ?? "", PERMISOS_MODULOS[$modulo] ?? [], true);
}

/** Token CSRF de la sesión: protege los formularios que modifican datos. */
function csrf_token(): string
{
    if (empty($_SESSION["csrf"])) {
        $_SESSION["csrf"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf"];
}

/** Campo oculto con el token, para poner dentro de cada formulario POST. */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

/** ¿El token enviado coincide con el de la sesión? */
function csrf_valido(): bool
{
    return isset($_POST["csrf"], $_SESSION["csrf"]) && hash_equals($_SESSION["csrf"], $_POST["csrf"]);
}

/** Niveles de usuario del sistema. */
const NIVELES = ["Super Admin", "Compras", "Ventas"];

/** Ruta pública de la foto de un usuario (o la imagen por defecto). */
function foto_usuario(?string $foto): string
{
    return "images/user/" . ($foto !== null && $foto !== "" ? rawurlencode($foto) : "user-default.png");
}

/**
 * Valida y guarda una foto subida. Devuelve [nombre_archivo, null] o [null, código_alerta].
 * Códigos (los mismos de la cátedra): 5 = error al subir, 6 = más de 1 MB, 7 = tipo no permitido.
 */
function guardar_foto(array $archivo, int $id_user, string $carpeta): array
{
    if ($archivo["error"] !== UPLOAD_ERR_OK) {
        return [null, $archivo["error"] === UPLOAD_ERR_INI_SIZE || $archivo["error"] === UPLOAD_ERR_FORM_SIZE ? 6 : 5];
    }
    if ($archivo["size"] > 1000000) {
        return [null, 6];
    }
    // Se verifica el contenido real del archivo, no solo la extensión que manda el navegador
    $tipos = [IMAGETYPE_JPEG => "jpg", IMAGETYPE_PNG => "png"];
    $info  = @getimagesize($archivo["tmp_name"]);
    if ($info === false || !isset($tipos[$info[2]])) {
        return [null, 7];
    }
    // Nombre único: evita sobrescribir fotos de otros usuarios o archivos del sistema
    $nombre = sprintf("user_%d_%s.%s", $id_user, bin2hex(random_bytes(4)), $tipos[$info[2]]);
    if (!move_uploaded_file($archivo["tmp_name"], rtrim($carpeta, "/") . "/" . $nombre)) {
        return [null, 5];
    }
    return [$nombre, null];
}

/** Borra la foto anterior de un usuario (nunca la imagen por defecto). */
function borrar_foto(?string $foto, string $carpeta): void
{
    if ($foto && basename($foto) === $foto && $foto !== "user-default.png") {
        $ruta = rtrim($carpeta, "/") . "/" . $foto;
        if (is_file($ruta)) {
            unlink($ruta);
        }
    }
}
