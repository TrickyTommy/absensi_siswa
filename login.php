<!-- login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login | SMK BOEM</title>
  <link rel="stylesheet" href="assets/AdminLTE/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="assets/AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <link rel="stylesheet" href="assets/AdminLTE/dist/css/adminlte.min.css">
</head>
<body class="hold-transition login-page">

<div class="login-box">
  <div class="login-logo">
    <b>LOGIN
  </div>

  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg">Login untuk masuk ke sistem</p>
      <form action="auth.php" method="post">
  <div class="input-group mb-3">
    <input type="email" name="email" class="form-control" placeholder="Email" required>
    <div class="input-group-append">
      <div class="input-group-text"><span class="fas fa-envelope"></span></div>
    </div>
  </div>

  <div class="input-group mb-3">
    <input type="password" name="password" class="form-control" placeholder="Password" required>
    <div class="input-group-append">
      <div class="input-group-text"><span class="fas fa-lock"></span></div>
    </div>
  </div>

  <div class="row">
    <div class="col-4">
      <button type="submit" class="btn btn-primary btn-block">Login</button>
    </div>
  </div>
</form>

    </div>
  </div>
</div>

<script src="assets/AdminLTE/plugins/jquery/jquery.min.js"></script>
<script src="assets/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="assets/AdminLTE/dist/js/adminlte.min.js"></script>
</body>
</html>
