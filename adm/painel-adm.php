<?php
session_start();

// Bloqueia acesso se não for adm
if (!isset($_SESSION["usuario_perfil"]) || $_SESSION["usuario_perfil"] !== "adm") {
    header("Location: ../auth/login.php?erro=Acesso restrito");
    exit;
}

$string_de_conexao = "sqlite:../banco.db";
$pdo = new \PDO($string_de_conexao);

$sql = "
    SELECT usuarios.id, usuarios.nome, usuarios.email, perfil.tipo as perfil_tipo
    FROM usuarios
    JOIN perfil ON usuarios.perfil_id = perfil.id
    ORDER BY usuarios.id ASC
";

$result_set_usuarios = $pdo->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel ADM - PlayZone</title>
  <link href="../style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="adm-body">

  <div class="adm-layout">

    <!-- SIDEBAR DO PAINEL -->
    <aside class="adm-sidebar">
      <div class="adm-sidebar-logo">
        <img src="../img/BlogLogo-01-01.svg" alt="PlayZone">
      </div>

      <nav class="adm-nav">
        <p class="adm-nav-label">Gerenciar</p>
        <a href="painel-adm.php" class="adm-nav-item active">
          <i class="bi bi-people-fill"></i> Usuários
        </a>
        <!-- Futuras opções virão aqui -->
      </nav>

      <div class="adm-sidebar-footer">
        <a href="../index.php" class="adm-nav-item">
          <i class="bi bi-house-fill"></i> Voltar ao blog
        </a>
        <a href="../auth/ctrl-logout.php" class="adm-nav-item adm-nav-item--sair">
          <i class="bi bi-box-arrow-right"></i> Sair
        </a>
      </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="adm-main">

      <div class="adm-topbar">
        <h4 class="adm-page-titulo">Usuários</h4>
        <span class="adm-usuario-logado">
          <i class="bi bi-person-circle"></i> <?= $_SESSION["usuario_nome"] ?>
        </span>
      </div>

      <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['sucesso']) ?></div>
      <?php endif; ?>

      <div class="adm-card">
        <table class="table adm-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Nome</th>
              <th>Email</th>
              <th>Perfil</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php while ($uma_linha = $result_set_usuarios->fetch(\PDO::FETCH_ASSOC)): ?>
              <?php
                $id     = $uma_linha["id"];
                $nome   = $uma_linha["nome"];
                $email  = $uma_linha["email"];
                $perfil = $uma_linha["perfil_tipo"];
              ?>
              <tr>
                <td><?= $id ?></td>
                <td><?= $nome ?></td>
                <td><?= $email ?></td>
                <td><span class="adm-badge adm-badge--<?= $perfil ?>"><?= $perfil ?></span></td>
                <td>
                  <?php if ($id !== (int)$_SESSION["usuario_id"]): ?>
                    <a href="ctrl-apagar-usuario.php?id=<?= $id ?>"
                       onclick="return confirm('Deletar <?= $nome ?>?')"
                       class="btn-adm-deletar">
                      <i class="bi bi-trash-fill"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>

    </main>
  </div>

</body>
</html>