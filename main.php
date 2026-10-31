<?php
/**
 * Proyecto 2 - Plantilla principal: menú superior, menú lateral, contenido y footer.
 */
require_once "config/session.php";
require_once "config/funciones.php";
require_once "config/database.php";

// Sin sesión no se puede entrar al sistema
if (!sesion_activa()) {
    header("Location: index.php?alert=3");
    exit;
}

$module = $_GET["module"] ?? "start";
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>SysWeb - Sistema de Compras, Ventas y Stock</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="description" content="Sistema Web de Compras, Ventas y Stock - UTIC">
    <meta name="author" content="Junior Rodriguez" />
    <link rel="shortcut icon" href="assets/img/favicon.ico" />

    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/font-awesome-4.6.3/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/datepicker/datepicker.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/chosen/css/chosen.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/skins/skin-blue.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
    <style>
      /* style.css aclara los fondos verde/rojo; en las etiquetas de estado se necesita contraste */
      .label.label-success { background-color: #00a65a !important; color: #fff; }
      .label.label-danger  { background-color: #dd4b39 !important; color: #fff; }
    </style>

    <script>
      // Permite escribir solo ciertos caracteres en un campo (ej.: teléfono solo números)
      function goodchars(e, goods, field) {
        var key = window.event ? window.event.keyCode : (e ? e.which : null);
        if (key == null) return true;
        var keychar = String.fromCharCode(key).toLowerCase();
        if (goods.toLowerCase().indexOf(keychar) != -1) return true;
        if (key == 0 || key == 8 || key == 9 || key == 27) return true;
        if (key == 13) {
          var i;
          for (i = 0; i < field.form.elements.length; i++) if (field == field.form.elements[i]) break;
          field.form.elements[(i + 1) % field.form.elements.length].focus();
          return false;
        }
        return false;
      }
    </script>
  </head>
  <body class="skin-blue fixed">
    <div class="wrapper">

      <header class="main-header">
        <a href="?module=start" class="logo">
          <img src="assets/img/logo_2019.png" width="70%" alt="Logo">
        </a>
        <nav class="navbar navbar-static-top" role="navigation">
          <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Mostrar/ocultar menú</span>
          </a>
          <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
              <?php include "top-menu.php"; ?>
            </ul>
          </div>
        </nav>
      </header>

      <aside class="main-sidebar">
        <section class="sidebar">
          <?php include "sidebar-menu.php"; ?>
        </section>
      </aside>

      <div class="content-wrapper">
        <?php include "content.php"; ?>

        <!-- Confirmación para salir del sistema -->
        <div class="modal fade" id="logout">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-sign-out"></i> Salir</h4>
              </div>
              <div class="modal-body">
                <p>¿Seguro que quieres salir del sistema?</p>
              </div>
              <div class="modal-footer">
                <a class="btn btn-danger" href="logout.php">Sí, salir</a>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <footer class="main-footer">
        <div class="pull-right hidden-xs">Lenguaje de Programación III</div>
        <strong>Copyright &copy; <?= date("Y") ?> - <a href="?module=start">UTIC - Proyecto Web SysWeb</a></strong>
        · Desarrollado por Junior Rodriguez
      </footer>
    </div>

    <script src="assets/plugins/jQuery/jQuery-2.1.3.min.js"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
    <script src="assets/plugins/datepicker/bootstrap-datepicker.min.js" type="text/javascript"></script>
    <script src="assets/plugins/chosen/js/chosen.jquery.min.js"></script>
    <script src="assets/plugins/datatables/jquery.dataTables.js" type="text/javascript"></script>
    <script src="assets/plugins/datatables/dataTables.bootstrap.js" type="text/javascript"></script>
    <script src="assets/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
    <script src="assets/plugins/fastclick/fastclick.min.js"></script>
    <script src="assets/js/app.min.js" type="text/javascript"></script>
    <script>
      var idioma_espanol = {
        "sProcessing": "Procesando...", "sLengthMenu": "Mostrar _MENU_ registros",
        "sZeroRecords": "No se encontraron resultados", "sEmptyTable": "Ningún dato disponible en esta tabla",
        "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
        "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "sInfoFiltered": "(filtrado de un total de _MAX_ registros)", "sSearch": "Buscar:",
        "sLoadingRecords": "Cargando...",
        "oPaginate": { "sFirst": "Primero", "sLast": "Último", "sNext": "Siguiente", "sPrevious": "Anterior" }
      };
      $(function () {
        $(".date-picker").datepicker({ autoclose: true, todayHighlight: true });
        $(".chosen-select").chosen({ allow_single_deselect: true });
        $("#dataTables1").dataTable({ "language": idioma_espanol });
      });
    </script>
  </body>
</html>
