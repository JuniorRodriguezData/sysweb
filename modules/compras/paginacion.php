<?php
/**
 * Proyecto 12 - Paginación: arma los botones de página que llaman a load(n) en JavaScript.
 *
 * @param int $pagina     Página actual.
 * @param int $total      Cantidad total de páginas.
 * @param int $adyacentes Cuántos números se muestran a cada lado de la página actual.
 */
function paginate(int $pagina, int $total, int $adyacentes = 2): string
{
    if ($total <= 1) {
        return "";
    }
    $boton = function (int $n, string $texto, bool $activo = false, bool $deshabilitado = false): string {
        $clase = $activo ? ' class="active"' : ($deshabilitado ? ' class="disabled"' : "");
        $click = $deshabilitado || $activo ? "" : ' onclick="load(' . $n . ')"';
        return "<li$clase><a href=\"javascript:void(0);\"$click>$texto</a></li>";
    };

    $html  = '<ul class="pagination pagination-sm" style="margin:5px 0">';
    $html .= $boton($pagina - 1, "&lsaquo; Anterior", false, $pagina <= 1);

    $desde = max(1, $pagina - $adyacentes);
    $hasta = min($total, $pagina + $adyacentes);
    if ($desde > 1) {
        $html .= $boton(1, "1");
        if ($desde > 2) {
            $html .= '<li class="disabled"><a>…</a></li>';
        }
    }
    for ($i = $desde; $i <= $hasta; $i++) {
        $html .= $boton($i, (string) $i, $i === $pagina);
    }
    if ($hasta < $total) {
        if ($hasta < $total - 1) {
            $html .= '<li class="disabled"><a>…</a></li>';
        }
        $html .= $boton($total, (string) $total);
    }

    $html .= $boton($pagina + 1, "Siguiente &rsaquo;", false, $pagina >= $total);
    return $html . "</ul>";
}
