<?php
/**
 * Proyecto 1 - Formulario de acceso al sistema.
 */
require_once "config/session.php";

// Si ya inició sesión, va directo al sistema
if (!empty($_SESSION["id_user"])) {
    header("Location: main.php?module=start");
    exit;
}

$alertas = [
    1 => ["danger",  "fa-times-circle", "Error al entrar!", "Usuario o contraseña incorrectos, vuelva a verificar sus datos."],
    2 => ["success", "fa-check-circle", "Éxito!",           "Has salido con éxito."],
    3 => ["warning", "fa-info-circle",  "Atención!",        "Debes ingresar un usuario y contraseña para interactuar dentro del sistema."],
    4 => ["danger",  "fa-ban",          "Usuario bloqueado", "Tu usuario está bloqueado. Consultá con el administrador del sistema."],
];
$alerta = $alertas[(int) ($_GET["alert"] ?? 0)] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <title>Login | SysWeb</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="shortcut icon" href="assets/img/favicon.ico" />
    <link href="assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/plugins/font-awesome-4.6.3/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <link href="assets/css/style.css" rel="stylesheet" type="text/css" />
  </head>
  <body class="login-page" style="background: url('assets/img/fondo.jpg') center / cover no-repeat fixed;">
    <br>
    <div class="login-box">
      <div style="color:#3c8dbc" class="login-logo">
        <img style="margin-top:-15px" src="assets/img/favicon.ico" alt="Logo" height="50"> <b>SysWeb</b>
      </div>

      <?php if ($alerta): ?>
        <div class="alert alert-<?= $alerta[0] ?> alert-dismissable">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
          <h4><i class="icon fa <?= $alerta[1] ?>"></i> <?= $alerta[2] ?></h4>
          <?= $alerta[3] ?>
        </div>
      <?php endif; ?>

      <div class="login-box-body">
        <p class="login-box-msg"><i class="fa fa-user icon-title"></i> Por favor inicie sesión</p>
        <br/>
        <form action="login-check.php" method="POST">
          <div class="form-group has-feedback">
            <input type="text" class="form-control" name="username" placeholder="Usuario" autocomplete="off" maxlength="50" required autofocus />
            <span class="glyphicon glyphicon-user form-control-feedback"></span>
          </div>
          <div class="form-group has-feedback">
            <input type="password" class="form-control" name="password" placeholder="Contraseña" required />
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
          </div>
          <br/>
          <div class="row">
            <div class="col-xs-12">
              <input type="submit" class="btn btn-primary btn-lg btn-block btn-flat" name="login" value="Ingresar" />
            </div>
          </div>
        </form>
      </div>
    </div>

    <script src="assets/plugins/jQuery/jQuery-2.1.3.min.js"></script>
    <script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
  </body>
</html>
