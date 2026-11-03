<?php
/**
 * Proyecto 8 - Generación de reportes PDF con HTML2PDF (instalado con Composer).
 * Cada módulo arma sus columnas y filas y llama a generar_reporte().
 */
require_once __DIR__ . "/../vendor/autoload.php";

use Spipu\Html2Pdf\Html2Pdf;
use Spipu\Html2Pdf\Exception\Html2PdfException;

/**
 * Genera y muestra en el navegador un reporte PDF con una tabla.
 *
 * @param string $titulo    Título del reporte.
 * @param array  $columnas  [encabezado => ancho en %].
 * @param array  $filas     Filas de datos (arreglos con los valores en el mismo orden que las columnas).
 * @param string $archivo   Nombre del archivo PDF.
 * @param string $orientacion "P" vertical o "L" horizontal.
 */
function generar_reporte(string $titulo, array $columnas, array $filas, string $archivo, string $orientacion = "P"): void
{
    $logo    = __DIR__ . "/../assets/img/logo-blue.png";
    $usuario = e($_SESSION["name_user"] ?? "");
    $fecha   = date("d/m/Y H:i");

    ob_start(); ?>
    <style>
        table.datos { border-collapse: collapse; width: 100%; }
        table.datos th { background-color: #3c8dbc; color: #ffffff; padding: 5px; font-size: 10pt; border: 1px solid #2d6f96; }
        table.datos td { padding: 4px 5px; font-size: 9.5pt; border: 1px solid #c8d3df; }
        tr.par td { background-color: #f2f6fa; }
        .titulo { font-size: 15pt; color: #0b2a5b; font-weight: bold; }
        .sub    { font-size: 9pt; color: #555555; }
    </style>
    <page backtop="30mm" backbottom="14mm" backleft="10mm" backright="10mm">
        <page_header>
            <table style="width: 100%; border-bottom: 1px solid #3c8dbc;">
                <tr>
                    <td style="width: 25%;"><?php if (is_file($logo)): ?><img src="<?= $logo ?>" style="height: 14mm;"><?php endif; ?></td>
                    <td style="width: 50%; text-align: center;">
                        <span class="titulo"><?= e($titulo) ?></span><br>
                        <span class="sub">SysWeb - Sistema de Compras, Ventas y Stock</span>
                    </td>
                    <td style="width: 25%; text-align: right;" class="sub">
                        Fecha: <?= $fecha ?><br>Usuario: <?= $usuario ?>
                    </td>
                </tr>
            </table>
        </page_header>
        <page_footer>
            <table style="width: 100%; border-top: 1px solid #c8d3df;">
                <tr>
                    <td style="width: 70%;" class="sub">UTIC - Lenguaje de Programación III</td>
                    <td style="width: 30%; text-align: right;" class="sub">Página [[page_cu]] de [[page_nb]]</td>
                </tr>
            </table>
        </page_footer>

        <table class="datos">
            <thead>
                <tr>
                    <?php foreach ($columnas as $encabezado => $ancho): ?>
                        <th style="width: <?= $ancho ?>%;"><?= e($encabezado) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($filas as $i => $fila): ?>
                    <tr class="<?= $i % 2 ? "par" : "" ?>">
                        <?php foreach (array_values($fila) as $valor): ?>
                            <td><?= e((string) $valor) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$filas): ?>
                    <tr><td colspan="<?= count($columnas) ?>" style="text-align: center;">No hay registros.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p class="sub">Total de registros: <?= count($filas) ?></p>
    </page>
    <?php
    $html = ob_get_clean();

    try {
        $pdf = new Html2Pdf($orientacion, "A4", "es", true, "UTF-8");
        $pdf->pdf->SetTitle($titulo);
        $pdf->pdf->SetAuthor("SysWeb");
        $pdf->writeHTML($html);
        $pdf->output($archivo, "I");   // "I": se muestra en el navegador
    } catch (Html2PdfException $e) {
        $pdf->clean();
        throw $e;   // la registra el manejador de errores de config/database.php
    }
}

/** Verifica sesión y permiso antes de generar un reporte (los reportes se abren directo, sin main.php). */
function exigir_permiso_reporte(string $modulo): void
{
    if (!sesion_activa()) {
        header("Location: ../../index.php?alert=3");
        exit;
    }
    if (!puede($modulo)) {
        http_response_code(403);
        exit("No tiene permiso para generar este reporte.");
    }
}
