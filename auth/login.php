<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - PlayZone</title>
  <link href="../style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="auth-body">

  <div class="auth-wrapper">
    <div class="auth-card">

      <div class="auth-logo">
        <img src="/img/BlogLogo-01-01.svg" alt="PlayZone">
      </div>

      <h4 class="auth-titulo">Entrar</h4>
      <p class="auth-subtitulo">Bem-vindo de volta!</p>

      <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
      <?php endif; ?>

      <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['sucesso']) ?></div>
      <?php endif; ?>

      <form action="ctrl-login.php" method="POST">

        <div class="mb-3">
          <label class="form-label">Usuário ou email</label>
          <input type="text" name="login" class="form-control auth-input" placeholder="Usuário ou email" required>
        </div>

        <div class="mb-4">
          <label class="form-label">Senha</label>
          <input type="password" name="senha" class="form-control auth-input" placeholder="Sua senha" required>
        </div>

        <button type="submit" class="btn btn-ver-mais w-100">Entrar</button>

      </form>

      <p class="auth-link">Não tem conta? <a href="cadastro.php">Cadastre-se</a></p>

    </div>
  </div>

</body>
</html>